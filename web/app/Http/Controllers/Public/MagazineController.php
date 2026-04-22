<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MagazineIssue;
use App\Services\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MagazineController extends Controller
{
    public function __construct(private readonly Audit $audit) {}

    public function index(): View
    {
        $issues = MagazineIssue::query()
            ->published()
            ->with('cover')
            ->orderByDesc('publication_date')
            ->paginate(12);

        return view('public.magazine', compact('issues'));
    }

    public function show(MagazineIssue $issue): View
    {
        abort_unless($issue->status === 'published', 404);

        $issue->load(['cover', 'summaryEntries.article']);

        return view('public.magazine-issue', compact('issue'));
    }

    /**
     * Lecteur PDF — redirige vers la page /magazine/{slug}/lecteur qui contient pdf.js
     * + embedded watermark JS-side.
     */
    public function reader(Request $request, MagazineIssue $issue): View
    {
        abort_unless($issue->status === 'published', 404);

        $user = $request->user();
        abort_unless($user !== null && $user->hasActiveSubscription(), 403, 'Abonnement requis pour lire le numéro en ligne.');
        abort_unless($issue->hasPdf(), 404);

        $this->audit->log('magazine.viewed', $issue, ['user_id' => $user->id]);

        return view('public.magazine-reader', [
            'issue' => $issue,
            'pdfStreamUrl' => route('magazine.pdf', ['issue' => $issue]),
            'watermark' => sprintf('%s · %s · %s', $user->email, $request->ip(), now()->format('Y-m-d H:i')),
        ]);
    }

    /**
     * Sert le binaire PDF avec header X-Watermark (consommé par le JS côté lecteur
     * qui applique le watermark overlay). Lien direct bloqué via rate-limiting + auth.
     */
    public function streamPdf(Request $request, MagazineIssue $issue): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user !== null && $user->hasActiveSubscription(), 403);
        abort_unless($issue->hasPdf(), 404);

        $this->audit->log('magazine.pdf.streamed', $issue, ['user_id' => $user->id]);

        return Storage::disk($issue->pdf_disk)->response($issue->pdf_path, null, [
            'Content-Type' => 'application/pdf',
            'X-Watermark' => sprintf('%s|%s|%s', $user->email, $request->ip(), now()->toIso8601String()),
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
