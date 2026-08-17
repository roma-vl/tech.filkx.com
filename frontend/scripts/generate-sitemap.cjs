#!/usr/bin/env node
/**
 * Rebuilds dist/sitemap.xml from the real public API after the production
 * build. Plain CJS + axios (no Vite/Vue involved) so it can run standalone
 * as `node scripts/generate-sitemap.cjs` — mirrors the same endpoints/params
 * used by src/shared/services/api/productApi.ts (`/v1/catalog/*`) and the
 * blog pages (`/v1/blog/*`, see src/pages/blog/BlogPage.vue).
 */
const fs = require("node:fs");
const path = require("node:path");
const axios = require("axios");

const ROOT = path.dirname(__dirname);
const DIST_DIR = path.join(ROOT, "dist");

// Vite-style env loading without depending on `vite` (ESM-only in this
// version) from a CJS script: base `.env`, overridden by the mode-specific
// file — same precedence `vite build` itself uses.
function loadEnvFile(filePath, into) {
  if (!fs.existsSync(filePath)) return;
  for (const line of fs.readFileSync(filePath, "utf-8").split("\n")) {
    const match = line.match(/^\s*([\w.-]+)\s*=\s*(.*)\s*$/);
    if (!match) continue;
    let value = match[2].trim();
    if (
      (value.startsWith('"') && value.endsWith('"')) ||
      (value.startsWith("'") && value.endsWith("'"))
    ) {
      value = value.slice(1, -1);
    }
    into[match[1]] = value;
  }
}

function loadEnv(mode) {
  const env = {};
  loadEnvFile(path.join(ROOT, ".env"), env);
  loadEnvFile(path.join(ROOT, `.env.${mode}`), env);
  return env;
}

const MODE = process.env.NODE_ENV === "development" ? "development" : "production";
const env = loadEnv(MODE);
const API_BASE_URL = process.env.VITE_API_BASE_URL || env.VITE_API_BASE_URL;
const SITE_BASE_URL = (
  process.env.VITE_BASE_URL ||
  env.VITE_BASE_URL ||
  "https://tech.filkx.com"
).replace(/\/$/, "");

if (!API_BASE_URL) {
  console.error("[sitemap] VITE_API_BASE_URL is not set (.env / .env.production) — aborting.");
  process.exit(1);
}

const api = axios.create({ baseURL: API_BASE_URL, timeout: 30000 });

// Static, slug-driven pages served by StaticPage.vue (see
// src/router/routes/application.js). Locale-prefixed marketing pages under
// src/router/routes/public.js (`/:locale(en|uk)/about` etc.) are a separate,
// duplicate-content set of components and are intentionally left out here
// to avoid listing two different URLs for overlapping content.
const STATIC_PAGES = [
  "shipping-payment",
  "warranty-returns",
  "service",
  "services",
  "installments",
  "filkx-exchange",
  "contacts",
  "about",
  "terms",
  "careers",
  "franchising",
  "promo-rules",
  "privacy",
  "oferta",
  "cookies",
];

function flattenCategories(categories, acc = []) {
  for (const cat of categories) {
    if (cat.slug) acc.push(cat.slug);
    if (Array.isArray(cat.children) && cat.children.length > 0) {
      flattenCategories(cat.children, acc);
    }
  }
  return acc;
}

async function fetchAllPaginated(url, params) {
  // Keyed by id, not pushed into a plain array: the API's default ordering
  // isn't guaranteed stable across separate paginated requests, so the same
  // row can land on two consecutive pages (and, in principle, a row could
  // shift out of every page entirely — acceptable for a sitemap, which only
  // needs to be a reasonably complete, duplicate-free list of real URLs).
  const itemsById = new Map();
  let page = 1;
  let lastPage = 1;
  do {
    const { data } = await api.get(url, { params: { ...params, page, per_page: 100 } });
    const payload = data?.data;
    const pageItems = payload?.data || (Array.isArray(payload) ? payload : []);
    for (const item of pageItems) {
      itemsById.set(item.id ?? item.slug, item);
    }
    lastPage = payload?.lastPage || 1;
    page += 1;
  } while (page <= lastPage);
  return [...itemsById.values()];
}

function xmlEscape(value) {
  return String(value).replace(/[<>&'"]/g, (c) => ({
    "<": "&lt;",
    ">": "&gt;",
    "&": "&amp;",
    "'": "&apos;",
    '"': "&quot;",
  })[c]);
}

function urlEntry(loc, { lastmod, changefreq = "weekly", priority = "0.5" } = {}) {
  return [
    "  <url>",
    `    <loc>${xmlEscape(loc)}</loc>`,
    lastmod ? `    <lastmod>${xmlEscape(lastmod)}</lastmod>` : null,
    `    <changefreq>${changefreq}</changefreq>`,
    `    <priority>${priority}</priority>`,
    "  </url>",
  ]
    .filter(Boolean)
    .join("\n");
}

async function main() {
  const entries = [];

  entries.push(urlEntry(`${SITE_BASE_URL}/`, { changefreq: "daily", priority: "1.0" }));
  entries.push(urlEntry(`${SITE_BASE_URL}/catalog`, { changefreq: "daily", priority: "0.9" }));
  entries.push(urlEntry(`${SITE_BASE_URL}/blog`, { changefreq: "daily", priority: "0.7" }));

  for (const slug of STATIC_PAGES) {
    entries.push(urlEntry(`${SITE_BASE_URL}/${slug}`, { changefreq: "monthly", priority: "0.3" }));
  }

  try {
    const { data } = await api.get("/v1/catalog/categories");
    const categorySlugs = flattenCategories(data?.data || []);
    for (const slug of categorySlugs) {
      entries.push(
        urlEntry(`${SITE_BASE_URL}/catalog?category=${encodeURIComponent(slug)}`, {
          changefreq: "daily",
          priority: "0.7",
        }),
      );
    }
    console.log(`[sitemap] ${categorySlugs.length} categories`);
  } catch (e) {
    console.warn("[sitemap] failed to load categories:", e.message);
  }

  try {
    const products = await fetchAllPaginated("/v1/catalog/products", {});
    for (const product of products) {
      const idOrSlug = product.slug || product.id;
      entries.push(
        urlEntry(`${SITE_BASE_URL}/product/${encodeURIComponent(idOrSlug)}`, {
          lastmod: product.updatedAt,
          changefreq: "weekly",
          priority: "0.8",
        }),
      );
    }
    console.log(`[sitemap] ${products.length} products`);
  } catch (e) {
    console.warn("[sitemap] failed to load products:", e.message);
  }

  try {
    const posts = await fetchAllPaginated("/v1/blog/posts", {});
    for (const post of posts) {
      if (!post.slug) continue;
      entries.push(
        urlEntry(`${SITE_BASE_URL}/blog/${encodeURIComponent(post.slug)}`, {
          lastmod: post.updatedAt || post.publishedAt,
          changefreq: "monthly",
          priority: "0.6",
        }),
      );
    }
    console.log(`[sitemap] ${posts.length} blog posts`);
  } catch (e) {
    console.warn("[sitemap] failed to load blog posts:", e.message);
  }

  const xml =
    '<?xml version="1.0" encoding="UTF-8"?>\n' +
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
    entries.join("\n") +
    "\n</urlset>\n";

  fs.mkdirSync(DIST_DIR, { recursive: true });
  fs.writeFileSync(path.join(DIST_DIR, "sitemap.xml"), xml, "utf-8");
  console.log(`[sitemap] wrote dist/sitemap.xml with ${entries.length} URLs`);
}

main().catch((err) => {
  console.error("[sitemap] fatal:", err);
  process.exitCode = 1;
});
