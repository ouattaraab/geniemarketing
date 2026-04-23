<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

/**
 * Pages légales statiques (EP-15 / EP-22) — contenu paramétrable depuis les
 * settings du BO (groupe « legal »). Les quatre pages sont :
 *   - /mentions-legales       → mentions()
 *   - /cgu                    → terms()
 *   - /confidentialite        → privacy()
 *   - /cookies                → cookies()
 *
 * Chaque page lit sa date de mise à jour dans `legal.<slug>_updated_at`
 * pour être maîtrisée manuellement (pas de `now()` dynamique — anti-pattern
 * juridique : la date affichée doit refléter une révision réelle du texte).
 */
class LegalController extends Controller
{
    public function mentions(): View
    {
        return view('public.legal.mentions', [
            'publisher' => $this->publisherInfo(),
            'updatedAt' => $this->updatedAt('legal.mentions_updated_at'),
        ]);
    }

    public function privacy(): View
    {
        return view('public.legal.privacy', [
            'publisher' => $this->publisherInfo(),
            'updatedAt' => $this->updatedAt('legal.privacy_updated_at'),
        ]);
    }

    public function terms(): View
    {
        return view('public.legal.terms', [
            'publisher' => $this->publisherInfo(),
            'updatedAt' => $this->updatedAt('legal.terms_updated_at'),
        ]);
    }

    public function cookies(): View
    {
        return view('public.legal.cookies', [
            'publisher' => $this->publisherInfo(),
            'updatedAt' => $this->updatedAt('legal.cookies_updated_at'),
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function publisherInfo(): array
    {
        return [
            // Société éditrice
            'editor' => Setting::get('legal.editor', config('gm.publisher.name')),
            'editor_form' => Setting::get('legal.editor_form'),
            'editor_capital' => Setting::get('legal.editor_capital'),
            'editor_rccm' => Setting::get('legal.editor_rccm'),
            'editor_nif' => Setting::get('legal.editor_nif'),
            'editor_cc' => Setting::get('legal.editor_cc'),
            'director' => Setting::get('legal.director', '—'),
            'cppap' => Setting::get('legal.cppap_number'),

            // Contact
            'address' => Setting::get('contact.address'),
            'email' => Setting::get('contact.email'),
            'phone' => Setting::get('contact.phone'),
            'dpo_email' => Setting::get('legal.dpo_email'),

            // Hébergeur
            'host_name' => Setting::get('legal.host_name'),
            'host_address' => Setting::get('legal.host_address'),
            'host_url' => Setting::get('legal.host_url'),
            'host_phone' => Setting::get('legal.host_phone'),
        ];
    }

    private function updatedAt(string $key): string
    {
        $raw = (string) Setting::get($key, '');
        if ($raw === '') {
            return now()->locale('fr')->translatedFormat('j F Y');
        }

        try {
            return \Carbon\Carbon::parse($raw)->locale('fr')->translatedFormat('j F Y');
        } catch (\Throwable) {
            return $raw;
        }
    }
}
