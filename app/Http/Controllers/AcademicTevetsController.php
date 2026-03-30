<?php

namespace App\Http\Controllers;

use App\Models\AcademicAccount;
use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAnnouncement;
use App\Models\Designer;
use Illuminate\Http\Request;

/**
 * Displays the public directory of academic institutions, TVETs, and private-sector training providers.
 * Aggregates three distinct data sources (AcademicAccount for academic/TVET and Designer for private sector)
 * into a unified listing filterable by city and institution type.
 */
class AcademicTevetsController extends Controller
{
    /**
     * Show the combined academic/TVET/private-sector directory with city and type filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validated = $request->validate([
            'city' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:all,academic,tvet,ebdc,private_sector',
            'search' => 'nullable|string|max:255',
        ]);

        // Build three separate queries
        // 1. Academic institutions (universities, colleges, etc. — NOT tvet)
        $academicQuery = AcademicAccount::active()
            ->where('institution_type', '!=', 'tvet');

        // 2. TVETs (academic_accounts with institution_type = 'tvet')
        $tevetQuery = AcademicAccount::active()
            ->where('institution_type', 'tvet');

        // 3. Private sector (designers with is_tevet = true)
        $privateSectorQuery = Designer::where('is_tevet', true)
            ->where('is_active', true)
            ->whereIn('sector', ['manufacturer', 'showroom']);

        // Filter by city
        if (!empty($validated['city']) && $validated['city'] !== 'All Cities') {
            $city = strip_tags($validated['city']);
            $academicQuery->where('city', $city);
            $tevetQuery->where('city', $city);
            $privateSectorQuery->where('city', $city);
        }

        // Search
        if (!empty($validated['search'])) {
            $searchTerm = strip_tags($validated['search']);
            $academicQuery->search($searchTerm);
            $tevetQuery->search($searchTerm);
            $privateSectorQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%")
                  ->orWhere('city', 'like', "%{$searchTerm}%");
            });
        }

        // Get results based on type filter
        $type = $validated['type'] ?? 'all';

        if ($type === 'academic') {
            $academicInstitutions = $academicQuery->orderBy('name')->paginate(12)->withQueryString();
            $tevetInstitutions = collect();
            $privateSectors = collect();
        } elseif ($type === 'tvet') {
            $academicInstitutions = collect();
            $tevetInstitutions = $tevetQuery->orderBy('name')->paginate(12)->withQueryString();
            $privateSectors = collect();
        } elseif ($type === 'ebdc') {
            $academicInstitutions = AcademicAccount::active()
                ->where('institution_type', 'ebdc')
                ->orderBy('name')->paginate(12)->withQueryString();
            $tevetInstitutions = collect();
            $privateSectors = collect();
        } elseif ($type === 'private_sector') {
            $academicInstitutions = collect();
            $tevetInstitutions = collect();
            $privateSectors = $privateSectorQuery->orderBy('name')->paginate(12)->withQueryString();
        } else {
            // Show all three
            $academicInstitutions = $academicQuery->orderBy('name')->get();
            $tevetInstitutions = $tevetQuery->orderBy('name')->get();
            $privateSectors = $privateSectorQuery->orderBy('name')->get();
        }

        // Get unique cities for filter (from all three sources)
        $academicCities = AcademicAccount::active()
            ->where('institution_type', '!=', 'tvet')
            ->distinct()->pluck('city')->filter();

        $tevetCities = AcademicAccount::active()
            ->where('institution_type', 'tvet')
            ->distinct()->pluck('city')->filter();

        $privateSectorCities = Designer::where('is_tevet', true)
            ->where('is_active', true)
            ->whereIn('sector', ['manufacturer', 'showroom'])
            ->distinct()
            ->pluck('city')
            ->filter();

        $cities = $academicCities->merge($tevetCities)->merge($privateSectorCities)
            ->unique()->sort()->values();

        // Stats
        $totalAcademic = AcademicAccount::active()
            ->where('institution_type', '!=', 'tvet')
            ->count();

        $totalTevets = AcademicAccount::active()
            ->where('institution_type', 'tvet')
            ->count();

        $totalPrivateSectors = Designer::where('is_tevet', true)
            ->where('is_active', true)
            ->whereIn('sector', ['manufacturer', 'showroom'])
            ->count();

        return view('academic-tevets', compact(
            'academicInstitutions',
            'tevetInstitutions',
            'privateSectors',
            'cities',
            'totalAcademic',
            'totalTevets',
            'totalPrivateSectors'
        ));
    }

    /**
     * Show an academic institution detail page with its trainings, workshops, and announcements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function showAcademic(Request $request, $locale, $id)
    {
        // Validate ID parameter
        if (!is_numeric($id) || $id < 1) {
            abort(404);
        }

        $institution = AcademicAccount::active()->findOrFail($id);

        // Get approved trainings
        $trainings = $institution->trainings()
            ->publicVisible()
            ->orderBy('start_date', 'desc')
            ->get();

        // Get approved workshops
        $workshops = $institution->workshops()
            ->publicVisible()
            ->orderBy('workshop_date', 'desc')
            ->get();

        // Get approved announcements (publicVisible = approved + published + not expired)
        $announcements = $institution->announcements()
            ->publicVisible()
            ->orderBy('created_at', 'desc')
            ->get();

        // Get other institutions from the same city for "Related" section
        $relatedInstitutions = AcademicAccount::active()
            ->where('id', '!=', $id)
            ->where('city', $institution->city)
            ->limit(4)
            ->get();

        return view('academic-institution-detail', compact(
            'institution',
            'trainings',
            'workshops',
            'announcements',
            'relatedInstitutions'
        ));
    }
}
