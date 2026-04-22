<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\CampaignMailer;
use App\Models\Campaign;
use App\Models\NewsletterSubscription;
use App\Services\Audit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoie une campagne aux abonnés confirmés d'une newsletter.
 * Chunk 100 destinataires par itération pour rester mémoire-safe.
 */
class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 min

    public function __construct(public readonly int $campaignId) {}

    public function handle(Audit $audit): void
    {
        $campaign = Campaign::with('newsletter')->find($this->campaignId);
        if ($campaign === null) {
            return;
        }

        if ($campaign->status === 'sent') {
            return; // déjà traité
        }

        $campaign->update(['status' => 'sending']);

        $sent = 0;
        $failed = 0;
        $total = 0;

        NewsletterSubscription::query()
            ->where('newsletter_id', $campaign->newsletter_id)
            ->confirmed()
            ->chunkById(100, function ($subscriptions) use ($campaign, &$sent, &$failed, &$total): void {
                foreach ($subscriptions as $sub) {
                    $total++;
                    try {
                        Mail::to($sub->email)->send(new CampaignMailer($campaign, $sub));
                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('Campaign send failed', [
                            'campaign' => $campaign->id,
                            'email' => $sub->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $campaign->update([
            'status' => $failed > 0 && $sent === 0 ? 'failed' : 'sent',
            'sent_at' => now(),
            'recipients_count' => $total,
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);

        $audit->log('campaign.sent', $campaign, [
            'recipients' => $total,
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }
}
