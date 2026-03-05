<?php

namespace App\Http\Controllers\Academic;

use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAnnouncement;
use Illuminate\Http\Request;

class AcademicDashboardController extends AcademicBaseController
{
    /**
     * Display the academic dashboard.
     */
    public function index(Request $request, $locale)
    {
        $account = $this->getAccount();
        $accountId = $this->getAccountId();

        // Content counts
        $counts = [
            'trainings' => [
                'total' => AcademicTraining::where('academic_account_id', $accountId)->count(),
                'pending' => AcademicTraining::where('academic_account_id', $accountId)->pending()->count(),
                'approved' => AcademicTraining::where('academic_account_id', $accountId)->approved()->count(),
                'rejected' => AcademicTraining::where('academic_account_id', $accountId)->rejected()->count(),
                'active' => AcademicTraining::where('academic_account_id', $accountId)->active()->count(),
                'expired' => AcademicTraining::where('academic_account_id', $accountId)->expired()->count(),
            ],
            'workshops' => [
                'total' => AcademicWorkshop::where('academic_account_id', $accountId)->count(),
                'pending' => AcademicWorkshop::where('academic_account_id', $accountId)->pending()->count(),
                'approved' => AcademicWorkshop::where('academic_account_id', $accountId)->approved()->count(),
                'rejected' => AcademicWorkshop::where('academic_account_id', $accountId)->rejected()->count(),
                'active' => AcademicWorkshop::where('academic_account_id', $accountId)->active()->count(),
                'expired' => AcademicWorkshop::where('academic_account_id', $accountId)->expired()->count(),
            ],
            'announcements' => [
                'total' => AcademicAnnouncement::where('academic_account_id', $accountId)->count(),
                'pending' => AcademicAnnouncement::where('academic_account_id', $accountId)->pending()->count(),
                'approved' => AcademicAnnouncement::where('academic_account_id', $accountId)->approved()->count(),
                'rejected' => AcademicAnnouncement::where('academic_account_id', $accountId)->rejected()->count(),
                'active' => AcademicAnnouncement::where('academic_account_id', $accountId)->active()->count(),
                'expired' => AcademicAnnouncement::where('academic_account_id', $accountId)->expired()->count(),
            ],
        ];

        // Recent content
        $recentTrainings = AcademicTraining::where('academic_account_id', $accountId)
            ->latest()
            ->take(5)
            ->get();

        $recentWorkshops = AcademicWorkshop::where('academic_account_id', $accountId)
            ->latest()
            ->take(5)
            ->get();

        $recentAnnouncements = AcademicAnnouncement::where('academic_account_id', $accountId)
            ->latest()
            ->take(5)
            ->get();

        // Upcoming events
        $upcomingTrainings = AcademicTraining::where('academic_account_id', $accountId)
            ->approved()
            ->upcoming()
            ->orderBy('start_date')
            ->take(5)
            ->get();

        $upcomingWorkshops = AcademicWorkshop::where('academic_account_id', $accountId)
            ->approved()
            ->upcoming()
            ->orderBy('workshop_date')
            ->take(5)
            ->get();

        return view('academic.dashboard', compact(
            'account',
            'counts',
            'recentTrainings',
            'recentWorkshops',
            'recentAnnouncements',
            'upcomingTrainings',
            'upcomingWorkshops'
        ));
    }
}
