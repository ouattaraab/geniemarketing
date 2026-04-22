<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Subscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre abonnement arrive à échéance — GÉNIE MARKETING Mag',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.renewal-reminder',
            with: [
                'subscription' => $this->subscription,
                'user' => $this->subscription->user,
                'plan' => $this->subscription->plan,
                'daysUntilExpiration' => max(0, (int) now()->diffInDays($this->subscription->end_date, false)),
            ],
        );
    }
}
