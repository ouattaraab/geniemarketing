@props(['article'])

@php
    $url = rawurlencode(route('article.show', $article));
    $title = rawurlencode($article->title);
    $text = rawurlencode($article->title.' — '.($article->lede ?? ''));
@endphp

<div
    x-data="{ copied: false, copy() {
        navigator.clipboard.writeText('{{ route('article.show', $article) }}').then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        });
    } }"
    class="flex flex-wrap items-center gap-2"
>
    <span class="gm-meta mr-2">Partager</span>

    <a
        href="https://www.linkedin.com/sharing/share-offsite/?url={{ $url }}"
        target="_blank" rel="noopener noreferrer"
        class="gm-meta border border-gm-gray-line bg-white px-3 py-2 transition hover:border-gm-red hover:text-gm-red"
        title="Partager sur LinkedIn"
    >LinkedIn</a>

    <a
        href="https://x.com/intent/tweet?url={{ $url }}&text={{ $title }}&via=geniemagci"
        target="_blank" rel="noopener noreferrer"
        class="gm-meta border border-gm-gray-line bg-white px-3 py-2 transition hover:border-gm-red hover:text-gm-red"
        title="Partager sur X"
    >X</a>

    <a
        href="https://wa.me/?text={{ $text }}%20{{ $url }}"
        target="_blank" rel="noopener noreferrer"
        class="gm-meta border border-gm-gray-line bg-white px-3 py-2 transition hover:border-gm-red hover:text-gm-red"
        title="Partager sur WhatsApp"
    >WhatsApp</a>

    <a
        href="mailto:?subject={{ $title }}&body={{ $text }}%20{{ $url }}"
        class="gm-meta border border-gm-gray-line bg-white px-3 py-2 transition hover:border-gm-red hover:text-gm-red"
        title="Partager par email"
    >Email</a>

    <button
        type="button"
        @click="copy()"
        class="gm-meta border border-gm-gray-line bg-white px-3 py-2 transition hover:border-gm-red hover:text-gm-red"
        :class="copied && 'border-gm-red text-gm-red'"
    >
        <span x-show="! copied">Copier le lien</span>
        <span x-show="copied" x-cloak>Lien copié ✓</span>
    </button>
</div>
