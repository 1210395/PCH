<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Http\Request;

class TenderController extends Controller
{
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
