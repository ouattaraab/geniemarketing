<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Taxonomies;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin', ['title' => 'Taxonomies · Rubriques'])]
#[Title('Rubriques — GM Admin')]
class CategoryList extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    // État modal create/edit
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $colorHex = '';
    public ?int $parentId = null;
    public int $position = 0;
    public bool $isActive = true;

    public function openCreate(?int $parentId = null): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description', 'colorHex', 'parentId', 'position', 'isActive']);
        $this->isActive = true;
        $this->parentId = $parentId;
        $this->position = Category::where('parent_id', $parentId)->max('position') + 1;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editingId = $cat->id;
        $this->name = $cat->name;
        $this->slug = $cat->slug;
        $this->description = $cat->description ?? '';
        $this->colorHex = $cat->color_hex ?? '';
        $this->parentId = $cat->parent_id;
        $this->position = $cat->position;
        $this->isActive = $cat->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function updatedName(string $value): void
    {
        if ($this->editingId === null && ($this->slug === '' || $this->slug === Str::slug($this->name ?? ''))) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['edit', 'adm', 'sup']), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash',
                'unique:categories,slug'.($this->editingId ? ','.$this->editingId : ''),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'colorHex' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'parentId' => ['nullable', 'exists:categories,id'],
            'position' => ['integer', 'min:0'],
            'isActive' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?: null,
            'color_hex' => $validated['colorHex'] ?: null,
            'parent_id' => $validated['parentId'] ?: null,
            'position' => $validated['position'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            Category::where('id', $this->editingId)->update($data);
            session()->flash('status', 'Rubrique mise à jour.');
        } else {
            Category::create($data);
            session()->flash('status', 'Rubrique créée.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['edit', 'adm', 'sup']), 403);

        $cat = Category::withCount(['children', 'articles'])->findOrFail($id);

        if ($cat->children_count > 0) {
            session()->flash('status', 'Impossible : cette rubrique contient des sous-rubriques.');
            return;
        }
        if ($cat->articles_count > 0) {
            session()->flash('status', 'Impossible : cette rubrique contient '.$cat->articles_count.' article(s).');
            return;
        }

        $cat->delete();
        session()->flash('status', 'Rubrique supprimée.');
    }

    #[Computed]
    public function roots(): Collection
    {
        $query = Category::query()
            ->whereNull('parent_id')
            ->withCount(['children', 'articles'])
            ->orderBy('position');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->get();
    }

    #[Computed]
    public function allCategories(): Collection
    {
        return Category::orderBy('position')->get();
    }

    public function render(): View
    {
        return view('livewire.admin.taxonomies.category-list');
    }
}
