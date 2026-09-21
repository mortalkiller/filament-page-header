<x-filament-panels::page>
    <div data-navigation-content style="min-height: 1800px">
        <label for="navigation-note">Unsaved navigation note</label>
        <input id="navigation-note" wire:model="note" />
        <x-filament::button wire:click="$toggle('retain')">Toggle compact navigation</x-filament::button>
        <p>The header links use native Filament SPA navigation.</p>
    </div>
</x-filament-panels::page>
