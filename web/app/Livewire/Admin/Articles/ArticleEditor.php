<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleVersion;
use App\Models\Author;
use App\Models\Category;
use App\Models\EditorialCategory;
use App\Models\Media;
use App\Models\Tag;
use App\Services\MediaManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Édition d\'un article — GM Admin')]
class ArticleEditor extends Component
{
    use WithFileUploads;

    public ?Article $article = null;

    // Form fields
    public string $title = '';

    public string $slug = '';

    public string $lede = '';

    /** @var array<string, mixed>|null JSON ProseMirror émis par TipTap */
    public ?array $body = null;

    public ?int $categoryId = null;

    public ?int $editorialCategoryId = null;

    public string $status = 'draft';

    public string $accessLevel = 'subscriber';

    public int $price = 0;                // en unité principale (XOF), 0 = gratuit

    public string $priceCurrency = 'XOF';

    public ?string $metaTitle = null;

    public ?string $metaDescription = null;

    public ?int $readingTime = null;

    public ?string $scheduledAt = null;

    /** @var array<int> */
    public array $selectedAuthorIds = [];

    /** @var array<int> */
    public array $selectedTagIds = [];

    public ?int $coverMediaId = null;

    public string $coverAlt = '';

    public string $coverCaption = '';

    public string $coverCredit = '';

    public ?TemporaryUploadedFile $coverUpload = null;

    public ?string $lastSavedAt = null;

    public function mount(?Article $article = null): void
    {
        if ($article && $article->exists) {
            abort_unless(auth()->user()?->can('update', $article), 403);
            $this->fillFromArticle($article);

            return;
        }

        abort_unless(auth()->user()?->can('create', Article::class), 403);
    }

    private function fillFromArticle(Article $article): void
    {
        $this->article = $article;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->lede = $article->lede ?? '';
        $this->body = $this->normaliseBodyForEditor($article->body);
        $this->categoryId = $article->category_id;
        $this->editorialCategoryId = $article->editorial_category_id;
        $this->status = $article->status->value;
        $this->accessLevel = $article->access_level->value;
        $this->price = (int) round(($article->price_cents ?? 0) / 100);
        $this->priceCurrency = $article->price_currency ?: 'XOF';
        $this->metaTitle = $article->meta_title;
        $this->metaDescription = $article->meta_description;
        $this->readingTime = $article->reading_time_minutes;
        $this->scheduledAt = $article->scheduled_at?->format('Y-m-d\TH:i');
        $this->selectedAuthorIds = $article->authors->pluck('id')->toArray();
        $this->selectedTagIds = $article->tags->pluck('id')->toArray();

        if ($article->cover) {
            $this->coverMediaId = $article->cover->id;
            $this->coverAlt = $article->cover->alt ?? '';
            $this->coverCaption = $article->cover->caption ?? '';
            $this->coverCredit = $article->cover->credit ?? '';
        }
    }

    public function updatedTitle(string $value): void
    {
        if ($this->slug === '' || $this->slug === Str::slug($this->title ?? '')) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedCoverUpload(): void
    {
        $this->validate([
            'coverUpload' => ['image', 'max:8192'], // 8 Mo
        ]);
    }

    public function removeCover(): void
    {
        $this->coverUpload = null;
        $this->coverMediaId = null;
        $this->coverAlt = '';
        $this->coverCaption = '';
        $this->coverCredit = '';
    }

    public function toggleTag(int $tagId): void
    {
        $index = array_search($tagId, $this->selectedTagIds, true);
        if ($index === false) {
            $this->selectedTagIds[] = $tagId;
        } else {
            array_splice($this->selectedTagIds, $index, 1);
        }
    }

    public function toggleAuthor(int $authorId): void
    {
        $index = array_search($authorId, $this->selectedAuthorIds, true);
        if ($index === false) {
            $this->selectedAuthorIds[] = $authorId;
        } else {
            array_splice($this->selectedAuthorIds, $index, 1);
        }
    }

    #[Computed]
    public function coverPreviewUrl(): ?string
    {
        if ($this->coverUpload) {
            return $this->coverUpload->temporaryUrl();
        }

        if ($this->coverMediaId) {
            return Media::find($this->coverMediaId)?->url();
        }

        return null;
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('position')->get();
    }

    #[Computed]
    public function editorialCategories()
    {
        return EditorialCategory::orderBy('position')->get();
    }

    #[Computed]
    public function authors()
    {
        return Author::active()->orderBy('name')->get();
    }

    #[Computed]
    public function tags()
    {
        return Tag::orderBy('name')->get();
    }

    #[Computed]
    public function ledeCount(): int
    {
        return mb_strlen($this->lede);
    }

    #[Computed]
    public function availableTransitions(): array
    {
        if (! $this->article?->exists) {
            return [];
        }

        $user = auth()->user();
        $status = $this->article->status;
        $out = [];

        if ($status === ArticleStatus::Draft && $user->can('update', $this->article)) {
            $out['review'] = 'Soumettre à relecture';
        }
        if ($status === ArticleStatus::Review && $user->can('publish', $this->article)) {
            $out['draft'] = 'Renvoyer en brouillon';
            $out['published'] = 'Publier maintenant';
            $out['scheduled'] = 'Planifier';
        }
        if ($status === ArticleStatus::Scheduled && $user->can('publish', $this->article)) {
            $out['published'] = 'Publier immédiatement';
            $out['draft'] = 'Annuler la planification';
        }
        if ($status === ArticleStatus::Published && $user->can('publish', $this->article)) {
            $out['archived'] = 'Archiver';
        }
        if ($status === ArticleStatus::Archived && $user->can('publish', $this->article)) {
            $out['draft'] = 'Désarchiver (brouillon)';
        }

        return $out;
    }

    public function applyTransition(string $to): void
    {
        abort_unless($this->article?->exists, 404);
        $user = auth()->user();

        $targetStatus = ArticleStatus::from($to);

        // Map vers la policy.
        $ability = match ($targetStatus) {
            ArticleStatus::Review => 'update',
            default => 'publish',
        };
        abort_unless($user->can($ability, $this->article), 403);

        // Garde la photo de couverture obligatoire pour publier (US-013).
        if ($targetStatus === ArticleStatus::Published && ! $this->article->cover_media_id) {
            session()->flash('status', 'Impossible de publier : une image de couverture est requise.');

            return;
        }

        $this->article->status = $targetStatus;
        if ($targetStatus === ArticleStatus::Published && $this->article->published_at === null) {
            $this->article->published_at = now();
        }
        $this->article->save();

        $this->status = $targetStatus->value;

        session()->flash('status', sprintf('Article passé au statut « %s ».', $targetStatus->label()));
    }

    public function save(): void
    {
        $this->authorizeRequest();

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash',
                'unique:articles,slug'.($this->article?->id ? ','.$this->article->id : ''),
            ],
            'lede' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'array'],
            'categoryId' => ['required', 'exists:categories,id'],
            'editorialCategoryId' => ['nullable', 'exists:editorial_categories,id'],
            'status' => ['required', 'in:draft,review,scheduled,published,archived'],
            'accessLevel' => ['required', 'in:free,registered,subscriber,premium'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'priceCurrency' => ['required', 'string', 'size:3'],
            'readingTime' => ['nullable', 'integer', 'min:1', 'max:120'],
            'scheduledAt' => ['nullable', 'date'],
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string', 'max:320'],
            'selectedAuthorIds' => ['array'],
            'selectedAuthorIds.*' => ['integer', 'exists:authors,id'],
            'selectedTagIds' => ['array'],
            'selectedTagIds.*' => ['integer', 'exists:tags,id'],
            'coverAlt' => ['nullable', 'string', 'max:255'],
            'coverCaption' => ['nullable', 'string', 'max:500'],
            'coverCredit' => ['nullable', 'string', 'max:255'],
        ]);

        // Handle cover upload avant création article
        if ($this->coverUpload) {
            $this->validate(['coverUpload' => ['image', 'max:8192']]);

            if (trim($this->coverAlt) === '') {
                $this->addError('coverAlt', 'Le texte alternatif (alt) est obligatoire pour une image de couverture.');

                return;
            }

            /** @var MediaManager $mm */
            $mm = app(MediaManager::class);
            $media = $mm->upload(
                file: $this->coverUpload,
                uploadedByUserId: auth()->id(),
                alt: $this->coverAlt,
                caption: $this->coverCaption ?: null,
                credit: $this->coverCredit ?: null,
            );
            $this->coverMediaId = $media->id;
            $this->coverUpload = null;
        } elseif ($this->coverMediaId) {
            // Mise à jour métadonnées de la cover existante (si modifiées)
            Media::where('id', $this->coverMediaId)->update([
                'alt' => $this->coverAlt ?: null,
                'caption' => $this->coverCaption ?: null,
                'credit' => $this->coverCredit ?: null,
            ]);
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'lede' => $validated['lede'] ?? null,
            'body' => $validated['body'] ?? ['type' => 'doc', 'content' => []],
            'category_id' => $validated['categoryId'],
            'editorial_category_id' => $validated['editorialCategoryId'] ?? null,
            'cover_media_id' => $this->coverMediaId,
            'status' => $validated['status'],
            'access_level' => $validated['accessLevel'],
            // Prix à l'unité : pris en compte uniquement si access_level=premium.
            // Sinon forcé à 0 pour qu'un article Free/Registered/Subscriber ne
            // puisse pas afficher un prix par erreur.
            'price_cents' => $validated['accessLevel'] === 'premium' ? (int) $validated['price'] * 100 : 0,
            'price_currency' => strtoupper($validated['priceCurrency']),
            'meta_title' => $validated['metaTitle'] ?? null,
            'meta_description' => $validated['metaDescription'] ?? null,
            'reading_time_minutes' => $validated['readingTime'] ?? null,
            'scheduled_at' => $validated['scheduledAt'] ?? null,
        ];

        if ($this->article?->exists) {
            $this->article->fill($data);
            if ($this->status === 'published' && $this->article->published_at === null) {
                $this->article->published_at = now();
            }
            $this->article->save();
        } else {
            $data['created_by_user_id'] = auth()->id();
            if ($this->status === 'published') {
                $data['published_at'] = now();
            }
            $this->article = Article::create($data);
        }

        $this->article->authors()->sync(
            collect($this->selectedAuthorIds)
                ->mapWithKeys(fn (int $id, int $position): array => [$id => ['position' => $position, 'role' => 'auteur']])
                ->toArray(),
        );
        $this->article->tags()->sync($this->selectedTagIds);

        $lastRevision = $this->article->versions()->max('revision') ?? 0;
        ArticleVersion::create([
            'article_id' => $this->article->id,
            'created_by_user_id' => auth()->id(),
            'revision' => $lastRevision + 1,
            'title' => $this->article->title,
            'lede' => $this->article->lede,
            'body' => $this->article->body,
        ]);

        $this->lastSavedAt = now()->format('H:i:s');

        session()->flash('status', 'Article enregistré.');
        $this->dispatch('article-saved');

        if (! request()->routeIs('admin.articles.edit')) {
            $this->redirectRoute('admin.articles.edit', ['article' => $this->article], navigate: true);
        }
    }

    private function authorizeRequest(): void
    {
        $user = auth()->user();
        abort_if($user === null, 401);

        if ($this->article?->exists) {
            abort_unless($user->can('update', $this->article), 403);
        } else {
            abort_unless($user->can('create', Article::class), 403);
        }
    }

    /**
     * Normalise différents formats historiques de body vers un doc TipTap valide :
     *   - Doc TipTap (type=doc) : inchangé.
     *   - Ancien format {blocks:[{type:'paragraph', content:'texte'}]} : converti.
     *   - Chaîne ou null : doc vide.
     *
     * @return array<string, mixed>
     */
    private function normaliseBodyForEditor(mixed $body): array
    {
        if (is_array($body) && ($body['type'] ?? null) === 'doc') {
            return $body;
        }

        if (is_array($body) && isset($body['blocks']) && is_array($body['blocks'])) {
            $content = [];
            foreach ($body['blocks'] as $block) {
                $text = (string) ($block['content'] ?? '');
                if ($text === '') {
                    continue;
                }
                $content[] = [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ];
            }

            return ['type' => 'doc', 'content' => $content];
        }

        return ['type' => 'doc', 'content' => []];
    }

    public function render(): View
    {
        return view('livewire.admin.articles.article-editor');
    }
}
