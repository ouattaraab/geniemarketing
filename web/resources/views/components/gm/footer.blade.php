<footer class="mt-20 border-t border-gm-gray-line bg-gm-ink text-gm-paper">
    <div class="mx-auto max-w-container px-8 py-12">
        <div class="grid gap-10 md:grid-cols-[2fr_1fr_1fr_1fr]">
            <div>
                <h2 class="font-slab text-2xl font-bold italic leading-none">
                    GÉNIE <span class="text-gm-red-bright">MARKETING</span>
                </h2>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-gm-paper/70">
                    Décrypter, inspirer, transformer — le magazine de référence du marketing ivoirien et panafricain.
                </p>
            </div>

            <div>
                <h3 class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red-bright">Rubriques</h3>
                <ul class="mt-4 space-y-2 text-sm text-gm-paper/75">
                    <li><a href="#" class="hover:text-white">La Une</a></li>
                    <li><a href="#" class="hover:text-white">Analyses</a></li>
                    <li><a href="#" class="hover:text-white">Succès</a></li>
                    <li><a href="#" class="hover:text-white">Interviews</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red-bright">Abonnement</h3>
                <ul class="mt-4 space-y-2 text-sm text-gm-paper/75">
                    <li><a href="#" class="hover:text-white">Digital — 24 000 FCFA/an</a></li>
                    <li><a href="#" class="hover:text-white">Combo papier + digital — 48 000 FCFA/an</a></li>
                    <li><a href="#" class="hover:text-white">Entreprise — sur mesure</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red-bright">Magazine</h3>
                <ul class="mt-4 space-y-2 text-sm text-gm-paper/75">
                    <li><a href="{{ route('legal.mentions') }}" class="hover:text-white">Mentions légales</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-white">Politique de confidentialité</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-white">CGU & CGV</a></li>
                    <li><a href="{{ route('legal.cookies') }}" class="hover:text-white">Cookies</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 border-t border-gm-paper/10 pt-6 font-mono text-[11px] uppercase tracking-[0.1em] text-gm-paper/50">
            © {{ date('Y') }} GÉNIE MARKETING Mag — Tous droits réservés · Abidjan, Côte d'Ivoire
        </div>
    </div>
</footer>
