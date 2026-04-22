<x-layouts.public :title="'Lecture · '.$issue->title">
    <div class="relative">
        {{-- Barre de lecture --}}
        <div class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-gm-gray-line bg-white px-6 py-3">
            <div>
                <a href="{{ route('magazine') }}" class="gm-meta hover:text-gm-red">← Magazine</a>
                <div class="mt-1 font-slab text-lg font-bold italic text-gm-ink">
                    N° {{ $issue->number }} · {{ $issue->title }}
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="gm-meta">Page <span id="gm-page">1</span>/<span id="gm-total">…</span></span>
                <button id="gm-prev" class="gm-meta border border-gm-gray-line bg-white px-3 py-1 hover:border-gm-red">◀</button>
                <button id="gm-next" class="gm-meta border border-gm-gray-line bg-white px-3 py-1 hover:border-gm-red">▶</button>
            </div>
        </div>

        {{-- Zone de lecture avec overlay watermark --}}
        <div id="gm-reader" class="relative mx-auto max-w-4xl bg-gm-cream py-6" data-pdf-url="{{ $pdfStreamUrl }}">
            <div id="gm-canvas-wrap" class="relative mx-auto select-none" oncontextmenu="return false;">
                <canvas id="gm-canvas" class="block mx-auto shadow-lg"></canvas>
                {{-- Watermark permanent (diagonale, CSS-only pour ne pas être retiré facilement) --}}
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    @php
                        $lines = str_split(str_repeat($watermark.'   ', 30), 2000);
                    @endphp
                    <div class="absolute inset-0 flex flex-wrap opacity-10 text-gm-ink rotate-[-25deg] origin-center font-mono text-[10px] leading-loose whitespace-nowrap">
                        @for ($i = 0; $i < 12; $i++)
                            <div class="w-full truncate">{{ $watermark }} · {{ $watermark }} · {{ $watermark }}</div>
                        @endfor
                    </div>
                </div>
            </div>

            <p class="gm-meta mt-6 text-center">
                Lecture réservée à <strong class="text-gm-ink">{{ auth()->user()->email }}</strong> · Tout partage non autorisé est tracé.
            </p>
        </div>
    </div>

    @push('head')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.6.82/pdf.min.mjs" type="module"></script>
        <script type="module">
            import * as pdfjsLib from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.6.82/pdf.min.mjs';
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.6.82/pdf.worker.min.mjs';

            const reader = document.getElementById('gm-reader');
            const pdfUrl = reader.dataset.pdfUrl;
            const canvas = document.getElementById('gm-canvas');
            const pageLabel = document.getElementById('gm-page');
            const totalLabel = document.getElementById('gm-total');
            const prevBtn = document.getElementById('gm-prev');
            const nextBtn = document.getElementById('gm-next');

            let pdf = null;
            let currentPage = 1;
            let rendering = false;

            async function render(pageNum) {
                if (rendering || !pdf) return;
                rendering = true;
                const page = await pdf.getPage(pageNum);
                const viewport = page.getViewport({ scale: Math.min(2, window.devicePixelRatio + 0.5) });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.style.width = Math.min(viewport.width / (window.devicePixelRatio + 0.5), 960) + 'px';
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                pageLabel.textContent = pageNum;
                rendering = false;
            }

            (async () => {
                try {
                    const task = pdfjsLib.getDocument({ url: pdfUrl, disableAutoFetch: true });
                    pdf = await task.promise;
                    totalLabel.textContent = pdf.numPages;
                    await render(currentPage);
                } catch (e) {
                    reader.innerHTML = '<p class="gm-meta p-6 text-center">Impossible de charger le document. Merci de rafraîchir la page.</p>';
                    console.error(e);
                }
            })();

            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) { currentPage--; render(currentPage); }
            });
            nextBtn.addEventListener('click', () => {
                if (pdf && currentPage < pdf.numPages) { currentPage++; render(currentPage); }
            });

            // Désactiver le raccourci de téléchargement PDF natif (ne protège pas à 100% mais ralentit)
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && ['s', 'p'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                }
            });
        </script>
    @endpush
</x-layouts.public>
