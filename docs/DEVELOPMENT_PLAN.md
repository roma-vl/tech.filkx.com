# Development Plan — tech.filkx.com (FilkxTech)

Production-readiness and growth plan. Originally audited against actual code on 2026-08-16 (branch
`master`, clean tree); **re-audited 2026-08-17 against branch `add-new-logic`**, which has since
diverged from `master` by 14 commits (payment gateway, rate limiting, Sentry, backups, 2FA, legal
page content, backend unit tests, homepage/header/footer i18n, cart dark-mode/i18n, homepage banner
CMS, newsletter) — several items below flipped status as a result; each corrected line says so.
Companion to `docs/PROJECT_MAP.md` (navigation) and supersedes the stale `implementation_roadmap.md`
(written 2026-06-11, before ~20 subsequent "fix" commits) for status purposes — that file's
checkboxes are corrected below, item by item.

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
| Home components wired to real API instead of demo data | ✅ done | `features/home/composables/useHome.ts` calls `productApi` (real HTTP), maps live product/variant/attribute payloads — not a static array. As of commit `cf9d503`, homepage hero/promo content itself is now admin-manageable (`home_banners` table, `AdminHomeBannerController`) rather than hardcoded arrays in `HeroSlider`/`FlashDeals`/`BrandPartners`/`CatalogSection`/`TechBlog`. |
| Cart/wishlist persistence (LocalStorage + sync) | ✅ done | `cartStore.ts` persists wishlist/compare/viewed to `localStorage`, and dedicated sync endpoints exist backend-side (`/user/favorites/sync`, `/user/compares/sync`, `/user/viewed-products/sync`) |
| Full checkout process | ✅ done | `CheckoutController`, wired through `entities/order` + checkout pages; LiqPay now provides real online card payment behind the main cart flow (see §2.1) |

**Bottom line: the "e-commerce core absent, frontend on demo data" framing of `implementation_roadmap.md` is obsolete.** Catalog, cart, checkout, orders, coupons, reviews, wishlist, and blog are all real, API-backed, and reasonably wired end to end. The roadmap's Stage 1 and most of Stage 2/3 are functionally done; remaining gaps are narrower and listed below.

---

## 2. Path to production launch (prioritized)

### 2.1 E-commerce fundamentals — remaining gaps
- ✅ **Payment gateway integration** (done 2026-08-16, commit `1fd50e8`). LiqPay hosted-checkout is
  wired end to end: `LiqPayService` builds/signs the checkout payload and verifies callback
  signatures; `PaymentController` exposes `POST /payments/orders/{orderNumber}/liqpay` (initiate),
  `POST /payments/liqpay/callback` (public, signature-verified, idempotent), and
  `GET /payments/orders/{orderNumber}/status` (frontend polling after redirect back). Card data
  never touches the app's servers. `orders.payment_reference`/`orders.paid_at` columns added.
  The fake "type your card number into our form" modal that always reported success was removed.
  **Remaining caveat**: `LIQPAY_PUBLIC_KEY`/`LIQPAY_PRIVATE_KEY` are empty placeholders in
  `api/.env.example` — the initiate endpoint fails closed with a clear message until a real LiqPay
  merchant account is registered and the keys are set. Also note the separate one-click
  `CheckoutController::quickOrder` "buy now" path still hardcodes `payment_method => 'cod'` — only
  the main cart checkout flow goes through LiqPay today.
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
- ✅ **Rate limiting** (done 2026-08-16, commits `970661c` + `75517a9` + `eaafd44`). `throttle:`
  now covers auth (login/register/2FA), `POST /checkout` + `/checkout/quick` (10/min),
  `POST /payments/orders/{n}/liqpay` (10/min), `POST /coupons/validate` (20/min — loose enough for
  real retries, tight enough to slow brute-forcing), and `POST /newsletter/subscribe` (5/min). The
  LiqPay server-to-server callback is intentionally left unthrottled (it's a webhook LiqPay retries
  until it gets a 200, and authenticity comes from signature verification, not rate limiting).
- ✅ **TOTP two-factor authentication** (new, commit `970661c`), opt-in per user. `users.two_factor_*`
  columns (secret/recovery codes encrypted), `TwoFactorAuthenticationService` wraps
  `pragmarx/google2fa`; login returns a short-lived single-use challenge token instead of an access
  token when 2FA is enabled, exchanged via `AuthController::verifyTwoFactor()`. Every action
  (enable/confirm/disable/regenerate recovery codes) is audit-logged via the existing
  `AuditEvent`/`AuditLogDto` pattern.
- ✅ RBAC is real and enforced via `role:` middleware on the entire `/admin` group; `RoleMiddleware`
  now has direct unit coverage (`RoleMiddlewareTest`, commit `ca5ef97`) — unauthenticated,
  wrong-role, matching-role, and pipe-separated OR-semantics cases.
- ✅ File upload validation exists and is reasonable: `image|max:10240` (product images, 10MB),
  `image|max:5120` (blog images, 5MB) — both use Laravel's `image` rule (MIME+extension check).
- ✅ Eloquent/parameterized queries used throughout the sampled controllers — no raw string-interpolated SQL found.
- ❓ **`IdentifyImpersonation` middleware** exists and is applied broadly (including to all
  authenticated + admin routes) — audit exactly what it allows before launch; impersonation
  middleware is a common source of privilege-escalation bugs if not scoped tightly to holders of a
  specific permission. (`AuthServiceTest`, commit `ca5ef97`, does lock in a regression fix for an
  `impersonator_id isset()` guard in `refreshToken`, but that's narrower than a full audit of what
  the middleware allows.)
- 🟡 Passport tokens: standard OAuth2 grant tables in place; verify token TTLs / refresh-token
  rotation are configured appropriately for a public storefront (not verified this pass).
- reCAPTCHA v3 is wired on login/register (`VITE_RECAPTCHA_SITE_KEY` in `frontend/.env*`,
  used in `main.js`, `LoginPage.vue`, `RegisterPage.vue`) — ✅ good baseline bot mitigation.

### 2.3 Observability
- 🟡 **Error tracking wired but inert** (done 2026-08-16, commit `f0de4e4`). `sentry/sentry-laravel`
  is in the exception handler (`bootstrap/app.php`) and `@sentry/vue` in `main.js`, with
  `SENTRY_LARAVEL_DSN`/`VITE_SENTRY_DSN` placeholders added to `api/.env(.example)` and all three
  frontend env files. Both stay fully inert until a DSN is actually set — **no Sentry account
  exists yet**, so nothing is currently being reported anywhere. **Action**: create the Sentry
  project and set the DSNs before launch; the code-side integration itself is done.
- 🟡 **Logging**: standard Laravel `LOG_CHANNEL=stack` only; `AdminServerLogController` gives
  admins a UI to view/clear log files, which is a reasonable stop-gap but not a substitute for
  centralized/aggregated logging in production.
- ✅ **Health check**: default Laravel `/up` endpoint is registered (`bootstrap/app.php`,
  `health: '/up'`) — usable by uptime monitors / load balancer health checks as-is.
- ❌ No APM/metrics collection found.

### 2.4 CI/CD
- ❌ **No CI at all.** No `.github/` directory exists anywhere in the repo. `pint` (PHP lint),
  `format` (frontend prettier+eslint), and `test-backend`/`test-frontend` all exist as Makefile
  targets but nothing runs them automatically on push/PR.
- ⚠️ Also note: `test-backend` itself is still **broken** (`Makefile:27-28` — missing `test`
  argument to `php artisan`), so even a naive CI job that calls `make test-backend` would not
  actually run tests today. This is now more worth fixing than before: real backend test coverage
  exists to run (see below), it's just not reachable through the Makefile target.
- **Action**: minimally, add a GitHub Actions workflow running `pint --test`, frontend `lint`, and
  (once fixed) `test-backend`/`test-frontend` on every PR.
- 🟡 **Backend test coverage — no longer near-zero** (done 2026-08-16, commit `ca5ef97`, plus
  Feature tests added alongside the 2FA/newsletter/home-banner work). Beyond the framework stub,
  there's now real coverage of the security-critical paths: `AuthServiceTest` (29 tests — register,
  login incl. the 2FA-challenge path, logout, refresh-token, verify-email, forgot/reset password,
  OAuth login, new-device notification), unit tests for all four 2FA actions, `RoleMiddlewareTest`
  (the RBAC/authorization layer, previously untested anywhere), plus Feature tests for
  `AuthController`, `TwoFactorAuthenticationController`, `HomeController`, `NewsletterController`,
  and `AdminHomeBannerController`. Still 🟡 not ✅: this is auth/RBAC/home/newsletter coverage —
  checkout, orders, cart, catalog, and coupons remain untested.
- ❌ **Frontend test infrastructure still doesn't exist** — no `vitest.config.*`, no `.spec.ts`/
  `.test.ts` files anywhere in `frontend/`, despite `test:unit`/`test:e2e` npm scripts. Unchanged
  from the original audit.

### 2.5 Environment / secrets / backups
- `docker-compose-production.yml` bakes `VITE_API_BASE_URL`/`VITE_UPLOAD_BASE_URL`/`VITE_BASE_URL`
  as build args (correct approach for a Vite SPA — these must be compile-time). DB/Meilisearch
  credentials come from shell env vars (`${DB_USERNAME}`, `${DB_PASSWORD}`, `${MEILISEARCH_KEY}`) —
  reasonable, assuming the deploy host injects real secrets and `.env` files aren't committed
  (verify `.gitignore` covers `api/.env`, `frontend/.env.production` with real secrets — the
  repo's tracked `frontend/.env`/`.env.production`/`.env.staging` currently only contain a public
  reCAPTCHA **site** key, which is meant to be public, so that's fine).
- ✅ **Automated Postgres backups** (done 2026-08-16, commit `f0de4e4`). `spatie/laravel-backup`
  is installed and configured (DB connection from `DB_CONNECTION=pgsql`, local disk); scheduled
  `backup:clean` → `backup:run` → `backup:monitor` daily in `routes/console.php`. Along the way,
  `postgresql-client` (needed for `pg_dump`) was found missing from all three php-cli Docker images
  (dev/staging/production) and added — a real backup (DB dump + files, ~28MB) was verified
  end-to-end on the dev container. Off-host replication of the backup destination itself is not
  separately verified in this pass.
- `FILESYSTEM_DISK=local` in `api/.env.example`, `AWS_*` vars present but blank — object storage
  (S3/R2) is scaffolded (env vars exist, `league/flysystem-aws-s3-v3`-style setup is standard in
  Laravel) but not actually configured/used. Product/blog images currently save to local disk
  (`$request->file('image')->store('blog', 'public')` pattern) — fine for a single-server deploy,
  a scaling risk if the app ever runs multi-instance without shared storage.
- **Action**: decide whether local disk storage is acceptable for the initial deploy topology or
  needs S3/R2 first; confirm the backup destination itself is off-host, not just off-database.

### 2.6 SEO / SSR correctness
- ✅ **Meta tags / structured data**: `useHead()` (already-installed `@vueuse/head`) with
  Product/Offer and Article JSON-LD is wired into `BlogPage.vue`, `CatalogPage.vue`,
  `BlogPostPage.vue`, `ProductDetailPage.vue`.
- ⚠️ **SSR is still not implemented, by deliberate choice, not oversight**: the user was asked and
  chose static prerendering over full SSR, specifically to avoid a live Node process in production
  (`docker-compose-production.yml` serves the frontend as `tech-frontend`, nginx + static `dist/` —
  that stays unchanged). `serve:ssr`/`frontend-ssr` remain dangling references; no `server.js` was
  added and none should be.
- ✅ **Static prerendering exists instead** (working tree, uncommitted as of 2026-08-17 on
  `add-new-logic` — see `docs/PROJECT_MAP.md` "SSR" for the file-by-file breakdown):
  `frontend/src/entry-server.ts` + `frontend/scripts/prerender.mjs`, wired as a `postbuild` npm
  script. Uses `@vue/server-renderer`'s `renderToString()` directly (no headless browser — plain
  server-side Vue rendering) against a Vite dev-server-in-middleware-mode module graph (no separate
  SSR bundle build needed). Pages that fetch data in `onMounted` (which never fires without a DOM)
  were given an equivalent `onServerPrefetch` hook so `renderToString()` actually waits for real
  data before serializing — see `docs/PROJECT_MAP.md` for the full list of touched
  composables/pages.
  - **Verified with real prerendered HTML for**: `/` (home — hero banners, categories, flash deals,
    recommended products, blog teaser, header mega-menu, footer categories all populated), `/catalog`
    (real product grid), `/blog` (real post list), every prerendered `/product/:id` (real name,
    price, description, Product/Offer JSON-LD), every prerendered `/blog/:slug` (real title, body,
    Article JSON-LD). Verified by building to a clean output directory
    (`npx vite build --mode production --outDir <clean dir>` — see EACCES note below), running
    `node scripts/prerender.mjs` against it (`PRERENDER_MODE=development` to reach the sandbox's
    reachable dev API; `PRERENDER_MODE=production`/unset is the real default and matches what
    `vite build`'s default mode bakes into the client bundle), and grepping the output HTML for
    real product/post names, prices, and JSON-LD — not just checking the script exits 0.
  - **Descoped**: distinct category-listing pages. The app has no separate route per category —
    `CatalogPage.vue` filters via a `?category=` query string on the single `/catalog` path — so
    there's no distinct clean URL to prerender a file for. The sitemap (below) still lists
    `/catalog?category=<slug>` for every real category as a crawlable, if not statically
    prerendered, URL.
  - **Known caveat found during verification, not fixed (out of scope — backend data ordering, not
    a frontend bug)**: the public products-listing endpoint's default order doesn't appear fully
    stable across separate paginated requests — sequential `page=1,2,3…` calls can return a
    genuinely different row on one page between requests, causing a naive "fetch every page and
    concatenate" pagination helper to see the same product twice (or miss one). Both
    `generate-sitemap.cjs` and `prerender.mjs`'s route collection tolerate this (dedup by id;
    prerendering only ever needs a capped, representative subset), but a backend fix (a stable
    `ORDER BY` on that endpoint) would be worth adding before relying on exhaustive pagination
    elsewhere.
- ✅ **Sitemap generation rebuilt**: `frontend/scripts/generate-sitemap.cjs` (plain CJS + axios, no
  Vite/Vue) now exists and runs as part of `postbuild`. Paginates the real
  `/v1/catalog/categories`, `/v1/catalog/products`, `/v1/blog/posts` endpoints and writes
  `dist/sitemap.xml` — verified: ran it against the real API, parsed the output with
  `xml.etree.ElementTree`, confirmed well-formed XML, zero duplicate/placeholder URLs, and real
  `https://tech.filkx.com/...` product/category/post/static-page URLs (325 total in the run that
  produced this note: 3 top-level + 15 static content pages + 112 categories + 185 products + 10
  blog posts).
- ⚠️ **Pre-existing environment issue, not caused by this work**: this sandbox's `frontend/dist/`
  has root-owned leftover files (`dist/assets/`, and separately `node_modules/.vite/deps/`) from an
  earlier Docker-run build/dev-server, owned by `root` and not writable by the non-root user this
  session runs as — `npm run build`'s final asset-write step (and, separately, Vite's dependency
  pre-bundling step) fails with `EACCES` on those specific pre-existing files regardless of this
  change. Confirmed unrelated to this work by building to a clean `--outDir` instead, where the full
  `vite build` (client) completes with no errors, and prerendering/sitemap generation against that
  clean output both succeed end-to-end as described above. `vue-tsc --noEmit` is also clean for
  every file this task touched (pre-existing, unrelated type errors remain in a handful of other
  files already modified elsewhere on this branch — `OrdersTab.vue`, `useShoppingCart.ts`,
  `ShoppingCart.vue`, `CartItemsList.vue`, `AccountSidebar.vue`, `AccountDrawer.vue`).

### 2.7 Performance
- ✅ Meilisearch is genuinely integrated (Scout `Searchable` on `Product`), but **only for the
  free-text `search` keyword** (`ListProductsAction`, with a graceful SQL `LIKE` fallback if
  Meilisearch is unreachable) — category, brand, price range, discount/in-stock flags, and EAV
  attribute filters are all plain Eloquent `whereHas`/`where`, not Meilisearch facets. That's a
  reasonable, correct architecture at the current catalog size (~186 products per the sitemap
  generator's count as of 2026-08-17) — converting attribute/category filtering to Meilisearch
  facets would need real facet-distribution config and reindexing work, not justified without a
  measured performance problem (YAGNI). Revisit if catalog size grows substantially. Confirm the
  index is populated in each environment (no scheduled `scout:import`/re-index command exists in
  `app/Console/Commands` — reindexing after bulk seeding/import currently appears to be a manual
  step).
- ✅ **Caching now actually on Redis** (working tree, 2026-08-17). `CACHE_STORE` defaulted to
  `database` in both `api/.env` and `api/.env.example` despite Redis being provisioned — flipped to
  `redis` in both. Found and fixed two things that would have made that silently fail: (1)
  `REDIS_HOST` was `127.0.0.1`, meaningless from inside a container on the `filkx` network — changed
  to the actual service name `tech-redis`; (2) `REDIS_CLIENT=phpredis` selects the PHP `redis`
  C-extension, which isn't installed in `tech-api-php-cli`'s image (`php -m` confirms) — switched to
  `REDIS_CLIENT=predis` (the pure-PHP client, already a `composer.json` dependency, no extension
  needed). Verified via `php artisan tinker` inside the container: `Cache::put`/`Cache::get`
  round-trips through Redis, `config('cache.default')` reports `redis`.
  **Related, found but not fixed** (bigger blast radius, out of scope for this pass):
  `QUEUE_CONNECTION` is still `database`, not `redis`, even though `tech-api-queue`'s
  `docker-compose.yml` service `depends_on: tech-redis` — the dependency ordering exists but the
  queue driver itself doesn't use Redis. Flipping it would need draining/checking the `jobs` table
  first so in-flight jobs aren't orphaned (low risk today — only one queued Job exists,
  `NotifyProductWishlistsJob` — but still a deliberate follow-up, not a silent side effect of a cache
  config change).
- ❌ No image optimization/CDN pipeline (see §2.5 — local disk, no WebP/AVIF conversion observed
  in the upload actions inspected).

### 2.8 Legal / compliance
- ✅ **Terms/Privacy/Oferta/Cookies content written** (done 2026-08-16, commit `75517a9`). The
  original seed migration only had thin 2-paragraph placeholders with inconsistent business
  identity (two different legal entity names, a fabricated address, fabricated hotline number,
  fabricated physical stores, an installment partnership that was never integrated). A new
  migration standardizes the entity name to ТОВ «FilksTech» everywhere and expands
  terms/privacy/oferta/cookies to full documents: legal entity identification, what data is
  collected (naming the real processors — LiqPay for payments, Nova Poshta/Ukrposhta for delivery),
  user rights, the 14-day return law reference, force majeure, governing law, dispute resolution.
  **These are explicitly labeled drafts (with a last-updated date on each page) and have not been
  reviewed by a lawyer** — facts the business must still supply are left as bracketed placeholders
  ([ЮРИДИЧНА АДРЕСА], [ЄДРПОУ], etc.). **Action before launch**: have a lawyer review, and fill in
  the remaining bracketed placeholders with real values.
- ❌ No cookie-consent banner found — Footer only links to the `/cookies` policy page, there's no
  actual consent UI (banner/popup). Unchanged from the original audit; verify before EU/UA traffic
  if legally required.

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
  solid foundation for UA/EN. As of 2026-08-16/17, most of the previously-hardcoded storefront
  surface has actually been localized rather than just scaffolded: `Header.vue` (commit `789a1d3`,
  incl. fixing a `t()`-on-array bug that rendered popular-search tags one character at a time —
  `b352b89`), the mega menu / homepage / footer (commit `af58fcd`, which also fixed category and
  product names always showing the `uk` field regardless of active locale), and the shopping
  cart/checkout/drawer (dark-mode + i18n pass, commit `7d4c6ab`). Remaining hardcoded-string
  surface hasn't been swept exhaustively past those areas. No multi-currency support observed
  (prices are plain decimals with no currency column) — add if international expansion is planned.
- **Newsletter subscription** (new, commit `eaafd44`): `newsletter_subscribers` table,
  `POST /v1/newsletter/subscribe` (rate-limited 5/min), wired into Footer and Header.
- **Load/scale testing**: none observed (no k6/Locust/Artillery config in the repo) — worth doing
  once payment + SSR + backups are in place, before any real marketing push.

---

## 4. Suggested phase order (summary)

1. ~~Fix what's broken today~~ **Done** (working tree, 2026-08-17): `Makefile` `test-backend` now
   runs `php artisan test`; sitemap/SSR direction decided (static prerendering, see §2.6) and
   implemented; the dead `ContentRestrictionBanner`/`FeatureLockOverlay`/`TrialActivationBanner`
   leftovers are removed (see `PROJECT_MAP.md` §"Known technical debt").
2. **Launch blockers — mostly done now**: ~~payment gateway~~ ✅, ~~rate limiting~~ ✅,
   ~~Postgres backups~~ ✅, ~~Terms/Privacy content~~ ✅ (drafts, needs lawyer review),
   ~~Sentry code integration~~ ✅ (needs an actual DSN/account before it does anything). Still open:
   CI running lint+tests, a cookie-consent banner if legally required, lawyer review of the legal
   pages, creating the Sentry project.
3. **Launch-quality**: ~~SSR or prerendering + real sitemap generation for SEO~~ ✅ prerendering +
   sitemap generation now exist (see §2.6 — uncommitted working-tree state), Redis-backed caching,
   image storage strategy (confirm local-disk is acceptable or move to S3/R2), promotions engine
   hardening, shipping API integration if not already automated.
4. **Growth**: Meilisearch-backed faceted search on the frontend if not already, broaden backend
   test coverage beyond auth/RBAC/home/newsletter to checkout/orders/cart/catalog/coupons, build
   frontend test infrastructure from scratch (still literally zero), external analytics, load
   testing, multi-currency if expanding beyond Ukraine.
