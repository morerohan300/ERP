# Ledgerline ERP

Ledgerline is a modular, multi-company ERP and CRM built on Laravel 13, Livewire 4, Tailwind CSS 4 and MySQL 8. This repository currently contains **Phase 1 (Foundation)**: authentication, tenancy, roles and permissions, the Core admin screens, the shared design system, demo data and a MySQL Pest suite.

The full build plan and database schema live in [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

---

## Requirements

- PHP 8.3+
- Composer 2
- MySQL 8
- Node.js 18+ and npm

---

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
npm install
npm run build
```

Create two MySQL databases:

```sql
CREATE DATABASE ledgerline;
CREATE DATABASE ledgerline_test;
```

Configure `DB_*` in `.env` for the main database, then migrate and seed the demo workspace:

```bash
php artisan migrate --seed
php artisan serve
```

Open <http://127.0.0.1:8000>.

> If you see **“Vite manifest not found”**, run `npm install && npm run build`. Tests call `withoutVite()`, so the compiled assets are only needed when browsing the app.

---

## Demo accounts

`php artisan migrate --seed` creates a company named **Ledgerline Demo** with branches, departments, taxes, currencies, exchange rates, fiscal years, number sequences, custom fields, an approval workflow, a pending approval and five users.

| Role | Email | Password |
|---|---|---|
| Administrator / owner | `admin@ledgerline.test` | `password` |
| Manager | `manager@ledgerline.test` | `password` |
| Accountant | `accountant@ledgerline.test` | `password` |
| HR Manager | `hr@ledgerline.test` | `password` |
| Employee | `employee@ledgerline.test` | `password` |

The administrator can access every Core screen. The other accounts demonstrate permission differences — for example, an Employee cannot open **Settings** or manage branches.

---

## Tests

`phpunit.xml` is configured for MySQL using the `ledgerline_test` database (not SQLite). Create that database, then run:

```bash
php artisan test
# or a single file
php artisan test --filter=TenancyTest
```

The suite covers:

- Fortify auth views, seeded admin login and the profile/2FA screen
- Web CRUD for branches, departments and taxes, including validation
- Company isolation for tenant models and the API
- Permission denials for unauthorized users
- Sanctum API authentication, UUID resources and company selection
- Livewire table search and sorting

Tests use `RefreshDatabase`; each test receives a clean transaction and seeds its own copy of the demo data.

---

## Architecture

### Modules

Feature code lives under `Modules/<Module>/`. Each module owns its models, services, repositories, policies, Form Requests, controllers, resources, Livewire components, views, routes, migrations, factories and `module.json`. The in-house loader in `app/Support/Modules` reads `module.json`, checks dependencies and boots each module provider.

`app/` is reserved for kernel-level infrastructure shared by every module: tenancy, numbering, money, repositories, middleware and the base Blade components.

Only **Core** is implemented in Phase 1. Later phases add CRM, Sales, Purchase, Inventory, Accounting, HR, Projects, Manufacturing, POS and Reports.

### Layering

```text
Controller → FormRequest → Policy → Service/Action → Repository → Model
```

Web and API controllers reuse the same services. API responses use `JsonResource` classes that expose the public UUID as `id`.

### Tenancy and access control

- `BelongsToCompany` adds a global `CompanyScope` and fills `company_id` on create.
- Web requests resolve the company from the session; API requests use the `X-Company: <company-uuid>` header. Membership is checked against `company_user`.
- Spatie permissions use teams, where the team ID is the company ID. Call `setPermissionsTeamId()` before checking or assigning roles outside an HTTP request.
- Permission names follow `{module}.{entity}.{action}`, for example `core.branch.create`.
- Routes and `uuid` values are public; internal integer IDs are not exposed in API resources.
- Number sequences are row-locked and support `{PREFIX}`, `{YYYY}`, `{SEQ:4}` and yearly/monthly resets.

### Phase 1 web surface

Core web routes are unprefixed (`/dashboard`, `/branches`, `/users`, `/settings/*`, `/approvals`, …) and use `web`, `auth`, `verified` and `company` middleware. Core API routes are under `/api/v1/core` with `api.core.*` names and `auth:sanctum` + `company` middleware.

Interactive tables are Livewire components registered with these aliases:

```text
core.users-table          core.branches-table       core.departments-table
core.taxes-table          core.audit-log-table      core.login-history-table
core.notifications-dropdown                       core.global-search
```

Fortify provides login, registration, password reset, email verification, password confirmation, profile updates and two-factor authentication. Its views are overridden under `resources/views/auth/`.

### Design system

Tailwind 4 tokens are defined in `resources/css/app.css` with `@theme`:

- Navy `#1E2A44` (chrome), ivory `#F8F7F4` (canvas), gold `#B8975A` (accent)
- Success `#2F7D5B`, warning `#B7791F`, danger `#B23A3A`
- Libre Baskerville for headings, Inter for body and tabular data
- Logical spacing utilities (`ms-`, `pe-`) so RTL locales work automatically

Shared components live in `resources/views/components/` and include `x-layout.app`, `x-card`, `x-stat-card`, `x-table`, `x-button`, `x-badge`, `x-modal`, `x-slide-over`, `x-dropdown`, `x-tabs`, `x-toast`, `x-empty-state`, `x-custom-fields` and the `x-form.*` family.

---

## Useful commands

```bash
php artisan route:list --except-vendor
php artisan erp:sync-permissions --roles   # reapply default role permissions
php artisan erp:backup                     # queue a database backup
php artisan view:cache                     # compile Blade templates
php artisan view:clear
./vendor/bin/pint                          # format PHP
```

---

## API example

Create a Sanctum token for a user, then send the company UUID on every request:

```bash
curl -H "Authorization: Bearer <token>" \
     -H "X-Company: <company-uuid>" \
     http://127.0.0.1:8000/api/v1/core/branches
```

Lists support `filter[…]`, `sort`, `include` and `per_page` (1–100). Resources expose `id` as the UUID, never the internal integer key.
