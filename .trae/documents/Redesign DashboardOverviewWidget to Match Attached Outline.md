## Goal
Rebuild the Filament dashboard widget to match the provided outline with three Arabic sections and boxed totals, computed in millions.

## Sections & Metrics
- Section 1: "المهمات التي لا تحتاج لصيانة أو تم عمل صيانة لها"
  - Columns: "اجمالي التكلفه في حالة شراء جديد (بالمليون)", "إجمالي تكاليف الصيانة (بالمليون)", "التوفير (بالمليون)".
  - Data: Spare parts with statuses New, UsedInGoodState, Maintained.
  - Formulas:
    - purchase_total = SUM(estimated_cost * quantity)
    - maintenance_total = SUM(COALESCE(maintenance_cost, 0) * quantity)
    - savings = purchase_total − maintenance_total
- Section 2: "المهمات التي تم الاستفادة بها وتركيبها بالفعل"
  - Same three columns as above.
  - Data: Installation operations with status Completed, joined to spare parts.
  - Formulas:
    - purchase_total = SUM(spare_parts.estimated_cost * installation_operations.quantity)
    - maintenance_total = SUM(COALESCE(spare_parts.maintenance_cost, 0) * installation_operations.quantity)
    - savings = purchase_total − maintenance_total
- Section 3: "المهمات بحاجة لفحص وصيانة"
  - Column: "اجمالي القيمة في حالة شراء جديد (بالمليون)".
  - Data: Spare parts with status UsedNeedsMaintainance.
  - Formula:
    - purchase_total = SUM(estimated_cost * quantity)

## Implementation
- New Processor: `app/DataProcessors/DashboardMetricsDataProcessor.php`
  - Methods: `noMaintenanceTotals()`, `installedTotals()`, `needsMaintenanceTotals()`.
  - Use efficient aggregate queries (joins for installed totals) and return numeric totals; divide by 1,000,000 at the presentation layer.
- Update Widget: `app/Filament/Widgets/DashboardOverviewWidget.php`
  - Replace `getViewData()` to pull metrics from the processor.
  - Fix existing maintenance sum logic (use `maintenance_cost * quantity`).
  - Return data array:
    - `noMaintenance`, `installed`, `needsMaintenance`, `now`.
- Update View: `resources/views/filament/widgets/dashboard-overview.blade.php`
  - Replace current 3-card grid with stacked sections.
  - Use Tailwind borders to draw boxes; set `dir="rtl"` and right-aligned Arabic labels.
  - Display values as millions: `number_format($value / 1_000_000, 2)`.
  - Exact headings and column labels as in the image.

## Code References
- Widget data hook: `app/Filament/Widgets/DashboardOverviewWidget.php:16`.
- Current view: `resources/views/filament/widgets/dashboard-overview.blade.php:1`.
- Status enums: `app/Enums/SparePartStatusEnum.php:7–10`, `app/Enums/InstallationStatusEnum.php:7–10`.
- Models used: `app/Models/SparePart.php`, `app/Models/InstallationOperation.php`.

## Acceptance Criteria
- Visual layout mirrors the image: three titled sections, boxed columns, Arabic labels.
- Totals reflect correct formulas and units in millions.
- Section 1 includes New/UsedInGoodState/Maintained; Section 3 includes UsedNeedsMaintainance; Section 2 uses Completed installations.
- No business logic in the widget or view; all calculations in the processor.

## Optional Polishing
- Add thousand separators and consistent decimals.
- Add subtle background/border styling to match Filament aesthetic while keeping the exact box structure.
