@php
    $account = auth('academic')->user();
    $currentRoute = request()->route()->getName();
@endphp

<aside class="fixed {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} top-0 h-full bg-gradient-to-b from-green-800 to-blue-900 text-white transition-all duration-300 z-50"
       :class="{ 'w-64': sidebarOpen, 'w-20': !sidebarOpen }">

    <!-- Logo -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            @if($account && $account->logo)
                <img src="{{ url('media/' . $account->logo) }}" alt="{{ $account->name }}" class="w-10 h-10 rounded-lg object-cover">
            @else
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fas fa-university text-xl"></i>
                </div>
            @endif
            <span class="font-bold text-lg" x-show="sidebarOpen" x-transition>{{ __('Academic Portal') }}</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-white/10 transition-colors hidden lg:block">
            <i class="fas" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('academic.dashboard', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-white/10
                  {{ $currentRoute === 'academic.dashboard' ? 'active text-white' : '' }}">
            <i class="fas fa-tachometer-alt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition>{{ __('Dashboard') }}</span>
        </a>

        <!-- Content Management -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-white/50 uppercase tracking-wider mb-2" x-show="sidebarOpen" x-transition>
                {{ __('Content Management') }}
            </p>
        </div>

        <!-- Trainings -->
        <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-white/10
                  {{ str_starts_with($currentRoute, 'academic.trainings') ? 'active text-white' : '' }}">
            <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition>{{ __('Trainings') }}</span>
        </a>

        <!-- Workshops -->
        <a href="{{ route('academic.workshops.index', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-white/10
                  {{ str_starts_with($currentRoute, 'academic.workshops') ? 'active text-white' : '' }}">
            <i class="fas fa-tools w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition>{{ __('Workshops') }}</span>
        </a>

        <!-- Announcements -->
        <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-white/10
                  {{ str_starts_with($currentRoute, 'academic.announcements') ? 'active text-white' : '' }}">
            <i class="fas fa-bullhorn w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition>{{ __('Announcements') }}</span>
        </a>

        <!-- Settings Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-white/50 uppercase tracking-wider mb-2" x-show="sidebarOpen" x-transition>
                {{ __('Settings') }}
            </p>
        </div>

        <!-- Profile -->
        <a href="{{ route('academic.profile.edit', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-white/10
                  {{ str_starts_with($currentRoute, 'academic.profile') ? 'active text-white' : '' }}">
            <i class="fas fa-user-cog w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition>{{ __('Profile') }}</span>
        </a>

        <!-- Logout -->
        <form action="{{ route('academic.logout', ['locale' => app()->getLocale()]) }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-red-500/30 text-red-300">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition>{{ __('Logout') }}</span>
            </button>
        </form>
    </nav>

    <!-- Institution Info (at bottom) -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10" x-show="sidebarOpen" x-transition>
        <div class="flex items-center gap-3">
            @if($account && $account->logo)
                <img src="{{ url('media/' . $account->logo) }}" alt="{{ $account->name }}" class="w-10 h-10 rounded-lg object-cover">
            @else
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    {{ strtoupper(substr($account->name ?? 'A', 0, 2)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm truncate">{{ $account->name ?? 'Institution' }}</p>
                <p class="text-xs text-white/50 truncate">{{ $account->institution_type_label ?? '' }}</p>
            </div>
        </div>
    </div>
</aside>
