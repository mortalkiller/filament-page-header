@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
    $scopes = $page->getRenderHookScopes();
    $beforeActions = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, scopes: $scopes);
    $afterActions = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER, scopes: $scopes);
@endphp
<div
    class="fph-root"
    data-fph-root
    data-fph-options="{{ json_encode($options, JSON_THROW_ON_ERROR) }}"
    x-load
    x-load-src="{{ FilamentAsset::getAlpineComponentSrc('page-header', package: PageHeaderPlugin::PACKAGE) }}"
    x-data="pageHeader(@js($options))"
    x-load-css="[@js(FilamentAsset::getStyleHref('page-header', package: PageHeaderPlugin::PACKAGE))]"
>
    <header class="fi-header fph-header" data-fph-header>
        <div class="fph-content">
            @if ($breadcrumbs)
                <div class="fph-breadcrumbs" data-fph-hide-compact="{{ $options['hideBreadcrumbsWhenCompact'] ? 'true' : 'false' }}">
                    <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
                </div>
            @endif
            {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, scopes: $scopes) }}
            {{ $schema }}
            {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, scopes: $scopes) }}
        </div>
        @if ($actions || filled($beforeActions) || filled($afterActions))
            <div class="fi-header-actions-ctn fph-actions">
                {{ $beforeActions }}
                @if ($actions)
                    <x-filament::actions :actions="$actions" :alignment="$actionsAlignment" />
                @endif
                {{ $afterActions }}
            </div>
        @endif
    </header>
</div>
