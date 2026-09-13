@php($content = $component->getContent())
@if (trim(strip_tags($content->toHtml())) !== '')
    <h1 {{ (new \Illuminate\View\ComponentAttributeBag($component->getExtraAttributes()))->class(['fi-header-heading', 'fph-heading']) }}>
        @if ($icon = $component->getIcon())
            <span class="fph-heading-icon" aria-hidden="true">{{ \Filament\Support\generate_icon_html($icon) }}</span>
        @endif
        {{ $content }}
    </h1>
@endif
