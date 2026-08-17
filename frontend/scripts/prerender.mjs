#!/usr/bin/env node
/**
 * Static prerendering — no headless browser involved. Boots a Vite dev
 * server in middleware mode purely so its module graph can transform
 * TS/Vue/JS on demand (aliases, SFC compilation with ssrRender), loads
 * src/entry-server.ts through it, and uses @vue/server-renderer's
 * renderToString() — pure server-side Vue rendering, the same primitive
 * real SSR/SSG tooling is built on — to render a representative set of
 * routes to real HTML. Each result is written to dist/<route>/index.html so
 * nginx (docker-compose-production.yml's `tech-frontend`, static dist/) can
 * serve real content on first paint / to crawlers.
 *
 * There is no server.js / long-running Node SSR process in production —
 * see docs/PROJECT_MAP.md "SSR". This script only runs at build time.
 */
import { createServer } from "vite";
import { renderToString } from "@vue/server-renderer";
import { renderHeadToString } from "@vueuse/head";
import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const DIST = process.env.PRERENDER_DIST || path.join(ROOT, "dist");

// Matches `vite build`'s default mode so the data fetched here (and its
// baked-in API base URL) agrees with what the shipped client bundle uses.
// Override with PRERENDER_MODE=development for local/sandboxed testing
// against the dev API when the production API isn't reachable.
const MODE = process.env.PRERENDER_MODE || "production";
const MAX_PRODUCTS = Number(process.env.PRERENDER_MAX_PRODUCTS || 20);
const MAX_BLOG_POSTS = Number(process.env.PRERENDER_MAX_BLOG_POSTS || 50);

async function main() {
  const template = await fs.readFile(path.join(DIST, "index.html"), "utf-8");

  const vite = await createServer({
    root: ROOT,
    mode: MODE,
    cacheDir: process.env.PRERENDER_VITE_CACHE_DIR || undefined,
    server: { middlewareMode: true },
    appType: "custom",
  });

  try {
    const { renderPage } = await vite.ssrLoadModule("/src/entry-server.ts");
    const { productApi } = await vite.ssrLoadModule(
      "/src/shared/services/api/productApi.ts",
    );
    const { default: apiClient } = await vite.ssrLoadModule(
      "/src/shared/services/api/apiClient.ts",
    );

    const routes = await collectRoutes({ productApi, apiClient });
    console.log(`[prerender] ${routes.length} routes to render (mode=${MODE})`);

    let ok = 0;
    let failed = 0;
    for (const route of routes) {
      try {
        await renderRoute(route, { renderPage, template });
        ok += 1;
        console.log(`[prerender] ok    ${route.url} -> dist/${route.outFile}`);
      } catch (err) {
        failed += 1;
        console.warn(`[prerender] FAILED ${route.url}:`, err?.message || err);
      }
    }
    console.log(`[prerender] done: ${ok} ok, ${failed} failed, ${routes.length} total`);
    if (ok === 0) {
      throw new Error("prerender produced zero pages — treat as a build failure");
    }
  } finally {
    await vite.close();
  }
}

/**
 * A representative subset per route type, not the full catalog: the base
 * catalog/blog listing, every blog post (small — a few dozen at most), and
 * a capped number of products. `dist/sitemap.xml` (generated separately)
 * still lists every real product/category/post URL regardless of whether
 * it got a prerendered file.
 */
async function collectRoutes({ productApi, apiClient }) {
  const routes = [
    { url: "/", outFile: "index.html" },
    { url: "/catalog", outFile: "catalog/index.html" },
    { url: "/blog", outFile: "blog/index.html" },
  ];

  try {
    const { data } = await productApi.catalogGetProducts({ per_page: MAX_PRODUCTS });
    const products = data?.data?.data || [];
    for (const product of products) {
      const idOrSlug = product.slug || product.id;
      routes.push({
        url: `/product/${idOrSlug}`,
        outFile: `product/${idOrSlug}/index.html`,
      });
    }
  } catch (err) {
    console.warn("[prerender] failed to list products:", err?.message || err);
  }

  try {
    const { data } = await apiClient.get("/v1/blog/posts", {
      params: { per_page: MAX_BLOG_POSTS },
    });
    const posts = data?.data?.data || [];
    for (const post of posts) {
      if (!post.slug) continue;
      routes.push({
        url: `/blog/${post.slug}`,
        outFile: `blog/${post.slug}/index.html`,
      });
    }
  } catch (err) {
    console.warn("[prerender] failed to list blog posts:", err?.message || err);
  }

  return routes;
}

async function renderRoute(route, { renderPage, template }) {
  const { app, head } = await renderPage(route.url);
  const appHtml = await renderToString(app);
  const { headTags } = await renderHeadToString(head);

  const outPath = path.join(DIST, route.outFile);
  await fs.mkdir(path.dirname(outPath), { recursive: true });
  await fs.writeFile(outPath, injectIntoTemplate(template, appHtml, headTags), "utf-8");
}

/** Swaps the built index.html's static placeholder title/meta/app-root for
 * the page's real, server-rendered ones so crawlers don't see two
 * conflicting titles/descriptions. */
function injectIntoTemplate(template, appHtml, headTags) {
  let html = template
    .replace(/<title>.*?<\/title>\s*/s, "")
    .replace(/<meta name="description"[^>]*>\s*/, "")
    .replace(/<meta property="og:type"[^>]*>\s*/, "");

  html = html.replace("</head>", `${headTags}\n  </head>`);
  html = html.replace('<div id="app"></div>', `<div id="app">${appHtml}</div>`);
  return html;
}

main().catch((err) => {
  console.error("[prerender] fatal:", err);
  process.exitCode = 1;
});
