# CLAUDE.md — tech.filkx.com (FilkxTech)

This file provides guidance to Claude Code when working anywhere in this repository.

**Read these first for orientation before making changes:**
- `docs/PROJECT_MAP.md` — where things live: routing, domain models, frontend layering, known
  inconsistencies, file:line pointers into the actual code.
- `docs/DEVELOPMENT_PLAN.md` — current production-readiness state, verified against code, and the
  prioritized path to launch. Treat `implementation_roadmap.md` at the repo root as **stale** (written
  2026-06-11, before ~20 subsequent "fix" commits); `docs/DEVELOPMENT_PLAN.md` corrects it item by item.

No `api/CLAUDE.md` or `frontend/CLAUDE.md` exist yet — all guidance lives here for now.

---

## Project

**FilkxTech** is an e-commerce platform: a Laravel API backend and a Vue 3 SPA storefront +
admin back-office, selling physical products (catalog seeded from electronics data) with catalog
browsing, cart, checkout, order management, product reviews, coupons/promotions, a blog, customer
support tickets, and RBAC-based staff accounts.

Services:
- `api/` — Laravel 12 backend (PHP 8.2, PostgreSQL, Redis, Laravel Passport for auth,
  Laravel Scout + Meilisearch for product search). Structured as `app/Api/V1/*` (public/customer
  API) and `app/Api/Admin/*` (admin API), each with its own `Controllers/`, `Requests/`,
  `Resources/`, `Actions/`, `Dto/`. Business logic lives in single-purpose **Action classes**
  (`Actions/`), not in a DDD Domain/Application/Infrastructure layering — that structure was
  planned in `implementation_roadmap.md` but never built; don't assume it exists.
- `frontend/` — Vue 3 + Vite SPA, Feature-Sliced-Design-like structure (`entities/`, `features/`,
  `widgets/`, `shared/`, `pages/`, `stores/` re-export shims over FSD-located Pinia stores). A
  parallel legacy `src/components/` tree (mostly admin UI) still exists alongside it — see
  `docs/PROJECT_MAP.md` for what's actually duplicated vs. just parallel admin/storefront kits.
  **Note: despite an SSR-shaped npm script (`serve:ssr`), SSR is not actually implemented — there
  is no `server.js`. The app runs as a pure client-rendered SPA.** Don't assume SSR exists.
- `scraper/` — standalone Python scripts (`scrape_sota.py`, `scrape_blog.py`, `generate_seeder.py`,
  `generate_blog_seeder.py`) used once to scrape product/blog data into JSON and generate Laravel
  seeder PHP. Not part of the runtime app; its output seeders (`ProductsFromSotaSeeder`,
  `BlogPostsFromSotaSeeder`) are what's actually used at runtime.

---

## Dev Commands

The Docker network must exist before starting:

```bash
docker network create filkx    # one-time
make init                      # docker-down-clear, docker-pull, docker-build, docker-up, api-init, frontend-init
make up / make down / make restart
```

### API

```bash
make api-init                  # composer install + migrate + passport:client
make migrate
make passport-client
make pint                      # PHP lint: tech-api-php-cli ./vendor/bin/pint --parallel --max-processes=4
make swagger                   # l5-swagger:generate (OpenAPI docs)
make test / make test-backend  # NOTE: currently broken — Makefile runs `php artisan` with no `test`
                                # argument (Makefile:27-28). Fix this before relying on it; the
                                # intended command is `php artisan test`.
make test-coverage             # php artisan test --coverage-html=coverage (XDEBUG_MODE=coverage)
docker compose run --rm tech-api-php-cli php artisan <command>
```

`api/tests/` currently only contains the Laravel-generated stub tests — there is no real backend
test coverage yet (see `docs/DEVELOPMENT_PLAN.md` §2.4).

### Frontend

```bash
make frontend-install
make frontend-dev              # Vite dev server
make frontend-build
make frontend-ssr              # runs `npm run serve:ssr` → currently fails, server.js is missing
make format                    # prettier + eslint --fix via tech-frontend-node-cli
make test-frontend             # npm run test:unit (vitest) — NOTE: no vitest.config.* and no
                                # spec/test files exist yet; this currently has nothing to run
docker compose run --rm tech-frontend-node-cli npm run test:e2e   # playwright — same caveat, no config/tests exist yet
```

### Docker services (docker-compose.yml)

`tech-api`, `tech-api-php-fpm`, `tech-api-php-cli`, `tech-api-scheduler`, `tech-api-postgres`,
`tech-api-queue`, `tech-frontend-spa`, `tech-frontend-node-cli`, `tech-redis`, `tech-meilisearch`,
`tech-nginx-temp`. `filkx` (network) and `api-postgres`/`redis`/`meilisearch-data` (volumes) in the
compose file are named volume/network declarations, not separate services — nothing dead there,
just inconsistent naming (they don't carry the `tech-` prefix the services use).
`docker-compose-production.yml` uses `tech-frontend` (nginx+static dist) instead of the dev
`tech-frontend-spa`+`tech-frontend-node-cli` pair; otherwise mirrors the dev service set.

One real leftover bug: `tech-api-postgres`'s healthcheck in `docker-compose.yml` runs
`pg_isready -U live -d live` while the actual configured user/db is `tech`/`tech` — copy-pasted
from an unrelated project and never fixed.

---

## Architecture Priorities

This is a long-lived production e-commerce platform. When making any change, prioritize in this order:

1. **Maintainability** — future developers must understand the code without you
2. **Correctness** — the feature must be provably correct, not just apparently working
3. **Readability** — code is read far more than it is written
4. **Extensibility** — new requirements should require adding code, not rewriting it
5. **Performance** — only optimize when there is a measured problem

Never sacrifice maintainability for speed of delivery.
Never introduce technical debt to solve a task faster.

---

## Coding Principles

Apply these across both services and all languages:

**SOLID**
- Single Responsibility: one class, one reason to change
- Open/Closed: open for extension, closed for modification
- Liskov Substitution: subtypes must be substitutable for their base types
- Interface Segregation: prefer narrow interfaces over fat ones
- Dependency Inversion: depend on abstractions, not concretions

**DRY** — every piece of knowledge has a single authoritative representation.
Duplication is not just copy-paste; it is two places that must change together.

**KISS** — the simplest solution that correctly solves the problem is the right solution.
Complexity is a liability, not an asset.

**YAGNI** — do not implement functionality until it is needed.
Do not design for hypothetical future requirements.

**Tell, Don't Ask** — objects should do things, not expose state for the caller to decide.

**Command Query Separation** — a method either changes state (command) or returns data (query), never both.

**Composition over Inheritance** — prefer composing small focused objects over deep inheritance chains.

**Dependency Injection** — dependencies are passed in, never instantiated inside a class.

---

## Cross-Cutting Rules

### Never

- Move business logic into controllers — keep it in Action classes (backend) or composables/stores (frontend)
- Duplicate business logic across the public (`Api/V1`) and admin (`Api/Admin`) API layers
- Use static helper methods unless the pattern is already established in the codebase
- Introduce breaking changes to shared contracts (API response shapes, route paths, JSON field
  names consumed by `frontend/src/shared/services/api/`) without checking both sides
- Rewrite code unrelated to the current task
- Add error handling for cases that cannot happen
- Add comments that explain *what* the code does — only explain *why* when it is non-obvious
- Use feature flags, backwards-compatibility shims, or dead code stubs — just change the code
- Add new files under `app/Api/Admin/Policies/`, `app/Api/Admin/Repositories/`, or
  `app/Api/V1/Middleware/` without checking whether those directories being empty today reflects
  an intentional pattern change, not an oversight to blindly follow

### Always

- Read `docs/PROJECT_MAP.md` before exploring the codebase from scratch in a new session
- Prefer editing existing files over creating new ones
- Keep changes scoped to what the task requires — no opportunistic refactoring
- Verify that new code does not break existing tests before declaring done
- Write tests for new code (unit and/or feature/integration) — note the project currently has
  near-zero test coverage on both sides; new code is a good place to start actually building it
  rather than compounding the gap
- Update the OpenAPI/Swagger annotations and run `make swagger` when API endpoints are added or changed
- Write code that a new team member can understand without asking questions
- Use `frontend/src/shared/services/api/` for new frontend API calls, not direct `axios.*` — a
  handful of legacy admin pages still bypass it; don't add to that list

### Comments

Default: write no comments.
Only add a comment when the **why** is non-obvious: a hidden constraint, a workaround for a specific bug,
a subtle invariant that would surprise a reader. If removing the comment would not confuse a future reader, do not write it.
Never write docblocks that restate the method signature.

---

## i18n

Every user-visible string must be translated — this includes toast messages (`cartStore.addToast(...)`)
and `confirm()`/alert-style text, not just template interpolations.

- Uses `vue-i18n` (Composition API mode, `legacy: false`), configured in `frontend/src/i18n.js`.
- Translation keys live under `frontend/src/lang/public/` (customer-facing) and
  `frontend/src/lang/admin/` (admin panel) — **one file per feature per language**, not one flat
  file per language: `lang/public/{feature}/{en,uk}.js`, e.g. `lang/public/cart/uk.js`,
  `lang/public/header/en.js`. Each feature folder's `en.js`/`uk.js` default-exports a plain nested
  object; `lang/public/en.js`/`uk.js` are aggregators that import every feature submodule and merge
  them into one object (mirrors `lang/admin/en.js`/`uk.js` doing the same for the admin submodules),
  and `lang/index.js` merges both namespaces for `vue-i18n`.
- Keys are nested by feature, then by page/section: `cart.items.remove`, `header.search.placeholder`,
  `home.catalogSection.title`. A feature that's both a homepage widget and a full page gets two
  namespaces (`home.blog.*` for the homepage teaser vs. `blog.*` for the actual blog page) — don't
  collapse them into one just because the words overlap.
- **Only two locales**: `en` (default/fallback) and `uk`. Add both for every new key — never add one
  without the other.
- In `<script setup>`: `const { t } = useI18n();` then `t('key')` (also in the template). Interpolate
  with `t('key', { name: value })` matching `{name}` placeholders in the string.
- Skip `frontend/src/lang/admin/` and admin pages/components — the admin panel's i18n coverage is a
  separate, later pass, not part of the customer-facing (`lang/public/`) effort.

---

## API / Contract Notes

There's no multi-service contract table here — `api/` and `frontend/` are the only two runtime
services, and the contract between them is a conventional REST API, not a set of webhooks or
shared storage paths:

- **Transport**: `frontend/src/shared/services/api/apiClient.ts` (axios) talks to
  `api/routes/v1/api.php` (mounted at `/v1`) plus `api/routes/wishlist.php` (mounted separately via
  `WishlistServiceProvider`, at `/api/v1/wishlist`).
- **Auth**: Laravel Passport, `auth:api` guard, bearer tokens. The frontend keeps separate token
  storage keys for customer vs. admin sessions (`filkx_auth` vs `filkx_admin_auth` in
  `entities/user/model/authStore.ts`).
- **Admin vs public split**: same route file, distinguished by middleware —
  `role:admin|administrator|moderator|support` gates the entire `/admin` route group. There is no
  separate admin subdomain or API version.
- **No websocket/broadcast contract exists.** Grep for "Broadcast" in this codebase
  (`BroadcastNotificationAction`, etc.) refers to sending a notification row to many users, not
  Laravel Broadcasting/Pusher — there is no real-time channel between backend and frontend today.
  Do not add `Echo`/Pusher-dependent code assuming infrastructure that isn't there.

---

## Security

Grounded in what's actually implemented — see `docs/DEVELOPMENT_PLAN.md` §2.2 for the full gap list.

- **Auth**: Laravel Passport (OAuth2) tokens via `auth:api`; never bypass this guard on new routes
  that touch user data.
- **RBAC is real, not decorative**: `Role`/`Permission` models, `User::hasAnyRole()`/`hasPermission()`,
  enforced via the `role:` middleware alias (`RoleMiddleware`). Any new admin endpoint must be added
  inside the existing `admin` route group (which already carries the `role:` middleware) — never
  hand-roll a separate admin-check inside a controller.
- **Never bypass `role:` middleware in controllers** — authorization belongs at the route/middleware
  layer, matching the existing pattern.
- **SQL**: use Eloquent or parameterized query builder calls — never string-interpolate user input
  into raw SQL. This is consistently followed in the code sampled; keep it that way.
- **File uploads**: validate with Laravel's `image` rule plus an explicit size cap, matching the
  existing pattern (`image|max:10240` for product images, `image|max:5120` for blog images) — don't
  accept uploads without both a type and size constraint.
- **No rate limiting exists yet** (`throttle:`) on any route, including login/register/checkout —
  be aware of this when adding auth-adjacent or write-heavy endpoints; adding `throttle:` to a new
  sensitive endpoint is a reasonable proactive addition even though it's not yet the norm here.
- **Never log credentials, tokens, or PII.**
- **Never expose internal service URLs, admin-only fields, or other users' data in API Resources**
  consumed by the public `Api/V1` layer — check `Resources/` classes scope fields correctly per
  audience (public vs admin).
- **Audit trail**: security-relevant admin actions should go through `AuditLog`/`AuditEvent` where
  the existing code already does so (role/permission changes) — extend that pattern for new
  sensitive admin actions rather than inventing a new logging mechanism.
- **No payment gateway is integrated yet** — if you add one, do not store raw card data; use the
  gateway's hosted/tokenized flow and keep secrets out of `.env.example`/committed `.env*` files
  (the committed `frontend/.env*` files currently only contain a public reCAPTCHA site key, which
  is safe to commit — keep it that way; anything secret belongs in untracked `.env`/deploy secrets).
