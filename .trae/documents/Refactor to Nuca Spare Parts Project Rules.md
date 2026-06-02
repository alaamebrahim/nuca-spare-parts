## Current State
- Filament-driven app with business logic embedded in Pages, Resources, and Schemas; no API controllers/resources/DTOs.
- No `app/DataProcessors`, `app/Data` DTOs, `app/Traits/*BaseQueries`, `app/Http/Resources`, or `app/Http/Requests`.
- Enums exist (`app/Enums/SparePartStatusEnum.php`, `app/Enums/InstallationStatusEnum.php`).
- Examples of embedded logic:
  - `app/Filament/Resources/SpareParts/Tables/SparePartsTable.php:165` inline create of `InstallationOperation`.
  - `app/Filament/Pages/SparePartsReport.php:203–257` query composition and derived totals inline.
  - `app/Filament/Pages/InstallationOperationsReport.php:212` query composition inline.
  - `app/Filament/Resources/InstallationOperations/Schemas/InstallationOperationForm.php:41` options built via DB calls.

## Objectives
- Move repeatable/business logic into `app/DataProcessors` (and Actions) with small, composable units.
- Centralize list/filter queries inside `app/Traits/*BaseQueries` using Spatie Query Builder.
- Introduce DTOs in `app/Data` for filter/input mapping; use enums and typed transformers.
- Keep Filament thin: delegate to processors/DTOs; avoid business logic in hooks.
- Prepare optional API surface with `Http\Resources` and `FormRequest` if needed.

## Phase 1 — Extract Business Logic to DataProcessors/Actions
- Create `app/DataProcessors/SparePartsDataProcessor.php` with methods:
  - `labelForSparePartOption(SparePart): string` (used in select options).
  - `availableQuantity(SparePart): int` and validation helpers.
  - `computeEstimatedTotal(SparePart): int|float` and `computeMaintenanceTotal(SparePart): int|float`.
- Create `app/Actions/CreateInstallationOperationAction.php` to encapsulate the workflow currently in `SparePartsTable`:
  - Accept a DTO (see Phase 3), update part quantities/status, create `InstallationOperation`, and persist atomically (transaction).
  - Replace `SparePartsTable.php:165` closure with an action call.
- Create `app/DataProcessors/InstallationOperationsDataProcessor.php` for options and business rules in `InstallationOperationForm`.

## Phase 2 — Centralize Queries in *BaseQueries Traits
- `app/Traits/SparePartsBaseQueries.php`:
  - `public static function baseQuery(): QueryBuilder` with common `with([...])` and selects.
  - `public static function filtered(SparePartsReportFilterData $f): QueryBuilder` using Spatie Query Builder `AllowedFilter`s for status, type, category, city, date ranges.
- `app/Traits/InstallationOperationsBaseQueries.php` similarly for operations.
- Update:
  - `SparePartsReport::getFilteredQuery()` to call `SparePartsBaseQueries::filtered($filters)`.
  - `InstallationOperationsReport::getFilteredQuery()` to call `InstallationOperationsBaseQueries::filtered($filters)`.

## Phase 3 — Introduce DTOs (Spatie Data)
- `app/Data/SpareParts/SparePartsReportFilterData.php`:
  - Fields: `status?: SparePartStatusEnum`, `types?: int[]`, `categories?: int[]`, `cities?: int[]`, `from_date?: Carbon`, `to_date?: Carbon`.
  - Use casts/transformers for dates and enums; normalize arrays.
- `app/Data/InstallationOperations/InstallationOperationsFilterData.php` with analogous fields and `InstallationStatusEnum`.
- `app/Data/InstallationOperations/CreateInstallationOperationInputData.php`:
  - Fields: `spare_part_id`, `city_id`, `installation_date`, `quantity`, `notes?`; normalize and guard.
- Mirror to TS later via transformer if/when frontend outside Filament is used.

## Phase 4 — Validation & Authorization
- For future HTTP endpoints: add `app/Http/Requests/*Request.php` per feature; move normalization to `prepareForValidation()`.
- Define permission names (e.g., `view-spare-parts`, `manage-installations`) and, if controllers are added, gate via `$this->authorize(...)`.
- Keep Filament Shield for panel permissions and ensure actions check capabilities where appropriate.

## Phase 5 — Filament Refactor (Thin Layer)
- `SparePartsTable`
  - Replace inline action at `SparePartsTable.php:165–177` with: map form state → `CreateInstallationOperationInputData` → call `CreateInstallationOperationAction::run(...)` → refresh.
- `InstallationOperationForm`
  - Replace `options` closures (e.g., `InstallationOperationForm.php:41`) with a call to `SparePartsDataProcessor::availableOptionsFor(...)` returning preformatted labels.
  - Replace `maxValue` closure at `InstallationOperationForm.php:65` with processor method.
- `SparePartsReport`
  - Use `SparePartsBaseQueries::filtered(...)` for `getFilteredQuery()` and compute derived values via processors.
- `InstallationOperationsReport`
  - Same pattern: delegate filters and computations.

## Phase 6 — Enums & Model Hygiene
- Keep enums in `app/Enums/<Domain>`; ensure label helpers live in processors or dedicated enum labeler.
- Review `AppServiceProvider` global `Model::unguard()`; prefer explicit `$fillable` on models or guarded fields per domain to reduce risk.

## Phase 7 — Maintenance Routes
- Move `routes/web.php` closure utilities (migrate/optimize) to Artisan Commands or controller methods restricted by environment and permissions.

## Phase 8 — Testing
- Unit tests: processors (totals, available quantity, option labels) and actions (create installation) under transactions.
- Feature tests: report filtering via BaseQueries; ensure filters behave.
- If API added: controller tests with `FormRequest` validation and `Http\Resources` serialization.

## Phase 9 — Deliverables & File Map
- New folders: `app/DataProcessors`, `app/Actions`, `app/Data/SpareParts`, `app/Data/InstallationOperations`, `app/Traits`, `app/Http/Requests`, `app/Http/Resources`.
- Updated files:
  - `app/Filament/Resources/SpareParts/Tables/SparePartsTable.php:165–177` — replace inline workflow.
  - `app/Filament/Pages/SparePartsReport.php:197–257` — delegate filters/derivatives.
  - `app/Filament/Pages/InstallationOperationsReport.php:206–212` — delegate filters.
  - `app/Filament/Resources/InstallationOperations/Schemas/InstallationOperationForm.php:41,65` — delegate options/max logic.

## Acceptance Criteria
- Filament layer contains orchestration only; no non-trivial business logic in closures.
- Query composition centralized in `*BaseQueries` traits with AllowedFilters.
- DTOs handle input mapping and normalization; actions/processors encapsulate operations.
- Permissions consistent; tests cover processors/actions; app behavior unchanged for users.

## Optional Next Step — API Surface
- If an external frontend/API is planned: add thin controllers using `FormRequest` + DTO mapping + processors/actions and serialize via `Http\Resources`. Regenerate TS via `php artisan typescript:transform` if DTOs are shared.
