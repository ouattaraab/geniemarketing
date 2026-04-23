<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // === Branding ===
            ['branding', 1, 'branding.baseline', 'Baseline du magazine', 'string', 'Décrypter, inspirer, transformer — le magazine de référence du marketing ivoirien et panafricain.', 'Phrase d\'accroche utilisée sur le footer et les emails.'],
            ['branding', 2, 'branding.copyright', 'Mention de copyright', 'string', '© GÉNIE MARKETING Mag — Tous droits réservés · Abidjan, Côte d\'Ivoire', null],

            // === Contact ===
            ['contact', 1, 'contact.email', 'Email de contact', 'email', 'contact@geniemag.ci', null],
            ['contact', 2, 'contact.phone', 'Téléphone', 'string', '+225 01 00 00 00 00', null],
            ['contact', 3, 'contact.address', 'Adresse postale', 'text', 'Abidjan, Côte d\'Ivoire', null],

            // === Légal — Éditeur / Société ===
            ['legal', 1, 'legal.editor', 'Raison sociale de l\'éditeur', 'string', 'SARL GÉNIE MARKETING', 'Dénomination sociale exacte telle qu\'immatriculée au RCCM.'],
            ['legal', 2, 'legal.editor_form', 'Forme juridique', 'string', 'SARL', 'SARL, SA, SAS, Entreprise individuelle…'],
            ['legal', 3, 'legal.editor_capital', 'Capital social', 'string', '1 000 000 FCFA', 'Capital social en FCFA — obligatoire pour SARL/SA sur mentions légales.'],
            ['legal', 4, 'legal.editor_rccm', 'Numéro RCCM', 'string', 'CI-ABJ-2025-B-xxxxx', 'Registre du Commerce et du Crédit Mobilier (Abidjan).'],
            ['legal', 5, 'legal.editor_nif', 'Numéro NIF', 'string', '—', 'Numéro d\'Identification Fiscale délivré par la DGI.'],
            ['legal', 6, 'legal.editor_cc', 'Compte Contribuable', 'string', '—', 'Numéro de compte contribuable DGI — apparaît sur les factures.'],
            ['legal', 7, 'legal.director', 'Directeur de la publication', 'string', '—', 'Personne physique responsable au sens de la loi 2017-867 sur la presse.'],
            ['legal', 8, 'legal.cppap_number', 'Numéro CNPCI / presse', 'string', '—', 'Numéro délivré par le Conseil National de la Presse (CNPCI) si applicable.'],
            ['legal', 9, 'legal.dpo_email', 'DPO — email', 'email', 'dpo@geniemag.ci', 'Délégué à la protection des données (RGPD / Loi 2013-450 CI)'],

            // === Légal — Hébergeur (obligatoire loi 2013-451 CI) ===
            ['legal', 20, 'legal.host_name', 'Hébergeur — raison sociale', 'string', 'Hostinger International Ltd.', 'Obligation de publier le nom de l\'hébergeur.'],
            ['legal', 21, 'legal.host_address', 'Hébergeur — adresse', 'text', '61 Lordou Vironos Street, 6023 Larnaca, Chypre', null],
            ['legal', 22, 'legal.host_url', 'Hébergeur — site web', 'url', 'https://www.hostinger.com', null],
            ['legal', 23, 'legal.host_phone', 'Hébergeur — téléphone', 'string', '+357 24 030 706', null],

            // === Légal — Dates de mise à jour (affichées sur les pages) ===
            ['legal', 30, 'legal.terms_updated_at', 'CGU/CGV — dernière mise à jour', 'string', '2026-04-23', 'Format YYYY-MM-DD. À incrémenter à chaque modification des CGU/CGV.'],
            ['legal', 31, 'legal.privacy_updated_at', 'Politique de confidentialité — dernière mise à jour', 'string', '2026-04-23', 'Format YYYY-MM-DD.'],
            ['legal', 32, 'legal.mentions_updated_at', 'Mentions légales — dernière mise à jour', 'string', '2026-04-23', 'Format YYYY-MM-DD.'],
            ['legal', 33, 'legal.cookies_updated_at', 'Politique cookies — dernière mise à jour', 'string', '2026-04-23', 'Format YYYY-MM-DD.'],

            // === Légal — Liens publics ===
            ['legal', 40, 'legal.mentions_url', 'URL mentions légales', 'url', '/mentions-legales', null],

            // === Social ===
            ['social', 1, 'social.linkedin', 'LinkedIn', 'url', 'https://www.linkedin.com/company/genie-marketing-mag', null],
            ['social', 2, 'social.twitter', 'X (Twitter)', 'url', 'https://x.com/geniemagci', null],
            ['social', 3, 'social.facebook', 'Facebook', 'url', 'https://www.facebook.com/geniemag.ci', null],
            ['social', 4, 'social.instagram', 'Instagram', 'url', 'https://www.instagram.com/geniemag.ci', null],

            // === Promo / Bannières ===
            ['promo', 1, 'promo.banner_enabled', 'Bannière promotionnelle activée', 'boolean', '0', 'Affiche un bandeau en haut de page pour une offre limitée.'],
            ['promo', 2, 'promo.banner_text', 'Texte de la bannière', 'string', 'Lancement : -30% sur l\'abonnement annuel avec le code LANCEMENT30', null],
            ['promo', 3, 'promo.banner_cta', 'Libellé du bouton', 'string', 'En profiter', null],
            ['promo', 4, 'promo.banner_url', 'URL du bouton', 'url', '/abonnement', null],

            // === Freemium / Paywall ===
            ['paywall', 1, 'paywall.freemium_monthly_limit', 'Articles gratuits par mois', 'integer', '3', 'Nombre d\'articles premium accessibles gratuitement avant déclenchement du paywall.'],
        ];

        foreach ($settings as [$group, $position, $key, $label, $type, $value, $description]) {
            Setting::updateOrCreate(
                ['key' => $key],
                compact('group', 'position', 'label', 'type', 'value', 'description'),
            );
        }
    }
}
