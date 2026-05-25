<script setup>
import { ref } from 'vue';
import { store } from '../store.js';

const isLoggedIn = ref(true);
const emailInput = ref('');
const passwordInput = ref('');

const handleLogin = (e) => {
  e.preventDefault();
  if (emailInput.value && passwordInput.value) {
    isLoggedIn.value = true;
    store.addToast(`Welcome back, ${emailInput.value.split('@')[0]}!`, 'success');
  } else {
    store.addToast('Please fill in email and password.', 'error');
  }
};

const handleLogout = () => {
  isLoggedIn.value = false;
  store.addToast('You have been logged out.', 'info');
};

const orders = [
  {
    id: 'EL-90823',
    date: 'May 12, 2026',
    status: 'Delivered',
    total: 378.00,
    items: ['Studio Pro Headphones (2x)', 'FitTrack Pro smartwatch (1x)']
  },
  {
    id: 'EL-87114',
    date: 'April 02, 2026',
    status: 'Delivered',
    total: 1150.00,
    items: ['Lumix Alpha Prime v2 Mirrorless Camera']
  }
];
</script>

<template>
  <div 
    v-if="store.activeDrawer === 'account'" 
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in duration-200"
  >
    <!-- Modal Container -->
    <div 
      class="bg-white rounded-2xl max-w-lg w-full p-8 relative shadow-2xl border border-surface-variant flex flex-col max-h-[90vh] overflow-y-auto"
    >
      <!-- Close button -->
      <button 
        @click="store.closeDrawer()" 
        class="absolute top-4 right-4 text-on-surface-variant hover:text-primary transition-colors"
      >
        <span class="material-symbols-outlined text-[28px]">close</span>
      </button>

      <!-- Logged In State -->
      <div v-if="isLoggedIn" class="flex flex-col gap-6">
        <!-- Profile Card -->
        <div class="flex items-center gap-4 border-b border-surface-variant pb-6">
          <div class="w-16 h-16 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-2xl">
            JD
          </div>
          <div class="flex-grow">
            <h3 class="font-headline-md text-title-lg text-on-surface leading-tight">John Doe</h3>
            <p class="text-xs text-on-surface-variant">john.doe@electrolux.com</p>
            <div class="flex items-center gap-2 mt-1.5">
              <span class="bg-primary text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full">
                Obsidian Member
              </span>
              <span class="text-[10px] text-primary font-semibold">1,240 XP Points</span>
            </div>
          </div>
        </div>

        <!-- Orders -->
        <div class="flex flex-col gap-3">
          <h4 class="font-title-md text-sm text-on-surface uppercase tracking-wider">Recent Orders</h4>
          <div class="flex flex-col gap-3">
            <div 
              v-for="order in orders" 
              :key="order.id"
              class="border border-outline-variant/30 rounded-xl p-4 bg-surface-container-low/50"
            >
              <div class="flex justify-between items-center mb-2">
                <span class="font-bold text-xs text-on-surface">{{ order.id }}</span>
                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">
                  {{ order.status }}
                </span>
              </div>
              <p class="text-[11px] text-on-surface-variant mb-2">{{ order.date }}</p>
              <div class="text-[11px] text-on-surface font-medium mb-3">
                <div v-for="item in order.items" :key="item">• {{ item }}</div>
              </div>
              <div class="flex justify-between items-center text-xs border-t border-outline-variant/20 pt-2 font-semibold">
                <span class="text-on-surface-variant">Total</span>
                <span class="text-primary font-bold">${{ order.total.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Logout button -->
        <button 
          @click="handleLogout()"
          class="w-full py-3 border border-outline-variant text-on-surface rounded-xl font-bold hover:bg-error/5 hover:text-error hover:border-error/20 transition-all text-xs flex items-center justify-center gap-2"
        >
          Log Out <span class="material-symbols-outlined text-[16px]">logout</span>
        </button>
      </div>

      <!-- Logged Out State -->
      <div v-else class="flex flex-col gap-6">
        <div class="text-center">
          <h3 class="font-headline-md text-headline-md text-on-surface">Welcome back</h3>
          <p class="text-xs text-on-surface-variant mt-1">Log in to view orders, points, and loyalty benefits.</p>
        </div>

        <form @submit="handleLogin" class="flex flex-col gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Email address</label>
            <input 
              v-model="emailInput"
              type="email" 
              required
              placeholder="name@example.com"
              class="w-full h-12 px-4 bg-surface-container-low border-none ring-1 ring-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-sm"
            />
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Password</label>
            <input 
              v-model="passwordInput"
              type="password" 
              required
              placeholder="••••••••"
              class="w-full h-12 px-4 bg-surface-container-low border-none ring-1 ring-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-sm"
            />
          </div>

          <button 
            type="submit"
            class="w-full bg-primary text-white py-3.5 rounded-xl font-bold hover:bg-primary-container transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/10 mt-2"
          >
            Log In <span class="material-symbols-outlined text-[18px]">login</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
