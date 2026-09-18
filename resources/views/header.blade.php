@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
@endphp
@if ($breadcrumbs)
    <div class="fph-breadcrumbs">
        <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
    </div>
@endif
<div
    class="fph-root"
    data-fph-root
    wire:ignore.self
    data-fph-options="{{ json_encode($options, JSON_THROW_ON_ERROR) }}"
    x-load
    x-load-src="{{ FilamentAsset::getAlpineComponentSrc('page-header', package: PageHeaderPlugin::PACKAGE) }}"
    x-data="pageHeader(@js($options))"
>
    <header class="fph-header fi-section" data-fph-header>
        <div class="fph-schema">
            @if (! $headerComponent)
                {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, scopes: $page->getRenderHookScopes()) }}
            @endif
            {{ $schema }}
            @if (! $headerComponent)
                {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, scopes: $page->getRenderHookScopes()) }}
                @include('filament-page-header::actions', ['page' => $page])
            @endif
        </div>
    </header>
</div>
