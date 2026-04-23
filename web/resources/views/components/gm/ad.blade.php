@props([
    'placement' => 'article_top',
    'class' => '',
])

@php
    $ad = \App\Models\Advertisement::pickForPlacement($placement);
@endphp

@if ($ad)
    @php
        // Incrémente l'impression de manière non bloquante, après la sélection.
        $ad->trackImpression();
        $trackUrl = route('ad.click', $ad);
        $imgUrl = $ad->imageUrl();
        $relParts = array_filter([
            $ad->link_nofollow ? 'nofollow' : null,
            $ad->link_nofollow ? 'sponsored' : null,
            'noopener',
            $ad->link_new_tab ? 'noreferrer' : null,
        ]);
    @endphp
    <aside class="gm-ad gm-ad--{{ str_replace('_', '-', $placement) }} my-6 border border-gm-gray-line bg-white p-4 text-center {{ $class }}"
           aria-label="Contenu sponsorisé">
        <a href="{{ $trackUrl }}"
           @if ($ad->link_new_tab) target="_blank" @endif
           rel="{{ implode(' ', $relParts) }}"
           class="block">
            @if ($imgUrl)
                <img src="{{ $imgUrl }}"
                     alt="{{ $ad->alt_text ?? $ad->title }}"
                     loading="lazy"
                     class="mx-auto max-h-40 w-auto" />
            @else
                <span class="font-slab text-lg italic text-gm-ink">{{ $ad->title }}</span>
            @endif
        </a>
        <p class="gm-meta mt-2 normal-case tracking-normal text-gm-gray">
            @if ($ad->sponsor_name)
                Sponsorisé par {{ $ad->sponsor_name }}
            @else
                Publicité
            @endif
        </p>
    </aside>
@endif
