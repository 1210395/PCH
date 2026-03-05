<?php

namespace App\Http\Controllers;

use App\Models\FabLab;
use Illuminate\Http\Request;

class FabLabController extends Controller
{
    public function index(Request $request)
    {
        $query = FabLab::query();

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by city
        if ($request->has('city') && $request->city !== 'all') {
            $query->where('city', $request->city);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'rating');
        switch ($sort) {
            case 'members':
                $query->orderBy('members', 'desc');
                break;
            case 'reviews':
                $query->orderBy('reviews_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('rating', 'desc');
        }

        $fabLabs = $query->paginate(12);

        // Get unique cities and types for filters
        $cities = FabLab::distinct()->pluck('city');
        $types = ['university', 'community', 'private', 'government'];

        return view('fab-labs', compact('fabLabs', 'cities', 'types'));
    }

    public function show($id)
    {
        $fabLab = FabLab::findOrFail($id);

        // Get similar fab labs (same type or city)
        $similarLabs = FabLab::where(function ($query) use ($fabLab) {
            $query->where('type', $fabLab->type)
                  ->orWhere('city', $fabLab->city);
        })
        ->where('id', '!=', $id)
        ->take(3)
        ->get();

        return view('fab-lab-detail', compact('fabLab', 'similarLabs'));
    }
}
