<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Models\Media;
use App\Services\MediaManager;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Bibliothèque média'])]
#[Title('Médias — GM Admin')]
class MediaLibrary extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $type = 'all';

    #[Validate(['uploads.*' => 'file|max:16384|mimes:jpg,jpeg,png,gif,webp,pdf,mp4'])]
    public array $uploads = [];

    public ?int $editingMediaId = null;

    public string $editAlt = '';

    public string $editCaption = '';

    public string $editCredit = '';

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['red', 'chef', 'edit', 'adm', 'sup']),
            403,
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUploads(): void
    {
        $this->validate();

        /** @var MediaManager $mm */
        $mm = app(MediaManager::class);

        /** @var TemporaryUploadedFile $file */
        foreach ($this->uploads as $file) {
            $mm->upload(
                file: $file,
                uploadedByUserId: auth()->id(),
            );
        }

        $this->uploads = [];
        session()->flash('status', 'Médias importés avec succès.');
        $this->resetPage();
    }

    public function startEdit(int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);
        $this->editingMediaId = $mediaId;
        $this->editAlt = $media->alt ?? '';
        $this->editCaption = $media->caption ?? '';
        $this->editCredit = $media->credit ?? '';
    }

    public function saveEdit(): void
    {
        $validated = $this->validate([
            'editAlt' => ['nullable', 'string', 'max:255'],
            'editCaption' => ['nullable', 'string', 'max:500'],
            'editCredit' => ['nullable', 'string', 'max:255'],
        ]);

        Media::where('id', $this->editingMediaId)->update([
            'alt' => $validated['editAlt'] ?: null,
            'caption' => $validated['editCaption'] ?: null,
            'credit' => $validated['editCredit'] ?: null,
        ]);

        $this->editingMediaId = null;
        session()->flash('status', 'Métadonnées mises à jour.');
    }

    public function cancelEdit(): void
    {
        $this->editingMediaId = null;
    }

    public function deleteMedia(int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);

        abort_unless(
            auth()->user()->hasAnyRole(['chef', 'edit', 'adm', 'sup'])
            || $media->uploaded_by_user_id === auth()->id(),
            403,
        );

        if ($media->coveredArticles()->exists()) {
            session()->flash('status', 'Impossible de supprimer : ce média est utilisé comme couverture d\'un article.');

            return;
        }

        app(MediaManager::class)->delete($media);
        session()->flash('status', 'Média supprimé.');
    }

    #[Computed]
    public function kpis(): array
    {
        return [
            'total' => Media::count(),
            'images' => Media::where('type', MediaType::Image)->count(),
            'pdfs' => Media::where('type', MediaType::Pdf)->count(),
            'total_size_mb' => (int) round((Media::sum('size_bytes') ?: 0) / 1048576),
        ];
    }

    public function media(): LengthAwarePaginator
    {
        $query = Media::query()
            ->with('uploadedBy')
            ->latest();

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('alt', 'like', '%'.$this->search.'%')
                    ->orWhere('caption', 'like', '%'.$this->search.'%')
                    ->orWhere('credit', 'like', '%'.$this->search.'%')
                    ->orWhere('original_filename', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->type !== 'all') {
            $query->where('type', $this->type);
        }

        return $query->paginate(24);
    }

    public function render(): View
    {
        return view('livewire.admin.media.media-library', [
            'items' => $this->media(),
        ]);
    }
}
