<?php

declare(strict_types=1);

use App\Services\TiptapRenderer;

beforeEach(function (): void {
    $this->renderer = new TiptapRenderer();
});

it('renvoie chaîne vide pour doc null', function (): void {
    expect($this->renderer->toHtml(null))->toBe('');
});

it('rend un paragraphe simple', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Bonjour']]],
        ],
    ];
    expect($this->renderer->toHtml($doc))->toBe('<p>Bonjour</p>');
});

it('échappe le HTML des nœuds text', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '<script>alert(1)</script>']]],
        ],
    ];
    expect($this->renderer->toHtml($doc))
        ->toContain('&lt;script&gt;')
        ->not->toContain('<script>');
});

it('applique les marques bold/italic en imbriquant les balises', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [[
                'type' => 'text',
                'text' => 'oui',
                'marks' => [['type' => 'bold'], ['type' => 'italic']],
            ]]],
        ],
    ];
    expect($this->renderer->toHtml($doc))->toContain('<em><strong>oui</strong></em>');
});

it('génère un lien sûr avec target _blank et rel noopener', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [[
                'type' => 'text',
                'text' => 'ici',
                'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://geniemag.ci']]],
            ]]],
        ],
    ];
    $html = $this->renderer->toHtml($doc);
    expect($html)->toContain('href="https://geniemag.ci"');
    expect($html)->toContain('target="_blank"');
    expect($html)->toContain('rel="noopener noreferrer"');
});

it('rejette les schémas d\'URL dangereux (javascript:)', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [[
                'type' => 'text',
                'text' => 'ici',
                'marks' => [['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']]],
            ]]],
        ],
    ];
    expect($this->renderer->toHtml($doc))->not->toContain('href="javascript:');
});

it('clamp le niveau des headings entre 2 et 4', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'big']]],
            ['type' => 'heading', 'attrs' => ['level' => 7], 'content' => [['type' => 'text', 'text' => 'tiny']]],
        ],
    ];
    $html = $this->renderer->toHtml($doc);
    expect($html)->toContain('<h2>big</h2>');
    expect($html)->toContain('<h4>tiny</h4>');
});

it('valide l\'ID YouTube avant d\'émettre l\'iframe', function (): void {
    $doc = [
        'type' => 'doc',
        'content' => [
            ['type' => 'youtubeEmbed', 'attrs' => ['videoId' => 'dQw4w9WgXcQ']],
            ['type' => 'youtubeEmbed', 'attrs' => ['videoId' => 'nope!!injection']],
        ],
    ];
    $html = $this->renderer->toHtml($doc);
    expect($html)->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ');
    expect($html)->not->toContain('nope!!injection');
});
