@php
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
@endphp
<div class="fph-sub-navigation" data-fph-sub-navigation data-fph-hide-compact="{{ $headerComponent->isSlotHiddenWhenCompact('subNavigation') ? 'true' : 'false' }}">
    <div class="fph-sub-navigation-mobile">
        {{ FilamentView::renderHook(PanelsRenderHook::PAGE_SUB_NAVIGATION_MOBILE_MENU_BEFORE, scopes: $page->getRenderHookScopes()) }}
        <x-filament-panels::page.sub-navigation.mobile-menu :navigation="$subNavigation" />
        {{ FilamentView::renderHook(PanelsRenderHook::PAGE_SUB_NAVIGATION_MOBILE_MENU_AFTER, scopes: $page->getRenderHookScopes()) }}
    </div>
    <div class="fph-sub-navigation-desktop">
        {{ FilamentView::renderHook(PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_BEFORE, scopes: $page->getRenderHookScopes()) }}
        <x-filament-panels::page.sub-navigation.tabs :navigation="$subNavigation" />
        {{ FilamentView::renderHook(PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_AFTER, scopes: $page->getRenderHookScopes()) }}
    </div>
</div>
