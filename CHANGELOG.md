All notable changes to this project will be documented in this file. 

Note: see template at the bottom

---

# Changelog

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — Layout & SCSS Setup
**Type:** Refactor

### Description
Migrated the project frontend from CDN-based Bootstrap to a Vite + SCSS pipeline. Established the base SCSS architecture and removed all duplicate asset loading.

### Details
- Removed Bootstrap and Bootstrap Icons CDN links from the default layout
- Set up Bootstrap source imports via npm and Vite
- Created the base SCSS entry point `app.scss` with correct load order: variables → Bootstrap → Bootstrap Icons → layouts
- Created `_variables.scss` to override Bootstrap tokens (`$primary`, `$secondary`, `$body-bg`, typography, border-radius, shadows) before Bootstrap loads
- Moved Bootstrap JS import to `resources/js/app.js`
- Configured `vite.config.js` with global entry points

### Files Affected
- `resources/sass/app.scss` *(created)*
- `resources/sass/_variables.scss` *(created)*
- `resources/js/app.js` *(updated)*
- `vite.config.js` *(created)*

### Notes
Run `npm install bootstrap @popperjs/core sass bootstrap-icons` before building.

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — Default Layout
**Type:** Refactor | Improvement

### Description
Rewrote the default Blade layout to remove inline styles, fix the broken mobile sidebar toggle, and improve semantic HTML structure.

### Details
- Removed all inline `<style>` block — styles extracted to SCSS partials
- Moved `@stack('styles')` from inside `.page-content` to `<head>` to prevent flash of unstyled content
- Added working mobile topbar with hamburger toggle button (`#sidebarToggle`)
- Added `.sidebar-overlay` for tap-to-close sidebar on mobile
- Added ESC key listener to close sidebar
- Replaced `container-fluid > row > col` wrapper with `.layout-wrapper` flex layout
- Used semantic HTML tags: `<aside>`, `<header>`, `<main>`, `<footer>`
- Removed redundant `Auth::user() &&` null checks (auth middleware guarantees user exists)
- Fixed `@stack('scripts')` placement — moved to just before `</body>`
- Dynamic footer year using `{{ date('Y') }}`

### Files Affected
- `resources/views/layouts/default.blade.php` *(rewritten)*
- `resources/sass/layouts/_sidebar.scss` *(created)*
- `resources/sass/layouts/_layout.scss` *(created)*

### Notes
Bootstrap Icons moved to npm — remove any remaining CDN references for Bootstrap Icons in other views.

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — Default Layout Navigation
**Type:** Refactor

### Description
Extracted role-based sidebar navigation items into dedicated partials to improve maintainability. Each role now owns its own nav file.

### Details
- Split the large `@if/@elseif/@else` nav block in `default.blade.php` into three separate partial files
- Each partial contains only the `<li>` nav items for that role
- Default layout now uses `@include` to pull in the correct partial based on role
- Renamed partial files from `_nav-*.blade.php` convention to kebab-case (`nav-admin.blade.php`) to follow Laravel naming conventions

### Files Affected
- `resources/views/layouts/default.blade.php` *(updated)*
- `resources/views/partials/nav-admin.blade.php` *(created)*
- `resources/views/partials/nav-teacher.blade.php` *(created)*
- `resources/views/partials/nav-student.blade.php` *(created)*

### Notes
The sidebar shell (brand, user info, logout) remains in `default.blade.php`. Only the `<li>` menu items were extracted.

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — Admin Dashboard
**Type:** Improvement | Refactor

### Description
Rewrote the admin dashboard view and created its dedicated SCSS and JS files. Improved visual hierarchy, filter UX, table structure, and chart setup.

### Details
- Removed inline `<style>` block — chart height now handled properly in SCSS
- Moved `@stack('styles')` push to the top of the file (before `@section`)
- Replaced CDN Chart.js with npm import (`npm install chart.js`)
- Replaced `asset()` with `@vite()` for JS loading
- Split filter bar into two groups: selects (left) and action links (right)
- Removed `onchange` inline JS from filter selects — moved to `dashboard.js`
- KPI cards upgraded with icons, color-coded left borders, and proper label/value hierarchy
- All tables wrapped in `table-responsive` for mobile safety
- Added styled `.dash-empty` fallback for all `@empty` blocks
- Added inline progress bar column to category performance table
- Sentiment percentages replaced with styled pill badges
- Created `dashboard.js` with dual-axis Chart.js setup (Mean Rating + Positive Sentiment %)

### Files Affected
- `resources/views/admin/dashboard.blade.php` *(rewritten)*
- `resources/sass/pages/_dashboard.scss` *(created)*
- `resources/js/admin/dashboard.js` *(created)*
- `vite.config.js` *(updated — added dashboard entry points)*

### Notes
`dashboardData` inline script in the Blade view must appear above the `@vite('dashboard.js')` line so the variable is defined before the script runs.

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — Admin Department
**Type:** Improvement | Refactor

### Description
Rewrote the faculty directory view with improved card design, working search/filter with live feedback, and a new `department.js` for all interactive behavior.

### Details
- Replaced `asset()` CSS and JS references with `@vite()`
- Added page header with breadcrumb and subtitle
- Extracted filter controls into a dedicated filter bar above the card grid
- Added `data-name`, `data-email`, `data-subjects` attributes to each faculty card for JS filtering
- Fixed broken admin badge (`start-70` is not a valid Bootstrap class) — replaced with CSS-positioned badge centered below avatar
- Faculty cards now have two avatar color variants: blue gradient (faculty), amber gradient (admin)
- "View Profile" button changed from a dead `<button>` to a proper `<a>` tag with route
- Course badges upgraded from flat `bg-light` to soft blue-tinted style
- Added live results count in filter bar
- Added `#noResults` empty state (hidden by default, shown by JS)
- Created `department.js` with live search (name + email), course filter, clear button, and results counter

### Files Affected
- `resources/views/admin/department.blade.php` *(rewritten)*
- `resources/sass/pages/_department.scss` *(created)*
- `resources/js/admin/department.js` *(created)*
- `vite.config.js` *(updated — added department entry points)*

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — Admin Users
**Type:** Improvement | Refactor

### Description
Rewrote the users management view with improved table design, role-colored badges, a Bootstrap delete confirmation modal, and a new `users.js` for all interactive behavior.

### Details
- Replaced `asset()` references with `@vite()`
- Added page header with breadcrumb, moved "Manage Courses" button to header actions
- Extracted filter controls into a shared filter bar with live results count
- Added `data-name`, `data-email`, `data-roles` attributes on each table row for JS filtering
- Added small avatar circle next to each user's name in the table
- Role badges are now color-coded: admin (amber), teacher (blue), student (green)
- Replaced native browser `confirm()` delete dialog with a Bootstrap modal
- Delete button carries `data-user-name` and `data-delete-url` — one modal reused for all rows
- Removed `onsubmit` inline JS and `style="display:inline"` from delete form
- Added styled empty state for both no-data and no-search-results cases
- Pagination only renders when `$users->hasPages()` is true
- Created `users.js` with live search, role filter, clear button, and delete modal wiring

### Files Affected
- `resources/views/admin/users.blade.php` *(rewritten)*
- `resources/sass/pages/_users.scss` *(created)*
- `resources/js/admin/users.js` *(created)*
- `vite.config.js` *(updated — added users entry points)*

---

## [2026-03-18]

**Author:**
**Module/Area:** Frontend — SCSS Component Architecture
**Type:** Refactor

### Description
Extracted shared UI patterns from page-specific SCSS files into reusable component partials. Unified role badge styles and search input styles that were duplicated across multiple pages.

### Details
- Created `components/_page-header.scss` — extracted `.dash-header` and breadcrumb styles from `_dashboard.scss`
- Created `components/_search.scss` — extracted `.dash-filters`, `.dash-filter-group`, `.search-wrap`, `.search-clear`, `.results-count` from `_department.scss` and `_users.scss`
- Created `components/_badges.scss` — unified `.role-badge` variants (admin, teacher, student) previously duplicated in `_sidebar.scss` and `_users.scss`. Also contains `.dash-empty` shared empty state
- Added `--on-dark` modifier to `.role-badge` for sidebar use on dark backgrounds
- Removed extracted styles from `_dashboard.scss`, `_department.scss`, `_users.scss`, and `_sidebar.scss`
- Renamed all `.dept-*` prefixed classes to generic names: `.dept-search-wrap` → `.search-wrap`, `.dept-search-clear` → `.search-clear`, `.dept-results-count` → `.results-count`
- Renamed `.users-role-badge` and `.fcard__role-badge` to unified `.role-badge` across all Blade and JS files
- Updated `app.scss` to import all three new component partials globally
- Updated `default.blade.php` sidebar role badges to use `.role-badge--on-dark` modifier

### Files Affected
- `resources/sass/app.scss` *(updated)*
- `resources/sass/components/_page-header.scss` *(created)*
- `resources/sass/components/_search.scss` *(created)*
- `resources/sass/components/_badges.scss` *(created)*
- `resources/sass/layouts/_sidebar.scss` *(updated — removed old .role-badge block)*
- `resources/sass/pages/_dashboard.scss` *(updated — removed extracted styles)*
- `resources/sass/pages/_department.scss` *(updated — removed extracted styles)*
- `resources/sass/pages/_users.scss` *(updated — removed extracted styles)*
- `resources/views/layouts/default.blade.php` *(updated — role badge class names)*
- `resources/views/admin/department.blade.php` *(updated — class renames)*
- `resources/views/admin/users.blade.php` *(updated — class renames)*
- `resources/js/admin/department.js` *(updated — class renames)*
- `resources/js/admin/users.js` *(updated — class renames)*

### Notes
Any future page that needs a search input, filter bar, page header, or role badge should use the component classes and does not need to redefine them in its own page SCSS file.

---------------------------- TEMPLATE ----------------------------

## [YYYY-MM-DD]

**Author:** Name  
**Module/Area:** (optional – e.g., Authentication, Survey System, CQI Report Generator)  
**Type:** Feature | Fix | Improvement | Refactor | Documentation | Security

### Description
Short explanation of the change.

### Details
- Bullet points explaining what was done
- Mention files or components modified
- Include technical notes if needed

### Files Affected
- path/to/file1.php
- path/to/file2.blade.php
- app/Http/Controllers/ExampleController.php

### Notes
Optional notes, testing results, or related issues.