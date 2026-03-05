@extends('layout.main')

@section('content')
<x-portfolio.view-page
    :designer="$designer"
    :projects-data="$projectsData"
    :products-data="$productsData"
    :services-data="$servicesData"
/>
@endsection
