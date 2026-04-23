<?php

namespace App\Exports;

use App\Models\Designer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Exports a filtered set of designers to XLSX.
 *
 * Called from AdminDesignerController::export — respects the same query
 * string parameters as the admin designer list so the exported file
 * matches what the admin sees on screen.
 */
class DesignersExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $q = Designer::query()->where('is_admin', false);

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('id', $s);
            });
        }
        if (!empty($this->filters['sector']))     $q->where('sector', $this->filters['sector']);
        if (!empty($this->filters['sub_sector']))  $q->where('sub_sector', $this->filters['sub_sector']);
        if (!empty($this->filters['city']))        $q->where('city', $this->filters['city']);
        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $q->where('is_active', (bool) $this->filters['is_active']);
        }
        if (isset($this->filters['is_trusted']) && $this->filters['is_trusted'] !== '') {
            $q->where('is_trusted', (bool) $this->filters['is_trusted']);
        }

        return $q->orderByDesc('id')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Email', 'Phone', 'Sector', 'Sub-sector', 'City',
            'Followers', 'Views',
            'Active', 'Trusted', 'Email verified',
            'Registered at',
        ];
    }

    public function map($d): array
    {
        return [
            $d->id,
            $d->name,
            $d->email,
            trim(($d->phone_country ?? '') . ' ' . ($d->phone_number ?? '')),
            $d->sector ?? '',
            $d->sub_sector ?? '',
            $d->city ?? '',
            (int) ($d->followers_count ?? 0),
            (int) ($d->views_count ?? 0),
            $d->is_active ? 'Yes' : 'No',
            $d->is_trusted ? 'Yes' : 'No',
            $d->email_verified_at ? 'Yes' : 'No',
            optional($d->created_at)->toDateTimeString(),
        ];
    }
}
