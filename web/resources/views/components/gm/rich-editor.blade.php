@props([
    'model',            // nom de la propriété Livewire à entangler (ex: 'body')
    'placeholder' => 'Commencez à rédiger…',
])

<div
    x-data="gmRichEditor({
        model: @entangle($model).live,
        placeholder: @js($placeholder),
    })"
    x-init="init($refs.mount)"
    wire:ignore
    class="gm-rich-editor border border-gm-gray-line bg-white"
>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 border-b border-gm-gray-line bg-gm-paper px-3 py-2 font-mono text-[11px] uppercase tracking-[0.1em]">
        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
            :class="editor?.isActive('heading', { level: 2 }) ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">H2</button>
        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
            :class="editor?.isActive('heading', { level: 3 }) ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">H3</button>
        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 4 }).run()"
            :class="editor?.isActive('heading', { level: 4 }) ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">H4</button>

        <span class="mx-1 h-4 w-px bg-gm-gray-line"></span>

        <button type="button" @click="editor.chain().focus().toggleBold().run()"
            :class="editor?.isActive('bold') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors font-bold">B</button>
        <button type="button" @click="editor.chain().focus().toggleItalic().run()"
            :class="editor?.isActive('italic') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors italic">I</button>
        <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
            :class="editor?.isActive('underline') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors underline">U</button>
        <button type="button" @click="editor.chain().focus().toggleStrike().run()"
            :class="editor?.isActive('strike') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors line-through">S</button>

        <span class="mx-1 h-4 w-px bg-gm-gray-line"></span>

        <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
            :class="editor?.isActive('bulletList') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">• Liste</button>
        <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
            :class="editor?.isActive('orderedList') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">1. Liste</button>
        <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
            :class="editor?.isActive('blockquote') ? 'bg-gm-ink text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">Citation</button>
        <button type="button" @click="editor.chain().focus().setHorizontalRule().run()"
            class="px-2 py-1 text-gm-charcoal transition-colors hover:bg-white">— Séparateur</button>

        <span class="mx-1 h-4 w-px bg-gm-gray-line"></span>

        <button type="button" @click="addLink()"
            :class="editor?.isActive('link') ? 'bg-gm-red text-white' : 'text-gm-charcoal hover:bg-white'"
            class="px-2 py-1 transition-colors">🔗 Lien</button>
        <button type="button" @click="addYoutube()"
            class="px-2 py-1 text-gm-charcoal transition-colors hover:bg-white">▶ YouTube</button>

        <span class="ml-auto font-mono text-[10px] text-gm-gray normal-case tracking-normal">
            <span x-text="wordCount"></span> mots · <span x-text="charCount"></span> car.
        </span>
    </div>

    {{-- Zone d'édition --}}
    <div x-ref="mount" class="gm-rich-editor-content prose prose-slate max-w-none px-5 py-4 min-h-[420px] focus-within:outline-none font-sans text-base leading-relaxed"></div>
</div>

@once
    @push('head')
        <script type="importmap">
        {
            "imports": {
                "@tiptap/core": "https://esm.sh/@tiptap/core@2.9.1",
                "@tiptap/starter-kit": "https://esm.sh/@tiptap/starter-kit@2.9.1",
                "@tiptap/extension-link": "https://esm.sh/@tiptap/extension-link@2.9.1",
                "@tiptap/extension-underline": "https://esm.sh/@tiptap/extension-underline@2.9.1",
                "@tiptap/extension-placeholder": "https://esm.sh/@tiptap/extension-placeholder@2.9.1"
            }
        }
        </script>
        <script type="module">
            import { Editor } from '@tiptap/core';
            import StarterKit from '@tiptap/starter-kit';
            import Link from '@tiptap/extension-link';
            import Underline from '@tiptap/extension-underline';
            import Placeholder from '@tiptap/extension-placeholder';

            window.gmRichEditor = function({ model, placeholder }) {
                return {
                    editor: null,
                    content: model,
                    wordCount: 0,
                    charCount: 0,

                    init(mountEl) {
                        this.editor = new Editor({
                            element: mountEl,
                            extensions: [
                                StarterKit,
                                Underline,
                                Link.configure({ openOnClick: false, HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' } }),
                                Placeholder.configure({ placeholder }),
                            ],
                            content: this.content && typeof this.content === 'object' && this.content.type
                                ? this.content
                                : (typeof this.content === 'string' && this.content.trim()
                                    ? { type: 'doc', content: [{ type: 'paragraph', content: [{ type: 'text', text: this.content }] }] }
                                    : undefined),
                            onUpdate: ({ editor }) => {
                                const json = editor.getJSON();
                                this.content = json;
                                this.wordCount = editor.getText().trim().split(/\s+/).filter(Boolean).length;
                                this.charCount = editor.getText().length;
                            },
                            editorProps: {
                                attributes: {
                                    class: 'focus:outline-none min-h-[380px]',
                                },
                            },
                        });

                        // État initial des compteurs
                        this.wordCount = this.editor.getText().trim().split(/\s+/).filter(Boolean).length;
                        this.charCount = this.editor.getText().length;
                    },

                    addLink() {
                        const url = window.prompt('URL du lien :');
                        if (url === null) return;
                        if (url === '') {
                            this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
                            return;
                        }
                        this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                    },

                    addYoutube() {
                        const url = window.prompt('Lien YouTube (ex: https://www.youtube.com/watch?v=XXXX) :');
                        if (!url) return;
                        const match = url.match(/(?:youtu\.be\/|v=|embed\/)([A-Za-z0-9_-]{6,20})/);
                        if (!match) { alert('Lien YouTube non reconnu.'); return; }
                        const videoId = match[1];
                        this.editor
                            .chain()
                            .focus()
                            .insertContent({ type: 'youtubeEmbed', attrs: { videoId } })
                            .run();
                    },
                };
            };
        </script>
        <style>
            .gm-rich-editor-content p.is-editor-empty:first-child::before {
                content: attr(data-placeholder);
                float: left;
                color: var(--gm-gray);
                pointer-events: none;
                height: 0;
            }
            .gm-rich-editor-content h2, .gm-rich-editor-content h3, .gm-rich-editor-content h4 {
                font-family: 'Zilla Slab', Georgia, serif;
                font-weight: 700;
                color: var(--gm-ink);
                margin-top: 1.5em;
            }
            .gm-rich-editor-content h2 { font-size: 1.8em; }
            .gm-rich-editor-content h3 { font-size: 1.4em; }
            .gm-rich-editor-content h4 { font-size: 1.15em; }
            .gm-rich-editor-content blockquote {
                border-left: 3px solid var(--gm-red);
                margin: 1.5em 0;
                padding: 0.25em 0 0.25em 1em;
                font-family: 'Zilla Slab', Georgia, serif;
                font-style: italic;
                color: var(--gm-charcoal);
            }
            .gm-rich-editor-content a { color: var(--gm-red); text-decoration: underline; }
        </style>
    @endpush
@endonce
