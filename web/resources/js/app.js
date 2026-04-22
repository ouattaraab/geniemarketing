import './bootstrap';

// Livewire v3 embarque et démarre Alpine lui-même, puis expose window.Alpine.
// Ne pas importer alpinejs séparément — cela créerait une seconde instance
// et casse les composants Alpine (gmRichEditor, media-picker, etc.).
