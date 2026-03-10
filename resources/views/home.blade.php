@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Discover Creative Talent') }}</title>
<meta name="description" content="{{ __('Discover talented designers, MSMEs, and creative professionals. Browse portfolios, projects, and connect with the creative community in Palestine.') }}">
<meta name="keywords" content="{{ __('designers, creative professionals, portfolio, Palestine, MSMEs, creative industries') }}">
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Palestine Creative Hub",
    "alternateName": "مركز فلسطين الإبداعي",
    "url": "{{ url('/') }}",
    "description": "A digital hub and marketplace supporting designers, MSMEs, and creative industries in Palestine. Connecting talent with opportunities.",
    "inLanguage": ["en", "ar"],
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url(app()->getLocale() . '/search') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Palestine Creative Hub",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ asset('images/logo.png') }}"
        }
    }
}
</script>
@endsection

@section('content')
{{-- Discover Wizard --}}
<x-home.discover-wizard />

{{-- Hero Section --}}
<x-home.hero :stats="$stats" :badgeCounter="$badgeCounter ?? null" :statsCounters="$statsCounters ?? null" />

{{-- Top Designers Section --}}
<x-home.top-designers :designers="$topDesigners" />

{{-- Featured Products Section --}}
<x-home.featured-products :products="$featuredProducts" />

{{-- Manufacturers & Showrooms Section --}}
<x-home.manufacturers-showrooms :manufacturers="$manufacturersShowrooms" />

{{-- Featured Projects Section --}}
<x-home.featured-projects :projects="$featuredProjects" />
@endsection
