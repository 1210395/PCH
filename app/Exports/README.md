# app/Exports

Excel export classes for the Palestine Creative Hub, built with [Maatwebsite Excel](https://laravel-excel.com/) v3.

---

## Export Index

| File | Class | Sheets |
|---|---|---|
| `AnalyticsExport.php` | `AnalyticsExport` | Overview, Designer Growth, Content Trends, Approval Workflow, Ratings Trend |

---

## AnalyticsExport

Implements `WithMultipleSheets` to produce a multi-tab `.xlsx` file from the analytics data computed by `AdminAnalyticsController`.

### Constructor
```php
new AnalyticsExport(array $data, array $filters)
```
- `$data` — the array returned by `AdminAnalyticsController::computeAnalytics()`.
- `$filters` — the active filter values (`preset`, `dateFrom`, `dateTo`, `sector`, `city`), written to the Overview sheet for audit context.

### Sheets

| Sheet | Headings | Source |
|---|---|---|
| **Overview** | Metric, Value | KPI summary + active filter values + export timestamp |
| **Designer Growth** | Month, New Registrations | `$data['designerGrowth']` (monthly counts) |
| **Content Trends** | Month, Products, Projects, Services, Marketplace | `$data['contentTrends']` (monthly counts per type) |
| **Approval Workflow** | Content Type, Pending, Approved, Rejected | `$data['approvalWorkflow']` (global counts) |
| **Ratings Trend** | Month, Avg Rating, Count | `$data['ratingsTrend']` (monthly averages) |

### Usage
Called from `AdminAnalyticsController::export()`:
```php
return Excel::download(new AnalyticsExport($data, $filters), 'analytics-2026-03-17.xlsx');
```

---

## Adding New Exports

1. Create a new class in `app/Exports/` implementing the appropriate Maatwebsite concerns (`FromCollection`, `FromQuery`, `WithHeadings`, etc.).
2. Inject any required data via the constructor.
3. Call `Excel::download(new YourExport(...), 'filename.xlsx')` from the controller.
