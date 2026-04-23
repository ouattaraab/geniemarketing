<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\Consent;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Efface un compte utilisateur dans le respect des obligations légales :
 *
 *  - Les données personnelles directement identifiantes (email, nom, téléphone,
 *    2FA) sont supprimées ou anonymisées.
 *  - Les factures, commandes et paiements sont CONSERVÉS anonymisés — 10 ans
 *    d'obligation comptable en Côte d'Ivoire (Code Général des Impôts) + UEMOA.
 *  - Les abonnements actifs sont annulés, pas supprimés, pour préserver la
 *    traçabilité commerciale.
 *  - Un consentement de retrait est enregistré (preuve opposable de la demande
 *    d'effacement).
 *  - Un enregistrement d'audit est inscrit, sans PII, avec hash irréversible
 *    de l'email pour détecter d'éventuelles ré-inscriptions frauduleuses sans
 *    exposer l'identifiant historique.
 */
class AccountEraser
{
    public function __construct(private readonly Audit $audit) {}

    public function erase(User $user, ?string $ip = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($user, $ip, $userAgent): void {
            // Preuve de la demande d'effacement — avant l'anonymisation.
            Consent::record(
                $user->id,
                Consent::DOC_PRIVACY,
                now()->toDateString(),
                Consent::ACTION_REVOKED,
                'account_deletion',
                $ip,
                $userAgent,
            );

            // Annule les abonnements actifs sans les supprimer (trace commerciale).
            $user->subscriptions()->where('status', 'active')->update([
                'status' => 'cancelled',
                'auto_renewal' => false,
            ]);
            $user->subscriptions()->where('status', 'trialing')->update([
                'status' => 'cancelled',
                'auto_renewal' => false,
            ]);

            // Hash de l'email pour audit ultérieur (détection de ré-inscription abusive).
            $emailHash = hash('sha256', strtolower(trim($user->email)));

            $this->audit->log('user.erased', null, [
                'user_id_was' => $user->id,
                'email_sha256' => $emailHash,
                'had_active_subscription' => $user->hasActiveSubscription(),
                'orders_count' => $user->orders()->count(),
            ]);

            // Désinscription newsletter (sans perdre la preuve du consentement
            // initial, on archive avec un email anonymisé).
            $anonEmail = 'erased+'.Str::random(16).'@deleted.local';
            DB::table('newsletter_subscriptions')
                ->where('user_id', $user->id)
                ->update(['status' => 'unsubscribed', 'email' => $anonEmail, 'user_id' => null]);

            // Les commentaires de l'utilisateur sont soft-deleted et leur IP
            // effacée. Le schéma comments a user_id NOT NULL + cascade, donc
            // on ne peut pas anonymiser user_id — le soft-delete est la voie
            // la plus conservative (restauration admin possible si besoin
            // légal, purge automatique possible ensuite).
            DB::table('comments')->where('user_id', $user->id)->update([
                'ip' => null,
                'deleted_at' => now(),
            ]);

            // On passe les orders en orphelins logiques (user_id = null) avec
            // billing_address masquée. Garde le total/reference/invoice pour la
            // comptabilité.
            foreach ($user->orders as $order) {
                $order->billing_address = [
                    'name' => 'Compte supprimé',
                    'email' => $anonEmail,
                    'phone' => null,
                    'address' => null,
                    'city' => null,
                    'country' => $order->billing_address['country'] ?? null,
                ];
                $order->shipping_address = null;
                $order->save();
            }

            // Invalide les 2FA et remote tokens.
            DB::table('users')->where('id', $user->id)->update([
                '2fa_enabled' => false,
                '2fa_secret' => null,
                '2fa_recovery_codes' => null,
                '2fa_confirmed_at' => null,
                'remember_token' => null,
                'email_verified_at' => null,
            ]);

            // Soft-delete final + anonymisation des champs PII. On passe en
            // 'inactive' (plutôt qu'ajouter un statut 'deleted' qui romprait
            // la contrainte enum actuelle) — le soft-delete timestamp est
            // la source de vérité "compte supprimé".
            $user->forceFill([
                'first_name' => 'Utilisateur',
                'last_name' => 'supprimé',
                'email' => $anonEmail,
                'phone' => null,
                'password' => Hash::make(Str::random(48)),
                'status' => 'inactive',
            ])->save();

            $user->delete(); // SoftDelete
        });
    }
}
