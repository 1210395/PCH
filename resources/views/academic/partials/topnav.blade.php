@php
    $account = auth('academic')->user();
@endphp

<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between h-16 px-6">
        <!-- Left: Mobile menu toggle & Breadcrumb -->
        <div class="flex items-center gap-4">
            <!-- Mobile menu toggle -->
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden">
                <i class="fas fa-bars text-gray-600"></i>
            </button>

            <!-- Breadcrumb -->
            <nav class="hidden sm:flex items-center text-sm text-gray-500">
                <a href="{{ route('academic.dashboard', ['locale' => app()->getLocale()]) }}" class="hover:text-gray-700">
                    {{ __('Academic Portal') }}
                </a>
                @hasSection('breadcrumb')
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    @yield('breadcrumb')
                @endif
            </nav>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-4">
            <!-- View Site -->
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
               target="_blank"
               class="hidden sm:flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-external-link-alt"></i>
                {{ __('View Site') }}
            </a>

            <!-- Content Status Quick Access -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-gray-600"></i>
                    @php
                        $pendingCount = 0;
                        $rejectedCount = 0;
                        if ($account) {
                            $pendingCount = $account->trainings()->where('approval_status', 'pending')->count()
                                + $account->workshops()->where('approval_status', 'pending')->count()
                                + $account->announcements()->where('approval_status', 'pending')->count();
                            $rejectedCount = $account->trainings()->where('approval_status', 'rejected')->count()
                                + $account->workshops()->where('approval_status', 'rejected')->count()
                                + $account->announcements()->where('approval_status', 'rejected')->count();
                        }
                        $totalNotifications = $pendingCount + $rejectedCount;
                    @endphp
                    @if($totalNotifications > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-orange-500 text-white text-xs rounded-full flex items-center justify-center">
                            {{ $totalNotifications > 99 ? '99+' : $totalNotifications }}
                        </span>
                    @endif
                </button>

                <!-- Dropdown -->
                <div x-show="open"
                     x-transition
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">{{ __('Content Status') }}</h3>
                    </div>
                    <div class="py-2">
                        @if($pendingCount > 0)
                            <div class="px-4 py-2 flex items-center gap-2 text-orange-600">
                                <i class="fas fa-clock"></i>
                                <span>{{ $pendingCount }} {{ __('item(s) pending approval') }}</span>
                            </div>
                        @endif
                        @if($rejectedCount > 0)
                            <div class="px-4 py-2 flex items-center gap-2 text-red-600">
                                <i class="fas fa-times-circle"></i>
                                <span>{{ $rejectedCount }} {{ __('item(s) need revision') }}</span>
                            </div>
                        @endif
                        @if($totalNotifications === 0)
                            <div class="px-4 py-2 text-gray-500 text-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                {{ __('All content is up to date') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    @if($account && $account->logo)
                        <img src="{{ url('media/' . $account->logo) }}" alt="{{ $account->name }}" class="w-8 h-8 rounded-full object-cover">
                    @else
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr($account->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open"
                     x-transition
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="font-medium text-gray-800 truncate">{{ $account->name ?? __('Institution') }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $account->email ?? '' }}</p>
                    </div>
                    <a href="{{ route('academic.profile.edit', ['locale' => app()->getLocale()]) }}"
                       class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user-cog w-4"></i>
                        {{ __('Profile Settings') }}
                    </a>
                    <form action="{{ route('academic.logout', ['locale' => app()->getLocale()]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-4"></i>
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
