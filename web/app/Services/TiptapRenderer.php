<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Convertit le JSON ProseMirror émis par TipTap en HTML sûr (les textes
 * sont échappés ; seules les marques/nœuds d'une liste blanche produisent
 * du balisage).
 *
 * Noeuds supportés : doc, paragraph, heading (2/3/4), bulletList, orderedList,
 * listItem, blockquote, horizontalRule, hardBreak, image, youtubeEmbed, codeBlock.
 * Marques : bold, italic, underline, strike, code, link (rel="noopener noreferrer", target="_blank").
 */
final class TiptapRenderer
{
    /**
     * @param array<string, mixed>|null $doc
     */
    public function toHtml(?array $doc): string
    {
        if ($doc === null) {
            return '';
        }

        // Support transparent du format legacy {blocks: [{type: 'paragraph', content: 'texte'}]}
        if (! isset($doc['type']) && isset($doc['blocks']) && is_array($doc['blocks'])) {
            $doc = $this->migrateLegacyToTiptap($doc['blocks']);
        }

        if (($doc['type'] ?? null) !== 'doc') {
            return '';
        }

        return $this->renderChildren($doc['content'] ?? []);
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @return array<string, mixed>
     */
    private function migrateLegacyToTiptap(array $blocks): array
    {
        $content = [];
        foreach ($blocks as $block) {
            $text = (string) ($block['content'] ?? '');
            if ($text === '') {
                continue;
            }
            $type = $block['type'] ?? 'paragraph';
            if ($type === 'heading') {
                $content[] = [
                    'type' => 'heading',
                    'attrs' => ['level' => 2],
                    'content' => [['type' => 'text', 'text' => $text]],
                ];
            } elseif ($type === 'quote') {
                $content[] = [
                    'type' => 'blockquote',
                    'content' => [
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $text]]],
                    ],
                ];
            } else {
                $content[] = [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ];
            }
        }

        return ['type' => 'doc', 'content' => $content];
    }

    public function toPlainText(?array $doc, int $maxChars = 0): string
    {
        if ($doc === null) {
            return '';
        }

        $text = $this->collectText($doc);

        return $maxChars > 0
            ? mb_substr($text, 0, $maxChars)
            : $text;
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     */
    private function renderChildren(array $nodes): string
    {
        $out = '';
        foreach ($nodes as $node) {
            $out .= $this->renderNode($node);
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderNode(array $node): string
    {
        $type = $node['type'] ?? null;

        return match ($type) {
            'paragraph' => '<p>'.$this->renderChildren($node['content'] ?? []).'</p>',
            'heading' => $this->renderHeading($node),
            'bulletList' => '<ul>'.$this->renderChildren($node['content'] ?? []).'</ul>',
            'orderedList' => '<ol>'.$this->renderChildren($node['content'] ?? []).'</ol>',
            'listItem' => '<li>'.$this->renderChildren($node['content'] ?? []).'</li>',
            'blockquote' => '<blockquote>'.$this->renderChildren($node['content'] ?? []).'</blockquote>',
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'codeBlock' => '<pre><code>'.$this->renderChildren($node['content'] ?? []).'</code></pre>',
            'image' => $this->renderImage($node),
            'youtubeEmbed' => $this->renderYoutube($node),
            'text' => $this->renderText($node),
            default => '',
        };
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderHeading(array $node): string
    {
        $level = (int) ($node['attrs']['level'] ?? 2);
        $level = max(2, min(4, $level));
        $children = $this->renderChildren($node['content'] ?? []);

        return "<h{$level}>{$children}</h{$level}>";
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderText(array $node): string
    {
        $text = e((string) ($node['text'] ?? ''));
        $marks = $node['marks'] ?? [];

        foreach ($marks as $mark) {
            $text = match ($mark['type'] ?? null) {
                'bold' => "<strong>$text</strong>",
                'italic' => "<em>$text</em>",
                'underline' => "<u>$text</u>",
                'strike' => "<s>$text</s>",
                'code' => "<code>$text</code>",
                'link' => $this->wrapLink($text, $mark['attrs'] ?? []),
                default => $text,
            };
        }

        return $text;
    }

    /**
     * @param array<string, mixed> $attrs
     */
    private function wrapLink(string $text, array $attrs): string
    {
        $href = (string) ($attrs['href'] ?? '#');
        if (! preg_match('~^(https?:|mailto:|/|#)~i', $href)) {
            return $text;
        }
        $href = e($href);

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            $href,
            $text,
        );
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderImage(array $node): string
    {
        $src = (string) ($node['attrs']['src'] ?? '');
        if ($src === '') {
            return '';
        }

        $alt = e((string) ($node['attrs']['alt'] ?? ''));
        $caption = (string) ($node['attrs']['caption'] ?? '');

        $html = sprintf('<img src="%s" alt="%s" loading="lazy">', e($src), $alt);
        if ($caption !== '') {
            $html = sprintf('<figure>%s<figcaption>%s</figcaption></figure>', $html, e($caption));
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderYoutube(array $node): string
    {
        $videoId = (string) ($node['attrs']['videoId'] ?? '');
        if (! preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId)) {
            return '';
        }

        return sprintf(
            '<div class="gm-youtube"><iframe src="https://www.youtube-nocookie.com/embed/%s" allowfullscreen loading="lazy" title="Vidéo YouTube"></iframe></div>',
            e($videoId),
        );
    }

    private function collectText(array $node): string
    {
        if (($node['type'] ?? null) === 'text') {
            return (string) ($node['text'] ?? '');
        }

        $text = '';
        foreach (($node['content'] ?? []) as $child) {
            $text .= $this->collectText($child).' ';
        }

        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }
}
