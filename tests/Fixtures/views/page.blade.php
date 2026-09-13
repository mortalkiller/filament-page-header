<x-filament-panels::page>
    <p>Page content</p>
    @if (property_exists($this, 'note'))
        <input wire:model="note" aria-label="Note" />
    @endif
</x-filament-panels::page>
