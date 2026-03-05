@props(['designer', 'assetPaths' => []])

<div x-show="activeTab === 'profile'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
    <x-profile.guest-banner
        :designer="$designer"
    />

    <x-profile.photo-section
        :designer="$designer"
        :asset-paths="$assetPaths"
    />

    <x-profile.info-section
        :designer="$designer"
    />

    <x-profile.skills-section
        :skills="($designer->skills ?? collect())->pluck('name')->toArray()"
    />

    @if(($designer->sector ?? '') === 'designer')
    <x-profile.certifications-section :designer="$designer" />
    @endif
</div>
