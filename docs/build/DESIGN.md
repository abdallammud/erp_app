# Visual design reference

Binding for every screen built from here forward — see [`DECISIONS.md#d-040`](DECISIONS.md#d-040). Extracted from `Nova Humanitarian HRM.pdf` (project root) by direct visual inspection of ~22 representative pages (Sign In, Home/Dashboard, My Profile's five tabs, Dependents, Leave, Timesheet, Self-Assessment, Asset Verification, Safeguarding — each sampled for its list view and its create/edit modal). This document is the token set; re-read the PDF itself (via the `Read` tool's `pages` param, max 20 pages per call) for a screen not covered here before improvising a new pattern.

## Colors

- **Primary action / CTA buttons: near-black**, not a brand accent — `bg-slate-900 hover:bg-slate-800`, white text. Confirmed across Sign In, Edit Profile, Add Dependent, Request Leave, New Self-Assessment, Submit Timesheet — every primary button in the reference uses the same near-black, not a colored one. This is easy to get wrong if assuming "primary = brand color."
- **Accent / links / active nav state: blue** (`blue-600`/`blue-700`), not indigo.
- **Secondary buttons**: white background, `border-slate-300`, `text-slate-700`.
- **Danger**: `red-600`/`red-700`.
- **Badges/pills**: soft background + matching darker text — success (emerald), warning (amber), info (blue), danger (red), purple, neutral (slate). See `<x-badge>`.
- **Surface**: `bg-white` cards on `bg-slate-50` page background, `border-slate-200` borders, `rounded-xl`.

## Layout

- Persistent sidebar (nav) + a top **header bar** on every page: page title (large, bold) with the signed-in user's **email** as a constant subtitle underneath (not page-specific descriptive text — every screen in the reference shows the same user email under its title), plus a notification bell (red dot when unread) on the right. See `resources/views/components/layouts/app.blade.php`.
- Main content area: `max-w-6xl`, centered, `p-4 md:p-10`.
- Detail/settings screens with sub-sections (My Profile, etc.) use a **tab bar** inside a single card, not stacked separate cards or separate pages. Tabs: icon + label, active tab gets a `border-blue-600 text-blue-700` underline.

## Components

- `<x-button variant="primary|secondary|danger" tag="button|a">` — `resources/views/components/button.blade.php`.
- `<x-badge variant="success|warning|info|danger|purple|neutral">` — `resources/views/components/badge.blade.php`.
- `<x-stat-card label icon color value subtext>` — `resources/views/components/stat-card.blade.php`. Used for dashboard/summary number tiles.
- `<x-modal name maxWidth>` + `<x-modal-header title subtitle>` — Alpine-based. `maxWidth` accepts `sm`/`md`/`lg`/`xl`/`2xl`/`3xl` (default `md`); pick the smallest that comfortably fits the form's field count — Organization's forms use the default, Employees' larger form uses `2xl`. See "Modal open/close event contract" below for the exact trigger/close API — it's stricter than it looks, get this wrong and a modal silently won't close. The reference puts every create/edit form (Add Dependent, Request Leave, etc.) in a modal over the list view, not a separate page or an inline expanding form. A component with a form needs, at minimum: an `openCreate()` action (resets fields, dispatches `open-modal`) wired to the "Add X" button, `edit()` also dispatching `open-modal`, and both `save()` and `cancel()` dispatching `close-modal` — see `App\Livewire\Organization\Departments` or `App\Livewire\Hrm\Employees` for the reference implementation.
- Tabs: `x-data="{ tab: 'key' }"` + `x-show="tab === 'key'"` + `x-cloak` on pre-mounted Livewire components (see `my-profile.blade.php`) — instant switching, no extra request, since every tab's component is already mounted on page load.

## Applied so far vs. still pending

**Retrofitted to this system:** global layout/top bar, all Blade files' color tokens (indigo→blue, primary buttons→slate-900), My Profile (tabbed), Dashboard (profile header card + stat cards + quick actions, `Home.php`/`home.blade.php`), Organization — all four sections (Departments/DutyStations/Positions/SalaryGrades) now use `<x-modal>` create/edit forms instead of inline forms, plus `<x-badge>` for code/grade tags. Employees create/edit — also modal-based now (`maxWidth="2xl"`, the widest of the modal sizes, given how many fields the form carries). The legacy Breeze `/profile` page's Delete Account confirmation was also fixed to use the current modal contract (it was still on the pre-rewrite Jetstream-style `<x-modal :show focusable>` API and had silently broken — see the "modal open/close event contract" note below).

**Still using the old ad-hoc styling / inline forms, pending retrofit:** Audit Log, Super Admin portal, Approvals demo, Notifications, PersonalDetails' dependent/contact-info forms, coming-soon placeholder pages. Track progress against this list rather than assuming "the design pivot" is a single finished step — see `CHANGELOG.md` for what's landed in which session.

## Modal open/close event contract (bug fixed 2026-09-29)

`<x-modal>`'s `open-modal`/`close-modal` window events always carry an **object** detail with a `name` key — `{ name: '...' }` — never a bare string. This matches what Livewire's own `$this->dispatch('close-modal', name: '...')` produces (named params become the detail object); a plain JS/Alpine trigger must match that shape too: `$dispatch('open-modal', { name: '...' })`, not `$dispatch('open-modal', '...')`. An earlier version of the listener compared `$event.detail` directly against the modal's name string, which could only ever match a bare-string dispatch — a Livewire-side `close-modal` dispatch (always object-shaped) silently never closed anything. Caught while wiring Organization's modals, not by any automated test (Blade/Alpine event wiring isn't something Larastan/Pest/Pint can see) — a Pest test was added per Organization component afterward (`assertDispatched('open-modal', name: '...')` / `assertDispatched('close-modal', name: '...')`) specifically to lock in the dispatch call is made correctly, even though it can't verify the Alpine listener itself receives and applies it.

## Rule going forward

Per the user's explicit standing instruction: **every new screen or component built from this point on must follow this design system** — modal-based create/edit (not inline forms or new pages), `<x-button>`/`<x-badge>`/`<x-stat-card>` instead of ad-hoc Tailwind classes, near-black primary buttons, blue accents. If a new screen's pattern isn't covered above, check the reference PDF for the closest matching screen before improvising.
