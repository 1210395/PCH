@extends('layout.main')

@section('title', __('Edit Profile'))

@section('content')
    <x-profile.edit-page
        :designer="$designer"
        :projects-data="$projectsData"
        :products-data="$productsData"
        :services-data="$servicesData"
    />
@endsection
