@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    $actions = $page->getCachedHeaderActions();
    $before = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, scopes: $page->getRenderHookScopes());
    $after = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER, scopes: $page->getRenderHookScopes());
@endphp
@if ($actions || filled($before) || filled($after))
    <div class="fi-header-actions-ctn fph-actions">
        {{ $before }}
        @if ($actions)
            <x-filament::actions :actions="$actions" :alignment="Alignment::End" />
        @endif
        {{ $after }}
    </div>
@endif
