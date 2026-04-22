/**
 * GÉNIE MARKETING Mag — Preset Tailwind partagé
 * À importer dans web/ et admin/ via: presets: [require('@gm/ui-tokens/tailwind.preset')]
 */

module.exports = {
  theme: {
    extend: {
      colors: {
        gm: {
          red: '#B40F1E',
          'red-bright': '#D81B2A',
          'red-deep': '#8A0A15',
          'red-soft': '#FDECEE',
          ink: '#1A1A1A',
          charcoal: '#2D2D2D',
          'charcoal-2': '#4B4B4B',
          gray: '#7A7A7A',
          'gray-line': '#E5E2DC',
          'gray-soft': '#F1EEE7',
          paper: '#FAF8F4',
          cream: '#F2EFE8',
        },
      },
      fontFamily: {
        slab: ['Zilla Slab', 'Rockwell', 'Georgia', 'serif'],
        sans: ['Mulish', '-apple-system', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'Courier New', 'monospace'],
      },
      maxWidth: {
        container: '1400px',
        'container-narrow': '960px',
        prose: '720px',
      },
      boxShadow: {
        'gm-red': '0 4px 12px rgba(180, 15, 30, 0.3)',
      },
    },
  },
};
