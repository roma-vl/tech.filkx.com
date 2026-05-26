<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const isMegaMenuOpen = ref(false);
const activeCategory = ref(null);
const navbarRef = ref(null);

const navLinks = ['Smartphones', 'Laptops', 'Gaming', 'Audio', 'Wearables'];

const activeNavLink = ref('Smartphones');

const categories = [
  {
    id: 'smartphones',
    label: 'Smartphones',
    icon: 'smartphone',
    sub: [
      { name: 'iPhone 15 Series', badge: 'New' },
      { name: 'Samsung Galaxy S24', badge: '' },
      { name: 'Google Pixel 8', badge: '' },
      { name: 'Folding Phones', badge: 'Trending' },
      { name: 'Budget Phones', badge: '' },
      { name: 'Accessories', badge: '' },
    ],
  },
  {
    id: 'laptops',
    label: 'Laptops',
    icon: 'laptop',
    sub: [
      { name: 'MacBook Pro & Air', badge: 'New' },
      { name: 'Gaming Laptops', badge: 'Hot' },
      { name: 'Ultrabooks', badge: '' },
      { name: 'Workstations', badge: '' },
      { name: 'Chromebooks', badge: '' },
      { name: 'Laptop Accessories', badge: '' },
    ],
  },
  {
    id: 'gaming',
    label: 'Gaming',
    icon: 'sports_esports',
    sub: [
      { name: 'PlayStation 5', badge: 'Hot' },
      { name: 'Xbox Series X', badge: '' },
      { name: 'Nintendo Switch', badge: '' },
      { name: 'Gaming PCs', badge: '' },
      { name: 'Controllers & Pads', badge: '' },
      { name: 'Gaming Chairs', badge: '' },
    ],
  },
  {
    id: 'audio',
    label: 'Audio',
    icon: 'headphones',
    sub: [
      { name: 'Noise Cancelling', badge: '' },
      { name: 'True Wireless Earbuds', badge: 'New' },
      { name: 'Home Theater', badge: '' },
      { name: 'Smart Speakers', badge: '' },
      { name: 'Studio Monitors', badge: '' },
      { name: 'Soundbars', badge: '' },
    ],
  },
  {
    id: 'wearables',
    label: 'Wearables',
    icon: 'watch',
    sub: [
      { name: 'Apple Watch', badge: 'New' },
      { name: 'Galaxy Watch', badge: '' },
      { name: 'Fitness Trackers', badge: '' },
      { name: 'Smart Rings', badge: 'Trending' },
      { name: 'AR & VR Headsets', badge: '' },
      { name: 'Smart Glasses', badge: '' },
    ],
  },
  {
    id: 'cameras',
    label: 'Cameras',
    icon: 'photo_camera',
    sub: [
      { name: 'DSLR Cameras', badge: '' },
      { name: 'Mirrorless', badge: 'Hot' },
      { name: 'Action Cameras', badge: '' },
      { name: 'Drones', badge: '' },
      { name: 'Lenses', badge: '' },
      { name: 'Camera Bags', badge: '' },
    ],
  },
  {
    id: 'tablets',
    label: 'Tablets',
    icon: 'tablet',
    sub: [
      { name: 'iPad Pro & Air', badge: 'New' },
      { name: 'Samsung Galaxy Tab', badge: '' },
      { name: 'Android Tablets', badge: '' },
      { name: 'E-Readers', badge: '' },
      { name: 'Tablet Cases', badge: '' },
      { name: 'Stylus & Pens', badge: '' },
    ],
  },
  {
    id: 'smart-home',
    label: 'Smart Home',
    icon: 'home',
    sub: [
      { name: 'Smart Displays', badge: '' },
      { name: 'Smart Bulbs', badge: '' },
      { name: 'Security Cameras', badge: '' },
      { name: 'Smart Locks', badge: '' },
      { name: 'Robot Vacuums', badge: 'Hot' },
      { name: 'Smart Thermostats', badge: '' },
    ],
  },
];

const promos = [
  {
    img: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300&q=80',
    title: 'Weekly Special',
    desc: 'Up to 30% off selected accessories.',
  },
  {
    img: 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=300&q=80',
    title: 'New Arrivals',
    desc: 'Fresh drops every Monday.',
  },
];

const activeCat = ref(categories[0]);

const openMenu = () => {
  isMegaMenuOpen.value = true;
};

const closeMenu = () => {
  isMegaMenuOpen.value = false;
};

const hoverCategory = (cat) => {
  activeCat.value = cat;
};

const handleClickOutside = (e) => {
  if (navbarRef.value && !navbarRef.value.contains(e.target)) {
    closeMenu();
  }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleClickOutside));
</script>

<template>
  <nav ref="navbarRef" class="navbar-secondary hidden md:block w-full">
     <div class="max-w-container-max mx-auto h-16 px-margin-desktop flex items-center justify-between gap-gutter">

      <!-- Browse Categories Button -->
      <div class="browse-trigger-wrap" @mouseenter="openMenu" @mouseleave="closeMenu">
        <button
          class="browse-btn"
          :class="{ 'browse-btn--active': isMegaMenuOpen }"
          @click="isMegaMenuOpen = !isMegaMenuOpen"
          aria-haspopup="true"
          :aria-expanded="isMegaMenuOpen"
        >
          <span class="material-symbols-outlined browse-icon">{{ isMegaMenuOpen ? 'close' : 'menu' }}</span>
          Browse Categories
          <span class="material-symbols-outlined browse-chevron" :class="{ rotated: isMegaMenuOpen }">expand_more</span>
        </button>

        <!-- Mega Menu -->
        <Transition name="mega">
          <div v-if="isMegaMenuOpen" class="mega-menu" role="dialog" aria-label="Category Menu">
            <!-- Left: Category Sidebar -->
            <div class="mega-sidebar">
              <p class="mega-sidebar-label">All Categories</p>
              <ul class="mega-cat-list">
                <li
                  v-for="cat in categories"
                  :key="cat.id"
                  class="mega-cat-item"
                  :class="{ 'mega-cat-item--active': activeCat.id === cat.id }"
                  @mouseenter="hoverCategory(cat)"
                >
                  <span class="material-symbols-outlined cat-icon">{{ cat.icon }}</span>
                  <span class="cat-label">{{ cat.label }}</span>
                  <span class="material-symbols-outlined cat-arrow">chevron_right</span>
                </li>
              </ul>
            </div>

            <!-- Center: Sub-categories Panel -->
            <Transition name="slide-fade" mode="out-in">
              <div :key="activeCat.id" class="mega-panel">
                <div class="mega-panel-header">
                  <span class="material-symbols-outlined panel-icon">{{ activeCat.icon }}</span>
                  <h3 class="panel-title">{{ activeCat.label }}</h3>
                </div>
                <ul class="mega-sub-list">
                  <li
                    v-for="sub in activeCat.sub"
                    :key="sub.name"
                    class="mega-sub-item"
                  >
                    <span class="material-symbols-outlined sub-dot">arrow_right</span>
                    <span class="sub-name">{{ sub.name }}</span>
                    <span v-if="sub.badge" class="sub-badge" :class="`badge--${sub.badge.toLowerCase()}`">{{ sub.badge }}</span>
                  </li>
                </ul>
                <a href="#" class="view-all-link">
                  View all {{ activeCat.label }}
                  <span class="material-symbols-outlined">arrow_forward</span>
                </a>
              </div>
            </Transition>

            <!-- Right: Promo Cards -->
            <div class="mega-promos">
              <p class="promos-label">Featured Deals</p>
              <div
                v-for="promo in promos"
                :key="promo.title"
                class="promo-card"
              >
                <img :src="promo.img" :alt="promo.title" class="promo-img" />
                <div class="promo-body">
                  <p class="promo-title">{{ promo.title }}</p>
                  <p class="promo-desc">{{ promo.desc }}</p>
                  <button class="promo-cta">
                    Shop Now
                    <span class="material-symbols-outlined">arrow_forward</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Nav Links -->
      <div class="nav-links">
        <router-link
          v-for="link in navLinks"
          :key="link"
          :to="{ name: 'catalog', query: { category: link.toLowerCase() } }"
          class="nav-link"
          :class="{ 'nav-link--active': activeNavLink === link }"
          @click="activeNavLink = link"
        >
          {{ link }}
        </router-link>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* ── Navbar shell ─────────────────────────────────────── */
.navbar-secondary {
  background: var(--color-surface-container-lowest, #fff);
  border-top: 1px solid var(--color-surface-variant, #e7e0ec);
  position: relative;
  z-index: 50;
}

.navbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 50px;
  display: flex;
  align-items: center;
  height: 48px;
  gap: 0;
}

/* ── Browse button ────────────────────────────────────── */
.browse-trigger-wrap {
  position: relative;
  height: 100%;
  display: flex;
  align-items: center;
}

.browse-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #005228 0%, #006d37 100%);
  color: #fff;
  padding: 0 20px;
  height: 48px;
  font-size: 0.875rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  border: none;
  cursor: pointer;
  transition: background 0.25s ease, box-shadow 0.25s ease;
  white-space: nowrap;
}

.browse-btn:hover,
.browse-btn--active {
  background: linear-gradient(135deg, #006d37 0%, #80d997 100%);
  box-shadow: 0 4px 16px rgba(0, 82, 40, 0.35);
}

.browse-icon {
  font-size: 20px;
  transition: transform 0.25s ease;
}

.browse-chevron {
  font-size: 18px;
  transition: transform 0.3s ease;
  margin-left: auto;
}

.browse-chevron.rotated {
  transform: rotate(180deg);
}

/* ── Mega Menu ────────────────────────────────────────── */
.mega-menu {
  position: absolute;
  top: 100%;
  left: 0;
  display: grid;
  grid-template-columns: 220px 1fr 240px;
  width: 860px;
  background: #fff;
  border: 1px solid rgba(0, 82, 40, 0.12);
  border-top: 3px solid #005228;
  border-radius: 0 0 16px 16px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.14), 0 4px 16px rgba(0, 82, 40, 0.1);
  overflow: hidden;
  z-index: 60;
}

/* ── Sidebar ──────────────────────────────────────────── */
.mega-sidebar {
  background: #f3faf5;
  padding: 20px 0;
  border-right: 1px solid rgba(0, 82, 40, 0.1);
}

.mega-sidebar-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #006d37;
  padding: 0 16px 8px;
}

.mega-cat-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.mega-cat-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 16px;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  color: #4a4458;
  font-size: 0.84rem;
  font-weight: 500;
  border-left: 3px solid transparent;
}

.mega-cat-item:hover,
.mega-cat-item--active {
  background: rgba(0, 82, 40, 0.08);
  color: #005228;
  border-left-color: #005228;
}

.cat-icon {
  font-size: 19px;
  opacity: 0.75;
}

.cat-label {
  flex: 1;
}

.cat-arrow {
  font-size: 16px;
  opacity: 0;
  transition: opacity 0.15s, transform 0.15s;
}

.mega-cat-item:hover .cat-arrow,
.mega-cat-item--active .cat-arrow {
  opacity: 1;
  transform: translateX(2px);
}

/* ── Sub-category panel ───────────────────────────────── */
.mega-panel {
  padding: 24px 28px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.mega-panel-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e6f0e9;
}

.panel-icon {
  font-size: 22px;
  color: #005228;
  background: rgba(0, 82, 40, 0.1);
  border-radius: 8px;
  padding: 4px;
}

.panel-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1c1b1f;
  margin: 0;
}

.mega-sub-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
  flex: 1;
}

.mega-sub-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #49454f;
  font-size: 0.82rem;
  font-weight: 500;
  border: 1px solid transparent;
}

.mega-sub-item:hover {
  background: #ffffff;
  color: #005228;
  border-color: rgba(0, 82, 40, 0.15);
  box-shadow: 0 4px 12px rgba(0, 82, 40, 0.05);
  transform: translateY(-1px);
}

.sub-dot {
  font-size: 16px;
  color: #80d997;
  flex-shrink: 0;
  transition: transform 0.2s ease, color 0.2s ease;
}

.mega-sub-item:hover .sub-dot {
  color: #006d37;
  transform: translateX(3px);
}

.sub-name {
  flex: 1;
}

.sub-badge {
  font-size: 0.6rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  padding: 2px 6px;
  border-radius: 99px;
  text-transform: uppercase;
}

.badge--new    { background: #d1fadf; color: #12b76a; }
.badge--hot    { background: #ffe4d3; color: #f04438; }
.badge--trending { background: #fef3c7; color: #d97706; }

.view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 16px;
  font-size: 0.8rem;
  font-weight: 700;
  color: #005228;
  text-decoration: none;
  transition: gap 0.2s;
}

.view-all-link:hover {
  gap: 8px;
}

/* ── Promos ───────────────────────────────────────────── */
.mega-promos {
  background: #f3faf5;
  padding: 20px 16px;
  border-left: 1px solid rgba(0, 82, 40, 0.1);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.promos-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #006d37;
  margin-bottom: 4px;
}

.promo-card {
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid rgba(0, 82, 40, 0.1);
  box-shadow: 0 2px 8px rgba(0, 82, 40, 0.06);
  transition: box-shadow 0.2s, transform 0.2s;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.promo-card:hover {
  box-shadow: 0 8px 24px rgba(0, 82, 40, 0.14);
  transform: translateY(-2px);
}

.promo-img {
  width: 72px;
  height: 72px;
  object-fit: cover;
  flex-shrink: 0;
}

.promo-body {
  padding: 8px 10px;
  flex: 1;
}

.promo-title {
  font-size: 0.78rem;
  font-weight: 700;
  color: #1c1b1f;
  margin: 0 0 2px;
}

.promo-desc {
  font-size: 0.68rem;
  color: #79747e;
  margin: 0 0 4px;
  line-height: 1.2;
}

.promo-cta {
  display: flex;
  align-items: center;
  gap: 4px;
  background: none;
  border: none;
  color: #005228;
  font-size: 0.68rem;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  transition: gap 0.2s;
}

.promo-cta:hover {
  gap: 8px;
}

.promo-cta .material-symbols-outlined {
  font-size: 13px;
}

/* ── Nav links ────────────────────────────────────────── */
.nav-links {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-left: 32px;
}

.nav-link {
  display: inline-flex;
  align-items: center;
  padding: 0 14px;
  height: 48px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #49454f;
  text-decoration: none;
  position: relative;
  transition: color 0.2s;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 14px;
  right: 14px;
  height: 3px;
  background: #005228;
  border-radius: 2px 2px 0 0;
  transform: scaleX(0);
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-link:hover {
  color: #005228;
}

.nav-link:hover::after {
  transform: scaleX(0.7);
}

.nav-link--active {
  color: #005228;
}

.nav-link--active::after {
  transform: scaleX(1);
}

/* ── Transitions ──────────────────────────────────────── */
.mega-enter-active,
.mega-leave-active {
  transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.mega-enter-from,
.mega-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(8px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-8px);
}
</style>
