@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;
    $actions = $page->getCachedHeaderActions();
    $actionHeader = method_exists($page, 'getPageHeaderComponent') ? $page->getPageHeaderComponent() : null;
    $actions = $actionHeader?->prepareHeaderActions($actions) ?? $actions;
    $alignment = $actionHeader?->getActionsPosition() === HeaderActionsPosition::Start ? Alignment::Start : Alignment::End;
    $before = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, scopes: $page->getRenderHookScopes());
    $after = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER, scopes: $page->getRenderHookScopes());
    $hasHooks = filled($before) || filled($after);
@endphp
@if ($actions || $hasHooks)
    <div class="fi-header-actions-ctn fph-actions" data-fph-actions data-fph-actions-has-hooks="{{ $hasHooks ? 'true' : 'false' }}">
        {{ $before }}
        @if ($actions)
            <x-filament::actions :actions="$actions" :alignment="$alignment" />
        @endif
        {{ $after }}
    </div>
@endif
