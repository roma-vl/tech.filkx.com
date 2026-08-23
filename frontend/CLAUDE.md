# CLAUDE.md — frontend/

This file provides guidance to Claude Code when working under `frontend/`. See the root
`CLAUDE.md` for universal project guidance (architecture priorities, coding principles,
cross-cutting rules, security).

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
- The admin panel (`lang/admin/`, `pages/admin/`, `components/admin/`) follows the exact same
  convention as the public storefront — same per-feature file split, same `useI18n()`/`t()` pattern,
  same en+uk requirement. There is no longer a separate/deferred admin pass; treat it identically to
  `lang/public/`.
