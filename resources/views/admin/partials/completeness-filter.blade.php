{{-- Completeness filter dropdown — include in admin index forms --}}
<select name="completeness" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
    <option value="" {{ !request('completeness') ? 'selected' : '' }}>{{ __('All Records') }}</option>
    <option value="complete" {{ request('completeness') === 'complete' ? 'selected' : '' }}>{{ __('Complete') }}</option>
    <option value="incomplete" {{ request('completeness') === 'incomplete' ? 'selected' : '' }}>{{ __('Incomplete') }}</option>
    <option value="others" {{ request('completeness') === 'others' ? 'selected' : '' }}>{{ __('Has "Other"') }}</option>
</select>
