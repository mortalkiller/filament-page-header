@php
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    $slots = [];
    foreach (['leading', 'heading', 'badges', 'description', 'metadata', 'summary', 'default'] as $name) {
        $slots[$name] = $renderSlot($name);
    }
    $hasContent = static fn (string $html): bool => trim(strip_tags($html)) !== '' || preg_match('/<(?:img|svg|button|input)\\b/i', $html) === 1;
    $page = $getPage();
    $avatarUrl = $getAvatarUrl();
    $avatarName = $getAvatarName();
    $initials = $getInitials();
    $hasMetadata = $hasContent($slots['metadata']);
    $hasSummary = $hasContent($slots['summary']);
@endphp
<div {{ (new \Illuminate\View\ComponentAttributeBag($getExtraAttributes()))->class(['fph-layout']) }}>
    <div class="fph-top">
        <div class="fph-content">
            @if ($hasContent($slots['leading']))
                <div class="fph-slot fph-leading{{ $isImage() ? ' fph-image' : '' }}" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('leading') ? 'true' : 'false' }}">{!! $slots['leading'] !!}</div>
            @elseif ($avatarUrl || $initials !== '')
                <div class="fph-avatar{{ $isImage() ? ' fph-image' : '' }}" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('leading') ? 'true' : 'false' }}">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $avatarName }}" width="56" height="56" />
                    @else
                        <span aria-hidden="true">{{ $initials }}</span>
                    @endif
                </div>
            @endif
            <div class="fph-main">
                @if ($page)
                    {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, scopes: $page->getRenderHookScopes()) }}
                @endif
                <div class="fph-title-row">{!! $slots['heading'] !!}</div>
                @if ($hasContent($slots['description']))
                    <div class="fph-slot fph-description" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('description') ? 'true' : 'false' }}">{!! $slots['description'] !!}</div>
                @endif
                @if ($hasContent($slots['badges']))
                    <div class="fph-slot fph-inline fph-badges" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('badges') ? 'true' : 'false' }}">{!! $slots['badges'] !!}</div>
                @endif
                @if ($page)
                    {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, scopes: $page->getRenderHookScopes()) }}
                @endif
            </div>
        </div>
        @if ($page)
            @include('filament-page-header::actions', ['page' => $page])
        @endif
    </div>
    @if ($hasMetadata || $hasSummary)
        <div class="fph-details" data-fph-hide-compact="{{ (! $hasMetadata || $isSlotHiddenWhenCompact('metadata')) && (! $hasSummary || $isSlotHiddenWhenCompact('summary')) ? 'true' : 'false' }}">
            @if ($hasMetadata)
                <div class="fph-slot fph-metadata" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('metadata') ? 'true' : 'false' }}">{!! $slots['metadata'] !!}</div>
            @endif
            @if ($hasSummary)
                <div class="fph-slot fph-summary" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('summary') ? 'true' : 'false' }}">{!! $slots['summary'] !!}</div>
            @endif
        </div>
    @endif
    @if ($hasContent($slots['default']))
        <div class="fph-slot fph-default" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('default') ? 'true' : 'false' }}">{!! $slots['default'] !!}</div>
    @endif
</div>
