## Architecture & Routing

-   Multi-tenant SaaS built on Stancl Tenancy; central domains live in [config/tenancy.php](config/tenancy.php) and helpers such as `isCentralDomain()` are in [app/Helper/tenancyHelpers.php](app/Helper/tenancyHelpers.php).
-   Central onboarding/registration is served from [routes/web.php](routes/web.php) with [app/Http/Middleware/BlockTenantAccessToCentral.php](app/Http/Middleware/BlockTenantAccessToCentral.php); tenant UX lives in [routes/tenant.php](routes/tenant.php) and [routes/tenant-api.php](routes/tenant-api.php) guarded by `InitializeTenancyBySubdomain` + `PreventAccessFromCentralDomains`.
-   Shared Inertia props (active branch, discounts, cart snapshot, session info) are centralized in [app/Http/Middleware/HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php); prefer adding tenant data there instead of per-controller duplication.
-   Business logic is service-driven: see [app/Services](app/Services) (Cart, CashierSession, TableManagement, Discount, Modifier, Receipt, etc.) and reuse them from controllers rather than re-querying models directly.

## Backend Practices

-   Auth is handled manually inside [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php); branch selection is mandatory, `canLoginTo()` is enforced before login, and open cashier sessions block branch switching.
-   Attendance is coordinated by [app/Http/Controllers/AttendanceController.php](app/Http/Controllers/AttendanceController.php) plus [app/Rules/UserBelongsToBranch.php](app/Rules/UserBelongsToBranch.php); it creates/updates `Attendance` records and stores photos via Spatie media collections defined in [app/Models/Attendance.php](app/Models/Attendance.php).
-   Cart, order, and cashier flows go through [app/Services/CartService.php](app/Services/CartService.php) and [app/Http/Controllers/CashierSessionController.php](app/Http/Controllers/CashierSessionController.php); always call the service layer so VAT/discount logic, table state, and cashier session validation remain consistent.
-   Table and customer routes are grouped under the `resto.*`, `table-management.*`, `table-rooms.*`, and `transactions.*` prefixes inside [routes/tenant.php](routes/tenant.php); keep wildcards last to avoid routing conflicts.
-   Fortify/Jetstream handle password recovery from the central domain; use the Filament password reset URLs assembled in [resources/js/Pages/Auth/Login.vue](resources/js/Pages/Auth/Login.vue).

## Frontend Practices

-   Vue 3 + Inertia entrypoint is [resources/js/app.js](resources/js/app.js); it wires Pinia, PrimeVue (Aura theme), Ziggy, VueSweetalert2, Toast/Confirmation services, and disables PWAs during dev.
-   Axios is configured once in [resources/js/bootstrap.js](resources/js/bootstrap.js) with persistent CSRF token refreshing—do not override headers in components.
-   Layouts live in [resources/js/Layouts](resources/js/Layouts) and receive `active_branch` plus flash data automatically; global navigation is curated inside [resources/js/Pages/Home.vue](resources/js/Pages/Home.vue).
-   Attendance UI (camera access, zoom, preview, confirmation modal) is implemented in [resources/js/Pages/Attendance/Index.vue](resources/js/Pages/Attendance/Index.vue); keep that single-button UX intact when touching attendance features.
-   POS UI is split between [resources/js/Pages/Resto](resources/js/Pages/Resto) and reusable widgets in [resources/js/Components/Resto](resources/js/Components/Resto); stateful logic belongs in Pinia stores under [resources/js/stores](resources/js/stores) and printer integrations live in [resources/js/Services/ThermalPrinterService.ts](resources/js/Services/ThermalPrinterService.ts).

## Developer Workflow

-   Install dependencies with `composer install` and `npm install`. Use `composer dev` to run the full stack (Laravel server, queue listener, Pail log viewer, Vite) or `npm run dev`/`npm run build` for frontend-only work.
-   Central migrations run via `php artisan migrate`; tenant schemas live in `database/migrations/tenant` and are applied with `php artisan tenants:migrate` (or `tenants:seed`). Always run tenant migrations after altering tenant models.
-   Automated tests execute with `composer test`, which clears config before running `php artisan test`.
-   Queue-driven features (cashier session summaries, notifications, receipts) expect `php artisan queue:listen --tries=1`; keep `.env` domain settings (`CENTRAL_DOMAIN`, `VITE_CENTRAL_DOMAIN`) in sync for Ziggy/Inertia route resolution.

## Domain Requirements

-   Login must offer branch selection with optional branch validation plus password visibility; on success redirect to the Home action hub instead of `/dashboard` (see [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php) and [resources/js/Pages/Auth/Login.vue](resources/js/Pages/Auth/Login.vue)).
-   Home navigation should continue surfacing Dashboard (future), Start Cashiering, Table Ordering, and Clock In/Out cards as defined in [resources/js/Pages/Home.vue](resources/js/Pages/Home.vue); ensure logout stays available everywhere.
-   Attendance requires a single Clock In/Out button with camera capture, preview, and confirmation before persisting photos via Spatie (clock_in_photos & clock_out_photos collections in [app/Models/Attendance.php](app/Models/Attendance.php) and UI in [resources/js/Pages/Attendance/Index.vue](resources/js/Pages/Attendance/Index.vue)).
-   When toggling attendance, auto-detect whether to clock in or out by checking for an `Attendance` lacking `actual_timeout`; update `actual_timeout` when clocking out, create a fresh record otherwise, and enforce the 5‑minute wait implemented in [app/Http/Controllers/AttendanceController.php](app/Http/Controllers/AttendanceController.php).
-   Always verify the employee belongs to the currently selected branch before allowing attendance or cashier actions; branch context is stored in session and shared as `active_branch` via [app/Http/Middleware/HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php) and validated by [app/Rules/UserBelongsToBranch.php](app/Rules/UserBelongsToBranch.php).
-   Prevent branch switching while a cashier session is open (error copy exists in [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php)); table management, cashiering, and attendance features must all respect that active branch and session state.
