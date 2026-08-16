# Development Plan — tech.filkx.com (FilkxTech)

Production-readiness and growth plan. Audited against actual code on 2026-08-16 (branch `master`,
clean tree). Companion to `docs/PROJECT_MAP.md` (navigation) and supersedes the stale
`implementation_roadmap.md` (written 2026-06-11, before ~20 subsequent "fix" commits) for status
purposes — that file's checkboxes are corrected below, item by item.

Legend: **✅ verified-done** — **🟡 partially done** — **❌ verified-missing** — **❓ unverified/assumed**

---

## 1. Current state audit — `implementation_roadmap.md` items re-verified

### Backend, "already done" claims (June doc) — re-checked
| Claim | Status | Evidence |
|---|---|---|
| Users table, locale/timezone | ✅ | `User` model, migrations `add_locale_in_users`, `add_timezone_in_users` |
| OAuth (Passport + social accounts) | ✅ | `oauth_*` migrations, `OAuthAccount` model, `OAuthController` |
| RBAC (roles/permissions) | ✅ | `Role`/`Permission` models, `role_user`/`permission_role`/`permission_user`, `RoleMiddleware` |
| Security/audit logging | ✅ | `AuditLog` model, `audit_logs` + `permission_changes_log` tables, `AuditEvent` |
| Notification preferences | ✅ | `add_notification_preferences_to_users` migration, `NotificationController` |
| Support tickets | ✅ | `SupportTicket`/`SupportMessage`, full `SupportController` + `AdminSupportController` |
| System settings | ✅ | `Setting` model, `AdminSettingsController` |

### Backend, "e-commerce domain absent" — **this is the headline correction: it is now built**
| Claim (June: `[ ]` not started) | Actual status now | Evidence |
|---|---|---|
| Catalog (brands, categories, attributes, products, variants) | ✅ done | `Category` (tree), `Brand`, `Product` (+Scout search), `ProductVariant`, EAV `Attribute`/`AttributeValue`/`ProductAttributeValue`; migrations `create_catalog_tables`, `create_attributes_tables`, `create_category_attribute_table` |
| Warehouses/stock | ✅ done | `Warehouse`, `Stock` (`quantity`/`reserved`), reservation logic live in `CheckoutController::quickOrder` (`lockForUpdate`, increments `reserved`) |
| Pricing/discounts | 🟡 partial | `ProductVariant.price`/`old_price` exist; `Coupon`/`Promotion` models + `create_coupons_and_promotions_tables` exist and have admin CRUD (`AdminMarketingController`) and public validation (`CouponController`, `Api\V1\Actions\Coupon`) — but no dedicated `PriceCalculationService`, no date-scoped/rule-based promotion engine, no per-product/category targeting on promotions (flat `amount`+`type`+date-range only) |
| Orders/Checkout | ✅ done | `Order`/`OrderItem`, `Cart`/`CartItem`, `CheckoutController` (`placeOrder` via `PlaceOrderAction`, `quickOrder` inline), `AdminOrderController` for status management |
| Queues/search/CDN | 🟡 partial | Redis queue configured and running (`tech-api-queue` container, `queue:work`), Meilisearch wired via Scout on `Product` — but **no CDN/S3 integration** (see §2.5) and only one queued Job exists (`NotifyProductWishlistsJob`) |
| REST endpoints `GET /catalog/categories`, `/catalog/products`, `/catalog/products/{slug}`, `POST /cart/sync` (June: `[ ]`) | ✅ done (different shape) | `CatalogController::categories/products/product`, `CartController::show/add/updateItem/removeItem/merge` — cart sync is per-item REST, not a single bulk `/sync` endpoint, but the functional need is met |

### Frontend, "already done" claims — re-checked
| Claim | Status | Evidence |
|---|---|---|
| Home page design (Hero, Categories, FlashDeals, Recommended, USP, Blog) | ✅ | `widgets/Home/*`, `features/home/*` |
| Header/Footer | ✅ | `widgets/Header/Header.vue` (753 lines), `widgets/Footer/Footer.vue` (477 lines) |
| User cabinet (orders/wishlist/settings/support tabs) | ✅ | `widgets/Account/tabs/*` |
| Product cards w/ variants, reviews, discounts | ✅ | `widgets/Catalog/ProductCard.vue`, `entities/product` |
| Quick-view modal | ❓ unverified | not directly inspected this pass; likely under `features/product` or `widgets/ProductDetail` |
| Cart/wishlist/compare via Pinia | ✅ | `entities/order/model/cartStore.ts` |

### Frontend, "integration absent, demo data" — **corrected: mostly resolved**
| Claim (June: `[ ]`) | Actual status now | Evidence |
|---|---|---|
| Home components wired to real API instead of demo data | ✅ done | `features/home/composables/useHome.ts` calls `productApi` (real HTTP), maps live product/variant/attribute payloads — not a static array. (One `// mock discount` fallback remains for computing a display discount % when no real `old_price` is present — cosmetic, not data-source-level demo data.) |
| Cart/wishlist persistence (LocalStorage + sync) | ✅ done | `cartStore.ts` persists wishlist/compare/viewed to `localStorage`, and dedicated sync endpoints exist backend-side (`/user/favorites/sync`, `/user/compares/sync`, `/user/viewed-products/sync`) |
| Full checkout process | ✅ done (functionally) | `CheckoutController`, wired through `entities/order` + checkout pages — but **no real payment gateway behind it** (see §2.4) |

**Bottom line: the "e-commerce core absent, frontend on demo data" framing of `implementation_roadmap.md` is obsolete.** Catalog, cart, checkout, orders, coupons, reviews, wishlist, and blog are all real, API-backed, and reasonably wired end to end. The roadmap's Stage 1 and most of Stage 2/3 are functionally done; remaining gaps are narrower and listed below.

---

## 2. Path to production launch (prioritized)

### 2.1 E-commerce fundamentals — remaining gaps
- ❌ **Payment gateway integration.** `Order.payment_method` is a free string (`'cod'` hardcoded in
  `quickOrder`); no Monobank/LiqPay/Stripe SDK in `api/composer.json`, no webhook handler, no
  `Transaction`/`Payment` model. This blocks real online payment — currently the only supported
  flow is effectively manual/offline (cash on delivery, or admin manually confirming a bank
  transfer via `AdminAccountingController::confirmPayment` + `viewProof`, which exist but are a
  billing/subscription-style manual-proof flow, not checkout-integrated card payment).
  **Action**: pick one gateway (LiqPay or Monobank Acquiring are the standard choices for a UA
  storefront), add `Transaction` model + webhook endpoint + idempotent status reconciliation.
- 🟡 **Promotions/pricing engine.** Flat `Promotion`/`Coupon` models work for simple % / fixed-amount
  discounts but have no per-category/per-product targeting, stacking rules, or a
  `PriceCalculationService` to centralize "what does this variant cost right now" logic (currently
  computed ad hoc in controllers/frontend). Fine for launch, worth hardening before heavy marketing use.
- ❓ **Shipping/delivery integration.** `Order` has `delivery_method` (seen value: `'nova_poshta'`),
  `shipping_city`/`shipping_address`/`carrier`/`tracking_number` fields exist, but no evidence of
  an actual Nova Poshta API integration (rate calculation, address autocomplete, label generation)
  was found in this pass — worth a dedicated check before claiming it's automated vs. manually
  entered by staff.
- ✅ Stock reservation on order placement is real (`Stock.reserved`, `lockForUpdate` in
  `quickOrder`). Confirm `PlaceOrderAction` (the main checkout path) does the same locking —
  verify before shipping a high-traffic sale.

### 2.2 Security hardening
- ❌ **No rate limiting** anywhere in `api/routes/v1/api.php` — no `throttle:` middleware on login,
  register, password-reset, coupon-validate, or checkout endpoints. These are the classic
  brute-force/abuse targets for a public storefront. **Action**: add `throttle:` groups before
  launch, at minimum on `/auth/login`, `/auth/register`, `/auth/password/*`, `/checkout*`,
  `/coupons/validate`.
- ✅ RBAC is real and enforced via `role:` middleware on the entire `/admin` group.
- ✅ File upload validation exists and is reasonable: `image|max:10240` (product images, 10MB),
  `image|max:5120` (blog images, 5MB) — both use Laravel's `image` rule (MIME+extension check).
- ✅ Eloquent/parameterized queries used throughout the sampled controllers — no raw string-interpolated SQL found.
- ❓ **`IdentifyImpersonation` middleware** exists and is applied broadly (including to all
  authenticated + admin routes) — audit exactly what it allows before launch; impersonation
  middleware is a common source of privilege-escalation bugs if not scoped tightly to holders of a
  specific permission.
- 🟡 Passport tokens: standard OAuth2 grant tables in place; verify token TTLs / refresh-token
  rotation are configured appropriately for a public storefront (not verified this pass).
- reCAPTCHA v3 is wired on login/register (`VITE_RECAPTCHA_SITE_KEY` in `frontend/.env*`,
  used in `main.js`, `LoginPage.vue`, `RegisterPage.vue`) — ✅ good baseline bot mitigation.

### 2.3 Observability
- ❌ **No error tracking** (Sentry or equivalent) — no such dependency in `api/composer.json` or
  `frontend/package.json`, no DSN in any `.env*` file.
- 🟡 **Logging**: standard Laravel `LOG_CHANNEL=stack` only; `AdminServerLogController` gives
  admins a UI to view/clear log files, which is a reasonable stop-gap but not a substitute for
  centralized/aggregated logging in production.
- ✅ **Health check**: default Laravel `/up` endpoint is registered (`bootstrap/app.php`,
  `health: '/up'`) — usable by uptime monitors / load balancer health checks as-is.
- ❌ No APM/metrics collection found.
- **Action**: add Sentry (both `api/` via `sentry/sentry-laravel` and `frontend/` via
  `@sentry/vue`) before launch — this is cheap to add and high-value for a new public site.

### 2.4 CI/CD
- ❌ **No CI at all.** No `.github/` directory exists anywhere in the repo. `pint` (PHP lint),
  `format` (frontend prettier+eslint), and `test-backend`/`test-frontend` all exist as Makefile
  targets but nothing runs them automatically on push/PR.
- ⚠️ Also note: `test-backend` itself is currently **broken** (`Makefile:27-28` — missing `test`
  argument to `php artisan`), so even a naive CI job that calls `make test-backend` would not
  actually run tests today. Fix this before wiring CI.
- **Action**: minimally, add a GitHub Actions workflow running `pint --test`, frontend `lint`, and
  (once fixed) `test-backend`/`test-frontend` on every PR.

### 2.5 Environment / secrets / backups
- `docker-compose-production.yml` bakes `VITE_API_BASE_URL`/`VITE_UPLOAD_BASE_URL`/`VITE_BASE_URL`
  as build args (correct approach for a Vite SPA — these must be compile-time). DB/Meilisearch
  credentials come from shell env vars (`${DB_USERNAME}`, `${DB_PASSWORD}`, `${MEILISEARCH_KEY}`) —
  reasonable, assuming the deploy host injects real secrets and `.env` files aren't committed
  (verify `.gitignore` covers `api/.env`, `frontend/.env.production` with real secrets — the
  repo's tracked `frontend/.env`/`.env.production`/`.env.staging` currently only contain a public
  reCAPTCHA **site** key, which is meant to be public, so that's fine).
- ❌ **No backup strategy found** for the `tech-api-postgres-data` volume — no backup script,
  cron, or documented process in the repo. For a store handling real orders/customer PII, this is
  a launch blocker, not a nice-to-have.
- `FILESYSTEM_DISK=local` in `api/.env.example`, `AWS_*` vars present but blank — object storage
  (S3/R2) is scaffolded (env vars exist, `league/flysystem-aws-s3-v3`-style setup is standard in
  Laravel) but not actually configured/used. Product/blog images currently save to local disk
  (`$request->file('image')->store('blog', 'public')` pattern) — fine for a single-server deploy,
  a scaling risk if the app ever runs multi-instance without shared storage.
- **Action**: stand up automated Postgres backups (e.g. `pg_dump` cron to off-host storage) before
  launch; decide whether local disk storage is acceptable for the initial deploy topology or needs
  S3/R2 first.

### 2.6 SEO / SSR correctness
- ❌ **SSR is not implemented**, despite the `serve:ssr` npm script and `frontend-ssr` Makefile
  target implying it exists: `frontend/server.js` is missing, there's no Vite SSR build config, no
  `entry-server`/`entry-client` split. The app is a pure client-rendered SPA today. This matters
  for SEO (product/category/blog pages need to be crawlable and fast on first paint) — this is a
  **real gap for a public storefront**, not a documentation error to just fix in prose.
- ❌ **Sitemap generation is not wired up**: `package.json` references
  `scripts/generate-sitemap.cjs`, but `frontend/scripts/` does not exist in the repo at all. If a
  sitemap script existed previously it's gone; there is currently no automated sitemap.
- **Action** (in priority order for organic traffic): (1) decide SSR vs. prerendering vs.
  Meilisearch-fed static generation for product/category pages, (2) rebuild the sitemap generator
  and wire it into the build/deploy pipeline, (3) verify meta tags / structured data
  (Product/Offer schema.org) are present on rendered product pages — not checked this pass.

### 2.7 Performance
- ✅ Meilisearch is genuinely integrated (Scout `Searchable` on `Product`) — not just a provisioned,
  unused container. Confirm the index is populated in each environment (no scheduled
  `scout:import`/re-index command exists in `app/Console/Commands` — reindexing after bulk
  seeding/import currently appears to be a manual step).
- ❓ Caching strategy (`CACHE_STORE`) defaults to `database` in `.env.example` — fine for low
  traffic, should move to Redis (`tech-redis` is already provisioned and used for queues) for a
  "big site" target; not verified whether response/query caching is used anywhere in controllers.
- ❌ No image optimization/CDN pipeline (see §2.5 — local disk, no WebP/AVIF conversion observed
  in the upload actions inspected).

### 2.8 Legal / compliance
- 🟡 Routes exist (`terms`, `privacy` in `frontend/src/router/routes/public.js`, rendered via
  `pages/static/StaticPage.vue`, presumably backed by the `Page` model /
  `GET /v1/pages/{slug}`) — but **no seeder populates actual Terms/Privacy content** in the `Page`
  table (checked `api/database/seeders/` — only `CatalogSeeder`, `ProductsFromSotaSeeder`,
  `BlogPostsFromSotaSeeder` are registered in `DatabaseSeeder`). The CMS mechanism is real; the
  content is not yet there. **Action**: write and seed actual Terms of Service / Privacy Policy
  copy via the existing `AdminPageController` CRUD before public launch — this is a legal
  requirement for any storefront collecting customer/payment data, not just a content task.
- ❌ No cookie-consent banner found in this pass (not exhaustively searched — verify before EU/UA
  traffic if legally required).

---

## 3. Broader improvements for a serious, growing storefront

- **Search/facets via Meilisearch**: `CatalogController::filters` exists — verify it's driven by
  Meilisearch facet search rather than plain SQL `WHERE` filtering as catalog size grows; Meilisearch
  is already in the stack, underused if filtering is still DB-side.
- **Recommendations/related products**: `Product.is_recommended` is a manually-set flag today
  (admin-curated), not a computed/behavioral recommendation. Fine for launch scale; a real
  "customers also bought" feature would need order-history-driven logic later.
- **Analytics integration**: `AdminStatsController` (`analytics/overview`, `analytics/charts`,
  `analytics/distributions`) provides internal admin analytics from the app's own data — no
  external analytics (GA4/Plausible/etc.) verified on the storefront frontend in this pass.
- **Admin dashboard completeness**: reasonably built out — `widgets/DashboardStats`,
  `AdminStatsController`, plus dedicated Accounting/Billing, Marketing (coupons+promotions), Team,
  Roles, Server Logs, Blog CMS, and Static Pages CMS admin modules all exist and have real
  backend+frontend wiring, not just stubs.
- **Multi-warehouse/inventory scaling**: schema already supports multiple `Warehouse` rows with
  per-warehouse `Stock`; confirm checkout logic picks a sensible warehouse (nearest/priority) once
  more than one warehouse is in real use — `quickOrder` currently just takes `stocks->first()`.
- **Marketing features**: coupons and promotions are implemented (admin CRUD + public validation
  endpoint) — real, not aspirational, though the engine is flat (see §2.1).
- **Content/blog**: scraper-sourced blog content **is** seeded and rendered — `BlogPostsFromSotaSeeder`
  is registered in `DatabaseSeeder`, and public `BlogController` + `pages/blog/*` (per
  `PROJECT_MAP.md`) serve it, with a full `AdminBlogController` (posts/categories/tags CRUD +
  image upload) on the admin side. This roadmap item is done, contrary to it not being mentioned
  as complete in the June doc.
- **i18n/multi-currency**: `name`/`description` fields are JSON multi-language (`uk`/`en` observed)
  at the data layer, and the frontend has separate `lang/public` and `lang/admin` locale files —
  solid foundation for UA/EN. No multi-currency support observed (prices are plain decimals with
  no currency column) — add if international expansion is planned.
- **Load/scale testing**: none observed (no k6/Locust/Artillery config in the repo) — worth doing
  once payment + SSR + backups are in place, before any real marketing push.

---

## 4. Suggested phase order (summary)

1. **Fix what's broken today** (cheap, unblocks everything else): `Makefile` `test-backend` target;
   decide sitemap/SSR direction rather than leaving dangling npm scripts; remove or intentionally
   keep the dead `runnerNodesStore`/`runnerTranscodersStore`/video-modal leftovers (see
   `PROJECT_MAP.md` §"Known technical debt").
2. **Launch blockers**: payment gateway, rate limiting, Postgres backups, Terms/Privacy content,
   basic error tracking (Sentry), CI running lint+tests.
3. **Launch-quality**: SSR or prerendering + real sitemap generation for SEO, Redis-backed caching,
   image storage strategy (confirm local-disk is acceptable or move to S3/R2), promotions engine
   hardening, shipping API integration if not already automated.
4. **Growth**: Meilisearch-backed faceted search on the frontend if not already, real test suites
   (backend + frontend, currently both empty), external analytics, load testing, multi-currency if
   expanding beyond Ukraine.
