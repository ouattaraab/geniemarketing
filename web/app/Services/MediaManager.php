<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gère l'upload et la création de médias (EP-05).
 * MVP : stockage brut sur le disk configuré + lecture des dimensions pour les images.
 * À venir (V1) : génération de déclinaisons (thumbs, webp), traitement async via queue.
 */
class MediaManager
{
    public function upload(
        UploadedFile $file,
        ?int $uploadedByUserId = null,
        ?int $collectionId = null,
        ?string $alt = null,
        ?string $caption = null,
        ?string $credit = null,
    ): Media {
        $disk = config('filesystems.default', 'local');
        // Ne PAS utiliser getClientOriginalExtension() — l'extension est
        // attaquant-controllable (ex. evil.php.jpg). On la dérive du mime
        // détecté côté serveur. Fallback sur 'bin' pour les contenus
        // inconnus ; la whitelist mimes du composant Livewire empêche déjà
        // les binaires exécutables d'arriver jusqu'ici.
        $extension = $file->guessExtension() ?: 'bin';
        $directory = 'media/'.now()->format('Y/m');
        $filename = Str::uuid().'.'.$extension;

        // Lire taille + mime + dimensions AVANT storeAs — storeAs peut déplacer
        // le fichier temporaire (Livewire notamment) et rendre les lookups
        // ultérieurs impossibles (UnableToRetrieveMetadata).
        $mime = $file->getMimeType() ?? 'application/octet-stream';
        $type = $this->resolveType($mime);
        [$width, $height] = $this->resolveDimensions($file, $type);
        $size = $this->safeFileSize($file);

        $path = $file->storeAs($directory, $filename, $disk);

        return Media::create([
            'collection_id' => $collectionId,
            'uploaded_by_user_id' => $uploadedByUserId,
            'type' => $type,
            'disk' => $disk,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => $size,
            'width' => $width,
            'height' => $height,
            'alt' => $alt,
            'caption' => $caption,
            'credit' => $credit,
        ]);
    }

    private function safeFileSize(UploadedFile $file): int
    {
        try {
            return (int) ($file->getSize() ?: 0);
        } catch (\Throwable) {
            $real = $file->getRealPath();
            if (is_string($real) && is_readable($real)) {
                $size = @filesize($real);

                return is_int($size) ? $size : 0;
            }

            return 0;
        }
    }

    public function delete(Media $media): void
    {
        if ($media->path) {
            Storage::disk($media->disk)->delete($media->path);
        }
        $media->delete();
    }

    private function resolveType(string $mime): MediaType
    {
        return match (true) {
            str_starts_with($mime, 'image/') => MediaType::Image,
            str_starts_with($mime, 'video/') => MediaType::Video,
            str_starts_with($mime, 'audio/') => MediaType::Audio,
            $mime === 'application/pdf' => MediaType::Pdf,
            default => MediaType::Other,
        };
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveDimensions(UploadedFile $file, MediaType $type): array
    {
        if ($type !== MediaType::Image) {
            return [null, null];
        }

        $path = $file->getRealPath();
        if ($path === false || ! is_readable($path)) {
            return [null, null];
        }

        $info = @getimagesize($path);
        if ($info === false) {
            return [null, null];
        }

        return [$info[0] ?? null, $info[1] ?? null];
    }
}
