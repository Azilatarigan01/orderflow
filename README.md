# OrderFlow Procurement System

## Project Overview

OrderFlow is an internal procurement application for medium‑sized companies aiming to replace scattered purchase request processes that rely on WhatsApp, email, and spreadsheets. The system unifies the workflow for requesters, managers, procurement staff, finance, admins, and management, providing a traceable end‑to‑end solution.

## Core Process Flow

1. **Purchase Request** – Requester creates a request for goods or services.
2. **Approval** – Manager reviews and approves/rejects the request.
3. **Vendor Quotations** – Procurement gathers offers from vendors.
4. **Vendor Comparison** – Procurement compares prices, terms, and selects a vendor.
5. **Purchase Order (PO)** – System generates a PO linked to the approved request.
6. **Goods/Service Receipt** – Receipts are recorded and matched to the PO.
7. **Invoice Verification** – Finance verifies invoices against PO and receipt.
8. **Reporting & Audit Trail** – Management and auditors can view dashboards, reports, and a full audit log.

## Business Problems Addressed

| Problem | Impact |
|---------|--------|
| Requests scattered across chat & email | Easy to miss, hard to search later |
| No status on approvals | Requester must manually follow up |
| Vendor offers stored separately | Procurement struggles to compare |
| Manual PO creation | Risks vendor, item, price, quantity errors |
| Receipt not linked to PO | Difficult to track over/under‑delivery |
| No audit trail | Decisions and changes are hard to trace |

## User Roles & Responsibilities

| Role | Primary Responsibilities |
|------|---------------------------|
| **Requester** | Create, submit, revise, and monitor purchase requests |
| **Manager** | Review needs and make approval decisions |
| **Procurement** | Manage vendors, quotations, comparisons, and PO creation |
| **Finance** | Review budgets, verify invoices |
| **Admin** | Manage users, roles, divisions, categories, and system configuration |
| **Management / Auditor** | View dashboards, reports, and audit trails |

## Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel (PHP) – business logic, validation, authentication, authorization, API |
| **Database** | MySQL – stores users, transactions, vendors, approvals, audit logs |
| **Portal UI** | Laravel Blade + Tailwind CSS – forms and admin pages |
| **Dashboard** | React – interactive data visualisation consuming the REST API |
| **API** | Laravel API with Sanctum – secure data exchange |
| **Testing** | Postman & PHPUnit/Pest – API and business rule testing |
| **Environment** | Docker – consistent development and deployment containers |
| **Automation** | GitHub Actions – CI/CD pipeline for automated testing |
| **Hosting** | Cloud or VPS – production deployment |

## Getting Started (Development)

```bash
# Clone the repository
git clone <repo-url>
cd orderflow_app

# Copy environment example and configure
cp .env.example .env
# Edit .env (database credentials, etc.)

# Install dependencies
composer install
npm install

# Build assets
npm run dev

# Run Docker containers (if using Docker)
docker-compose up -d

# Run migrations & seed data
php artisan migrate --seed

# Start the application
php artisan serve
```

Open your browser at `http://127.0.0.1:8000`.
## Development Roadmap

The roadmap is divided into three phases. The first phase delivers a usable MVP, the second adds modern engineering standards and publishes the app, and the third adds advanced features once the core data and processes are stable.

| Phase | Focus | Outcome |
|------|------|--------|
| **0** | PRD & Design | Scope, user flow, ERD, wireframes, and backlog. |
| **1** | Project Foundation | Repository, Laravel setup, database, authentication, and roles. |
| **2** | Purchase Request | Request form, items, validation, and status tracking. |
| **3** | Approval | Multi‑level approvals, revisions, rejections, and history. |
| **4** | Vendor & Quotation | Vendor management, RFQ, quotations, and vendor comparison. |
| **5** | Purchase Order | PO generation, PDF, full/partial receipt handling. |
| **6** | Dashboard & Audit | KPI indicators, reports, export, and audit trail. |
| **7** | REST API | Endpoints, Sanctum auth, JSON responses, Postman collection. |
| **8** | React Dashboard | Interactive management UI consuming the API. |
| **9** | Testing | Manual tests, API tests, and automated test suite. |
| **10** | Docker | Containerised app, DB, and web server. |
| **11** | Publish & CI | Deployment, demo account, logging, GitHub Actions CI. |
| **12** | Advanced Features | OCR, AI, chatbot, integrations, predictive analytics, mobile support. |

**Transition Criteria**

- All core features of the previous phase are usable via the browser.
- No critical errors block the main workflow.
- Changes are committed to GitHub with clear messages.
- README and documentation are updated.
- Test scenarios for the phase have been executed successfully.
