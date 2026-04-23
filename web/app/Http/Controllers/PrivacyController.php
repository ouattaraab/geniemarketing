<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Privacy\UserDataExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoints RGPD / loi 2013-450 côté utilisateur :
 *   - export de ses données (portabilité — art. 20)
 *   - suppression du compte (droit à l'oubli — art. 17) → géré par ProfileController
 */
class PrivacyController extends Controller
{
    public function __construct(private readonly UserDataExporter $exporter) {}

    /**
     * GET /compte/mes-donnees/export
     * Renvoie un JSON téléchargeable contenant l'intégralité des données
     * personnelles de l'utilisateur connecté. Format stable versionné pour
     * pouvoir être ré-importé par un tiers.
     */
    public function export(Request $request): JsonResponse|Response|BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        Log::info('RGPD export requested', ['user_id' => $user->id, 'ip' => $request->ip()]);

        $data = $this->exporter->export($user);

        $filename = sprintf(
            'gm-donnees-%s-%s.json',
            $user->id,
            now()->format('Y-m-d-His'),
        );

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
