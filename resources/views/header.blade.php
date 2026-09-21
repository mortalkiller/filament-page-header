@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
    use MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition;
    $breadcrumbPosition = $headerComponent?->getBreadcrumbPosition() ?? BreadcrumbPosition::Outside;
    $hideBreadcrumbsWhenCompact = $headerComponent?->isSlotHiddenWhenCompact('breadcrumbs') ?? true;
    $keepOutsideBreadcrumbs = $breadcrumbs && $breadcrumbPosition === BreadcrumbPosition::Outside && ! $hideBreadcrumbsWhenCompact;
@endphp
@if ($breadcrumbs && $breadcrumbPosition === BreadcrumbPosition::Outside && ! $keepOutsideBreadcrumbs)
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
    <div class="fph-surface" data-fph-surface>
        @if ($keepOutsideBreadcrumbs)
            <div class="fph-breadcrumbs">
                <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
            </div>
        @endif
        <header
            class="fph-header fi-section"
            data-fph-header
            @if ($subNavigation) data-fph-has-sub-navigation @endif
        >
            @if ($breadcrumbs && $breadcrumbPosition === BreadcrumbPosition::Inside)
                <div class="fph-breadcrumbs" data-fph-hide-compact="{{ $hideBreadcrumbsWhenCompact ? 'true' : 'false' }}">
                    <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
                </div>
            @endif
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
            @if ($subNavigation)
                @include('filament-page-header::sub-navigation')
            @endif
        </header>
    </div>
</div>
