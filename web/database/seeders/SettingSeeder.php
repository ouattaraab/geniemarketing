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

            // === Légal ===
            ['legal', 1, 'legal.editor', 'Éditeur (mentions légales)', 'string', 'SARL GÉNIE MARKETING', null],
            ['legal', 2, 'legal.director', 'Directeur de publication', 'string', '—', null],
            ['legal', 3, 'legal.dpo_email', 'DPO — email', 'email', 'dpo@geniemag.ci', 'Délégué à la protection des données (RGPD / Loi 2013-450 CI)'],
            ['legal', 4, 'legal.mentions_url', 'URL mentions légales', 'url', '/mentions-legales', null],

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
