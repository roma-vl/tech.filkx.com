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
  background: #ffffff;
  border-top: 1px solid #e4e4e7;
  border-bottom: 1px solid #e4e4e7;
  position: relative;
  z-index: 40;
}

.navbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 32px;
  display: flex;
  align-items: center;
  height: 52px;
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
  background: #09090b;
  color: #ffffff;
  padding: 0 18px;
  height: 44px;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.03em;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  white-space: nowrap;
}

.browse-btn:hover,
.browse-btn--active {
  background: #27272a;
  box-shadow: 0 4px 12px rgba(9, 9, 11, 0.15);
}

.browse-icon {
  font-size: 18px;
  transition: transform 0.2s ease;
}

.browse-chevron {
  font-size: 16px;
  transition: transform 0.25s ease;
  margin-left: 8px;
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
  background: #ffffff;
  border: 1px solid #e4e4e7;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.02);
  margin-top: 8px;
  overflow: hidden;
  z-index: 60;
  transform-origin: top left;
}

/* ── Sidebar ──────────────────────────────────────────── */
.mega-sidebar {
  background: #fafafa;
  padding: 16px 0;
  border-right: 1px solid #e4e4e7;
}

.mega-sidebar-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #71717a;
  padding: 0 16px 10px;
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
  padding: 10px 16px;
  cursor: pointer;
  transition: all 0.15s ease;
  color: #3f3f46;
  font-size: 13px;
  font-weight: 500;
  border-left: 2px solid transparent;
}

.mega-cat-item:hover,
.mega-cat-item--active {
  background: #f4f4f5;
  color: #09090b;
  border-left-color: #09090b;
}

.cat-icon {
  font-size: 18px;
  opacity: 0.8;
}

.cat-label {
  flex: 1;
}

.cat-arrow {
  font-size: 14px;
  opacity: 0;
  transition: all 0.15s ease;
}

.mega-cat-item:hover .cat-arrow,
.mega-cat-item--active .cat-arrow {
  opacity: 1;
  transform: translateX(3px);
}

/* ── Sub-category panel ───────────────────────────────── */
.mega-panel {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.mega-panel-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f4f4f5;
}

.panel-icon {
  font-size: 20px;
  color: #09090b;
  background: #f4f4f5;
  border-radius: 6px;
  padding: 4px;
}

.panel-title {
  font-size: 15px;
  font-weight: 600;
  color: #09090b;
  margin: 0;
}

.mega-sub-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  flex: 1;
}

.mega-sub-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #52525b;
  font-size: 13px;
  font-weight: 500;
  border: 1px solid transparent;
}

.mega-sub-item:hover {
  background: #fafafa;
  color: #09090b;
  border-color: #e4e4e7;
}

.sub-dot {
  font-size: 14px;
  color: #a1a1aa;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.mega-sub-item:hover .sub-dot {
  color: #09090b;
  transform: translateX(2px);
}

.sub-name {
  flex: 1;
}

.sub-badge {
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.02em;
  padding: 1px 5px;
  border-radius: 4px;
  text-transform: uppercase;
}

.badge--new    { background: #f4f4f5; color: #18181b; border: 1px solid #e4e4e7; }
.badge--hot    { background: #fee2e2; color: #ef4444; }
.badge--trending { background: #fef3c7; color: #d97706; }

.view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #09090b;
  text-decoration: none;
  transition: all 0.2s ease;
}

.view-all-link:hover {
  gap: 6px;
}

/* ── Promos ───────────────────────────────────────────── */
.mega-promos {
  background: #fafafa;
  padding: 16px;
  border-left: 1px solid #e4e4e7;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.promos-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #71717a;
  margin-bottom: 4px;
}

.promo-card {
  background: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e4e4e7;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
  display: flex;
  align-items: center;
}

.promo-card:hover {
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04);
  border-color: #d4d4d8;
  transform: translateY(-1px);
}

.promo-img {
  width: 64px;
  height: 64px;
  object-fit: cover;
  flex-shrink: 0;
}

.promo-body {
  padding: 8px 12px;
  flex: 1;
  min-width: 0;
}

.promo-title {
  font-size: 12px;
  font-weight: 600;
  color: #09090b;
  margin: 0 0 2px;
}

.promo-desc {
  font-size: 10px;
  color: #71717a;
  margin: 0 0 4px;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.promo-cta {
  display: flex;
  align-items: center;
  gap: 4px;
  background: none;
  border: none;
  color: #09090b;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  transition: gap 0.2s;
}

.promo-cta:hover {
  gap: 6px;
}

.promo-cta .material-symbols-outlined {
  font-size: 12px;
}

/* ── Nav links ────────────────────────────────────────── */
.nav-links {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-left: 24px;
}

.nav-link {
  display: inline-flex;
  align-items: center;
  padding: 0 16px;
  height: 52px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #52525b;
  text-decoration: none;
  position: relative;
  transition: all 0.2s ease;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 16px;
  right: 16px;
  height: 2px;
  background: #09090b;
  border-radius: 2px 2px 0 0;
  transform: scaleX(0);
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.nav-link:hover {
  color: #09090b;
}

.nav-link:hover::after {
  transform: scaleX(0.7);
}

.nav-link--active {
  color: #09090b;
}

.nav-link--active::after {
  transform: scaleX(1);
}

/* ── Transitions ──────────────────────────────────────── */
.mega-enter-active,
.mega-leave-active {
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.mega-enter-from,
.mega-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.99);
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(4px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-4px);
}
</style>

