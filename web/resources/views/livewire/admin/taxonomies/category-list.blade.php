<div>
    {{-- Filtres --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher une rubrique…"
                class="w-full border border-gm-gray-line bg-white px-4 py-2.5 font-sans text-sm focus:border-gm-red focus:outline-none"
            />
        </div>

        <div class="flex items-center gap-1 border border-gm-gray-line bg-white p-1 font-mono text-[11px] uppercase tracking-[0.1em]">
            @foreach (['all' => 'Tous', 'active' => 'Actifs', 'inactive' => 'Inactifs'] as $key => $label)
                <button wire:click="$set('status', '{{ $key }}')" class="px-3 py-1.5 transition-colors {{ $status === $key ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-gm-paper' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="ml-auto">
            <button wire:click="openCreate" class="gm-btn-primary">Nouvelle rubrique</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="border border-gm-gray-line bg-white">
        <table class="w-full">
            <thead class="border-b border-gm-gray-line bg-gm-paper">
                <tr class="gm-meta text-left">
                    <th class="px-6 py-3">Rubrique</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3 text-center">Sous-rubriques</th>
                    <th class="px-6 py-3 text-center">Articles</th>
                    <th class="px-6 py-3 text-center">Position</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gm-gray-line">
                @forelse ($this->roots as $category)
                    <tr class="hover:bg-gm-paper">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if ($category->color_hex)
                                    <span class="inline-block h-4 w-1" style="background: {{ $category->color_hex }}"></span>
                                @endif
                                <div>
                                    <div class="font-slab text-lg font-bold italic text-gm-ink">{{ $category->name }}</div>
                                    @if ($category->description)
                                        <div class="gm-meta mt-0.5 normal-case tracking-normal">{{ Str::limit($category->description, 60) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="bg-gm-cream px-2 py-0.5 font-mono text-xs text-gm-charcoal">/{{ $category->slug }}</code>
                        </td>
                        <td class="px-6 py-4 text-center font-mono text-sm text-gm-charcoal">{{ $category->children_count }}</td>
                        <td class="px-6 py-4 text-center font-mono text-sm text-gm-charcoal">{{ $category->articles_count }}</td>
                        <td class="px-6 py-4 text-center gm-meta">{{ $category->position }}</td>
                        <td class="px-6 py-4 text-center">
                            @if ($category->is_active)
                                <span class="gm-meta inline-block border border-gm-red bg-gm-red-soft px-2 py-0.5 text-gm-red-deep">Actif</span>
                            @else
                                <span class="gm-meta inline-block border border-gm-gray-line px-2 py-0.5 text-gm-gray">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="openEdit({{ $category->id }})" class="gm-meta hover:text-gm-red">Éditer</button>
                            <span class="gm-meta mx-2">·</span>
                            <button wire:click="openCreate({{ $category->id }})" class="gm-meta hover:text-gm-red">+ Sous-rubrique</button>
                            <span class="gm-meta mx-2">·</span>
                            <button wire:click="delete({{ $category->id }})" wire:confirm="Supprimer cette rubrique ?" class="gm-meta hover:text-gm-red">Suppr.</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center">
                            <p class="gm-meta">Aucune rubrique ne correspond à vos filtres.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gm-ink/80 px-4" wire:click.self="closeModal">
            <div class="w-full max-w-xl border border-gm-gray-line bg-white">
                <header class="flex items-center justify-between border-b border-gm-gray-line bg-gm-paper px-6 py-4">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">
                        {{ $editingId ? 'Éditer la rubrique' : 'Nouvelle rubrique' }}
                    </h3>
                    <button wire:click="closeModal" class="gm-meta hover:text-gm-red">Fermer ✕</button>
                </header>

                <form wire:submit="save" class="space-y-4 p-6">
                    <div>
                        <label class="gm-meta mb-2 block">Nom</label>
                        <input type="text" wire:model.live.debounce.500ms="name" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        @error('name')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="gm-meta mb-2 block">Slug</label>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm text-gm-gray">/rubriques/</span>
                            <input type="text" wire:model="slug" class="flex-1 border border-gm-gray-line px-3 py-2 font-mono text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                        @error('slug')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="gm-meta mb-2 block">Description (optionnelle)</label>
                        <textarea wire:model="description" rows="2" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0"></textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="gm-meta mb-2 block">Parent</label>
                            <select wire:model="parentId" class="w-full border border-gm-gray-line bg-white px-3 py-2 font-sans text-sm">
                                <option value="">— (racine)</option>
                                @foreach ($this->allCategories as $c)
                                    @if ($c->id !== $editingId)
                                        <option value="{{ $c->id }}">{{ $c->parent_id ? '— ' : '' }}{{ $c->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="gm-meta mb-2 block">Position</label>
                            <input type="number" wire:model="position" min="0" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                        <div>
                            <label class="gm-meta mb-2 block">Couleur (#hex)</label>
                            <input type="text" wire:model="colorHex" placeholder="#B40F1E" class="w-full border border-gm-gray-line px-3 py-2 font-mono text-sm focus:border-gm-red focus:ring-0" />
                            @error('colorHex')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="isActive" class="border-gm-gray-line text-gm-red focus:ring-gm-red" />
                            <span>Actif (visible côté public)</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gm-gray-line pt-4">
                        <button type="button" wire:click="closeModal" class="gm-meta hover:text-gm-red">Annuler</button>
                        <button type="submit" class="gm-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
