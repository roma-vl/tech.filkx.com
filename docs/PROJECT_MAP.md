# Project Map — tech.filkx.com (FilkxTech)

Navigation reference for orienting quickly in this codebase. Verified against actual code on 2026-08-16.
For the production-readiness audit and roadmap, see `docs/DEVELOPMENT_PLAN.md`.

## What this is

An e-commerce platform ("FilkxTech") — Laravel API backend + Vue 3 SPA frontend, selling physical
products (electronics, per seeded data) with catalog, cart, checkout, reviews, coupons/promotions,
a blog, an admin back-office, and RBAC-based staff accounts.

## Top-level layout

```
api/           Laravel 12 backend (PHP 8.2, PostgreSQL, Redis, Passport, Scout+Meilisearch)
frontend/      Vue 3 + Vite SPA (client-side rendered only — see "SSR" note below)
scraper/       Standalone Python scripts, NOT part of the runtime app (see below)
docs/          This file + DEVELOPMENT_PLAN.md
Makefile, docker-compose*.yml
implementation_roadmap.md   Stale June 2026 planning doc — see DEVELOPMENT_PLAN.md for current audit
```

### `scraper/`
`scrape_sota.py`, `scrape_blog.py` scrape product/blog content into `products.json` /
`blog_posts.json`; `generate_seeder.py` / `generate_blog_seeder.py` turn that JSON into Laravel
seeder PHP. Output seeders live in `api/database/seeders/` (`ProductsFromSotaSeeder`,
`BlogPostsFromSotaSeeder`), both wired into `DatabaseSeeder`. This is a one-time content-import
tool, not something the running app depends on.

---

## Backend (`api/`)

### Routing
- `api/routes/v1/api.php` — the entire public + authenticated + admin API, mounted under `/v1`.
  Organized as: public auth (`/v1/auth/*`), public catalog (`/v1/catalog/*`), public blog
  (`/v1/blog/*`), public CMS pages (`/v1/pages/*`), cart (`/v1/cart/*`, guest-friendly via
  session, `merge` requires auth), checkout (`/checkout`, `/checkout/quick`), coupon validation.
  Everything under the top-level `auth:api` + `IdentifyImpersonation` group covers profile,
  favorites/compare/viewed-products, sessions, support tickets, notifications, reviews (write side).
  Nested inside that, `Route::prefix('admin')->middleware([..., 'role:admin|administrator|moderator|support'])`
  covers the entire admin back office (stats, settings, users, roles, products, orders, categories,
  brands, attributes, accounting/billing, marketing (coupons/promotions), notifications, blog CMS,
  static pages CMS, support, server logs).
- `api/routes/wishlist.php` — separate file, loaded via a dedicated
  `App\Providers\WishlistServiceProvider` (`loadRoutesFrom`), NOT auto-discovered — don't miss it
  when grepping only `api.php`. Covers `/api/v1/wishlist/*`, `auth:api` only.
- `api/routes/web.php` — just the default Laravel welcome view, unused by the SPA.
- `api/routes/console.php` — the stock `inspire` Artisan command only.
- Health check: default Laravel `/up` endpoint (registered via `health:` in `bootstrap/app.php`),
  not a custom one.
- `bootstrap/app.php` registers only one middleware alias: `role` → `RoleMiddleware`. No
  `throttle:` groups are applied anywhere in `api.php` — rate limiting is not yet in place beyond
  Laravel's framework defaults.

### Admin vs public API separation
Not separate route files/prefixes at the HTTP-guard level — both live in `api.php`. Separation is by:
- **Namespace**: `App\Api\V1\*` (public/customer) vs `App\Api\Admin\*` (admin), each with its own
  `Controllers/`, `Requests/`, `Resources/`, `Actions/`, `Dto/`. Admin additionally has `Policies/`
  and `Repositories/` subfolders (both present but currently empty — scaffolded, unused).
- **Middleware**: admin routes require `auth:api` + `IdentifyImpersonation` + `role:admin|administrator|moderator|support`.
  `RoleMiddleware` (`api/app/Http/Middleware/RoleMiddleware.php`) checks `$user->hasAnyRole()` against
  a pipe-separated role-slug list passed as the middleware parameter.

### Auth
Laravel Passport (`oauth_*` tables via migrations `2025_12_02_2228*`), `auth:api` guard. Plus a
custom `OAuthAccount` model/`OAuthController` for third-party social login (routes:
`/oauth/{provider}/redirect|callback|connect|disconnect`). `IdentifyImpersonation` middleware
exists (support for staff impersonating a user session — check `api/app/Http/Middleware/` for
details before relying on it in security-sensitive code).

### RBAC
Real, not aspirational: `Role`, `Permission` models with `roles`↔`permissions` (`permission_role`),
`roles`↔`users` (`role_user`, with `granted_by`/`granted_at`/`expires_at`), and a separate direct
`permissions`↔`users` grant table (`permission_user`) for one-off grants. `Role::scope` is
`global`/`contextual` (`RoleScopeEnum`). System roles/permissions are (re)seeded via
`RolesAndPermissionsSeeder`, invoked from migration `2025_12_17_135120_seed_roles_and_permissions.php`
(deletes rows where `is_system = true` then re-seeds — custom non-system roles survive re-runs).
`AuditLog` + `permission_changes_log` tables track security-relevant changes.

### Domain models (verified from `api/app/Models`, 29 files)
- **Catalog**: `Category` (self-referencing tree via `parent_id`, JSON multi-language `name`),
  `Brand`, `Product` (JSON `name`/`description`, `is_hot`/`is_recommended` flags, `Laravel\Scout\Searchable`
  with `toSearchableArray()` indexing uk/en name+description), `ProductVariant` (SKU, barcode,
  price/old_price, weight, JSON `dimensions` incl. images), `Attribute`/`AttributeValue`/
  `ProductAttributeValue` (EAV pattern for variant/product specs), `ProductReview` (with
  `approvedReviews` scope-style relation, moderation status).
- **Inventory**: `Warehouse`, `Stock` (`variant_id`, `warehouse_id`, `quantity`, `reserved`) —
  `ProductVariant::getTotalQuantityAttribute()` computes `quantity - reserved`. Stock reservation on
  order placement is implemented in `CheckoutController`/`PlaceOrderAction` (see below), not just
  schema.
- **Cart/Order**: `Cart`/`CartItem` (guest via `session_id` or `user_id`), `Order`/`OrderItem`
  (order_number formatted `FKX-YYYYMMDD-XXXXXX`, `payment_method`/`payment_status`/`status` still
  plain string fields, no gateway enum). LiqPay is integrated on top as of commit `1fd50e8`
  (`LiqPayService`, `PaymentController`, `orders.payment_reference`/`paid_at`) — see
  DEVELOPMENT_PLAN §2.1 for what's wired vs. still pending (real merchant keys).
- **Marketing**: `Coupon` (code, type, amount, usage_limit/used_count, expires_at), `Promotion`
  (type, amount, start/end date) — flat, no rule-engine/target-scoping tables yet.
  Actual coupon *validation* logic lives in `App\Api\V1\Actions\Coupon`.
- **Users/Auth/RBAC**: `User`, `OAuthAccount`, `Role`, `Permission`, `AuditLog`.
- **Support**: `SupportTicket`, `SupportMessage`.
- **Content/CMS**: `BlogPost`, `BlogCategory`, `BlogTag`, `Page` (static/CMS pages, admin CRUD via
  `AdminPageController`, public read via `PageController` at `/v1/pages/{slug}`).
- **Misc**: `Setting` (system settings, admin-managed), `Notification`, `Wishlist`.

### Where business logic lives
Pattern actually used is **Action classes + thin controllers + Resources/DTOs**, NOT the DDD
`app/Domain|Application|Infrastructure` layering that `implementation_roadmap.md` describes as the
target — that structure was never built. Concretely:
- `app/Api/V1/Actions/{Cart,Notification,User,Coupon,Checkout}/…` and `app/Api/Admin/Actions/{Product,Order,Brand,Attribute,...}/…`
  hold single-responsibility "do one thing" classes (e.g. `PlaceOrderAction`), invoked from
  controllers via constructor/method injection.
- `app/Api/V1/Dto/` and `Admin/Dto/` — typed request→data transfer objects (e.g. `PlaceOrderDto::fromRequest()`).
- `app/Api/V1/Resources/`, `Admin/Resources/` — JSON:API-ish response transformation (Laravel API Resources).
- `app/Services/` is nearly empty (`Auth/`, `WishlistService.php` only) — most logic is in Actions, not Services.
- `app/Api/Admin/Repositories/`, `app/Api/Admin/Policies/`, `app/Api/V1/Middleware/` exist as
  directories but are **empty** — scaffolded early, never used. Don't assume code lives there.
- Only one Observer (`ProductVariantObserver`), one Job (`NotifyProductWishlistsJob`), one custom
  Event (`AuditEvent`) exist. No Laravel Broadcasting/Pusher/WebSocket usage anywhere in `api/app`
  or `frontend/src` — despite "Broadcast" appearing in admin notification action names
  (`BroadcastNotificationAction`), that's just "send a notification to many users", not a
  websocket/broadcast channel.

### Search
`laravel/scout` is a real composer dependency, `Product` model uses the `Searchable` trait, and
`config/scout.php` / `.env.example` default `SCOUT_DRIVER=meilisearch` pointing at
`tech-meilisearch:7700`. Meilisearch is not just "provisioned but unused" — it's actually wired at
the model layer. (Whether the index is populated/kept in sync in practice — e.g. via
`scout:import` after seeding — should be verified operationally; no scheduled re-index job exists
in `app/Console/Commands`.)

### Testing (backend)
As of 2026-08-16 (commit `ca5ef97` + Feature tests added alongside the 2FA/newsletter/home-banner
work), `api/tests/` has real coverage — the Laravel-generated stubs are gone. `Unit/`:
`Services/Auth/AuthServiceTest.php` (29 tests), `Services/Auth/TwoFactorAuthenticationServiceTest.php`,
`Actions/User/TwoFactor/{Enable,Confirm,Disable,RegenerateRecoveryCodes}ActionTest.php`,
`Http/Middleware/RoleMiddlewareTest.php`. `Feature/`: `Auth/AuthControllerTest.php`,
`Auth/TwoFactorAuthenticationControllerTest.php`, `Home/HomeControllerTest.php`,
`Newsletter/NewsletterControllerTest.php`, `Admin/AdminHomeBannerControllerTest.php`. This is real,
substantial coverage of auth/RBAC/2FA — but still narrow: checkout, orders, cart, catalog, and
coupons remain untested. Run via `make test-backend` — but note the Makefile bug below, which means
this suite can't actually be run through the documented command today.

---

## Frontend (`frontend/src`)

### Routing
`src/router/index.js` composes `src/router/routes.js` + `src/router/routes/{public,auth,admin,application,billing}.js`.
`public.js` includes `terms`/`privacy` static-page routes (rendered via `pages/static/StaticPage.vue`,
fetching from `/v1/pages/{slug}`). As of commit `75517a9`, the `pages` table has real
terms/privacy/oferta/cookies content (not placeholder text) — still explicitly labeled as
lawyer-unreviewed drafts, see DEVELOPMENT_PLAN §2.8.

### State management (Pinia)
Real stores live under FSD-style locations, with `src/stores/*.js` reduced to **re-export shims**
(good — not duplicated logic):
- `src/entities/user/model/authStore.ts` — auth/session/token (customer + separate admin token
  keys), re-exported by `src/stores/auth.js`.
- `src/entities/order/model/cartStore.ts` — cart, wishlist, compare, viewed-products, toasts,
  active-drawer UI state; talks to the real API via `orderApi`/`authApi` (not demo data).
- `src/shared/model/uiStore.ts` — re-exported by `src/stores/ui.js`.
- `src/entities/user/model/systemStore.ts`, `subscriptionStore.ts` — re-exported by
  `src/stores/system.js` / `subscription.js`.
- `src/stores/admin/runnerNodesStore.js` and `runnerTranscodersStore.js`, previously noted here as
  dead leftover code from a different project, are **gone** (removed in commit `cb887b2`).

### FSD-like layering — partially, not fully, consistent
`entities/`, `features/`, `widgets/`, `shared/`, `pages/` are all populated and are where current
development happens. However a **parallel legacy `src/components/` tree still exists and is
actively used**, not just a stray leftover:
- `components/admin/{ui,features/*}` — a large parallel admin UI kit (`App*`-prefixed: `AppButton`,
  `AppModal`, `AppTable`, etc., ~30 components) plus most admin feature screens (catalog, blog,
  marketing, accounting, orders, roles, support, team, analytics, client, logs, pages, system,
  billing, emails).
  Coexists with `shared/ui` (`Ui*`-prefixed: `UiButton`, `UiSelect`, etc.) which is the
  customer-facing kit — two naming conventions, two component libraries, by area (admin vs
  storefront) rather than true duplication.
- `components/application/features/{settings,common,admin,support}` — more admin/account-area
  feature components.
- Despite the two parallel trees, an actual **basename collision check across all of `src/**/*.vue`
  found only 2 duplicates** (`OrderDetailsModal.vue` exists in both `widgets/OrderTable/` and
  `components/admin/features/orders/`; `SupportChatInput.vue` exists in both
  `components/application/features/support/` and `components/admin/features/support/`) — the
  previously-recorded "37 duplicate UI component names" and "duplicate `ProductCard`" no longer
  hold; only one `ProductCard.vue` exists now (`widgets/Catalog/ProductCard.vue`). Treat old notes
  about this as stale.
- `components/home/` (claimed in old notes to exist outside `widgets/`) does **not** exist —
  home page composition now lives correctly under `widgets/Home/` + `features/home/`.

### API client layer
`src/shared/services/api/` is the canonical layer: `apiClient.ts` (axios instance + interceptors),
`authApi.ts`, `orderApi.ts`, `productApi.ts`, `accountingApi.ts`, `billingApi.ts`, `index.ts`.
Direct `axios.*` calls bypassing this layer are now down to **5 files** (verified via grep), all
admin pages/components: `pages/admin/{AdminTeam,AdminSupport,AdminDashboard}.vue`,
`components/admin/features/support/{SupportUserSidebar,SupportStatsView}.vue`. A separate legacy
singleton `src/services/AccountingService.js` also exists outside the `shared/services/api` layer.
(Old notes claimed ~30 files bypass the API layer — that's now stale; the number is much smaller.)

### i18n
`src/lang/{public,admin}/{en,uk}.js`, wired through `src/lang/index.js` (vue-i18n). Two locale
namespaces (public storefront vs admin) rather than one flat catalog.

### SSR — still not implemented; static prerendering + sitemap generation now exist instead
The `"serve:ssr": "node server.js"` npm script and the Makefile's `frontend-ssr` target are still
dangling — **`frontend/server.js` does not exist**, there is no live Node SSR process, and none was
added. That was a deliberate choice (static prerendering over full SSR), not an oversight — see
below. `docker-compose-production.yml` still serves the frontend as `tech-frontend` (nginx + static
`dist/`), unchanged.

What now exists (working tree, uncommitted as of 2026-08-17 on `add-new-logic`):
- `frontend/src/entry-server.ts`: a minimal SSR-capable entry, separate from `main.js`. It mirrors
  only what's needed to *render* a page (pinia, its own router instance, i18n, `@vueuse/head`) and
  deliberately skips Sentry/reCAPTCHA/vue-toastification (client-interactivity concerns; the latter
  is also a CJS module Vite's SSR module runner can't import as named exports). It also uses its own
  route table — a subset of `@/router/routes` (home/catalog/product-detail/blog/blog-post only) —
  rather than the full app route table, so it doesn't pull in admin/auth pages and their
  browser-only dependencies.
- `frontend/scripts/prerender.mjs`: run as a `postbuild` npm script (`package.json`). Boots a Vite
  dev server in middleware mode (`ssrLoadModule`, no bundling, no headless browser), fetches a
  representative set of real routes from the public API (`productApi`, `apiClient`), renders each
  with `@vue/server-renderer`'s `renderToString()`, and writes `dist/<route>/index.html`. Verified
  end-to-end (see DEVELOPMENT_PLAN.md §2.6) to produce real content — product name/price/JSON-LD,
  blog post title/body, catalog product grid, home page sections — not empty shells, for `/`,
  `/catalog`, every prerendered `/product/:id`, `/blog`, and every prerendered `/blog/:slug`.
- `frontend/scripts/generate-sitemap.cjs`: also run as part of `postbuild`. Plain CJS + axios (no
  Vite/Vue involved), paginates the real `/v1/catalog/categories`, `/v1/catalog/products`, and
  `/v1/blog/posts` endpoints and writes `dist/sitemap.xml`. Not a dangling reference anymore.
- To make prerendering actually produce populated (not loading-skeleton) HTML, the composables/pages
  that fetch their data in `onMounted` (which never fires under SSR — there's no DOM) also register
  the same fetch via `onServerPrefetch`, which `renderToString()` awaits before serializing:
  `features/catalog/composables/useCatalog.ts`, `features/product/composables/useProductDetail.ts`,
  `features/home/composables/useHome.ts`, `pages/blog/BlogPage.vue`, `pages/blog/BlogPostPage.vue`,
  `widgets/Home/TechBlog.vue`, `widgets/Home/CatalogSection.vue`, `widgets/Header/Header.vue` (mega
  menu categories), `widgets/Footer/Footer.vue` (footer categories). This has no effect on client
  behavior — `onServerPrefetch` hooks are simply never invoked outside SSR.

### Testing (frontend)
`package.json` defines `test:unit` (vitest) and `test:e2e` (playwright), but there is **no
`vitest.config.*`, no `playwright.config.*`, and zero `*.spec.*`/`*.test.*` files anywhere in
`frontend/src`**. Frontend testing is entirely unset up, not just low-coverage.

---

## Known technical debt / inconsistencies (verified 2026-08-16, re-checked 2026-08-17 against
`add-new-logic`, which has diverged from `master` by 14 commits since — items below are corrected
where those commits changed the picture)

1. **`Makefile` `test-backend` target is broken**: `docker compose run --rm tech-api-php-cli php artisan`
   — missing the `test` argument, so it runs `php artisan` (prints command list) instead of the test
   suite. (`Makefile:27-28`) **Still broken** — and now higher-priority to fix, since a real test
   suite exists to run (see "Testing (backend)" above) and currently can't be reached this way.
2. **SSR is still a dangling reference** (`serve:ssr` script, no `server.js` — by design, static
   prerendering was chosen instead, see "SSR" above). **Sitemap generation is no longer dangling**:
   `frontend/scripts/generate-sitemap.cjs` and `frontend/scripts/prerender.mjs` now exist and are
   wired into `postbuild`, verified working end-to-end (uncommitted working-tree state as of
   2026-08-17 — see DEVELOPMENT_PLAN.md §2.6 for verification detail).
3. **Frontend has zero test infrastructure** despite `test:unit`/`test:e2e` scripts existing. Still
   true, unchanged.
4. ~~Backend has zero real test coverage~~ **No longer true** (commit `ca5ef97` + Feature tests
   added alongside 2FA/newsletter/home-banner work) — see "Testing (backend)" above for what's
   covered; still missing coverage on checkout/orders/cart/catalog/coupons.
5. **Dead code from a different project** ("Filkx Live", a video-streaming SaaS — the actual
   previous identity of the stale root `CLAUDE.md`):
   - ~~`frontend/src/stores/admin/runnerNodesStore.js`, `runnerTranscodersStore.js`,
     `components/admin/ui/OptimizeVideoModal.vue`~~ — **already removed** (deleted in commit
     `cb887b2`, the same commit that originally wrote this doc — the doc just hadn't caught up).
   - Still present: `frontend/src/components/admin/ui/ContentRestrictionBanner.vue`,
     `FeatureLockOverlay.vue`, `TrialActivationBanner.vue` — SaaS-plan/video-feature-gating
     components, unreferenced except in a stale code comment in `router/index.js`
     (`// Removed featureKey guard to allow access to locked features with FeatureLockOverlay`).
   - ~~`docker-compose.yml`'s `tech-api-postgres` healthcheck uses `pg_isready -U live -d live`~~ —
     **already fixed**, now reads `pg_isready -U tech -d tech` (`docker-compose.yml:124`), matching
     the actual configured user/db.
   - ~~Leftover Ukrainian-language i18n strings referencing transcoder/runner concepts~~ — **already
     gone** from `frontend/src/lang/admin/{en,uk}.js` (no `runner`/`transcoder` matches remain).
6. **Empty scaffolded directories**: `api/app/Api/Admin/Policies/`, `api/app/Api/Admin/Repositories/`,
   `api/app/Api/V1/Middleware/` exist but contain no files — planned structure that was never used.
   Still true, unchanged.
7. ~~No payment gateway integration~~ **Done** (commit `1fd50e8`) — LiqPay hosted checkout, see
   `DEVELOPMENT_PLAN.md` §2.1. `quickOrder()`'s one-click "buy now" path still hardcodes
   `payment_method => 'cod'`, unlike the main cart checkout flow.
8. ~~No rate limiting~~ **Done** (commits `970661c`, `75517a9`, `eaafd44`) — `throttle:` now covers
   auth, checkout/payment, coupon validation, and newsletter subscribe. See `DEVELOPMENT_PLAN.md`
   §2.2 for the full list.
9. **No CI**: no `.github/` directory anywhere in the repo — no GitHub Actions workflows exist.
   Still true, unchanged.
10. **Largest Vue files** (candidates for splitting, not re-measured this pass — line counts may
    have shifted slightly with the homepage/header/cart rewrites, but the same files are still the
    largest): `widgets/Account/tabs/AccountSettingsTab.vue` (1225 lines),
    `components/admin/features/catalog/ProductFormModal.vue` (1145 lines),
    `widgets/Account/tabs/AccountOrdersTab.vue` (869 lines), `widgets/Header/Header.vue` (~750+
    lines), `components/admin/features/catalog/ProductsTab.vue` (724 lines).
