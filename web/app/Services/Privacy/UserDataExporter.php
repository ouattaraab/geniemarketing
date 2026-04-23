<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Collecte toutes les données d'un utilisateur dans un JSON structuré, pour
 * répondre aux obligations de portabilité (RGPD art. 20 / loi 2013-450).
 *
 * Format : clés stables, snake_case, datetimes ISO-8601, aucune donnée
 * technique non pertinente pour l'utilisateur. Le mot de passe, les secrets
 * 2FA et les codes de récupération sont exclus par principe (ils ne sont
 * pas des données personnelles portables mais des secrets de sécurité).
 */
class UserDataExporter
{
    /**
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        $fresh = $user->fresh([
            'orders.plan',
            'orders.invoice',
            'orders.payments',
            'subscriptions.plan',
            'consents',
            'roles',
        ]);

        return [
            'export_metadata' => [
                'generated_at' => now()->toIso8601String(),
                'format_version' => '1.0',
                'schema' => 'gm-user-export/v1',
                'legal_basis' => 'RGPD art. 20 / Loi 2013-450 art. 40 (portabilité)',
            ],
            'account' => [
                'id' => $fresh->id,
                'first_name' => $fresh->first_name,
                'last_name' => $fresh->last_name,
                'email' => $fresh->email,
                'phone' => $fresh->phone,
                'type' => $fresh->type,
                'status' => $fresh->status,
                'email_verified_at' => optional($fresh->email_verified_at)?->toIso8601String(),
                'created_at' => $fresh->created_at?->toIso8601String(),
                'two_factor_enabled' => (bool) $fresh->{'2fa_enabled'},
                'roles' => $fresh->roles->pluck('name')->values()->all(),
            ],
            'subscriptions' => $fresh->subscriptions->map(fn ($s) => [
                'id' => $s->id,
                'plan_code' => $s->plan?->code,
                'plan_name' => $s->plan?->name,
                'status' => $s->status->value,
                'start_date' => $s->start_date?->toIso8601String(),
                'end_date' => $s->end_date?->toIso8601String(),
                'trial_ends_at' => optional($s->trial_ends_at)?->toIso8601String(),
                'auto_renewal' => (bool) $s->auto_renewal,
            ])->values()->all(),
            'orders' => $fresh->orders->map(fn ($o) => [
                'reference' => $o->reference,
                'plan' => $o->plan?->code,
                'status' => $o->status->value,
                'subtotal_cents' => $o->subtotal_cents,
                'discount_cents' => $o->discount_cents,
                'tax_cents' => $o->tax_cents,
                'total_cents' => $o->total_cents,
                'currency' => $o->currency,
                'paid_at' => optional($o->paid_at)?->toIso8601String(),
                'created_at' => $o->created_at?->toIso8601String(),
                'billing_address' => $o->billing_address,
                'invoice_number' => $o->invoice?->number,
                'payments' => $o->payments->map(fn ($p) => [
                    'provider' => $p->provider,
                    'channel' => $p->channel,
                    'status' => $p->status->value,
                    'amount_cents' => $p->amount_cents,
                    'currency' => $p->currency,
                    'captured_at' => optional($p->captured_at)?->toIso8601String(),
                ])->values()->all(),
            ])->values()->all(),
            'consents' => $fresh->consents->map(fn ($c) => [
                'document' => $c->document,
                'version' => $c->version,
                'action' => $c->action,
                'source' => $c->source,
                'at' => $c->created_at?->toIso8601String(),
                'ip' => $c->ip,
            ])->values()->all(),
            'newsletter_subscriptions' => DB::table('newsletter_subscriptions')
                ->where('user_id', $fresh->id)
                ->orWhere('email', $fresh->email)
                ->get(['newsletter_id', 'email', 'status', 'confirmed_at', 'source', 'created_at'])
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all(),
            'comments' => DB::table('comments')
                ->where('user_id', $fresh->id)
                ->get(['article_id', 'body', 'status', 'created_at'])
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all(),
        ];
    }
}
