@extends('academic.layouts.app')

@section('title', __('Trainings'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Trainings') }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Trainings') }}</h1>
            <p class="text-gray-500">{{ __('Manage your training programs') }}</p>
        </div>
        <a href="{{ route('academic.trainings.create', ['locale' => app()->getLocale()]) }}"
           class="px-4 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>{{ __('Add Training') }}
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Search trainings...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="all">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>
            <select name="expired" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">{{ __('All') }}</option>
                <option value="no" {{ request('expired') === 'no' ? 'selected' : '' }}>{{ __('Active Only') }}</option>
                <option value="yes" {{ request('expired') === 'yes' ? 'selected' : '' }}>{{ __('Expired Only') }}</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'status', 'expired']))
                <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <!-- Trainings List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Training') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Dates') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($trainings as $training)
                        <tr class="hover:bg-gray-50 {{ $training->is_expired ? 'bg-gray-50 opacity-75' : '' }}">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if($training->image)
                                        <img src="{{ asset('storage/' . $training->image) }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ Str::limit($training->title, 40) }}</p>
                                        <p class="text-sm text-gray-500">{{ $training->category ?? __('No category') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-800">{{ $training->start_date->format('M d, Y') }}</p>
                                @if($training->end_date)
                                    <p class="text-sm text-gray-500">{{ __('to') }} {{ $training->end_date->format('M d, Y') }}</p>
                                @endif
                                @if($training->is_expired)
                                    <span class="text-xs text-red-600 font-medium">{{ __('Expired') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($training->approval_status === 'approved') bg-green-100 text-green-700
                                    @elseif($training->approval_status === 'rejected') bg-red-100 text-red-700
                                    @else bg-orange-100 text-orange-700 @endif">
                                    {{ ucfirst($training->approval_status) }}
                                </span>
                                @if($training->approval_status === 'rejected' && $training->rejection_reason)
                                    <p class="text-xs text-red-600 mt-1">{{ Str::limit($training->rejection_reason, 30) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('academic.trainings.show', ['locale' => app()->getLocale(), 'id' => $training->id]) }}"
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('academic.trainings.edit', ['locale' => app()->getLocale(), 'id' => $training->id]) }}"
                                       class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('academic.trainings.destroy', ['locale' => app()->getLocale(), 'id' => $training->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this training?') }}')">
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
                            <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-chalkboard-teacher text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">{{ __('No trainings found') }}</p>
                                <p class="text-sm mb-4">{{ __('Start by creating your first training program') }}</p>
                                <a href="{{ route('academic.trainings.create', ['locale' => app()->getLocale()]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>{{ __('Add Training') }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trainings->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $trainings->links() }}</div>
        @endif
    </div>
</div>
@endsection
