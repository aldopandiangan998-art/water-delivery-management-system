# UI Discovery & Architecture Report

**Project:** AquaFlow - Water Delivery & Depot Management System  
**Date:** November 25, 2026  
**Branch:** `feature/ui-redesign`  

---

## 1. Environment & Technical Stack

| Component | Detected Version / Specification | Notes |
|---|---|---|
| **Framework** | Laravel `^13.17` (Modern Skeleton) | Runs PHP 8.4+ environment |
| **PHP** | `^8.3` | Composer minimum requirement |
| **Build Tooling** | Vite `^8.0.0` with `laravel-vite-plugin` `^3.1` | Ultra-fast asset bundler |
| **CSS Architecture** | Tailwind CSS `^4.0.0` (`@tailwindcss/vite`) | Native Tailwind v4 engine |
| **Client JS** | Alpine.js `^3.17.5`, Leaflet `^1.9.4` | For maps and reactive UI states |
| **Iconography** | Font Awesome 6.5 (CDN) | Unified icon set throughout |
| **Existing UI Libraries** | Pure Tailwind CSS + Alpine.js | **Zero Bootstrap / Filament / Inertia conflicts** |
| **Test Suite** | PHPUnit 12 | 40/40 tests passing (100% green) |

---

## 2. Page Inventory & Layout Hierarchy

### Base Layouts & Global Components
- `resources/views/layouts/app.blade.php`: Public guest layout (home, catalog, service areas, auth).
- `resources/views/layouts/admin.blade.php`: Dedicated Admin layout with collapsible sidebar and top header.
- `resources/views/layouts/customer.blade.php`: Dedicated Customer/Member layout with navigation and floating order CTA.
- `resources/views/layouts/navigation.blade.php`: Responsive top navbar for public visitors and logged-in shortcuts.
- `resources/views/partials/theme-lang-controls.blade.php`: Reusable Dark/Light mode toggle and ID/EN language selector.
- `resources/views/components/confirm-modal.blade.php`: Global Alpine.js confirmation modal.

### Views to Modernize & Redesign

#### Public & Authentication (3 views)
1. `public/index.blade.php`: Landing page with hero banner, benefits, product highlights, service areas, and customer testimonials.
2. `public/service-areas.blade.php`: Service coverage maps and delivery tariff list.
3. `auth/login.blade.php` & `auth/register.blade.php`: Authentication flow.

#### Customer Portal (8 views)
1. `customer/dashboard.blade.php`: Member overview, active order radar alert, fast order buttons, and recent activity.
2. `customer/orders/create.blade.php`: Water order checkout form with address selector and gallon exchange options.
3. `customer/orders/index.blade.php`: Customer order history with status filters.
4. `customer/orders/show.blade.php`: Order details, delivery status, and invoice items.
5. `customer/orders/track.blade.php`: Real-time Leaflet delivery tracking map.
6. `customer/addresses/index.blade.php`: Customer address book management.
7. `customer/store-profile.blade.php`: Depot store profile, address, operating hours, and Leaflet location map.
8. `customer/profile.blade.php`: Customer profile, account details, and password update.

#### Admin Operations (14 views)
1. `admin/dashboard.blade.php`: Business KPIs, sales metrics, low stock alert, recent orders table, and quick shortcuts.
2. `admin/orders/index.blade.php`: Order management grid with search, date filters, and status tabs.
3. `admin/orders/show.blade.php`: Detailed order inspection, delivery dispatch, and status transition actions.
4. `admin/deliveries/index.blade.php`: Delivery tasks list and assignment monitor.
5. `admin/deliveries/show.blade.php`: Delivery task details, timeline, and photo proof.
6. `admin/deliveries/map.blade.php`: Real-time interactive radar map showing active courier locations.
7. `admin/products/index.blade.php`, `create.blade.php`, `edit.blade.php`: Gallon products, pricing, stock levels, and category management.
8. `admin/service-areas/index.blade.php`: Delivery zones, minimum order thresholds, and shipping fees.
9. `admin/addresses/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`: Customer address directory.
10. `admin/finance/index.blade.php`: Cashflow summary, revenue, and expense recording.
11. `admin/reviews/index.blade.php`: Customer ratings and feedback moderation.
12. `admin/users/index.blade.php`: User management (Admin and Customer roles).

---

## 3. Primary Color Strategy

Since the domain is a **Hygienic Drinking Water Delivery & Depot Management System** (*Depot Air Minum & Layanan Antar*), clean, refreshing aquatic tones represent the core brand identity:

- **Option 1 (Recommended): Ocean Cyan `#0891b2` (`cyan-600`)**  
  Evokes pure, fresh, crystalline drinking water with vibrant cyan accents and optimal contrast.
- **Option 2: Deep Royal Blue `#2563eb` (`blue-600`)**  
  Emphasizes operational trust, logistics speed, and corporate clarity.
- **Option 3: Modern Indigo `#4f46e5` (`indigo-600`)**  
  Tech-forward app interface standard.

> **Default Selected:** **Ocean Cyan (`#0891b2`)** with complementary slate dark/light contrast.

---

## 4. Risks & Conflicts Assessment

| Potential Issue | Severity | Mitigation Strategy |
|---|---|---|
| **Bootstrap / Framework Collision** | **None** | No Bootstrap, Filament, or Inertia exists. Clean Tailwind v4 code throughout. |
| **Tailwind v4 Plugin Ecosystem** | **Low** | Use Tailwind v4 compatible packages (`daisyui@latest`, `@tailwindcss/forms`). |
| **Dark Mode Consistency** | **Low** | Dark mode is already configured with class-based `.dark` on `<html>`, anti-flicker `<head>` scripts, and synchronized localStorage. |
| **Existing Business Logic** | **None** | All routes, models, database schemas, and controllers remain 100% untouched. |

---

## 5. Next Steps (Proceeding to Phase 1)

1. Install frontend packages (`daisyui`, `@tailwindcss/forms`, `chart.js`, `flatpickr`).
2. Configure theme tokens and colors in design system.
3. Build reusable Blade components (`<x-button>`, `<x-input>`, `<x-card>`, `<x-table>`, etc.).
4. Add mobile-first UX patterns (bottom navigation, global search `/`, toast notifications, and familiar everyday app features).
