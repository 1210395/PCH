@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Discover Creative Talent') }}</title>
<meta name="description" content="{{ __('Discover talented designers, MSMEs, and creative professionals. Browse portfolios, projects, and connect with the creative community in Palestine.') }}">
<meta name="keywords" content="{{ __('designers, creative professionals, portfolio, Palestine, MSMEs, creative industries') }}">
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
