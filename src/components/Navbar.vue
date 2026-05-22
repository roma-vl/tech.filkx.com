<script setup>
import { ref } from 'vue';

const activeMenu = ref('Smartphones');
const categories = ['Smartphones', 'Laptops', 'Gaming', 'Audio', 'Wearables'];

const selectMenu = (menu) => {
  activeMenu.value = menu;
};
</script>

<template>
  <!-- Navigation Links (High Contrast) -->
  <nav class="bg-surface-container-lowest border-t border-surface-variant px-margin-desktop hidden md:block">
    <div class="max-w-container-max mx-auto flex items-center h-12">
      <div class="relative group mega-menu-trigger h-full flex items-center">
        <button class="flex items-center gap-3 bg-primary text-white px-6 h-full font-title-md hover:bg-primary-container transition-colors">
          <span class="material-symbols-outlined text-[20px]">menu</span>
          Browse Categories
        </button>
        <!-- Mega Menu Content -->
        <div class="mega-menu hidden absolute top-full left-0 w-[800px] bg-white shadow-2xl border border-surface-variant rounded-b-xl grid grid-cols-4 p-8 gap-8 z-[60]">
          <div>
            <h4 class="font-title-md text-primary mb-4 border-b border-surface-variant pb-2">Smartphones</h4>
            <ul class="space-y-3 text-on-surface-variant font-body-md">
              <li class="hover:text-primary cursor-pointer transition-colors">iPhone 15 Series</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Samsung Galaxy S24</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Google Pixel 8</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Folding Phones</li>
              <li class="hover:text-primary cursor-pointer transition-colors font-semibold text-primary">
                View All <span class="material-symbols-outlined text-sm align-middle">chevron_right</span>
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-title-md text-primary mb-4 border-b border-surface-variant pb-2">Laptops</h4>
            <ul class="space-y-3 text-on-surface-variant font-body-md">
              <li class="hover:text-primary cursor-pointer transition-colors">MacBook Pro &amp; Air</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Gaming Laptops</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Ultrabooks</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Workstations</li>
            </ul>
          </div>
          <div>
            <h4 class="font-title-md text-primary mb-4 border-b border-surface-variant pb-2">Audio</h4>
            <ul class="space-y-3 text-on-surface-variant font-body-md">
              <li class="hover:text-primary cursor-pointer transition-colors">Noise Cancelling</li>
              <li class="hover:text-primary cursor-pointer transition-colors">True Wireless</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Home Theater</li>
              <li class="hover:text-primary cursor-pointer transition-colors">Smart Speakers</li>
            </ul>
          </div>
          <div class="bg-surface-container-low p-4 rounded-lg border border-outline-variant/30">
            <img class="rounded-lg mb-4 w-full h-32 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAo4SVYBoIj8-Y0aNgpFBTR-BlAWpfhfn6Yknefa93sZYlFQ67tdyFUMjqskBFEpna4Q4AKDYNwDHwOcoGD7ez01wEe6G92iwcNr4-mA7Ttzo5h5pu_0kEO3CGr2RZ-curX97K-qBqy2PrmjobmcJxaHzqkKY5zE2pQeaD8rqC44fKEAuZq2F-K5OdI28HaDYgp8DTenHHTnSMtJg-IN1_RXk-6DjRlKnJUEBgrM5tIwSXYY9oobt_hFe1uTU3u1h87rBXcPYITmB4"/>
            <p class="font-title-md text-sm mb-1">Weekly Special</p>
            <p class="text-on-surface-variant text-xs mb-3">Up to 30% off selected accessories.</p>
            <button class="text-primary font-bold text-xs flex items-center gap-1 group/btn">
              Shop Now <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
            </button>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-10 ml-10 font-label-md text-on-surface uppercase tracking-[0.12em] font-bold">
        <a 
          v-for="cat in categories" 
          :key="cat"
          @click.prevent="selectMenu(cat)"
          :class="[
            activeMenu === cat 
              ? 'text-primary border-b-2 border-primary py-2.5' 
              : 'hover:text-primary transition-colors py-2.5'
          ]"
          href="#"
        >
          {{ cat }}
        </a>
      </div>
    </div>
  </nav>
</template>
