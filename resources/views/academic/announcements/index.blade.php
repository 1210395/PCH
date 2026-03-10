@extends('academic.layouts.app')

@section('title', __('Announcements'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Announcements') }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Announcements') }}</h1>
            <p class="text-gray-500">{{ __('Manage your announcements and notices') }}</p>
        </div>
        <a href="{{ route('academic.announcements.create', ['locale' => app()->getLocale()]) }}"
           class="px-4 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>{{ __('Add Announcement') }}
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 rtl:right-3 rtl:left-auto top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Search announcements...') }}"
                           class="w-full pl-10 rtl:pr-10 rtl:pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="all">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>
            <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="all">{{ __('All Categories') }}</option>
                <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>{{ __('General') }}</option>
                <option value="admission" {{ request('category') === 'admission' ? 'selected' : '' }}>{{ __('Admission') }}</option>
                <option value="event" {{ request('category') === 'event' ? 'selected' : '' }}>{{ __('Event') }}</option>
                <option value="scholarship" {{ request('category') === 'scholarship' ? 'selected' : '' }}>{{ __('Scholarship') }}</option>
                <option value="job" {{ request('category') === 'job' ? 'selected' : '' }}>{{ __('Job') }}</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
            </select>
            <select name="expired" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">{{ __('All') }}</option>
                <option value="no" {{ request('expired') === 'no' ? 'selected' : '' }}>{{ __('Active Only') }}</option>
                <option value="yes" {{ request('expired') === 'yes' ? 'selected' : '' }}>{{ __('Expired Only') }}</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'status', 'category', 'expired']))
                <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <!-- Announcements List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Announcement') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Category') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Dates') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($announcements as $announcement)
                        <tr class="hover:bg-gray-50 {{ $announcement->is_expired ? 'bg-gray-50 opacity-75' : '' }}">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if($announcement->image)
                                        <img src="{{ asset('storage/' . $announcement->image) }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                                            <i class="fas fa-bullhorn text-purple-600"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ Str::limit($announcement->title, 40) }}</p>
                                        @if($announcement->priority === 'urgent')
                                            <span class="text-xs text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ __('Urgent') }}</span>
                                        @elseif($announcement->priority === 'important')
                                            <span class="text-xs text-orange-600 font-medium"><i class="fas fa-star mr-1"></i>{{ __('Important') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($announcement->category === 'admission') bg-blue-100 text-blue-700
                                    @elseif($announcement->category === 'scholarship') bg-green-100 text-green-700
                                    @elseif($announcement->category === 'job') bg-purple-100 text-purple-700
                                    @elseif($announcement->category === 'event') bg-orange-100 text-orange-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ __(ucfirst($announcement->category ?? 'general')) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-800">{{ $announcement->publish_date->format('M d, Y') }}</p>
                                @if($announcement->expiry_date)
                                    <p class="text-sm text-gray-500">{{ __('Expires:') }} {{ $announcement->expiry_date->format('M d, Y') }}</p>
                                @endif
                                @if($announcement->is_expired)
                                    <span class="text-xs text-red-600 font-medium">{{ __('Expired') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($announcement->approval_status === 'approved') bg-green-100 text-green-700
                                    @elseif($announcement->approval_status === 'rejected') bg-red-100 text-red-700
                                    @else bg-orange-100 text-orange-700 @endif">
                                    {{ __(ucfirst($announcement->approval_status)) }}
                                </span>
                                @if($announcement->approval_status === 'rejected' && $announcement->rejection_reason)
                                    <p class="text-xs text-red-600 mt-1">{{ Str::limit($announcement->rejection_reason, 30) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('academic.announcements.show', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}"
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('academic.announcements.edit', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}"
                                       class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('academic.announcements.destroy', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this announcement?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-bullhorn text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">{{ __('No announcements found') }}</p>
                                <p class="text-sm mb-4">{{ __('Start by creating your first announcement') }}</p>
                                <a href="{{ route('academic.announcements.create', ['locale' => app()->getLocale()]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    <i class="fas fa-plus mr-2"></i>{{ __('Add Announcement') }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($announcements->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $announcements->links() }}</div>
        @endif
    </div>
</div>
@endsection
