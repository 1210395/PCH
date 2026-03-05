@props(['designer', 'isOwner'])

<!-- About Tab - REDESIGNED -->
<div id="about-tab" class="tab-content">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
        <div class="md:col-span-2 space-y-6">
            <!-- Bio Section -->
            <x-portfolio.bio-section
                :designer="$designer"
                :is-owner="$isOwner"
            />

            <!-- Skills Section -->
            <x-portfolio.skills-display
                :designer="$designer"
                :is-owner="$isOwner"
            />

            <!-- Certifications Section -->
            <x-portfolio.certifications-display
                :designer="$designer"
            />
        </div>

        <!-- Contact Info -->
        <x-portfolio.contact-section
            :designer="$designer"
        />
    </div>
</div>
