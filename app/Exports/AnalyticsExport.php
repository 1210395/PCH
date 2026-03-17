<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Multi-sheet Excel export for admin analytics.
 *
 * Produces an .xlsx file with five sheets: Overview (KPIs + filter context),
 * Designer Growth, Content Trends, Approval Workflow, and Ratings Trend.
 * Each sheet is an anonymous class implementing the appropriate Maatwebsite
 * Excel concerns to keep the export self-contained.
 */
class AnalyticsExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $data     Analytics data array from AdminAnalyticsController::computeAnalytics()
     * @param  array<string, mixed>  $filters  Active filters (preset, dateFrom, dateTo, sector, city)
     */
    public function __construct(
        private array $data,
        private array $filters
    ) {}

    /**
     * Return the ordered list of sheet objects for the workbook.
     *
     * @return array<int, \Maatwebsite\Excel\Concerns\FromArray>
     */
    public function sheets(): array
    {
        $data    = $this->data;
        $filters = $this->filters;

        return [

            // ── Sheet 1: Overview ────────────────────────────────────────────
            new class($data, $filters) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private array $data, private array $filters) {}
                public function title(): string { return 'Overview'; }
                public function headings(): array { return ['Metric', 'Value']; }
                public function array(): array
                {
                    return [
                        ['Total Designers',        $this->data['totalDesigners']],
                        ['Active Designers',        $this->data['activeDesigners']],
                        ['Total Approved Content',  $this->data['totalApprovedContent']],
                        ['Total Pending Items',     $this->data['pendingTotal']],
                        ['Total Approved Ratings',  $this->data['totalRatings']],
                        ['Average Rating',          $this->data['averageRating']],
                        ['---', '---'],
                        ['Date From',   $this->filters['dateFrom'] ?? 'All time'],
                        ['Date To',     $this->filters['dateTo']   ?? 'All time'],
                        ['Sector Filter', $this->filters['sector'] ?? 'All sectors'],
                        ['City Filter',   $this->filters['city']   ?? 'All cities'],
                        ['Exported At',   now()->format('Y-m-d H:i:s')],
                    ];
                }
            },

            // ── Sheet 2: Designer Growth ─────────────────────────────────────
            new class($data['designerGrowth']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Designer Growth'; }
                public function headings(): array { return ['Month', 'New Registrations']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['month'], $r['count']])->toArray();
                }
            },

            // ── Sheet 3: Content Trends ──────────────────────────────────────
            new class($data['contentTrends']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Content Trends'; }
                public function headings(): array { return ['Month', 'Products', 'Projects', 'Services', 'Marketplace']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [
                        $r['month'], $r['products'], $r['projects'], $r['services'], $r['marketplace'],
                    ])->toArray();
                }
            },

            // ── Sheet 4: Approval Workflow ───────────────────────────────────
            new class($data['approvalWorkflow']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private array $rows) {}
                public function title(): string { return 'Approval Workflow'; }
                public function headings(): array { return ['Content Type', 'Pending', 'Approved', 'Rejected']; }
                public function array(): array
                {
                    return array_map(
                        fn($r) => [$r['type'], $r['pending'], $r['approved'], $r['rejected']],
                        $this->rows
                    );
                }
            },

            // ── Sheet 5: Ratings Trend ───────────────────────────────────────
            new class($data['ratingsTrend']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Ratings Trend'; }
                public function headings(): array { return ['Month', 'Avg Rating', 'Count']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [
                        $r['month'], $r['avg_rating'], $r['count'],
                    ])->toArray();
                }
            },

        ];
    }
}
