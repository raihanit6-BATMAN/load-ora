# LOADORA — Logistics & Dispatch Management System

> **Private Internal Logistics & Dispatch Operations Management Software**  
> Designed to replace manual spreadsheet workflows with enterprise-grade reliability, weighbridge reconciliation, and automated analytics.

---

## 1. System Overview

**LOADORA** is a purpose-built logistics dispatch and receiving control platform built for industrial aggregate transport, bulk hauling, and site material reconciliations.

### Key Capabilities
- **Real-Time Weighbridge Reconciliation**: Automatic variance and transit loss calculation ($\text{Deduction} = \text{Loaded Quantity} - \text{Received Quantity}$).
- **Sequential Dispatch Lifecycle**: Unique format `DSP-YYYY-XXXX` with status tracking (`Pending`, `Partial`, `Received`).
- **Partial Receiving Support**: Multiple delivery slips per dispatch when split or delayed.
- **Enterprise Role-Based Access Control**: Strict segregation between **Admin** (master control, imports, deletion, user provisioning) and **Staff** (dispatch entry, receipt recording, operational reports).
- **Historical Google Sheets Migration**: Automated data pipeline with fuzzy header detection, date-inference fallback, and duplicate prevention.
- **Audit Trail & Observability**: Complete JSON-backed activity logging for all logins, mutations, and export operations.
- **High-Performance Reporting**: On-demand CSV streaming with UTF-8 BOM and DomPDF reconciliation reports with site totals.

---

## 2. Technology Stack

- **Framework**: PHP 8.3 (ZTS x64) + [Laravel 11](https://laravel.com)
- **Interactive UI**: [Livewire 3 / 4](https://livewire.laravel.com) + [Alpine.js](https://alpinejs.dev)
- **Styling**: [Tailwind CSS v3](https://tailwindcss.com) with LOADORA Deep Navy & Slate Theme
- **Database**: [MySQL 8.0 / MariaDB 10.11](https://mariadb.org) with strict decimal precision (`DECIMAL(10, 3)`)
- **PDF Generation**: [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **CSV & Excel Engine**: [OpenSpout](https://github.com/OpenSpout/OpenSpout) & Native RFC 4180 Streaming
- **Data Visualization**: [Chart.js 4](https://www.chartjs.org) + [Lucide Icons](https://lucide.dev)

---

## 3. Pre-Configured Portable Environment

LOADORA includes self-contained portable binaries located in `tools/`:
- **PHP 8.3**: `F:\ERP\tools\php\php.exe`
- **Composer 2.11**: `F:\ERP\tools\composer.bat`
- **MariaDB 10.11**: `F:\ERP\tools\mariadb\bin\mysqld.exe`

### Database Credentials (`.env`)
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loadora
DB_USERNAME=root
DB_PASSWORD=
```

---

## 4. Default User Credentials

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@loadora.test` | `Admin@12345` | Full system control, Settings, Data Import, User Management, Activity Logs, Deletions |

*Note: Operational Staff accounts can be created and managed on-demand by the Admin via the User Management panel (`/users`).*

---

## 5. Quick Start Instructions

### Starting the Database Daemon (if not running)
```powershell
F:\ERP\tools\mariadb\bin\mysqld.exe --defaults-file="F:\ERP\tools\mariadb\my.ini" --console
```

### Starting the Web Server
```powershell
F:\ERP\tools\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```
Open your browser to: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

### Compiling Frontend Assets (Development)
```powershell
npm run dev
# Or build for production:
npm run build
```

---

## 6. Running Automated Tests

LOADORA includes a comprehensive suite of 29 automated Feature and Unit tests covering Authentication, Authorization Policies, Livewire component interactions, Weighbridge Variance calculations, Report streaming, and Spreadsheet import pipelines.

```powershell
F:\ERP\tools\php\php.exe artisan test
```

### Test Suite Summary
- `Tests\Unit\ExampleTest`: Unit sanity checks
- `Tests\Feature\AuthTest`: Login credentials verification, brute-force rate-limiting, active status enforcement, and logout activity tracking
- `Tests\Feature\RolePermissionTest`: Admin vs Staff route access, 403 Forbidden checks, and policy deletion barriers
- `Tests\Feature\DispatchTest`: Dispatch creation via Livewire, sequential number formatting, input validation, and 24-hour edit policies
- `Tests\Feature\ReceiptTest`: Slip entry, automatic variance/deduction calculation ($L\_Qty - R\_Qty$), and dispatch status auto-reconciliation
- `Tests\Feature\ReportTest`: Filtered report queries, UTF-8 BOM CSV streaming, and DomPDF generation
- `Tests\Feature\SpreadsheetImportTest`: CSV header fuzzy mapping, date inference fallback, and database transaction safety

---

## 7. Historical Data Migration

Historical Google Sheets data containing **214 Dispatches** and **212 Receipts** has already been imported and verified:
- **Total Loaded Weight**: `6,307.292 MT`
- **Total Received Weight**: `6,171.432 MT`
- **Total Recorded Deductions / Transit Shortages**: `23.540 MT`
- **Pending Deliveries**: `113.680 MT` (2 dispatches from 01/10/2026 flagged as `⚠️ NOT RECEIVED`)
- **Registered Haulage Vehicles**: `169 unique vehicles`

To re-import or import another batch from CLI:
```powershell
F:\ERP\tools\php\php.exe artisan loadora:import-sheet F:\ERP\sheet_export.csv
```
Or use the **Data Import Center** in the Admin UI (`/import`).

---

## 8. Directory Structure

```text
F:\ERP
├── app/
│   ├── Http/Controllers/       # Auth & Report Export Controllers
│   ├── Livewire/               # 11 Interactive Livewire Full-Page Components
│   │   ├── Activity/           # Activity Log Viewer
│   │   ├── Clients/            # Client / Party Registry
│   │   ├── Dashboard/          # Executive Analytics with Chart.js
│   │   ├── Dispatches/         # Dispatch Index & Detail Reconciliation
│   │   ├── Goods/              # Material & Category Catalog
│   │   ├── ImportCenter/       # Admin Spreadsheet Migration Center
│   │   ├── Receipts/           # Receiving & Deduction Entry
│   │   ├── Reports/            # Multi-Filter Analytics Builder
│   │   ├── Settings/           # Organization & App Settings
│   │   ├── Users/              # Staff Account Management
│   │   └── Vehicles/           # Fleet & Haulier Directory
│   ├── Models/                 # 10 Eloquent Models with Relationships & Casts
│   ├── Policies/               # Granular Role-Based Access Policies
│   └── Services/               # Spreadsheet Import & Mapping Engine
├── database/
│   ├── factories/              # Test factories for all core models
│   ├── migrations/             # 10 Normalized MySQL Schema Migrations
│   └── seeders/                # Default Admin, Staff, Goods & Client Seeders
├── resources/
│   ├── views/
│   │   ├── auth/               # Branded Modern Login View
│   │   ├── layouts/            # Clean Responsive App Layout with Lucide Icons
│   │   ├── livewire/           # Blade Views for all Livewire Components
│   │   └── reports/            # Print-Ready DomPDF Template
│   ├── css/                    # Tailwind CSS Design Directives
│   └── js/                     # Chart.js & Lucide Initializers
├── routes/
│   └── web.php                 # 29 Secure Authenticated & Admin Routes
├── tests/
│   ├── Feature/                # 6 Test Suites covering all modules
│   └── Unit/                   # Unit Tests
└── tools/                      # Portable PHP 8.3 & MariaDB 10.11
```

---

## 9. Security & Architecture Principles

- **No Client-Facing Access**: Pure private internal application.
- **Strict Authorization**: Policies checked server-side via `Gate::authorize()` and middleware, never reliant solely on UI visibility.
- **High-Precision Decimals**: Never uses IEEE-754 floats for business quantities. All quantities are stored as `DECIMAL(10, 3)` in MySQL and formatted with 3 decimal places.
- **Immutable Financial Trail**: Dispatches and receipts utilize Soft Deletes, maintaining complete accountability.
