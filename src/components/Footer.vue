<script setup>
import { ref } from 'vue';

const email = ref('');
const showToast = ref(false);

const handleSubscribe = () => {
  if (email.value.trim()) {
    showToast.value = true;
    setTimeout(() => {
      showToast.value = false;
      email.value = '';
    }, 4000);
  }
};
</script>

<template>
  <!-- Footer -->
  <footer class="bg-surface-container-highest pt-20 pb-10 relative">
    <div class="max-w-container-max mx-auto px-margin-desktop">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter mb-20">
        <!-- Brand Info -->
        <div class="md:col-span-4 pr-10">
          <a class="font-headline-md text-headline-md font-bold text-primary mb-6 block" href="/">ElectroLux</a>
          <p class="text-on-surface-variant font-body-md mb-8 leading-relaxed">
            Redefining premium electronics shopping through expert curation, technical transparency, and world-class customer support since 2010. Our mission is to connect you with the most advanced technology on earth.
          </p>
          <div class="flex gap-5">
            <a class="w-11 h-11 rounded-lg bg-white border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white transition-all shadow-sm" href="#">
              <span class="material-symbols-outlined text-[20px]">public</span>
            </a>
            <a class="w-11 h-11 rounded-lg bg-white border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white transition-all shadow-sm" href="#">
              <span class="material-symbols-outlined text-[20px]">alternate_email</span>
            </a>
            <a class="w-11 h-11 rounded-lg bg-white border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white transition-all shadow-sm" href="#">
              <span class="material-symbols-outlined text-[20px]">chat_bubble</span>
            </a>
          </div>
        </div>
        
        <!-- Sitemap Columns -->
        <div class="md:col-span-2 col-span-6">
          <h4 class="font-title-lg text-on-surface mb-6 font-bold">Shop</h4>
          <ul class="space-y-4 text-on-surface-variant font-medium">
            <li><a class="hover:text-primary transition-colors" href="#">All Products</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">New Arrivals</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Flash Deals</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Outlet</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Brands</a></li>
          </ul>
        </div>
        
        <div class="md:col-span-2 col-span-6">
          <h4 class="font-title-lg text-on-surface mb-6 font-bold">Support</h4>
          <ul class="space-y-4 text-on-surface-variant font-medium">
            <li><a class="hover:text-primary transition-colors" href="#">Help Center</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Shipping &amp; Returns</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Warranty Policy</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Contact Us</a></li>
            <li><a class="hover:text-primary transition-colors" href="#">Technical Support</a></li>
          </ul>
        </div>
        
        <!-- Subscription Form -->
        <div class="md:col-span-4 bg-white p-8 rounded-xl border border-outline-variant/30 shadow-sm col-span-12">
          <h4 class="font-title-lg text-on-surface mb-4 font-bold">Join the Elite</h4>
          <p class="text-on-surface-variant mb-6 font-body-md">Subscribe for early access to product drops and exclusive technical insights.</p>
          <form @submit.prevent="handleSubscribe" class="flex gap-2">
            <input 
              v-model="email"
              class="bg-surface-container-low border border-outline-variant px-4 py-3 rounded-lg flex-grow focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm" 
              placeholder="Enter your email" 
              type="email"
              required
            />
            <button class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-container transition-all shadow-md">Join</button>
          </form>
          <p class="text-[10px] text-on-surface-variant mt-4 opacity-60">By subscribing, you agree to our Privacy Policy and Terms of Service.</p>
        </div>
      </div>
      
      <!-- Bottom Bar -->
      <div class="border-t border-outline-variant/50 pt-10 flex flex-col md:flex-row justify-between items-center gap-6 text-on-surface-variant font-label-md">
        <p class="font-medium">© 2024 ElectroLux Premium Electronics. All rights reserved.</p>
        <div class="flex gap-10 font-bold uppercase tracking-widest text-[10px]">
          <a class="hover:text-primary transition-all" href="#">Privacy</a>
          <a class="hover:text-primary transition-all" href="#">Terms</a>
          <a class="hover:text-primary transition-all" href="#">Sitemap</a>
          <a class="hover:text-primary transition-all" href="#">Cookies</a>
        </div>
        <div class="flex items-center gap-6 opacity-60">
          <span class="material-symbols-outlined text-2xl">payments</span>
        </div>
      </div>
    </div>

    <!-- Toast Notification for Subscription Success -->
    <div 
      v-if="showToast" 
      class="fixed bottom-8 right-8 z-[200] bg-primary text-white px-6 py-4 rounded-xl shadow-2xl border border-primary-fixed-dim/20 flex items-center gap-3 animate-bounce"
    >
      <span class="material-symbols-outlined">mark_email_read</span>
      <div>
        <p class="font-bold text-sm">Successfully Subscribed!</p>
        <p class="text-xs text-primary-fixed-dim">Thank you for joining our elite newsletter.</p>
      </div>
    </div>
  </footer>
</template>
