<?php

namespace App\Http\Controllers;

use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAnnouncement;
use App\Helpers\DropdownHelper;
use Illuminate\Http\Request;

/**
 * Aggregates and displays approved academic trainings, workshops, and announcements on a single public page.
 * Content from all three models is merged in PHP and manually paginated, filtered, and sorted.
 */
class TrainingController extends Controller
{
    /**
     * Show the combined training/workshop/announcement listing with filtering and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'type' => 'nullable|string|in:online,in-person,hybrid,training,workshop,announcement',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:date,title',
        ]);

        $contentType = $validated['type'] ?? 'all';
        $allItems = collect();

        // No language filter — content should be visible in both locales

        // Get trainings if type is all or training
        if ($contentType === 'all' || $contentType === 'training' || in_array($contentType, ['online', 'in-person', 'hybrid'])) {
            $trainingsQuery = AcademicTraining::with('academicAccount')
                ->where('approval_status', 'approved')

                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->toDateString());
                });

            // Filter by category
            if (!empty($validated['category']) && $validated['category'] !== 'all') {
                $trainingsQuery->where('category', \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'training_category'));
            }

            // Filter by level
            if (!empty($validated['level']) && $validated['level'] !== 'all') {
                $trainingsQuery->where('level', $validated['level']);
            }

            // Filter by location type (online, in-person, hybrid)
            if (in_array($contentType, ['online', 'in-person', 'hybrid'])) {
                $trainingsQuery->where('location_type', $contentType);
            }

            // Search
            if (!empty($validated['search'])) {
                $searchTerm = strip_tags($validated['search']);
                $trainingsQuery->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $trainings = $trainingsQuery->get()->map(function ($item) {
                $item->content_type = 'training';
                $item->sort_date = $item->start_date;
                $item->institution = $item->academicAccount->name ?? 'Institution';
                return $item;
            });

            $allItems = $allItems->concat($trainings);
        }

        // Get workshops if type is all or workshop
        if ($contentType === 'all' || $contentType === 'workshop') {
            $workshopsQuery = AcademicWorkshop::with('academicAccount')
                ->where('approval_status', 'approved')
                ->whereRaw($arabicFilter);

            // Filter by category
            if (!empty($validated['category']) && $validated['category'] !== 'all') {
                $workshopsQuery->where('category', \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'training_category'));
            }

            // Search
            if (!empty($validated['search'])) {
                $searchTerm = strip_tags($validated['search']);
                $workshopsQuery->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $workshops = $workshopsQuery->get()->map(function ($item) {
                $item->content_type = 'workshop';
                $item->sort_date = $item->workshop_date;
                $item->institution = $item->academicAccount->name ?? 'Institution';
                return $item;
            });

            $allItems = $allItems->concat($workshops);
        }

        // Get announcements if type is all or announcement
        if ($contentType === 'all' || $contentType === 'announcement') {
            $announcementsQuery = AcademicAnnouncement::with('academicAccount')
                ->where('approval_status', 'approved')

                ->where(function ($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now()->toDateString());
                });

            // Filter by category
            if (!empty($validated['category']) && $validated['category'] !== 'all') {
                $announcementsQuery->where('category', \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'training_category'));
            }

            // Search
            if (!empty($validated['search'])) {
                $searchTerm = strip_tags($validated['search']);
                $announcementsQuery->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $announcements = $announcementsQuery->get()->map(function ($item) {
                $item->content_type = 'announcement';
                $item->sort_date = $item->publish_date ?? $item->created_at;
                $item->institution = $item->academicAccount->name ?? 'Institution';
                return $item;
            });

            $allItems = $allItems->concat($announcements);
        }

        // Sort
        $sort = $validated['sort'] ?? 'date';
        if ($sort === 'title') {
            $allItems = $allItems->sortBy('title');
        } else {
            $allItems = $allItems->sortBy('sort_date');
        }

        // Manual pagination
        $page = $request->get('page', 1);
        $perPage = 12;
        $total = $allItems->count();
        $trainings = new \Illuminate\Pagination\LengthAwarePaginator(
            $allItems->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get categories for filter
        $categories = DropdownHelper::trainingCategories();

        // Get stats for hero section
        $totalTrainings = AcademicTraining::approved()->count()
            + AcademicWorkshop::approved()->count()
            + AcademicAnnouncement::approved()->count();
        $totalStudents = 0;

        return view('trainings', compact('trainings', 'categories', 'totalTrainings', 'totalStudents'));
    }

    /**
     * Show a detail page for a training, workshop, or announcement identified by type + ID.
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

        // Determine type from request or try to find in each table
        $type = request()->get('type', 'training');

        $training = null;
        $relatedTrainings = collect();

        if ($type === 'training') {
            $training = AcademicTraining::with('academicAccount')
                ->where('approval_status', 'approved')
                ->find($id);

            if ($training) {
                $training->content_type = 'training';
                $training->institution = $training->academicAccount->name ?? 'Institution';
                $training->incrementViews();

                $relatedTrainings = AcademicTraining::where('category', $training->category)
                    ->where('id', '!=', $id)
                    ->where('approval_status', 'approved')
                    ->take(3)
                    ->get()
                    ->map(function ($item) {
                        $item->content_type = 'training';
                        return $item;
                    });
            }
        } elseif ($type === 'workshop') {
            $training = AcademicWorkshop::with('academicAccount')
                ->where('approval_status', 'approved')
                ->find($id);

            if ($training) {
                $training->content_type = 'workshop';
                $training->institution = $training->academicAccount->name ?? 'Institution';
                $training->incrementViews();

                $relatedTrainings = AcademicWorkshop::where('category', $training->category)
                    ->where('id', '!=', $id)
                    ->where('approval_status', 'approved')
                    ->take(3)
                    ->get()
                    ->map(function ($item) {
                        $item->content_type = 'workshop';
                        return $item;
                    });
            }
        } elseif ($type === 'announcement') {
            $training = AcademicAnnouncement::with('academicAccount')
                ->where('approval_status', 'approved')
                ->find($id);

            if ($training) {
                $training->content_type = 'announcement';
                $training->institution = $training->academicAccount->name ?? 'Institution';
                $training->incrementViews();

                $relatedTrainings = AcademicAnnouncement::where('category', $training->category)
                    ->where('id', '!=', $id)
                    ->where('approval_status', 'approved')
                    ->take(3)
                    ->get()
                    ->map(function ($item) {
                        $item->content_type = 'announcement';
                        return $item;
                    });
            }
        }

        if (!$training) {
            abort(404);
        }

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'training' => $training
            ]);
        }

        return view('training-detail', compact('training', 'relatedTrainings'));
    }
}
