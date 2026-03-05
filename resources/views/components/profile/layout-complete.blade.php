@props(['designer', 'projectsData', 'productsData', 'servicesData'])

{{-- This file contains the complete Alpine.js state from the original profile-edit-alpine-data.blade.php --}}
{{-- It's kept as a reference and can be integrated gradually --}}

<div class="min-h-screen bg-gray-50" x-data="{
    activeTab: 'profile',
    saving: false,
    form: @js([
        'name' => $designer->name,
        'title' => $designer->title ?? '',
        'bio' => $designer->bio ?? '',
        'email' => $designer->email,
        'phone' => $designer->phone_number ?? '',
        'city' => $designer->city ?? '',
        'address' => $designer->address ?? '',
        'website' => $designer->website ?? '',
        'avatarPath' => null,
        'avatarPreview' => null,
        'avatarUploading' => false,
        'coverPath' => null,
        'coverPreview' => null,
        'coverUploading' => false
    ]),
    skills: @js(($designer->skills ?? collect())->pluck('name')->toArray()),
    selectedSkill: '',
    customSkill: '',
    skillOptions: @js(App\View\Components\Profile\SkillsSection::getSkillOptions()),
    projects: @js($projectsData),
    products: @js($productsData),
    services: @js($servicesData),
    projectModal: false,
    projectForm: {id: null, title: '', description: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false},
    projectSubmitting: false,
    productModal: false,
    productForm: {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false},
    productSubmitting: false,
    serviceModal: false,
    serviceForm: {id: null, name: '', description: '', category: '', customCategory: '', imagePath: null, imagePreview: null, uploading: false},
    serviceSubmitting: false,
    uploadSession: '{{ App\View\Components\Profile\Layout::generateUUID() }}',

    init() {
    }
}" x-init="init()">
    {{ $slot }}
</div>

{{-- Note: The Alpine.js methods (saveProfile, openProjectModal, etc.) need to be added to this file --}}
{{-- For now, they remain in the original profile-edit-alpine-data.blade.php --}}
{{-- This is a transitional architecture where we use components for structure but keep Alpine logic centralized --}}
