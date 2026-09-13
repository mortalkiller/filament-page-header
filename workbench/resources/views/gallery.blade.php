<x-filament-panels::page>
    <div data-demo-content style="min-width:0;display:grid;gap:1.5rem">
        <nav aria-label="Header variants" style="display:flex;flex-wrap:wrap;gap:.75rem">
            @foreach (range(1, 10) as $example)
                <a href="{{ '/demo/headers?variant='.$example.'&mode='.$this->mode }}" wire:navigate>Example {{ $example }}</a>
            @endforeach
            <a href="/demo/native" wire:navigate>Native page</a>
        </nav>
        <nav aria-label="Scroll modes" style="display:flex;flex-wrap:wrap;gap:.75rem">
            @foreach (['normal', 'sticky', 'compact'] as $mode)
                <a href="{{ '/demo/headers?variant='.$this->variant.'&mode='.$mode }}" wire:navigate>{{ ucfirst($mode) }} mode</a>
            @endforeach
        </nav>
        <form id="demo-form" wire:submit="save" style="display:grid;gap:1rem">
            <label>Unsaved note
                <input aria-label="Unsaved note" wire:model="note" style="display:block;border:1px solid #9ca3af;border-radius:.5rem;padding:.75rem;width:100%;max-width:32rem;background:transparent" />
            </label>
            @error('note')<p role="alert">{{ $message }}</p>@enderror
            <p data-save-count>Saved {{ $this->saveCount }} times</p>
            <p data-inline-count>Inline action {{ $this->inlineCount }} times</p>
        </form>
        @foreach (range(1, 18) as $section)
            <section style="min-height:9rem;padding:1rem;border:1px solid #9ca3af;border-radius:.75rem">
                <h2>Content section {{ $section }}</h2>
                <p>Scroll this independent workbench to verify the header behavior.</p>
                <label>Field {{ $section }} <input id="field-{{ $section }}" aria-label="Field {{ $section }}" style="border:1px solid #9ca3af;padding:.5rem;background:transparent;max-width:100%" /></label>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
