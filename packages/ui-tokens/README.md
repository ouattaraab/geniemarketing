# @gm/ui-tokens

Design tokens partagés entre `web/` (Next.js) et `admin/` (React+Vite).

Source : extraits de `template/genie-marketing-v2.html` (charte issue du logo GÉNIE MARKETING Mag).

## Usage

### CSS vanilla

```css
@import '@gm/ui-tokens/tokens.css';

.button {
  background: var(--gm-red);
  font-family: var(--gm-font-mono);
}
```

### Tailwind CSS

```js
// tailwind.config.js
module.exports = {
  presets: [require('@gm/ui-tokens/tailwind.preset')],
  content: ['./src/**/*.{ts,tsx}'],
};
```

Classes disponibles : `bg-gm-red`, `text-gm-ink`, `font-slab`, `font-mono`, `max-w-container`, etc.

## Palette

| Token | Hex | Usage |
|---|---|---|
| `gm-red` | `#B40F1E` | Primaire, CTA, accents |
| `gm-red-bright` | `#D81B2A` | Hover, états actifs |
| `gm-red-deep` | `#8A0A15` | Pressed, contrastes forts |
| `gm-ink` | `#1A1A1A` | Texte principal |
| `gm-charcoal` | `#2D2D2D` | Texte secondaire |
| `gm-paper` | `#FAF8F4` | Fond principal |
| `gm-cream` | `#F2EFE8` | Fond alternatif |

## Typographies

| Rôle | Police | Usage |
|---|---|---|
| `font-slab` | Zilla Slab (italique) | Titres éditoriaux |
| `font-sans` | Mulish | Corps de texte |
| `font-mono` | JetBrains Mono | Labels, métadonnées, dates |

Polices à charger via Google Fonts ou self-hosted.
