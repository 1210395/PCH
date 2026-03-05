<?php

namespace App\Http\Controllers;

use App\Models\FabLab;
use Illuminate\Http\Request;

class FabLabController extends Controller
{
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validated = $request->validate([
            'city' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:university,community,private,government',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:rating,members,name',
        ]);

        $query = FabLab::query();

        // Filter by city (with XSS protection)
        if (!empty($validated['city']) && $validated['city'] !== 'All Cities') {
            $city = strip_tags($validated['city']);
            $query->byCity($city);
        }

        // Filter by type (whitelisted values only)
        if (!empty($validated['type']) && $validated['type'] !== 'All Types') {
            $query->byType($validated['type']);
        }

        // Search (with XSS protection and SQL injection prevention via parameter binding)
        if (!empty($validated['search'])) {
            $searchTerm = strip_tags($validated['search']);
            $query->search($searchTerm);
        }

        // Sort (whitelisted values only)
        $sort = $validated['sort'] ?? 'rating';
        switch ($sort) {
            case 'members':
                $query->orderBy('members', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('rating', 'desc');
        }

        $fabLabs = $query->paginate(12)->withQueryString();

        // Get unique cities for filter (safe query)
        $cities = FabLab::distinct()->pluck('city')->filter()->sort()->values();

        return view('fab-labs', compact('fabLabs', 'cities'));
    }

    public function show($locale, $id)
    {
        // Validate ID parameter
        if (!is_numeric($id) || $id < 1) {
            abort(404);
        }

        $fabLab = FabLab::findOrFail($id);

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'fabLab' => $fabLab
            ]);
        }

        // Get related fab labs from same city
        $relatedFabLabs = FabLab::where('city', $fabLab->city)
            ->where('id', '!=', $id)
            ->take(3)
            ->get();

        return view('fab-lab-detail', compact('fabLab', 'relatedFabLabs'));
    }
}
