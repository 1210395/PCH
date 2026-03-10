@extends('layout.main')

@php
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $designerName = $sanitize($designer->name ?? '');
@endphp

@section('title', $designerName . ' - ' . __('Designer Portfolio'))

@section('head')
@php
    $ogAvatar = $designer->avatar ? asset('storage/' . $designer->avatar) : asset('images/logo.png');
    $ogBio = \App\Helpers\DropdownHelper::sanitizeUtf8(Str::limit($designer->bio ?? '', 160));
@endphp
<meta property="og:title" content="{{ $designerName }} - {{ __('Designer Portfolio') }}">
<meta property="og:description" content="{{ $ogBio }}">
<meta property="og:image" content="{{ $ogAvatar }}">
<meta property="og:type" content="profile">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $designerName }}">
<meta name="twitter:description" content="{{ $ogBio }}">
<meta name="twitter:image" content="{{ $ogAvatar }}">
@endsection

@section('content')
@php
    $assetPaths = [
        'avatar' => $designer->avatar ? asset('storage/' . $designer->avatar) : null,
        'cover' => $designer->cover_image ? asset('storage/' . $designer->cover_image) : null,
    ];
    $isOwner = auth('designer')->check() && auth('designer')->id() == $designer->id;
@endphp

<x-portfolio.view-page
    :designer="$designer"
    :projects-data="$projectsData"
    :products-data="$productsData"
    :services-data="$servicesData"
    :marketplace-data="$marketplaceData ?? []"
    :asset-paths="$assetPaths"
    :is-owner="$isOwner"
/>
@endsection
