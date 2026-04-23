<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Audit;
use App\Services\Privacy\UserDataExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoints RGPD / loi 2013-450 côté utilisateur :
 *   - export de ses données (portabilité — art. 20)
 *   - suppression du compte (droit à l'oubli — art. 17) → géré par ProfileController
 */
class PrivacyController extends Controller
{
    public function __construct(
        private readonly UserDataExporter $exporter,
        private readonly Audit $audit,
    ) {}

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

        $data = $this->exporter->export($user);
        $payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // M5 — trace opposable dans l'audit log en cas de compromission du
        // compte (un attaquant qui exfiltre via session hijack laisse une
        // ligne visible par l'équipe sécurité + le user peut la constater).
        $this->audit->log('privacy.data_exported', $user, [
            'ip' => $request->ip(),
            'ua' => mb_substr((string) $request->userAgent(), 0, 255),
            'size_bytes' => strlen($payload),
        ]);

        $filename = sprintf(
            'gm-donnees-%s-%s.json',
            $user->id,
            now()->format('Y-m-d-His'),
        );

        return response($payload, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
        ]);
    }
}
