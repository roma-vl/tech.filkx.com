<script setup>
import { ref } from 'vue';

const showLangDropdown = ref(false);
const activeLang = ref('EN / USD');

const selectLang = (lang) => {
  activeLang.value = lang;
  showLangDropdown.value = false;
};
</script>

<template>
  <!-- Promo Top Bar -->
  <div class="bg-inverse-surface text-inverse-on-surface py-2.5 px-margin-desktop hidden md:block">
    <div class="max-w-container-max mx-auto flex justify-between items-center font-label-md text-label-md">
      <div class="flex items-center gap-6">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-[16px] text-primary-fixed-dim">local_shipping</span> 
          Free Express Shipping on orders over $500
        </span>
        <span class="w-px h-3 bg-outline/30"></span>
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-[16px] text-primary-fixed-dim">verified</span> 
          2-Year Premium Warranty
        </span>
      </div>
      <div class="flex items-center gap-8">
        <a class="hover:text-primary-fixed transition-colors" href="#">Store Locator</a>
        <a class="hover:text-primary-fixed transition-colors" href="#">Track Order</a>
        <div class="relative">
          <div 
            @click="showLangDropdown = !showLangDropdown" 
            class="flex items-center gap-1 cursor-pointer group"
          >
            <span class="group-hover:text-primary-fixed transition-colors">{{ activeLang }}</span>
            <span class="material-symbols-outlined text-[16px] group-hover:text-primary-fixed transition-colors">expand_more</span>
          </div>
          <!-- Dropdown -->
          <div 
            v-if="showLangDropdown" 
            class="absolute right-0 top-full mt-2 bg-white text-on-background border border-surface-variant rounded-lg shadow-xl z-50 py-2 w-32 font-body-md"
          >
            <button 
              @click="selectLang('EN / USD')" 
              class="w-full text-left px-4 py-2 hover:bg-surface-container transition-colors text-xs font-semibold"
            >
              EN / USD
            </button>
            <button 
              @click="selectLang('UA / UAH')" 
              class="w-full text-left px-4 py-2 hover:bg-surface-container transition-colors text-xs font-semibold"
            >
              UA / UAH
            </button>
            <button 
              @click="selectLang('DE / EUR')" 
              class="w-full text-left px-4 py-2 hover:bg-surface-container transition-colors text-xs font-semibold"
            >
              DE / EUR
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
