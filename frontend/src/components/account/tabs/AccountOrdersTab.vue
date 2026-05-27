<script setup>
import { ref, computed } from 'vue';
import { store } from '@/store.js';

const defaultOrders = [
  {
    id: '120934812', date: 'Oct 24, 2024', total: 1299.00, shipTo: 'John Doe',
    status: 'In Transit - Arriving Tomorrow', statusIcon: 'local_shipping',
    statusClass: 'text-primary bg-primary/10 border border-primary/20', statusCode: 'shipped',
    trackingSteps: [
      { name: 'Order Placed', date: 'Oct 24, 2024 10:00 AM', done: true },
      { name: 'Processing', date: 'Oct 24, 2024 02:00 PM', done: true },
      { name: 'In Transit', date: 'Oct 25, 2024 09:00 AM', done: true },
      { name: 'Out for Delivery', date: 'Expected Oct 28, 2024', done: false },
      { name: 'Delivered', date: 'Expected Oct 29, 2024', done: false },
    ],
    shippingAddress: { recipient: 'John Doe', street: '123 Emerald Ave, Suite 400', city: 'Seattle', state: 'WA', zip: '98101', country: 'United States' },
    paymentMethod: { type: 'Visa', number: '•••• 4242' },
    items: [{ id: 101, name: 'ElectroLux ProBook 16" - M3 Max / 32GB RAM', price: 1299.00, returnWindow: 'Nov 24, 2024',
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuVg-kXP_Dz4eDubPsXwLRjC9ddkd7vuALwu9d44EXhvUcmau1ettEqBuExcpfD_u05Iro8mRrCfjRLlEyElDWNK2XCXugMhg8BlFQmzkH5QXS1DmI_-nGJmj7Qj2nzbNxTaMQKQp0bQWxjEJSBQKmRMm8yVY7heCmjBY2zXzrTzybDqI72Tff2a3iARsbv4capMzqEVs456EoLHm-kOY-mlW9RKHwerz8Fm73OO4YjBf74fI_5VFz-bK8GP4E1iscPSLxhUpP1ho' }],
  },
  {
    id: '992184021', date: 'Sept 12, 2024', total: 349.99, shipTo: 'John Doe',
    status: 'Delivered Sept 15, 2024', statusIcon: 'check_circle',
    statusClass: 'text-on-surface-variant bg-surface-container-high border border-outline-variant', statusCode: 'delivered',
    trackingSteps: [
      { name: 'Order Placed', date: 'Sept 12, 2024 08:30 AM', done: true },
      { name: 'Processing', date: 'Sept 12, 2024 11:00 AM', done: true },
      { name: 'In Transit', date: 'Sept 13, 2024 04:00 PM', done: true },
      { name: 'Out for Delivery', date: 'Sept 15, 2024 08:00 AM', done: true },
      { name: 'Delivered', date: 'Sept 15, 2024 02:30 PM', done: true },
    ],
    shippingAddress: { recipient: 'John Doe', street: '123 Emerald Ave, Suite 400', city: 'Seattle', state: 'WA', zip: '98101', country: 'United States' },
    paymentMethod: { type: 'Mastercard', number: '•••• 9876' },
    items: [{ id: 105, name: 'ElectroLux SonicPro ANC Headphones', price: 349.99, note: 'Delivered to the front porch.',
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA' }],
  },
  {
    id: '483920194', date: 'May 02, 2026', total: 1999.00, shipTo: 'John Doe',
    status: 'Cancelled May 02, 2026', statusIcon: 'cancel',
    statusClass: 'text-error bg-error-container border border-error/20', statusCode: 'cancelled',
    trackingSteps: [
      { name: 'Order Placed', date: 'May 02, 2026 09:00 AM', done: true },
      { name: 'Cancelled', date: 'May 02, 2026 09:30 AM', done: true },
    ],
    shippingAddress: { recipient: 'John Doe', street: '123 Emerald Ave, Suite 400', city: 'Seattle', state: 'WA', zip: '98101', country: 'United States' },
    paymentMethod: { type: 'Visa', number: '•••• 4242' },
    items: [{ id: 101, name: 'MacBook Pro 14" - M3 Pro Chip, 18GB RAM', price: 1999.00,
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM' }],
  },
];

const ordersList = ref([...defaultOrders]);
const ordersFilter = ref('all');
const ordersSearchQuery = ref('');

const selectedOrder = ref(null);
const isDetailsOpen = ref(false);
const trackingOrder = ref(null);
const isTrackingOpen = ref(false);
const reviewProduct = ref(null);
const reviewRating = ref(5);
const reviewComment = ref('');
const isReviewOpen = ref(false);

const filteredOrders = computed(() =>
  ordersList.value.filter(o => {
    if (ordersFilter.value !== 'all' && o.statusCode !== ordersFilter.value) return false;
    if (ordersSearchQuery.value.trim()) {
      const q = ordersSearchQuery.value.toLowerCase();
      return o.id.toLowerCase().includes(q) || o.items.some(i => i.name.toLowerCase().includes(q));
    }
    return true;
  })
);

const openDetails  = (o) => { selectedOrder.value = o; isDetailsOpen.value  = true; };
const openTracking = (o) => { trackingOrder.value  = o; isTrackingOpen.value = true; };
const openReview   = (i) => { reviewProduct.value  = i; reviewRating.value = 5; reviewComment.value = ''; isReviewOpen.value = true; };
const buyItAgain   = (i) => store.addToCart(i);
const submitReview = () => { store.addToast(`Thank you! Your ${reviewRating.value}-star review has been published.`, 'success'); isReviewOpen.value = false; };

const filterBtns = [
  { key: 'all', label: 'All' }, { key: 'shipped', label: 'Shipped' },
  { key: 'delivered', label: 'Delivered' }, { key: 'cancelled', label: 'Cancelled' },
];
</script>

<template>
  <div class="space-y-stack-md animate-fade">

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface-container-lowest p-4 rounded-xl border border-outline-variant shadow-sm">
      <div class="flex items-center bg-surface-container-low p-1 rounded-xl w-fit">
        <button v-for="btn in filterBtns" :key="btn.key" @click="ordersFilter = btn.key"
                :class="ordersFilter === btn.key ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                class="py-1.5 px-4 rounded-lg font-label-md text-xs transition-all">{{ btn.label }}</button>
      </div>
      <div class="relative flex-1 max-w-md">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
        <input v-model="ordersSearchQuery" type="text" placeholder="Search orders by ID or product name..."
               class="w-full bg-surface-container-low border border-outline-variant rounded-xl pl-10 pr-4 py-2 text-xs focus:ring-1 focus:ring-primary focus:border-primary text-on-surface outline-none" />
        <button v-if="ordersSearchQuery" @click="ordersSearchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
      </div>
    </div>

    <!-- Orders -->
    <div v-if="filteredOrders.length > 0" class="space-y-gutter">
      <div v-for="order in filteredOrders" :key="order.id"
           class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        <div class="bg-surface-container-low px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
          <div class="flex gap-8 flex-wrap">
            <div><p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order Placed</p><p class="font-title-md text-on-surface text-sm">{{ order.date }}</p></div>
            <div><p class="text-label-md text-on-surface-variant uppercase text-[10px]">Total</p><p class="font-title-md text-on-surface text-sm">${{ order.total.toFixed(2) }}</p></div>
            <div><p class="text-label-md text-on-surface-variant uppercase text-[10px]">Ship To</p><p class="font-title-md text-on-surface text-sm">{{ order.shipTo }}</p></div>
            <div>
              <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Status</p>
              <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="order.statusClass">
                <span class="material-symbols-outlined text-[12px]">{{ order.statusIcon }}</span>{{ order.status }}
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end gap-1">
            <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order #{{ order.id }}</p>
            <button @click="openDetails(order)" class="text-primary font-bold text-label-md hover:underline text-[11px]">View Order Details</button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div v-for="item in order.items" :key="item.name" class="flex gap-6 flex-col sm:flex-row">
            <img class="w-24 h-24 object-cover rounded-lg border border-outline-variant bg-white p-1" :src="item.image" :alt="item.name" />
            <div class="flex-1">
              <h3 class="font-title-lg text-on-surface text-lg leading-tight">{{ item.name }}</h3>
              <p v-if="item.returnWindow" class="text-body-md text-on-surface-variant mt-1 text-xs">Return window closes on {{ item.returnWindow }}</p>
              <p v-if="item.note" class="text-body-md text-on-surface-variant mt-1 text-xs">{{ item.note }}</p>
              <div class="mt-4 flex gap-3 flex-wrap">
                <button @click="buyItAgain(item)" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-title-md hover:bg-primary-container transition-all text-xs">Buy It Again</button>
                <button v-if="order.statusCode === 'delivered'" @click="openReview(item)" class="border border-outline text-on-surface px-5 py-2 rounded-lg font-title-md hover:bg-surface-container-high transition-all text-xs">Write a Review</button>
                <button v-if="order.statusCode !== 'cancelled'" @click="openTracking(order)" class="border border-outline text-on-surface px-5 py-2 rounded-lg font-title-md hover:bg-surface-container-high transition-all text-xs flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">track_changes</span> Track Shipment
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center shadow-sm">
      <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-4">shopping_bag</span>
      <h3 class="font-title-lg text-on-surface text-lg">No orders found</h3>
      <p class="text-xs text-on-surface-variant max-w-sm mx-auto mt-2">Try a different filter or search term.</p>
      <a href="/catalog" class="inline-block bg-primary text-on-primary font-bold text-xs py-2.5 px-6 rounded-lg hover:bg-primary-container transition-all mt-6">Browse Catalog</a>
    </div>
  </div>

  <!-- Details Modal -->
  <div v-if="isDetailsOpen && selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <div><h3 class="font-title-lg text-on-surface text-base">Order Details</h3><p class="text-[10px] text-on-surface-variant">Order #{{ selectedOrder.id }} • {{ selectedOrder.date }}</p></div>
        <button @click="isDetailsOpen = false"><span class="material-symbols-outlined text-on-surface-variant">close</span></button>
      </div>
      <div class="p-6 overflow-y-auto space-y-6 text-xs divide-y divide-outline-variant/60">
        <div class="space-y-3">
          <h4 class="font-bold text-on-surface-variant uppercase text-[10px] tracking-wider">Items Purchased</h4>
          <div v-for="item in selectedOrder.items" :key="item.name" class="flex gap-4 items-center">
            <img :src="item.image" :alt="item.name" class="w-12 h-12 object-cover rounded bg-white border p-1" />
            <div class="flex-1"><p class="font-semibold line-clamp-1">{{ item.name }}</p><p class="text-[10px] text-on-surface-variant">1x • ${{ selectedOrder.total.toFixed(2) }}</p></div>
            <button @click="buyItAgain(item)" class="text-primary font-bold hover:underline">Reorder</button>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
          <div><h4 class="font-bold text-on-surface-variant uppercase text-[10px] tracking-wider mb-2">Shipping Address</h4>
            <p class="font-semibold">{{ selectedOrder.shippingAddress.recipient }}</p>
            <p class="text-on-surface-variant">{{ selectedOrder.shippingAddress.street }}</p>
            <p class="text-on-surface-variant">{{ selectedOrder.shippingAddress.city }}, {{ selectedOrder.shippingAddress.state }} {{ selectedOrder.shippingAddress.zip }}</p>
          </div>
          <div><h4 class="font-bold text-on-surface-variant uppercase text-[10px] tracking-wider mb-2">Payment</h4>
            <p class="flex items-center gap-1.5 font-semibold"><span class="material-symbols-outlined text-[18px]">credit_card</span>{{ selectedOrder.paymentMethod.type }} {{ selectedOrder.paymentMethod.number }}</p>
          </div>
        </div>
        <div class="pt-4 space-y-2">
          <h4 class="font-bold text-on-surface-variant uppercase text-[10px] tracking-wider">Summary</h4>
          <div class="flex justify-between text-on-surface-variant"><span>Subtotal</span><span>${{ (selectedOrder.total - 15).toFixed(2) }}</span></div>
          <div class="flex justify-between text-on-surface-variant"><span>Shipping</span><span>$15.00</span></div>
          <div class="flex justify-between font-bold text-sm pt-2 border-t border-outline-variant/30"><span>Total</span><span class="text-primary">${{ selectedOrder.total.toFixed(2) }}</span></div>
        </div>
      </div>
      <div class="bg-surface-container-low border-t border-outline-variant px-6 py-4 text-right">
        <button @click="isDetailsOpen = false" class="bg-primary text-on-primary px-5 py-1.5 rounded-lg font-bold text-xs hover:bg-primary-container">Close</button>
      </div>
    </div>
  </div>

  <!-- Tracking Modal -->
  <div v-if="isTrackingOpen && trackingOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <div><h3 class="font-title-lg text-on-surface text-base font-bold">Track Shipment</h3><p class="text-[10px] text-on-surface-variant">Order #{{ trackingOrder.id }}</p></div>
        <button @click="isTrackingOpen = false"><span class="material-symbols-outlined text-on-surface-variant">close</span></button>
      </div>
      <div class="p-6 space-y-4">
        <div v-for="(step, idx) in trackingOrder.trackingSteps" :key="idx" class="flex gap-4 relative">
          <div v-if="idx < trackingOrder.trackingSteps.length - 1" class="absolute left-3 top-6 bottom-0 w-0.5 -translate-x-1/2"
               :class="step.done && trackingOrder.trackingSteps[idx+1].done ? 'bg-primary' : 'bg-outline-variant'"></div>
          <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 z-10"
               :class="step.done ? 'bg-primary text-on-primary' : 'bg-surface-container-high border border-outline-variant text-on-surface-variant'">
            <span class="material-symbols-outlined text-[14px]">{{ step.done ? 'done' : 'hourglass_empty' }}</span>
          </div>
          <div class="text-xs"><p class="font-bold" :class="step.done ? 'text-on-surface' : 'text-on-surface-variant'">{{ step.name }}</p><p class="text-[10px] text-on-surface-variant">{{ step.date }}</p></div>
        </div>
      </div>
      <div class="bg-surface-container-low border-t border-outline-variant px-6 py-4 text-right">
        <button @click="isTrackingOpen = false" class="bg-primary text-on-primary px-5 py-1.5 rounded-lg font-bold text-xs hover:bg-primary-container">Done</button>
      </div>
    </div>
  </div>

  <!-- Review Modal -->
  <div v-if="isReviewOpen && reviewProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <h3 class="font-title-lg text-on-surface text-base font-bold">Write a Review</h3>
        <button @click="isReviewOpen = false"><span class="material-symbols-outlined text-on-surface-variant">close</span></button>
      </div>
      <form @submit.prevent="submitReview" class="p-6 space-y-4 text-xs">
        <div class="flex items-center gap-3">
          <img :src="reviewProduct.image" :alt="reviewProduct.name" class="w-12 h-12 object-cover rounded border bg-white p-1" />
          <p class="font-semibold text-on-surface">{{ reviewProduct.name }}</p>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Rating</label>
          <div class="flex gap-1 text-star-rating cursor-pointer">
            <span v-for="star in 5" :key="star" @click="reviewRating = star" class="material-symbols-outlined text-[24px]"
                  :style="star <= reviewRating ? 'font-variation-settings: \'FILL\' 1;' : ''">star</span>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Comments</label>
          <textarea v-model="reviewComment" rows="4" placeholder="What did you think?" required
                    class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
        </div>
        <div class="bg-surface-container-low border-t border-outline-variant -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isReviewOpen = false" class="border border-outline text-on-surface px-4 py-2 rounded-lg font-bold hover:bg-surface-container-high transition-all">Cancel</button>
          <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-bold hover:bg-primary-container transition-all">Submit</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
