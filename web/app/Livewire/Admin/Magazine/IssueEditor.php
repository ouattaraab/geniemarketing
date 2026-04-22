<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Magazine;

use App\Models\MagazineIssue;
use App\Models\Media;
use App\Services\MediaManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Numéro magazine — GM Admin')]
class IssueEditor extends Component
{
    use WithFileUploads;

    public ?MagazineIssue $issue = null;

    public int $number = 1;
    public string $title = '';
    public string $theme = '';
    public string $slug = '';
    public string $publicationDate = '';
    public ?int $pricePaper = null;
    public ?int $pricePdf = null;
    public int $stockPaper = 0;
    public string $status = 'draft';

    public ?int $coverMediaId = null;
    public ?TemporaryUploadedFile $coverUpload = null;
    public string $coverAlt = '';

    public ?TemporaryUploadedFile $pdfUpload = null;

    public function mount(?MagazineIssue $issue = null): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['chef', 'edit', 'adm', 'sup']),
            403,
        );

        if ($issue && $issue->exists) {
            $this->issue = $issue;
            $this->number = $issue->number;
            $this->title = $issue->title;
            $this->theme = $issue->theme ?? '';
            $this->slug = $issue->slug;
            $this->publicationDate = $issue->publication_date?->format('Y-m-d') ?? '';
            $this->pricePaper = $issue->price_paper_cents ? (int) ($issue->price_paper_cents / 100) : null;
            $this->pricePdf = $issue->price_pdf_cents ? (int) ($issue->price_pdf_cents / 100) : null;
            $this->stockPaper = $issue->stock_paper;
            $this->status = $issue->status;
            $this->coverMediaId = $issue->cover_media_id;
            $this->coverAlt = $issue->cover?->alt ?? '';

            return;
        }

        // Pré-remplissage création : numéro suivant + date du jour
        $this->number = ((int) MagazineIssue::max('number')) + 1;
        $this->publicationDate = now()->format('Y-m-d');
    }

    public function updatedTitle(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug('n-'.$this->number.'-'.$value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'number' => ['required', 'integer', 'min:1',
                'unique:magazine_issues,number'.($this->issue?->id ? ','.$this->issue->id : ''),
            ],
            'title' => ['required', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'max:500'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash',
                'unique:magazine_issues,slug'.($this->issue?->id ? ','.$this->issue->id : ''),
            ],
            'publicationDate' => ['required', 'date'],
            'pricePaper' => ['nullable', 'integer', 'min:0'],
            'pricePdf' => ['nullable', 'integer', 'min:0'],
            'stockPaper' => ['integer', 'min:0'],
            'status' => ['required', 'in:draft,scheduled,published,archived'],
            'coverUpload' => ['nullable', 'image', 'max:8192'],
            'coverAlt' => ['nullable', 'string', 'max:255'],
            'pdfUpload' => ['nullable', 'file', 'mimes:pdf', 'max:40960'], // 40 Mo
        ]);

        $data = [
            'number' => $validated['number'],
            'title' => $validated['title'],
            'theme' => $validated['theme'] ?: null,
            'slug' => $validated['slug'],
            'publication_date' => $validated['publicationDate'],
            'price_paper_cents' => $validated['pricePaper'] ? $validated['pricePaper'] * 100 : null,
            'price_pdf_cents' => $validated['pricePdf'] ? $validated['pricePdf'] * 100 : null,
            'stock_paper' => $validated['stockPaper'],
            'status' => $validated['status'],
            'currency' => 'XOF',
        ];

        // Upload cover
        if ($this->coverUpload) {
            $media = app(MediaManager::class)->upload(
                file: $this->coverUpload,
                uploadedByUserId: auth()->id(),
                alt: $this->coverAlt ?: 'Couverture numéro '.$this->number,
            );
            $data['cover_media_id'] = $media->id;
            $this->coverUpload = null;
        } elseif ($this->coverMediaId) {
            $data['cover_media_id'] = $this->coverMediaId;
        }

        // Upload PDF
        if ($this->pdfUpload) {
            $disk = config('filesystems.default', 'local');
            $path = 'issues/'.now()->format('Y/m').'/'.Str::uuid().'.pdf';
            $this->pdfUpload->storeAs(dirname($path), basename($path), ['disk' => $disk]);
            $data['pdf_disk'] = $disk;
            $data['pdf_path'] = $path;
            $data['pdf_size_bytes'] = $this->pdfUpload->getSize();
            $this->pdfUpload = null;
        }

        if ($this->issue?->exists) {
            $this->issue->update($data);
        } else {
            $this->issue = MagazineIssue::create($data);
        }

        session()->flash('status', 'Numéro enregistré.');
        $this->redirectRoute('admin.issues.edit', ['issue' => $this->issue], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.magazine.issue-editor');
    }
}
