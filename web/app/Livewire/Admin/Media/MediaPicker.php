<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal léger qui s'ouvre depuis l'éditeur TipTap pour sélectionner une image
 * de la bibliothèque. Déclenché par l'événement Livewire "open-media-picker",
 * émet l'événement navigateur "media-picker:selected" lors de la sélection.
 */
class MediaPicker extends Component
{
    public bool $open = false;

    public string $search = '';

    #[On('open-media-picker')]
    public function show(): void
    {
        $this->open = true;
        $this->search = '';
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function pick(int $mediaId): void
    {
        $media = Media::find($mediaId);
        if ($media === null) {
            return;
        }

        $this->dispatch('media-picker:selected', [
            'src' => $media->url(),
            'alt' => $media->alt,
            'caption' => $media->caption,
            'credit' => $media->credit,
        ]);

        $this->open = false;
    }

    public function media(): Collection
    {
        $query = Media::query()
            ->where('type', MediaType::Image)
            ->latest()
            ->limit(24);

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('alt', 'like', '%'.$this->search.'%')
                    ->orWhere('caption', 'like', '%'.$this->search.'%')
                    ->orWhere('original_filename', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get();
    }

    public function render(): View
    {
        return view('livewire.admin.media.media-picker', [
            'items' => $this->media(),
        ]);
    }
}
