<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

/**
 * Pages légales statiques (EP-15/EP-22) — contenu paramétrable depuis les settings.
 */
class LegalController extends Controller
{
    public function mentions(): View
    {
        return view('public.legal.mentions', [
            'publisher' => $this->publisherInfo(),
        ]);
    }

    public function privacy(): View
    {
        return view('public.legal.privacy', [
            'publisher' => $this->publisherInfo(),
        ]);
    }

    public function terms(): View
    {
        return view('public.legal.terms', [
            'publisher' => $this->publisherInfo(),
        ]);
    }

    public function cookies(): View
    {
        return view('public.legal.cookies', [
            'publisher' => $this->publisherInfo(),
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function publisherInfo(): array
    {
        return [
            'editor' => Setting::get('legal.editor', config('gm.publisher.name')),
            'director' => Setting::get('legal.director', '—'),
            'address' => Setting::get('contact.address'),
            'email' => Setting::get('contact.email'),
            'phone' => Setting::get('contact.phone'),
            'dpo_email' => Setting::get('legal.dpo_email'),
        ];
    }
}
