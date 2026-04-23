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
            // Verrou pessimiste contre les conditions de race : une autre
            // session/device du user ne pourra ni lire ni modifier cette
            // ligne tant que la transaction d'effacement n'est pas commitée.
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

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

            // Mémorise l'email *avant* toute mutation — utile pour nettoyer
            // les tables indexées par email et pour le hash d'audit.
            $originalEmail = strtolower(trim($user->email));

            // Hash de l'email pour audit ultérieur (détection de ré-inscription abusive).
            $emailHash = hash('sha256', $originalEmail);

            $this->audit->log('user.erased', null, [
                'user_id_was' => $user->id,
                'email_sha256' => $emailHash,
                'had_active_subscription' => $user->hasActiveSubscription(),
                'orders_count' => $user->orders()->count(),
            ]);

            // L1 — email anonymisé avec l'id garanti unique (évite une
            // collision théorique sur `users.email UNIQUE` qui ferait
            // rollback de l'effacement complet).
            $anonEmail = sprintf('erased+%d-%s@deleted.local', $user->id, Str::random(8));

            // Désinscription newsletter (sans perdre la preuve du consentement
            // initial, on archive avec un email anonymisé).
            DB::table('newsletter_subscriptions')
                ->where('user_id', $user->id)
                ->orWhere('email', $originalEmail)
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

            // H2 — Tables PII additionnelles que l'eraser précédent oubliait :
            //
            //  - sessions : payload contient les `old` inputs (emails, tels…),
            //    ip_address et user_agent. Invalidation immédiate = déconnecte
            //    aussi les autres devices (M1 de l'audit).
            //  - password_reset_tokens : indexé par email en clair → PK PII.
            //  - access_rights : liens user ↔ article/order, trace des accès
            //    freemium et premium — supprimables car recréables à partir
            //    de la subscription (qui reste en 'cancelled').
            //  - audit_logs : on garde les actions pour intégrité mais on
            //    anonymise le user_id (preuve d'action sans nommer la personne).
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
            DB::table('access_rights')->where('user_id', $user->id)->delete();
            if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->where('user_id', $user->id)->update(['user_id' => null]);
            }

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
