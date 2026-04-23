<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Advertisements;

use App\Models\Advertisement;
use App\Models\Media;
use App\Services\Audit;
use App\Services\MediaManager;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin', ['title' => 'Bannière publicitaire'])]
#[Title('Bannière — GM Admin')]
class AdEditor extends Component
{
    use WithFileUploads;

    public ?int $adId = null;

    public string $title = '';

    public string $placement = Advertisement::PLACEMENT_ARTICLE_TOP;

    public ?int $mediaId = null;

    public string $imageUrl = '';

    public string $altText = '';

    public string $linkUrl = '';

    public bool $linkNofollow = true;

    public bool $linkNewTab = true;

    public int $priority = 10;

    public ?string $startsAt = null;

    public ?string $endsAt = null;

    public bool $isActive = true;

    public string $sponsorName = '';

    public ?TemporaryUploadedFile $imageUpload = null;

    public function mount(?Advertisement $advertisement = null): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['com', 'adm', 'sup']), 403);

        if ($advertisement && $advertisement->exists) {
            $this->adId = $advertisement->id;
            $this->title = $advertisement->title;
            $this->placement = $advertisement->placement;
            $this->mediaId = $advertisement->media_id;
            $this->imageUrl = (string) $advertisement->image_url;
            $this->altText = (string) $advertisement->alt_text;
            $this->linkUrl = $advertisement->link_url;
            $this->linkNofollow = (bool) $advertisement->link_nofollow;
            $this->linkNewTab = (bool) $advertisement->link_new_tab;
            $this->priority = (int) $advertisement->priority;
            $this->startsAt = $advertisement->starts_at?->format('Y-m-d\TH:i');
            $this->endsAt = $advertisement->ends_at?->format('Y-m-d\TH:i');
            $this->isActive = (bool) $advertisement->is_active;
            $this->sponsorName = (string) $advertisement->sponsor_name;
        }
    }

    public function updatedImageUpload(): void
    {
        $this->validate([
            'imageUpload' => ['image', 'max:4096'],
        ]);
    }

    public function removeImage(): void
    {
        $this->imageUpload = null;
        $this->mediaId = null;
        $this->imageUrl = '';
    }

    #[Computed]
    public function previewUrl(): ?string
    {
        if ($this->imageUpload) {
            return $this->imageUpload->temporaryUrl();
        }

        if ($this->mediaId) {
            return Media::find($this->mediaId)?->url();
        }

        return $this->imageUrl !== '' ? $this->imageUrl : null;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'placement' => ['required', 'in:article_top,article_bottom,article_sidebar,home_leaderboard,home_sidebar'],
            'imageUrl' => ['nullable', 'url', 'max:500'],
            'altText' => ['nullable', 'string', 'max:200'],
            'linkUrl' => ['required', 'url', 'max:1000'],
            'linkNofollow' => ['boolean'],
            'linkNewTab' => ['boolean'],
            'priority' => ['required', 'integer', 'min:0', 'max:100'],
            'startsAt' => ['nullable', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'isActive' => ['boolean'],
            'sponsorName' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['com', 'adm', 'sup']), 403);

        $data = $this->validate();

        // Handle upload → Media
        if ($this->imageUpload) {
            if (trim($this->altText) === '') {
                $this->addError('altText', 'Le texte alternatif (alt) est requis lors de l\'upload d\'une image.');

                return;
            }
            /** @var MediaManager $mm */
            $mm = app(MediaManager::class);
            $media = $mm->upload(
                file: $this->imageUpload,
                uploadedByUserId: auth()->id(),
                alt: $this->altText,
                caption: null,
                credit: null,
            );
            $this->mediaId = $media->id;
            $this->imageUpload = null;
        }

        // Au moins une source d'image
        if ($this->mediaId === null && trim($data['imageUrl']) === '') {
            $this->addError('imageUpload', 'Uploadez une image OU renseignez une URL externe.');

            return;
        }

        $payload = [
            'title' => $data['title'],
            'placement' => $data['placement'],
            'media_id' => $this->mediaId,
            'image_url' => $data['imageUrl'] ?: null,
            'alt_text' => $data['altText'] ?: null,
            'link_url' => $data['linkUrl'],
            'link_nofollow' => $data['linkNofollow'] ?? true,
            'link_new_tab' => $data['linkNewTab'] ?? true,
            'priority' => $data['priority'],
            'starts_at' => $data['startsAt'] ?: null,
            'ends_at' => $data['endsAt'] ?: null,
            'is_active' => $data['isActive'] ?? false,
            'sponsor_name' => $data['sponsorName'] ?: null,
        ];

        if ($this->adId) {
            $ad = Advertisement::findOrFail($this->adId);
            $ad->update($payload);
            app(Audit::class)->log('ad.updated', $ad, ['title' => $ad->title]);
        } else {
            $payload['created_by_user_id'] = auth()->id();
            $ad = Advertisement::create($payload);
            $this->adId = $ad->id;
            app(Audit::class)->log('ad.created', $ad, ['title' => $ad->title]);
        }

        session()->flash('status', 'Bannière enregistrée.');
        $this->redirect(route('admin.ads.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.advertisements.ad-editor');
    }
}
