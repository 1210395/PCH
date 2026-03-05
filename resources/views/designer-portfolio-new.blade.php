@extends('layout.main')

@php
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $designerName = $sanitize($designer->name ?? '');
@endphp

@section('title', $designerName . ' - ' . __('Designer Portfolio'))

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
