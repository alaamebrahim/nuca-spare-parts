# Trae AI Development Guidelines — Nuca spare parts

These guidelines instruct Trae (and developers) how to implement features in this codebase. They adopt Spatie’s AI guidelines and codify local conventions: extract repeatable logic into DataProcessors, expose APIs via HTTP Resources, and map request/response payloads through Data objects (DTOs).

Reference: https://spatie.be/laravel-php-ai-guidelines.md

## Core Principles
- Favor small, composable units: Controllers orchestrate; logic lives in DataProcessors.
- All API responses use `Http\Resources` for serialization and formatting.
- Use Spatie Laravel Data for DTOs; annotate with `TypeScript` when shared with frontend.
- Query composition lives in Traits (BaseQueries) using Spatie Query Builder.
- Validate input with FormRequests; never trust raw `Request`.
- Follow role/permission checks consistently with `$this->authorize(...)` and Spatie Permission.

## Folder Responsibilities
- `app/DataProcessors`: Business logic, actions, repeatable code. Example: `OwnersDataProcessor`, `ContractCalculationProcessor`, `CreatePreviousBalanceTransactionAction`.
- `app/Http/Resources`: API serializers. Example: `Owners/OwnerResource`, `UnitContracts/UnitContractResource`.
- `app/Data`: DTOs used for input mapping and computed fields. Example: `RealEstates/RealEstateData`, `UnitContracts/ContractCalculationInputData`.
- `app/Traits/*BaseQueries.php`: Query composition, filters, eager loads via Spatie Query Builder.
- `app/Services`: External integrations (APIs, SMS) returning DTOs or typed results.
- `app/Http/Requests`: Validation and input normalization.

## Roles & Permissions
- `App\Models\User` uses Spatie `HasRoles` and `HasPermissions`.
- Gate via `$this->authorize('permission-name')` in controllers.
- Initialize or adjust user permissions via a dedicated DataProcessor/Action (e.g. `Users/SetupUserPermissionsAction`).
- When adding a feature, define clear permission names (e.g. `view-owners`, `manage-invoices`) and use them consistently in UI and API.

## DataProcessors — How to Use
- Extract repeatable logic and side-effect operations into `DataProcessors`.
- Prefer stateless `static` methods for pure functions; use `Action` classes for orchestrated workflows.
- Example (abbrev):
```php
class OwnersDataProcessor {
    public static function bankFromIban(string $iban): ?Bank { /* map to Bank */ }
}
```
- Naming: `FeatureDataProcessor` (pure logic), `DoSomethingAction` (workflow), `*Processor` for calculators.

## HTTP Resources — API Serialization
- Always return a Resource or Resource Collection from controllers.
- Compute derived fields here or in DTOs when possible; format dates and numbers.
- Example (abbrev):
```php
class OwnerResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'initial_balance' => formattedNumber(/* ... */),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
        ];
    }
}
```

## Data Objects (DTOs) — Mapping & Computations
- Use `Spatie\LaravelData\Data` for request/response mapping and small computations.
- Annotate with `#[TypeScript]` where needed, and casts/transformers for dates and numbers.
- Examples (abbrev):
```php
#[TypeScript]
class RealEstateData extends Data { /* owners => average_commission */ }

#[TypeScript]
class ContractCalculationInputData extends Data {
    public function __construct(
        public ContractRentalTypeEnum $rent_type,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        #[WithTransformer(DateTransformer::class)]
        public ?string $start_date,
    ) { /* normalize */ }
}
```

## Traits & Base Queries — QueryBuilder
- Keep index/list queries in `*BaseQueries` traits using Spatie Query Builder.
- Provide `baseQuery()` with selects, relations, and `AllowedFilter`s.
- Example (abbrev):
```php
trait UnitContractsBaseQueries {
    public static function baseQuery(): QueryBuilder { /* filters, with */ }
}
```
- Controllers call `self::baseQuery()->paginate(...)` and feed results into Resources.

## Services — External Integrations
- Put external calls in `app/Services`; return DTOs or typed data.
- Handle retries and map raw responses into `app/Data/Api/*` objects.
- Example: `PersonDataService::getData(): PersonResponseData|string` with retry/backoff.

## Requests — Validation & Normalization
- Create a FormRequest per endpoint. Normalize/prepare data in `prepareForValidation()` when needed.
- Controllers type-hint the FormRequest and pass normalized data to DTOs/DataProcessors.

## Controllers — Orchestration Only
- Responsibilities:
  - Authorize: `$this->authorize('feature-permission')`.
  - Validate: use FormRequests.
  - Map: `Data::from($request->all())`.
  - Delegate: call DataProcessors/Actions.
  - Serialize: return Resource/Resource::collection.
- Keep logic out of controllers; use Inertia for views, Resources for API.

## FilamentPHP — Keep Patterns Intact
- Filament resources/forms/tables should call DataProcessors and DTOs.
- Do not put business logic in Filament hooks; delegate to processors and reuse Resources/DTOs.

## Adding a New Feature — Checklist
1. Define permissions and add authorization points in controllers/UI.
2. Create FormRequest for validation and normalization.
3. Create/extend DTO(s) in `app/Data` for input/output mapping.
4. Implement logic in `app/DataProcessors` (pure/static or `Action`).
5. Add/extend BaseQueries trait if listing/filtering is needed.
6. Create Resource(s) to serialize responses.
7. Wire routes in `routes/api.php` and keep controllers thin.
8. If external integration is needed, add a `Service` that returns DTOs.
9. Add unit/feature tests around processors and controllers.

## Conventions — Naming & Style
- Permissions: `verb-noun` (e.g. `view-owners`, `manage-invoices`).
- Processors: `FeatureDataProcessor`, `CreateSomethingAction`, `*Calculator`.
- Resources: `EntityResource` in domain folders.
- DTOs: `*Data` or `*InputData`/`*OutputData`.
- Enums: domain-specific under `app/Enums/<Domain>`.
- Helpers: format via `formattedNumber`, date via `Carbon` or transformers.

## References
- Spatie AI Guidelines: https://spatie.be/laravel-php-ai-guidelines.md
- Spatie Laravel Data, Query Builder, Permission packages already in use.

Adhering to this file ensures Trae and developers keep logic modular, APIs consistent, and data mapping explicit across the app.

## Frontend Conventions (Vue + Inertia + Vite)
- Structure: keep pages under `resources/scripts/components/<Domain>` matching backend domains (Owners, Tenants, RealEstates, UnitContracts, etc.).
- Layout: use `PersistentLayout.vue` by default; exceptions for `Auth/` and `Public/` as in `main.ts`.
- TypeScript-first: use `.ts` for helpers, composables, directives; keep component names in PascalCase.
- Path aliases: use `@` for `resources` and `@nm` for `node_modules` (per `vite.config.js`).

**DTO Types & Mapping**
- Backend DTOs (`app/Data/*`) are mirrored to TS via Spatie TypeScript Transformer into `resources/types/generated.d.ts`.
- When you change/add DTOs, regenerate types (recommended): `php artisan typescript:transform`.
- Frontend resource typings live in `resources/scripts/Helpers/response-data.ts`; prefer using these or the generated types for strict typing.

**API Client & Response Envelope**
- Use `resources/scripts/Helpers/Api.ts` (Axios) with interceptors.
- Standard response: `{ success: boolean, message?: string, data?: T, errors?: Record<string,string[]> }`.
- Example usage:
```ts
import Api from '@/scripts/Helpers/Api';
import type { JsonResponse, OwnerResource } from '@/scripts/Helpers/response-data';

const res = await Api.post<JsonResponse<OwnerResource>>('/api/resource/owner', { owner_id });
if (res.success) {
  // res.data contains typed payload
}
```

**Permissions**
- Use directive `v-can` (permission-directive) to guard UI elements.
- Backend (Fortify login) returns `permissions` array; store in `localStorage('permissions')` on login; clear on logout.
- Check permissions with `Helpers/permissions.ts` (`can([...])`). Keep permission names identical to backend (`verb-noun`).

**Directives**
- Input normalization/validation via directives: `v-numeric`, `v-decimals`, `v-ndecimals`, `v-identity`, `v-iban`, `v-uniqueidentity`, `v-uniqueiban`, `v-max`, `v-min`, `v-mobile`, `v-segel`, `v-autoresize`, `v-shortcode`.
- Keep directive names/action aligned with backend rules (`IbanRule`, identity checks, etc.).

**Preferred Frontend Packages**
- Core: `vue@3`, `@inertiajs/vue3`, `@vitejs/plugin-vue`, `laravel-vite-plugin`.
- UI/UX: `floating-vue`, `preline`, `flowbite`, `simplebar`, `sweetalert2`.
- Data viz: `apexcharts`, `vue3-apexcharts`, `echarts`, `chart.js` (use one per context; prefer ApexCharts for dashboards).
- Forms & widgets: `tom-select`, `tabulator-tables`, `flatpickr`, `nouislider`.
- Utilities: `lodash`, `sass`, `unplugin-auto-import`, `unplugin-vue-components`.

**Vite & Bundling**
- Entry points: manage via `laravel-vite-plugin` in `vite.config.js`. When adding new global scripts/styles, include them in the `laravel([...])` list.
- Keep aliases and auto-import/component plugins consistent.

**Component & File Naming**
- Components: PascalCase (`OwnerCard.vue`, `TenantList.vue`).
- Composables/helpers: camelCase (`useOwners.ts`, `objectElementSum.ts`).
- Domain folders: singular, capitalized (`Owners`, `Tenants`, etc.) mirroring backend.

## Preferred Backend Packages
- Spatie: `laravel-permission`, `laravel-data`, `laravel-typescript-transformer`, `laravel-query-builder`.
- Laravel: `fortify` (auth), `sanctum` (API tokens), `horizon` (queues), `telescope` (debugging).
- Keep usage consistent with current providers and configuration.

## Project Setup (Baseline)
- Backend: `composer install`, configure `.env`, `php artisan migrate`, seed if applicable.
- Frontend: `npm install`, `npm run dev` (Vite). For production, `npm run build`.
- Types: regenerate TS DTO types when DTOs change: `php artisan typescript:transform`.
- Routes: wire API endpoints in `routes/api.php`; expose via Resources.

## FilamentPHP Usage
- Filament forms/tables/actions must delegate to DataProcessors and DTOs; avoid business logic in Filament layer.
- Use existing Http Resources for API responses consumed by Filament widgets.

## Trae Prompt Snippet (paste into new tasks)
```
Follow Real Estates Abumouti architecture:
- Backend: put repeatable logic in DataProcessors; serialize responses via Http Resources; map inputs/outputs with Spatie Data DTOs (+ TypeScript). Use BaseQueries traits for list/filter. Enforce permissions via $this->authorize('verb-noun').
- Frontend: Vue 3 + Inertia. Place pages under resources/scripts/components/<Domain> with PersistentLayout by default. Use Api.ts axios client, JsonResponse<T> envelope, v-can permissions directive, and path aliases @ and @nm. Use FloatingVue/Preline/Flowbite for UI, ApexCharts for charts. Keep typing via resources/types/generated.d.ts and response-data.ts.
- When adding features: define permissions, create FormRequest, add DTO(s), implement DataProcessor/Action, add BaseQuery if needed, create Resource(s), wire routes, update frontend types and components, and use Api.ts to call.
```