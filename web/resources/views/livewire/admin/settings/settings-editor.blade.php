<div>
    @php
        $groupLabels = [
            'branding' => ['Identité', 'Baseline, copyright et ton éditorial.'],
            'contact' => ['Contact', 'Coordonnées affichées sur le site et dans les mails.'],
            'legal' => ['Mentions légales', 'Éditeur, DPO, lien mentions.'],
            'social' => ['Réseaux sociaux', 'Liens vers vos profils publics.'],
            'promo' => ['Bannière promo', 'Bannière en tête de page pour une offre limitée.'],
            'paywall' => ['Paywall & freemium', 'Quota gratuit et règles d\'accès.'],
        ];
    @endphp

    <form wire:submit="save" class="space-y-8">
        @foreach ($this->grouped as $group => $settings)
            @php [$label, $help] = $groupLabels[$group] ?? [ucfirst($group), null]; @endphp

            <section class="border border-gm-gray-line bg-white">
                <header class="border-b border-gm-gray-line bg-gm-paper px-6 py-4">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">{{ $label }}</h3>
                    @if ($help)
                        <p class="gm-meta mt-1 normal-case tracking-normal">{{ $help }}</p>
                    @endif
                </header>

                <div class="grid gap-5 p-6 md:grid-cols-2">
                    @foreach ($settings as $setting)
                        <div class="{{ in_array($setting->type, ['text', 'json']) ? 'md:col-span-2' : '' }}">
                            <label class="gm-meta mb-2 block">{{ $setting->label }}</label>
                            @switch($setting->type)
                                @case('text')
                                    <textarea
                                        wire:model="values.{{ $setting->key }}"
                                        rows="3"
                                        class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0"
                                    ></textarea>
                                    @break

                                @case('boolean')
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="values.{{ $setting->key }}" class="border-gm-gray-line text-gm-red focus:ring-gm-red" />
                                        <span>Activé</span>
                                    </label>
                                    @break

                                @case('integer')
                                    <input type="number" wire:model="values.{{ $setting->key }}" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                                    @break

                                @case('email')
                                    <input type="email" wire:model="values.{{ $setting->key }}" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                                    @break

                                @case('url')
                                    <input type="url" wire:model="values.{{ $setting->key }}" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" placeholder="https://…" />
                                    @break

                                @default
                                    <input type="text" wire:model="values.{{ $setting->key }}" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                            @endswitch

                            @error('values.'.$setting->key)<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror

                            @if ($setting->description)
                                <p class="gm-meta mt-1 normal-case tracking-normal">{{ $setting->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex items-center justify-end gap-3">
            <button type="submit" wire:loading.attr="disabled" class="gm-btn-primary">
                <span wire:loading.remove wire:target="save">Enregistrer les paramètres</span>
                <span wire:loading wire:target="save">Enregistrement…</span>
            </button>
        </div>
    </form>
</div>
