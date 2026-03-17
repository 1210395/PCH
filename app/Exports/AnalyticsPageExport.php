<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Per-page Excel export for admin analytics.
 *
 * Returns only the sheets relevant to the requested page so each
 * sub-page exports a focused, clean workbook.
 *
 * Pages: overview | engagement | traffic | geographic | workflow | improvement
 */
class AnalyticsPageExport implements WithMultipleSheets
{
    public function __construct(
        private string $page,
        private array  $data,
        private array  $filters
    ) {}

    public function sheets(): array
    {
        return match ($this->page) {
            'overview'    => $this->overviewSheets(),
            'engagement'  => $this->engagementSheets(),
            'traffic'     => $this->trafficSheets(),
            'geographic'  => $this->geographicSheets(),
            'workflow'    => $this->workflowSheets(),
            'improvement' => $this->improvementSheets(),
            default       => $this->overviewSheets(),
        };
    }

    // ── Overview ──────────────────────────────────────────────────────────────

    private function overviewSheets(): array
    {
        $data    = $this->data;
        $filters = $this->filters;

        return [
            new class($data, $filters) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private array $data, private array $filters) {}
                public function title(): string { return 'KPIs'; }
                public function headings(): array { return ['Metric', 'Value']; }
                public function array(): array
                {
                    return [
                        ['Total Designers',       $this->data['totalDesigners']],
                        ['Active Designers',       $this->data['activeDesigners']],
                        ['Total Approved Content', $this->data['totalApprovedContent']],
                        ['Total Pending Items',    $this->data['pendingTotal']],
                        ['Total Approved Ratings', $this->data['totalRatings']],
                        ['Average Rating',         $this->data['averageRating']],
                        ['---', '---'],
                        ['Date From',     $this->filters['dateFrom'] ?? 'All time'],
                        ['Date To',       $this->filters['dateTo']   ?? 'All time'],
                        ['Sector Filter', $this->filters['sector']   ?? 'All sectors'],
                        ['City Filter',   $this->filters['city']     ?? 'All cities'],
                        ['Exported At',   now()->format('Y-m-d H:i:s')],
                    ];
                }
            },

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
        ];
    }

    // ── Engagement ────────────────────────────────────────────────────────────

    private function engagementSheets(): array
    {
        $data = $this->data;

        return [
            new class($data['engagementTrend']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Engagement Trend'; }
                public function headings(): array { return ['Month', 'Views', 'Likes']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['month'], $r['views'], $r['likes']])->toArray();
                }
            },

            new class($data['topViewedContent']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Most Viewed'; }
                public function headings(): array { return ['Type', 'Title', 'Views', 'Likes']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['type'], $r['title'], $r['views'], $r['likes']])->toArray();
                }
            },

            new class($data['topLikedContent']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Most Liked'; }
                public function headings(): array { return ['Type', 'Title', 'Likes', 'Views']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['type'], $r['title'], $r['likes'], $r['views']])->toArray();
                }
            },

            new class($data['topFollowedDesigners']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Most Followed'; }
                public function headings(): array { return ['Name', 'City', 'Sector', 'Followers', 'Profile Views']; }
                public function array(): array
                {
                    return $this->rows->map(fn($d) => [
                        $d->name, $d->city ?? '', $d->sector ?? '',
                        $d->followers_count, $d->views_count,
                    ])->toArray();
                }
            },
        ];
    }

    // ── Traffic ───────────────────────────────────────────────────────────────

    private function trafficSheets(): array
    {
        $data = $this->data;

        return [
            new class($data['pageTrafficTotals']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Page Traffic'; }
                public function headings(): array { return ['Page', 'Total Visits']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['page'], $r['count']])->toArray();
                }
            },
        ];
    }

    // ── Geographic ────────────────────────────────────────────────────────────

    private function geographicSheets(): array
    {
        $data = $this->data;

        return [
            new class($data['byCity']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'By City'; }
                public function headings(): array { return ['City', 'Designers']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r->city, $r->count])->toArray();
                }
            },

            new class($data['bySector']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'By Sector'; }
                public function headings(): array { return ['Sector', 'Designers']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r->sector, $r->count])->toArray();
                }
            },

            new class($data['topDesigners']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Top Designers'; }
                public function headings(): array { return ['Name', 'City', 'Sector', 'Products', 'Projects', 'Services', 'Marketplace', 'Total']; }
                public function array(): array
                {
                    return $this->rows->map(fn($d) => [
                        $d['name'], $d['city'] ?? '', $d['sector'] ?? '',
                        $d['products'], $d['projects'], $d['services'], $d['marketplace'], $d['total'],
                    ])->toArray();
                }
            },
        ];
    }

    // ── Workflow ──────────────────────────────────────────────────────────────

    private function workflowSheets(): array
    {
        $data = $this->data;

        return [
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
                    return array_map(fn($r) => [$r['type'], $r['pending'], $r['approved'], $r['rejected']], $this->rows);
                }
            },

            new class($data['avgApprovalTime']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private array $rows) {}
                public function title(): string { return 'Avg Time to Approve'; }
                public function headings(): array { return ['Content Type', 'Avg Hours']; }
                public function array(): array
                {
                    return array_map(fn($r) => [$r['type'], $r['avg_hours']], $this->rows);
                }
            },

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
                    return $this->rows->map(fn($r) => [$r['month'], $r['avg_rating'], $r['count']])->toArray();
                }
            },
        ];
    }

    // ── Improvement ───────────────────────────────────────────────────────────

    private function improvementSheets(): array
    {
        $data = $this->data;

        return [
            new class($data['zeroViewsContent']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Zero Views'; }
                public function headings(): array { return ['Type', 'Title']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['type'], $r['title']])->toArray();
                }
            },

            new class($data['zeroLikesContent']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Zero Likes'; }
                public function headings(): array { return ['Type', 'Title', 'Views']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['type'], $r['title'], $r['views']])->toArray();
                }
            },

            new class($data['highViewLowLikes']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'High Views No Likes'; }
                public function headings(): array { return ['Type', 'Title', 'Views']; }
                public function array(): array
                {
                    return $this->rows->map(fn($r) => [$r['type'], $r['title'], $r['views']])->toArray();
                }
            },

            new class($data['inactiveDesigners']) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithTitle,
                \Maatwebsite\Excel\Concerns\WithHeadings
            {
                public function __construct(private $rows) {}
                public function title(): string { return 'Inactive Designers'; }
                public function headings(): array { return ['Name', 'City', 'Sector', 'Joined']; }
                public function array(): array
                {
                    return $this->rows->map(fn($d) => [
                        $d->name, $d->city ?? '', $d->sector ?? '',
                        $d->created_at->format('Y-m-d'),
                    ])->toArray();
                }
            },
        ];
    }
}
