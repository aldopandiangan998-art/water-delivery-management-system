# UI Guidelines & Design System

**Project:** AquaFlow - Water Delivery & Depot Management System  
**Version:** 2.0 (Modernized UI)  
**Branch:** `feature/ui-redesign`  

---

## 1. Single Source of Truth: Configurable Primary Color

The application's primary brand color is defined in one place: [resources/css/app.css](file:///c:/Projects/water-delivery-management-system/resources/css/app.css) under `@theme`.

```css
@theme {
    /* To change the primary color of the entire app, update this hex code */
    --color-primary: #0891b2;         /* Ocean Cyan (Default) */
    --color-primary-hover: #0e7490;   /* Darker for hover states */
    --color-primary-focus: #155e75;   /* Focus ring tint */
    --color-primary-light: #ecfeff;   /* Soft background tint */
    --color-primary-content: #ffffff; /* Readable text on primary */

    --color-secondary: #0284c7;       /* Royal Blue */
    --color-accent: #06b6d4;          /* Sky Cyan */
}
```

### Supported Presets
- **Ocean Cyan (Active):** `#0891b2` — Ideal for hygienic drinking water and fresh delivery.
- **Deep Royal Blue:** `#2563eb` — Professional logistics and commercial depot.
- **Modern Indigo:** `#4f46e5` — High-tech enterprise default.

---

## 2. Typography & Hierarchy

- **Base Font:** `'Instrument Sans', ui-sans-serif, system-ui, sans-serif`
- **Headings:**
  - `H1` (Page Headers): `text-2xl sm:text-3xl font-black tracking-tight`
  - `H2` (Card / Section Headers): `text-lg font-extrabold tracking-tight`
  - `H3` (Subsections): `text-base font-bold`
- **Body & Labels:**
  - Regular Text: `text-sm font-normal text-slate-700 dark:text-slate-300`
  - Secondary/Caption Text: `text-xs text-slate-500 dark:text-slate-400`
  - Micro Badges & Tags: `text-[10px] font-bold uppercase tracking-wider`

---

## 3. Dark & Light Theme Contrast

- **Light Mode Canvas:** `#f8fafc` (`bg-slate-50`) or `#f1f5f9` (`bg-slate-100`)
- **Light Mode Cards:** `#ffffff` (`bg-white`) with border `border-slate-200`
- **Dark Mode Canvas:** `#0b1329` (`bg-slate-950`)
- **Dark Mode Cards:** `#111c35` with border `rgba(51, 65, 85, 0.6)`
- **Contrast Compliance:** Meets WCAG AA (>= 4.5:1 ratio for text against all card and badge backgrounds).
- **Anti-Flicker Persistence:** Handled instantly via `<head>` inline script and `localStorage.theme`.

---

## 4. Spacing, Radii & Depth

- **Border Radius:**
  - Micro elements & Badges: `rounded-lg` (`0.5rem`)
  - Buttons & Inputs: `rounded-xl` (`0.75rem`)
  - Cards & Sheet Panels: `rounded-2xl` (`1rem`)
  - Banners & Hero Containers: `rounded-3xl` (`1.5rem`)
- **Elevation / Shadows:**
  - Subtle cards: `shadow-xs` / `shadow-sm`
  - Floating actions & Dropdowns: `shadow-xl` / `shadow-2xl`
  - Dark mode automatically softens all elevation shadows to deep slate ambient drop-shadows.

---

## 5. Standard Interactive Components

1. **Buttons:** Minimum touch target `44px` height (`py-2.5 px-4`). Visible focus rings (`focus:ring-2 focus:ring-primary/40`).
2. **Form Inputs:** Consistent `rounded-xl`, clear border feedback, and auto-adapting dark mode backgrounds (`#091024`).
3. **Data Tables:** Compact padded rows, uppercase tracking table headers, and responsive horizontal overflow handling.
4. **Status Badges:** Semantically colored pills (Pending = Amber, Transit = Cyan/Blue, Completed = Emerald, Cancelled = Gray/Rose) with tinted dark mode variants.
