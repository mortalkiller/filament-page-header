@php
    $slots = [];
    foreach (['leading', 'heading', 'badges', 'subheading', 'metadata', 'trailing', 'default'] as $name) {
        $slots[$name] = $renderSlot($name);
    }
    $hasContent = static fn (string $html): bool => trim(strip_tags($html)) !== '' || preg_match('/<(?:img|svg|button|input)\\b/i', $html) === 1;
@endphp
<div {{ (new \Illuminate\View\ComponentAttributeBag($getExtraAttributes()))->class(['fph-layout']) }}>
    @if ($hasContent($slots['leading']))
        <div class="fph-leading" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('leading') ? 'true' : 'false' }}">{!! $slots['leading'] !!}</div>
    @endif
    <div class="fph-main">
        <div class="fph-title-row">
            {!! $slots['heading'] !!}
            @if ($hasContent($slots['badges']))
                <div class="fph-inline fph-badges" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('badges') ? 'true' : 'false' }}">{!! $slots['badges'] !!}</div>
            @endif
        </div>
        @foreach (['subheading', 'metadata', 'default'] as $name)
            @if ($hasContent($slots[$name]))
                <div class="fph-{{ $name }}{{ $name === 'metadata' ? ' fph-inline' : '' }}" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact($name) ? 'true' : 'false' }}">{!! $slots[$name] !!}</div>
            @endif
        @endforeach
    </div>
    @if ($hasContent($slots['trailing']))
        <div class="fph-trailing" data-fph-hide-compact="{{ $isSlotHiddenWhenCompact('trailing') ? 'true' : 'false' }}">{!! $slots['trailing'] !!}</div>
    @endif
</div>
