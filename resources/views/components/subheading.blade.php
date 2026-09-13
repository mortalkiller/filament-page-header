@php($content = $component->getContent())
@if (trim(strip_tags($content->toHtml())) !== '')
    <div {{ (new \Illuminate\View\ComponentAttributeBag($component->getExtraAttributes()))->class(['fi-header-subheading', 'fph-subheading']) }}>{{ $content }}</div>
@endif
