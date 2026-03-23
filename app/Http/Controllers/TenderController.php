<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Http\Request;

/**
 * Displays admin-managed tender listings and detail pages.
 * Tenders do not require designer approval; they are managed directly through the admin panel.
 */
class TenderController extends Controller
{
    /**
     * Show the paginated tender listing with status/publisher-type filters, search, and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validated = $request->validate([
            'status' => 'nullable|string|in:open,closing_soon,closed',
            'publisher_type' => 'nullable|string|in:government,ngo,private,academic,media,other',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:deadline,published_date,title',
        ]);

        // Query all tenders (admin-managed, no approval needed)
        $query = Tender::query();

        // Filter by language based on current locale
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            $query->whereRaw("title REGEXP '[ء-ي]'");
        } else {
            $query->whereRaw("title NOT REGEXP '[ء-ي]'");
        }

        // Filter by status
        if (!empty($validated['status']) && $validated['status'] !== 'all') {
            $query->byStatus($validated['status']);
        }

        // Filter by publisher type
        if (!empty($validated['publisher_type']) && $validated['publisher_type'] !== 'all') {
            $query->byPublisherType($validated['publisher_type']);
        }

        // Search
        if (!empty($validated['search'])) {
            $searchTerm = strip_tags($validated['search']);
            $query->search($searchTerm);
        }

        // Sort
        $sort = $validated['sort'] ?? 'deadline';
        switch ($sort) {
            case 'published_date':
                $query->orderBy('published_date', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('deadline', 'asc');
        }

        $tenders = $query->paginate(12)->withQueryString();

        // Get stats for hero section
        $openTenders = Tender::open()->count();
        $closingSoon = Tender::closingSoon()->count();

        return view('tenders', compact('tenders', 'openTenders', 'closingSoon'));
    }

    /**
     * Show a single tender detail page; returns JSON for AJAX requests.
     *
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($locale, $id)
    {
        // Validate ID parameter
        if (!is_numeric($id) || $id < 1) {
            abort(404);
        }

        $tender = Tender::findOrFail($id);

        // Increment view count
        $tender->incrementViews();

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'tender' => $tender
            ]);
        }

        return view('tender-detail', compact('tender'));
    }
}
