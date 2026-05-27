<script setup>
import { ref, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { store } from '@/store.js';
import { useAuthStore } from '@/stores/auth';
import AccountSidebar from '@/components/account/AccountSidebar.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Reactivity & Active Tab
const activeTab = computed(() => route.query.tab || 'dashboard');

const navTabs = [
  { name: 'Dashboard', icon: 'dashboard', routeName: 'account', query: { tab: 'dashboard' } },
  { name: 'Orders', icon: 'shopping_bag', routeName: 'account', query: { tab: 'orders' } },
  { name: 'Favorites', icon: 'favorite', routeName: 'account', query: { tab: 'favorites' } },
  { name: 'Compare', icon: 'compare_arrows', routeName: 'account', query: { tab: 'compare' } },
  { name: 'Settings', icon: 'settings', routeName: 'account', query: { tab: 'settings' } },
  { name: 'Support', icon: 'help', routeName: 'account', query: { tab: 'support' } },
];

const userName = computed(() => authStore.user?.name || 'John');
const userEmail = computed(() => authStore.user?.email || 'john.doe@example.com');

// 1. Dashboard Tab Data
const summaryStats = [
  {
    icon: 'local_shipping',
    label: 'Active Shipments',
    value: '01',
    color: 'text-primary',
    progress: '75%',
    progressColor: 'bg-primary-container',
    tab: 'orders'
  },
  {
    icon: 'account_balance_wallet',
    label: 'Member Savings',
    value: '$428.50',
    color: 'text-secondary',
    trend: '+12% this year',
    trendIcon: 'trending_up',
    tab: 'dashboard'
  },
  {
    icon: 'verified',
    label: 'Reward Points',
    value: '15,400',
    color: 'text-tertiary',
    action: 'Redeem Now'
  }
];

const handleRedeem = () => {
  store.addToast('Successfully redeemed 1,000 points for a $10 discount code!', 'success');
};

// 2. Orders Tab State & Data
const defaultOrders = [
  {
    id: '120934812',
    date: 'Oct 24, 2024',
    total: 1299.00,
    shipTo: 'John Doe',
    status: 'In Transit - Arriving Tomorrow',
    statusIcon: 'local_shipping',
    statusClass: 'text-primary bg-primary/10 border border-primary/20',
    statusCode: 'shipped',
    trackingSteps: [
      { name: 'Order Placed', date: 'Oct 24, 2024 10:00 AM', done: true },
      { name: 'Processing', date: 'Oct 24, 2024 02:00 PM', done: true },
      { name: 'In Transit', date: 'Oct 25, 2024 09:00 AM', done: true },
      { name: 'Out for Delivery', date: 'Expected Oct 28, 2024', done: false },
      { name: 'Delivered', date: 'Expected Oct 29, 2024', done: false }
    ],
    shippingAddress: {
      recipient: 'John Doe',
      street: '123 Emerald Ave, Suite 400',
      city: 'Seattle',
      state: 'WA',
      zip: '98101',
      country: 'United States'
    },
    paymentMethod: {
      type: 'Visa',
      number: '•••• 4242'
    },
    items: [
      {
        id: 101,
        name: 'ElectroLux ProBook 16" - M3 Max / 32GB RAM',
        price: 1299.00,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuVg-kXP_Dz4eDubPsXwLRjC9ddkd7vuALwu9d44EXhvUcmau1ettEqBuExcpfD_u05Iro8mRrCfjRLlEyElDWNK2XCXugMhg8BlFQmzkH5QXS1DmI_-nGJmj7Qj2nzbNxTaMQKQp0bQWxjEJSBQKmRMm8yVY7heCmjBY2zXzrTzybDqI72Tff2a3iARsbv4capMzqEVs456EoLHm-kOY-mlW9RKHwerz8Fm73OO4YjBf74fI_5VFz-bK8GP4E1iscPSLxhUpP1ho',
        returnWindow: 'Nov 24, 2024'
      }
    ]
  },
  {
    id: '992184021',
    date: 'Sept 12, 2024',
    total: 349.99,
    shipTo: 'John Doe',
    status: 'Delivered Sept 15, 2024',
    statusIcon: 'check_circle',
    statusClass: 'text-on-surface-variant bg-surface-container-high border border-outline-variant',
    statusCode: 'delivered',
    trackingSteps: [
      { name: 'Order Placed', date: 'Sept 12, 2024 08:30 AM', done: true },
      { name: 'Processing', date: 'Sept 12, 2024 11:00 AM', done: true },
      { name: 'In Transit', date: 'Sept 13, 2024 04:00 PM', done: true },
      { name: 'Out for Delivery', date: 'Sept 15, 2024 08:00 AM', done: true },
      { name: 'Delivered', date: 'Sept 15, 2024 02:30 PM', done: true }
    ],
    shippingAddress: {
      recipient: 'John Doe',
      street: '123 Emerald Ave, Suite 400',
      city: 'Seattle',
      state: 'WA',
      zip: '98101',
      country: 'United States'
    },
    paymentMethod: {
      type: 'Mastercard',
      number: '•••• 9876'
    },
    items: [
      {
        id: 105,
        name: 'ElectroLux SonicPro ANC Headphones',
        price: 349.99,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA',
        note: 'Order was delivered to the front porch.'
      }
    ]
  },
  {
    id: '483920194',
    date: 'May 02, 2026',
    total: 1999.00,
    shipTo: 'John Doe',
    status: 'Cancelled May 02, 2026',
    statusIcon: 'cancel',
    statusClass: 'text-error bg-error-container border border-error/20',
    statusCode: 'cancelled',
    trackingSteps: [
      { name: 'Order Placed', date: 'May 02, 2026 09:00 AM', done: true },
      { name: 'Cancelled', date: 'May 02, 2026 09:30 AM', done: true }
    ],
    shippingAddress: {
      recipient: 'John Doe',
      street: '123 Emerald Ave, Suite 400',
      city: 'Seattle',
      state: 'WA',
      zip: '98101',
      country: 'United States'
    },
    paymentMethod: {
      type: 'Visa',
      number: '•••• 4242'
    },
    items: [
      {
        id: 101,
        name: 'MacBook Pro 14" - M3 Pro Chip, 18GB RAM',
        price: 1999.00,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM'
      }
    ]
  }
];

const ordersList = ref([...defaultOrders]);
const ordersFilter = ref('all');
const ordersSearchQuery = ref('');

// Modals State
const selectedOrder = ref(null);
const isDetailsModalOpen = ref(false);

const trackingOrder = ref(null);
const isTrackingModalOpen = ref(false);

const reviewProduct = ref(null);
const reviewRating = ref(5);
const reviewComment = ref('');
const isReviewModalOpen = ref(false);

// Filter and Search computed list
const filteredOrders = computed(() => {
  return ordersList.value.filter(order => {
    // 1. Filter status
    if (ordersFilter.value !== 'all') {
      if (ordersFilter.value === 'shipped' && order.statusCode !== 'shipped') return false;
      if (ordersFilter.value === 'delivered' && order.statusCode !== 'delivered') return false;
      if (ordersFilter.value === 'cancelled' && order.statusCode !== 'cancelled') return false;
    }

    // 2. Search query
    if (ordersSearchQuery.value.trim()) {
      const q = ordersSearchQuery.value.toLowerCase();
      const matchesId = order.id.toLowerCase().includes(q);
      const matchesItem = order.items.some(item => item.name.toLowerCase().includes(q));
      return matchesId || matchesItem;
    }

    return true;
  });
});

const openDetailsModal = (order) => {
  selectedOrder.value = order;
  isDetailsModalOpen.value = true;
};

const openTrackingModal = (order) => {
  trackingOrder.value = order;
  isTrackingModalOpen.value = true;
};

const openReviewModal = (item) => {
  reviewProduct.value = item;
  reviewRating.value = 5;
  reviewComment.value = '';
  isReviewModalOpen.value = true;
};

const submitReview = () => {
  store.addToast(`Thank you! Your ${reviewRating.value}-star review for "${reviewProduct.value.name}" has been published.`, 'success');
  isReviewModalOpen.value = false;
};

const buyItAgain = (item) => {
  store.addToCart(item);
};

// 3. Settings Tab State & Actions
const profileForm = reactive({
  name: userName.value,
  email: userEmail.value,
  phone: '+1 (555) 019-2834',
  language: 'English'
});

const saveProfile = () => {
  if (authStore.user) {
    authStore.user.name = profileForm.name;
  }
  store.addToast('Profile updated successfully!', 'success');
};

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
});

const updatePassword = () => {
  if (!passwordForm.current || !passwordForm.new || !passwordForm.confirm) {
    store.addToast('Please fill out all password fields.', 'warning');
    return;
  }
  if (passwordForm.new !== passwordForm.confirm) {
    store.addToast('New passwords do not match.', 'error');
    return;
  }
  store.addToast('Password changed successfully!', 'success');
  passwordForm.current = '';
  passwordForm.new = '';
  passwordForm.confirm = '';
};

// Addresses Sub-state
const addressesList = ref([
  {
    id: 1,
    type: 'Home',
    recipient: 'John Doe',
    street: '123 Emerald Ave, Suite 400',
    city: 'Seattle',
    state: 'WA',
    zip: '98101',
    country: 'United States',
    phone: '+1 (555) 019-2834',
    isDefault: true
  },
  {
    id: 2,
    type: 'Office',
    recipient: 'John Doe',
    street: '500 Tech Plaza, Floor 12',
    city: 'San Francisco',
    state: 'CA',
    zip: '94105',
    country: 'United States',
    phone: '+1 (555) 019-7788',
    isDefault: false
  }
]);

const isAddressModalOpen = ref(false);
const addressForm = reactive({
  id: null,
  type: 'Home',
  recipient: '',
  street: '',
  city: '',
  state: '',
  zip: '',
  country: 'United States',
  phone: ''
});

const openAddressModal = (address = null) => {
  if (address) {
    Object.assign(addressForm, address);
  } else {
    Object.assign(addressForm, {
      id: null,
      type: 'Home',
      recipient: '',
      street: '',
      city: '',
      state: '',
      zip: '',
      country: 'United States',
      phone: ''
    });
  }
  isAddressModalOpen.value = true;
};

const saveAddress = () => {
  if (!addressForm.recipient || !addressForm.street || !addressForm.city || !addressForm.zip) {
    store.addToast('Please fill in required fields.', 'warning');
    return;
  }

  if (addressForm.id) {
    // Edit existing
    const idx = addressesList.value.findIndex(a => a.id === addressForm.id);
    if (idx !== -1) {
      addressesList.value[idx] = { ...addressForm };
    }
    store.addToast('Address updated successfully!', 'success');
  } else {
    // Add new
    const newId = Math.max(...addressesList.value.map(a => a.id), 0) + 1;
    addressesList.value.push({
      ...addressForm,
      id: newId,
      isDefault: addressesList.value.length === 0
    });
    store.addToast('New address saved!', 'success');
  }
  isAddressModalOpen.value = false;
};

const deleteAddress = (id) => {
  const idx = addressesList.value.findIndex(a => a.id === id);
  if (idx !== -1) {
    const wasDefault = addressesList.value[idx].isDefault;
    addressesList.value.splice(idx, 1);
    if (wasDefault && addressesList.value.length > 0) {
      addressesList.value[0].isDefault = true;
    }
    store.addToast('Address deleted.', 'info');
  }
};

const setAddressDefault = (id) => {
  addressesList.value.forEach(a => a.isDefault = a.id === id);
  store.addToast('Default address updated.', 'success');
};

// Payment Cards Sub-state
const cardsList = ref([
  {
    id: 1,
    type: 'Visa',
    number: '•••• •••• •••• 4242',
    holder: 'JOHN DOE',
    expiry: '12/28',
    isDefault: true
  },
  {
    id: 2,
    type: 'Mastercard',
    number: '•••• •••• •••• 9876',
    holder: 'JOHN DOE',
    expiry: '06/27',
    isDefault: false
  }
]);

const isCardModalOpen = ref(false);
const cardForm = reactive({
  number: '',
  holder: '',
  expiry: '',
  cvv: '',
  type: 'Visa'
});

const openCardModal = () => {
  Object.assign(cardForm, {
    number: '',
    holder: '',
    expiry: '',
    cvv: '',
    type: 'Visa'
  });
  isCardModalOpen.value = true;
};

const saveCard = () => {
  if (!cardForm.number || !cardForm.holder || !cardForm.expiry || !cardForm.cvv) {
    store.addToast('Please fill in all card details.', 'warning');
    return;
  }

  // Format card number to last 4 digits mock representation
  const digits = cardForm.number.replace(/\D/g, '');
  const lastFour = digits.substring(digits.length - 4) || '1111';
  const newId = Math.max(...cardsList.value.map(c => c.id), 0) + 1;

  cardsList.value.push({
    id: newId,
    type: cardForm.type,
    number: `•••• •••• •••• ${lastFour}`,
    holder: cardForm.holder.toUpperCase(),
    expiry: cardForm.expiry,
    isDefault: cardsList.value.length === 0
  });

  store.addToast('Card added successfully!', 'success');
  isCardModalOpen.value = false;
};

const deleteCard = (id) => {
  const idx = cardsList.value.findIndex(c => c.id === id);
  if (idx !== -1) {
    const wasDefault = cardsList.value[idx].isDefault;
    cardsList.value.splice(idx, 1);
    if (wasDefault && cardsList.value.length > 0) {
      cardsList.value[0].isDefault = true;
    }
    store.addToast('Card removed.', 'info');
  }
};

const setCardDefault = (id) => {
  cardsList.value.forEach(c => c.isDefault = c.id === id);
  store.addToast('Default payment method updated.', 'success');
};

// 4. Support Tab State & Actions
const activeFaqIndex = ref(null);
const faqs = [
  {
    q: 'How long does shipping take?',
    a: 'Standard shipping takes 3-5 business days. Express shipping takes 1-2 business days. Premium members enjoy free express shipping on all orders.'
  },
  {
    q: 'What is your return policy?',
    a: 'We offer a 30-day return policy on all hardware and electronics. Items must be in their original packaging, unused, and include all manuals/accessories.'
  },
  {
    q: 'How do I claim my product warranty?',
    a: 'All items come with a standard 2-year warranty. You can register claims by submitting a support ticket here, or by visiting any authorized ElectroLux center with your order invoice.'
  },
  {
    q: 'Can I change my delivery address after placing an order?',
    a: 'Addresses can be updated within 1 hour of placing your order by contacting support or from the dashboard if the order status is "Processing". Once "Shipped", we cannot redirect it.'
  }
];

const toggleFaq = (index) => {
  activeFaqIndex.value = activeFaqIndex.value === index ? null : index;
};

const ticketForm = reactive({
  subject: '',
  category: 'Order Issue',
  message: ''
});

const ticketsList = ref([
  {
    id: 'TK-8492',
    subject: 'Delay in delivery',
    category: 'Order Issue',
    date: 'May 24, 2026',
    status: 'In Progress',
    statusClass: 'text-secondary bg-secondary-container/30 border border-secondary/20'
  },
  {
    id: 'TK-3921',
    subject: 'Broken headphone cable',
    category: 'Warranty & Returns',
    date: 'Jan 12, 2026',
    status: 'Closed',
    statusClass: 'text-on-surface-variant bg-surface-container-high border border-outline-variant'
  }
]);

const submitTicket = () => {
  if (!ticketForm.subject || !ticketForm.message) {
    store.addToast('Please fill out the ticket subject and description.', 'warning');
    return;
  }

  const newTicketId = `TK-${Math.floor(1000 + Math.random() * 9000)}`;
  ticketsList.value.unshift({
    id: newTicketId,
    subject: ticketForm.subject,
    category: ticketForm.category,
    date: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
    status: 'Open',
    statusClass: 'text-primary bg-primary/10 border border-primary/20'
  });

  store.addToast(`Support ticket ${newTicketId} created successfully! Our team will respond shortly.`, 'success');
  ticketForm.subject = '';
  ticketForm.message = '';
};

// Switch Tab helper
const selectTab = (tabName) => {
  router.push({ name: 'account', query: { tab: tabName } });
};

</script>

<template>
  <div class="max-w-container-max mx-auto flex min-h-screen bg-surface relative">
    <!-- Desktop Sidebar -->
    <AccountSidebar />

    <!-- Content Workspace -->
    <div class="flex-1 px-margin-mobile md:px-margin-desktop py-stack-lg min-w-0">
      <!-- Header -->
      <header class="mb-6 border-b border-outline-variant pb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface capitalize">
              {{ activeTab === 'dashboard' ? 'Account Dashboard' : activeTab }}
            </h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">
              Welcome back, {{ userName }}! Manage your store account settings and order status.
            </p>
          </div>
          <!-- Quick stats / Premium tag visible on header for mobile -->
          <div class="lg:hidden self-start flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-container/20 border border-primary/20">
            <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
            <span class="text-[11px] font-bold text-primary uppercase tracking-widest">Premium Member</span>
          </div>
        </div>
      </header>

      <!-- Mobile Navigation Scroll Bar -->
      <div class="lg:hidden mb-6 -mx-margin-mobile px-margin-mobile overflow-x-auto scrollbar-none flex gap-2 border-b border-outline-variant pb-4">
        <router-link
          v-for="item in navTabs"
          :key="item.name"
          :to="item.query ? { name: item.routeName, query: item.query } : { name: item.routeName }"
          class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all shadow-sm border border-outline-variant"
          :class="activeTab === item.query?.tab ? 'bg-primary text-on-primary border-primary' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low'"
        >
          <span class="material-symbols-outlined text-[16px]">{{ item.icon }}</span>
          {{ item.name }}
        </router-link>
      </div>

      <!-- Tab Content Area -->
      <main class="space-y-stack-lg">
        
        <!-- DASHBOARD TAB -->
        <div v-if="activeTab === 'dashboard'" class="space-y-stack-lg animate-fade">
          <!-- Summary Grid -->
          <section class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div v-for="stat in summaryStats" :key="stat.label"
                 class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col gap-2 hover:shadow-md transition-shadow relative overflow-hidden group">
              <span class="material-symbols-outlined text-[32px] transition-transform group-hover:scale-110 duration-200" :class="stat.color">{{ stat.icon }}</span>
              <p class="font-label-md text-label-md text-on-surface-variant uppercase">{{ stat.label }}</p>
              <p class="font-display-lg text-headline-lg text-on-surface">{{ stat.value }}</p>

              <div v-if="stat.progress" class="h-1 w-full bg-surface-container rounded-full overflow-hidden mt-2">
                <div class="h-full" :class="stat.progressColor" :style="{ width: stat.progress }"></div>
              </div>

              <p v-if="stat.trend" class="text-[12px] text-primary font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">{{ stat.trendIcon }}</span> {{ stat.trend }}
              </p>

              <button v-if="stat.action" @click="handleRedeem" class="text-primary text-label-md font-bold uppercase tracking-wider flex items-center gap-1 mt-2 text-left hover:underline">
                {{ stat.action }} <span class="material-symbols-outlined text-[16px]">chevron_right</span>
              </button>
              
              <button v-else-if="stat.tab" @click="selectTab(stat.tab)" class="absolute top-4 right-4 text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]">open_in_new</span>
              </button>
            </div>
          </section>

          <!-- Overview & Quick Actions -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
            <!-- Left Side: Recent Orders Summary -->
            <section class="lg:col-span-2 space-y-gutter">
              <div class="flex items-center justify-between">
                <h2 class="font-title-lg text-on-surface text-lg">Recent Orders</h2>
                <button @click="selectTab('orders')" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
                  View All Orders <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </button>
              </div>

              <div class="space-y-gutter">
                <div v-for="order in ordersList.slice(0, 2)" :key="order.id"
                     class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                  <div class="bg-surface-container-low px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
                    <div class="flex gap-6 flex-wrap">
                      <div>
                        <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order Placed</p>
                        <p class="font-title-md text-on-surface text-sm">{{ order.date }}</p>
                      </div>
                      <div>
                        <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Total</p>
                        <p class="font-title-md text-on-surface text-sm">${{ order.total.toFixed(2) }}</p>
                      </div>
                      <div>
                        <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Status</p>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="order.statusClass">
                          <span class="material-symbols-outlined text-[12px]">{{ order.statusIcon }}</span>
                          {{ order.status }}
                        </span>
                      </div>
                    </div>
                    <button @click="openDetailsModal(order)" class="text-primary font-bold text-label-md hover:underline text-[11px]">
                      View Details
                    </button>
                  </div>
                  
                  <div class="p-6">
                    <div v-for="item in order.items" :key="item.name" class="flex gap-4 flex-col sm:flex-row items-center sm:items-start">
                      <img class="w-16 h-16 object-cover rounded-lg border border-outline-variant bg-white p-1" :src="item.image" :alt="item.name" />
                      <div class="flex-1 text-center sm:text-left">
                        <h3 class="font-title-md text-on-surface text-sm line-clamp-1">{{ item.name }}</h3>
                        <p v-if="item.returnWindow" class="text-[12px] text-on-surface-variant mt-0.5">Return closes on {{ item.returnWindow }}</p>
                      </div>
                      <div class="flex gap-2">
                        <button v-if="order.statusCode !== 'cancelled'" @click="openTrackingModal(order)" class="border border-outline text-on-surface px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-surface-container-high transition-all">Track</button>
                        <button @click="buyItAgain(item)" class="bg-primary text-on-primary px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-primary-container transition-all">Reorder</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Right Side: Profile Summary & Shortcuts -->
            <section class="space-y-gutter">
              <h2 class="font-title-lg text-on-surface text-lg">My Information</h2>
              <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-6">
                <!-- User Profile card -->
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-lg border border-primary">
                    {{ userName.charAt(0) }}
                  </div>
                  <div>
                    <h3 class="font-title-md text-on-surface text-base">{{ userName }}</h3>
                    <p class="text-xs text-on-surface-variant line-clamp-1">{{ userEmail }}</p>
                  </div>
                </div>

                <!-- Addresses Summary -->
                <div class="border-t border-outline-variant pt-4 space-y-2">
                  <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Default Address</p>
                  <div v-if="addressesList.find(a => a.isDefault)" class="text-xs text-on-surface space-y-1">
                    <p class="font-semibold">{{ addressesList.find(a => a.isDefault).recipient }}</p>
                    <p class="text-on-surface-variant">{{ addressesList.find(a => a.isDefault).street }}</p>
                    <p class="text-on-surface-variant">{{ addressesList.find(a => a.isDefault).city }}, {{ addressesList.find(a => a.isDefault).zip }}</p>
                  </div>
                  <p v-else class="text-xs text-on-surface-variant italic">No address saved.</p>
                </div>

                <!-- Quick links -->
                <div class="border-t border-outline-variant pt-4 space-y-2">
                  <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Quick Actions</p>
                  <div class="grid grid-cols-2 gap-2">
                    <button @click="selectTab('settings')" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                      <span class="material-symbols-outlined text-[16px]">edit</span> Edit Profile
                    </button>
                    <button @click="selectTab('favorites')" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                      <span class="material-symbols-outlined text-[16px]">favorite</span> Wishlist
                    </button>
                    <button @click="selectTab('support')" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                      <span class="material-symbols-outlined text-[16px]">help</span> Help Center
                    </button>
                    <a href="/catalog" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                      <span class="material-symbols-outlined text-[16px]">grid_view</span> Catalog
                    </a>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>

        <!-- ORDERS TAB -->
        <div v-else-if="activeTab === 'orders'" class="space-y-stack-md animate-fade">
          <!-- Filters & Search -->
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface-container-lowest p-4 rounded-xl border border-outline-variant shadow-sm">
            <div class="flex items-center bg-surface-container-low p-1 rounded-xl w-fit">
              <button @click="ordersFilter = 'all'"
                      :class="ordersFilter === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                      class="py-1.5 px-4 rounded-lg font-label-md text-xs transition-all">All</button>
              <button @click="ordersFilter = 'shipped'"
                      :class="ordersFilter === 'shipped' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                      class="py-1.5 px-4 rounded-lg font-label-md text-xs transition-all">Shipped</button>
              <button @click="ordersFilter = 'delivered'"
                      :class="ordersFilter === 'delivered' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                      class="py-1.5 px-4 rounded-lg font-label-md text-xs transition-all">Delivered</button>
              <button @click="ordersFilter = 'cancelled'"
                      :class="ordersFilter === 'cancelled' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                      class="py-1.5 px-4 rounded-lg font-label-md text-xs transition-all">Cancelled</button>
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

          <!-- Orders Cards -->
          <div v-if="filteredOrders.length > 0" class="space-y-gutter">
            <div v-for="order in filteredOrders" :key="order.id"
                 class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
              
              <!-- Header info card -->
              <div class="bg-surface-container-low px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
                <div class="flex gap-8 flex-wrap">
                  <div>
                    <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order Placed</p>
                    <p class="font-title-md text-on-surface text-sm">{{ order.date }}</p>
                  </div>
                  <div>
                    <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Total</p>
                    <p class="font-title-md text-on-surface text-sm">${{ order.total.toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Ship To</p>
                    <p class="font-title-md text-on-surface text-sm">{{ order.shipTo }}</p>
                  </div>
                  <div>
                    <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Status</p>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="order.statusClass">
                      <span class="material-symbols-outlined text-[12px]">{{ order.statusIcon }}</span>
                      {{ order.status }}
                    </span>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order #{{ order.id }}</p>
                  <button @click="openDetailsModal(order)" class="text-primary font-bold text-label-md hover:underline text-[11px]">View Order Details</button>
                </div>
              </div>

              <!-- Content card items -->
              <div class="p-6 space-y-6">
                <div v-for="item in order.items" :key="item.name" class="flex gap-6 flex-col sm:flex-row">
                  <img class="w-24 h-24 object-cover rounded-lg border border-outline-variant bg-white p-1" :src="item.image" :alt="item.name" />
                  <div class="flex-1">
                    <h3 class="font-title-lg text-on-surface text-lg leading-tight">{{ item.name }}</h3>
                    <p v-if="item.returnWindow" class="text-body-md text-on-surface-variant mt-1 text-xs">Return window closes on {{ item.returnWindow }}</p>
                    <p v-if="item.note" class="text-body-md text-on-surface-variant mt-1 text-xs">{{ item.note }}</p>
                    
                    <div class="mt-4 flex gap-3 flex-wrap">
                      <button @click="buyItAgain(item)" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-title-md hover:bg-primary-container transition-all text-xs">Buy It Again</button>
                      <button v-if="order.statusCode === 'delivered'" @click="openReviewModal(item)" class="border border-outline text-on-surface px-5 py-2 rounded-lg font-title-md hover:bg-surface-container-high transition-all text-xs">Write a Review</button>
                      <button v-if="order.statusCode !== 'cancelled'" @click="openTrackingModal(order)" class="border border-outline text-on-surface px-5 py-2 rounded-lg font-title-md hover:bg-surface-container-high transition-all text-xs flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">track_changes</span> Track Shipment
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Empty State -->
          <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center shadow-sm">
            <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-4">shopping_bag</span>
            <h3 class="font-title-lg text-on-surface text-lg">No orders found</h3>
            <p class="text-xs text-on-surface-variant max-w-sm mx-auto mt-2">
              We couldn't find any orders matching your search or filter. Try typing something else or browse the store catalog.
            </p>
            <a href="/catalog" class="inline-block bg-primary text-on-primary font-bold text-xs py-2.5 px-6 rounded-lg hover:bg-primary-container transition-all mt-6">
              Browse Catalog
            </a>
          </div>
        </div>

        <!-- FAVORITES TAB -->
        <div v-else-if="activeTab === 'favorites'" class="space-y-stack-md animate-fade">
          <div v-if="store.wishlist.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="product in store.wishlist" :key="product.id"
                 class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group flex flex-col justify-between">
              
              <!-- Card Head / Image -->
              <div class="p-4 bg-white relative flex justify-center items-center aspect-square border-b border-outline-variant">
                <img :src="product.image" :alt="product.name" class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300" />
                <button @click="store.toggleWishlist(product)" class="absolute top-3 right-3 p-1.5 bg-surface-container-low hover:bg-error-container hover:text-error text-on-surface-variant rounded-full transition-colors">
                  <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
              </div>

              <!-- Card Body -->
              <div class="p-4 flex-1 flex flex-col justify-between gap-4">
                <div>
                  <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider mb-1">{{ product.category }}</p>
                  <h3 class="font-title-md text-on-surface text-sm line-clamp-2 leading-snug group-hover:text-primary transition-colors">{{ product.name }}</h3>
                </div>
                <div class="flex items-center justify-between gap-2 mt-auto">
                  <span class="font-bold text-primary text-lg">${{ product.price.toFixed(2) }}</span>
                  <button @click="store.addToCart(product)" class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold text-xs hover:bg-primary-container transition-all uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">shopping_cart</span> Add
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- Empty State -->
          <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-error-container/10 border border-error-container text-error rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
            </div>
            <h3 class="font-title-lg text-on-surface text-lg">Your Wishlist is Empty</h3>
            <p class="text-xs text-on-surface-variant max-w-sm mx-auto mt-2">
              Keep track of items you love! Click the heart icon on any product in the store to save it here.
            </p>
            <a href="/catalog" class="inline-block bg-primary text-on-primary font-bold text-xs py-2.5 px-6 rounded-lg hover:bg-primary-container transition-all mt-6">
              Explore Products
            </a>
          </div>
        </div>

        <!-- COMPARE TAB -->
        <div v-else-if="activeTab === 'compare'" class="space-y-stack-md animate-fade">
          <div v-if="store.compare.length > 0" class="overflow-x-auto bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
            <table class="w-full min-w-[700px] border-collapse text-left text-xs">
              <thead>
                <tr class="bg-surface-container-low border-b border-outline-variant text-on-surface-variant font-bold uppercase text-[10px]">
                  <th class="p-4 w-1/4">Product details</th>
                  <th v-for="product in store.compare" :key="product.id" class="p-4 relative">
                    <button @click="store.removeFromCompare(product.id)" class="absolute top-2 right-2 text-on-surface-variant hover:text-error transition-colors">
                      <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                    <span class="inline-block py-2">Item</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant text-on-surface">
                <!-- Thumbnail & Name -->
                <tr>
                  <td class="p-4 font-semibold bg-surface-container-low/30">Preview</td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4">
                    <div class="flex flex-col gap-2">
                      <img :src="product.image" :alt="product.name" class="w-20 h-20 object-contain mx-auto bg-white rounded border p-1" />
                      <h4 class="font-bold text-center line-clamp-2 text-[11px] leading-snug">{{ product.name }}</h4>
                    </div>
                  </td>
                </tr>
                <!-- Price -->
                <tr>
                  <td class="p-4 font-semibold bg-surface-container-low/30">Price</td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4 font-bold text-primary text-sm text-center">
                    ${{ product.price.toFixed(2) }}
                  </td>
                </tr>
                <!-- Brand -->
                <tr>
                  <td class="p-4 font-semibold bg-surface-container-low/30">Brand</td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">
                    {{ product.brand }}
                  </td>
                </tr>
                <!-- RAM -->
                <tr>
                  <td class="p-4 font-semibold bg-surface-container-low/30">RAM</td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">
                    {{ product.ram || 'N/A' }}
                  </td>
                </tr>
                <!-- Rating -->
                <tr>
                  <td class="p-4 font-semibold bg-surface-container-low/30">Rating</td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">
                    <div class="flex items-center justify-center gap-1 text-star-rating">
                      <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
                      <span class="font-bold text-on-surface-variant text-[11px]">{{ product.rating }} ({{ product.reviews }})</span>
                    </div>
                  </td>
                </tr>
                <!-- Description -->
                <tr>
                  <td class="p-4 font-semibold bg-surface-container-low/30">Description</td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4 text-on-surface-variant text-[11px] leading-relaxed">
                    {{ product.description }}
                  </td>
                </tr>
                <!-- Actions -->
                <tr>
                  <td class="p-4 bg-surface-container-low/30"></td>
                  <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">
                    <button @click="store.addToCart(product)" class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold text-[10px] uppercase hover:bg-primary-container transition-all tracking-wider inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px]">shopping_cart</span> Add to Cart
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Empty State -->
          <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center shadow-sm">
            <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-4">compare_arrows</span>
            <h3 class="font-title-lg text-on-surface text-lg">No Items to Compare</h3>
            <p class="text-xs text-on-surface-variant max-w-sm mx-auto mt-2">
              Compare product specifications side by side. Add products using the "Compare" checkbox on the catalog page.
            </p>
            <a href="/catalog" class="inline-block bg-primary text-on-primary font-bold text-xs py-2.5 px-6 rounded-lg hover:bg-primary-container transition-all mt-6">
              View Catalog
            </a>
          </div>
        </div>

        <!-- SETTINGS TAB -->
        <div v-else-if="activeTab === 'settings'" class="space-y-gutter animate-fade">
          <!-- Sub-grid settings -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
            
            <!-- Left 2 columns: Profile & Password -->
            <div class="lg:col-span-2 space-y-gutter">
              <!-- Edit Profile -->
              <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Profile Information</h3>
                <form @submit.prevent="saveProfile" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Full Name</label>
                    <input v-model="profileForm.name" type="text" required
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                  </div>
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Email Address</label>
                    <input v-model="profileForm.email" type="email" required
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                  </div>
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Phone Number</label>
                    <input v-model="profileForm.phone" type="text"
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                  </div>
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Preferred Language</label>
                    <select v-model="profileForm.language"
                            class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                      <option>English</option>
                      <option>Ukrainian</option>
                    </select>
                  </div>
                  <div class="md:col-span-2 pt-2 text-right">
                    <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-bold text-xs hover:bg-primary-container transition-all uppercase tracking-wider">
                      Save Profile
                    </button>
                  </div>
                </form>
              </section>

              <!-- Change Password -->
              <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Change Password</h3>
                <form @submit.prevent="updatePassword" class="space-y-4">
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Current Password</label>
                    <input v-model="passwordForm.current" type="password" required
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">New Password</label>
                      <input v-model="passwordForm.new" type="password" required
                             class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Confirm New Password</label>
                      <input v-model="passwordForm.confirm" type="password" required
                             class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                    </div>
                  </div>
                  <div class="pt-2 text-right">
                    <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-bold text-xs hover:bg-primary-container transition-all uppercase tracking-wider">
                      Update Password
                    </button>
                  </div>
                </form>
              </section>
            </div>

            <!-- Right column: Address Book & Payment Cards -->
            <div class="space-y-gutter">
              <!-- Addresses -->
              <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-outline-variant pb-2">
                  <h3 class="font-title-lg text-on-surface text-base">Address Book</h3>
                  <button @click="openAddressModal()" class="text-primary text-[11px] font-bold hover:underline flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[16px]">add</span> Add
                  </button>
                </div>
                
                <div class="space-y-3">
                  <div v-for="address in addressesList" :key="address.id"
                       class="border border-outline-variant rounded-lg p-4 relative text-xs hover:bg-surface-container-low/55 transition-colors">
                    <div class="flex items-center justify-between mb-2">
                      <span class="font-bold text-on-surface">{{ address.type }}</span>
                      <span v-if="address.isDefault" class="bg-primary/10 text-primary text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded border border-primary/20">DEFAULT</span>
                    </div>
                    <p class="text-on-surface font-medium">{{ address.recipient }}</p>
                    <p class="text-on-surface-variant mt-1">{{ address.street }}</p>
                    <p class="text-on-surface-variant">{{ address.city }}, {{ address.state }} {{ address.zip }}</p>
                    <p class="text-on-surface-variant">{{ address.country }}</p>
                    
                    <div class="mt-4 flex gap-3 border-t border-outline-variant pt-2">
                      <button @click="openAddressModal(address)" class="text-on-surface-variant hover:text-primary transition-colors font-semibold">Edit</button>
                      <button @click="deleteAddress(address.id)" class="text-on-surface-variant hover:text-error transition-colors font-semibold">Delete</button>
                      <button v-if="!address.isDefault" @click="setAddressDefault(address.id)" class="text-primary hover:underline ml-auto font-bold text-[10px] uppercase">Set default</button>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Payment Methods -->
              <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-outline-variant pb-2">
                  <h3 class="font-title-lg text-on-surface text-base">Saved Cards</h3>
                  <button @click="openCardModal()" class="text-primary text-[11px] font-bold hover:underline flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[16px]">add</span> Add
                  </button>
                </div>

                <div class="space-y-4">
                  <!-- Stylized Credit Card visual -->
                  <div v-for="card in cardsList" :key="card.id"
                       class="rounded-xl p-5 border relative overflow-hidden text-white flex flex-col justify-between aspect-[1.58/1] shadow-sm select-none"
                       :class="card.isDefault ? 'bg-gradient-to-tr from-teal-950 via-primary to-emerald-800 border-primary/30' : 'bg-gradient-to-tr from-slate-900 via-slate-800 to-slate-700 border-slate-700'">
                    <!-- Top card layout -->
                    <div class="flex justify-between items-start">
                      <div>
                        <p class="text-[9px] font-black uppercase tracking-widest opacity-80">{{ card.type }} Card</p>
                        <p class="text-[10px] font-bold mt-1" v-if="card.isDefault">DEFAULT METHOD</p>
                      </div>
                      <span class="material-symbols-outlined text-2xl opacity-90">{{ card.type === 'Visa' ? 'credit_card' : 'payments' }}</span>
                    </div>

                    <!-- Middle layout -->
                    <p class="font-mono text-base tracking-widest my-2">{{ card.number }}</p>

                    <!-- Bottom layout -->
                    <div class="flex justify-between items-end">
                      <div class="text-left">
                        <p class="text-[8px] uppercase tracking-wider opacity-60">Card Holder</p>
                        <p class="font-bold text-[11px] tracking-wide">{{ card.holder }}</p>
                      </div>
                      <div class="text-right">
                        <p class="text-[8px] uppercase tracking-wider opacity-60">Expires</p>
                        <p class="font-bold text-[11px]">{{ card.expiry }}</p>
                      </div>
                    </div>

                    <!-- Hover action overlay inside card -->
                    <div class="absolute inset-0 bg-black/85 backdrop-blur-sm opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                      <button v-if="!card.isDefault" @click="setCardDefault(card.id)" class="bg-white text-black font-bold text-[10px] px-3 py-1.5 rounded uppercase tracking-wider hover:bg-slate-200 transition-colors">Default</button>
                      <button @click="deleteCard(card.id)" class="bg-error text-white font-bold text-[10px] px-3 py-1.5 rounded uppercase tracking-wider hover:bg-error/80 transition-colors">Delete</button>
                    </div>
                  </div>
                </div>
              </section>
            </div>

          </div>
        </div>

        <!-- SUPPORT TAB -->
        <div v-else-if="activeTab === 'support'" class="space-y-gutter animate-fade">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
            
            <!-- Left 2 cols: FAQ accordion & History -->
            <div class="lg:col-span-2 space-y-gutter">
              <!-- FAQs -->
              <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Frequently Asked Questions</h3>
                <div class="divide-y divide-outline-variant">
                  <div v-for="(faq, idx) in faqs" :key="idx" class="py-3">
                    <button @click="toggleFaq(idx)" class="w-full flex items-center justify-between text-left font-semibold text-xs text-on-surface hover:text-primary transition-colors py-1">
                      <span>{{ faq.q }}</span>
                      <span class="material-symbols-outlined text-[18px] transition-transform duration-300"
                            :class="activeFaqIndex === idx ? 'rotate-180 text-primary' : 'text-on-surface-variant'">
                        expand_more
                      </span>
                    </button>
                    <!-- Expandable content -->
                    <div v-show="activeFaqIndex === idx" class="mt-2 text-xs text-on-surface-variant leading-relaxed animate-slide-down">
                      {{ faq.a }}
                    </div>
                  </div>
                </div>
              </section>

              <!-- Ticket History -->
              <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Support Tickets</h3>
                
                <div v-if="ticketsList.length > 0" class="divide-y divide-outline-variant">
                  <div v-for="ticket in ticketsList" :key="ticket.id" class="py-4 first:pt-0 last:pb-0 flex flex-wrap justify-between items-center gap-4 text-xs">
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-on-surface text-sm">{{ ticket.subject }}</span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant bg-surface-container-high px-2 py-0.5 rounded">{{ ticket.category }}</span>
                      </div>
                      <p class="text-on-surface-variant text-[11px] mt-1">Ticket ID: {{ ticket.id }} • Created: {{ ticket.date }}</p>
                    </div>
                    <div>
                      <span class="inline-block px-3 py-1 rounded-full font-bold uppercase text-[9px] tracking-wider" :class="ticket.statusClass">
                        {{ ticket.status }}
                      </span>
                    </div>
                  </div>
                </div>
                <p v-else class="text-xs text-on-surface-variant italic text-center py-4">No tickets created.</p>
              </section>
            </div>

            <!-- Right col: Create Support Ticket form -->
            <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4 h-fit">
              <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Submit a Ticket</h3>
              <form @submit.prevent="submitTicket" class="space-y-4">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Category</label>
                  <select v-model="ticketForm.category"
                          class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                    <option>Order Issue</option>
                    <option>Warranty & Returns</option>
                    <option>Technical Support</option>
                    <option>Billing & Invoices</option>
                  </select>
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Subject</label>
                  <input v-model="ticketForm.subject" type="text" placeholder="Brief subject description..." required
                         class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Message Description</label>
                  <textarea v-model="ticketForm.message" rows="4" placeholder="How can we help you today?" required
                            class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none resize-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-primary text-on-primary font-bold text-xs py-2.5 rounded-lg hover:bg-primary-container transition-all uppercase tracking-wider flex items-center justify-center gap-1.5">
                  <span class="material-symbols-outlined text-[16px]">send</span> Send Ticket
                </button>
              </form>
            </section>

          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- DETAILS MODAL -->
  <div v-if="isDetailsModalOpen && selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
      <!-- Head -->
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <div>
          <h3 class="font-title-lg text-on-surface text-base leading-tight">Order Details</h3>
          <p class="text-[10px] text-on-surface-variant">Order ID: {{ selectedOrder.id }} • Placed on {{ selectedOrder.date }}</p>
        </div>
        <button @click="isDetailsModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <!-- Scrollable content -->
      <div class="p-6 overflow-y-auto space-y-6 text-xs text-on-surface divide-y divide-outline-variant/60">
        <!-- Products List -->
        <div class="space-y-4">
          <h4 class="font-bold text-on-surface-variant uppercase tracking-wider text-[10px]">Items Purchased</h4>
          <div v-for="item in selectedOrder.items" :key="item.name" class="flex gap-4 items-center">
            <img :src="item.image" :alt="item.name" class="w-12 h-12 object-cover rounded bg-white border p-1" />
            <div class="flex-1">
              <p class="font-semibold text-on-surface line-clamp-1">{{ item.name }}</p>
              <p class="text-[10px] text-on-surface-variant">1x • ${{ selectedOrder.total.toFixed(2) }}</p>
            </div>
            <button @click="buyItAgain(item)" class="text-primary font-bold hover:underline">Reorder</button>
          </div>
        </div>

        <!-- Address & Payment -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
          <div class="space-y-1">
            <h4 class="font-bold text-on-surface-variant uppercase tracking-wider text-[10px]">Shipping Address</h4>
            <p class="font-semibold">{{ selectedOrder.shippingAddress.recipient }}</p>
            <p class="text-on-surface-variant">{{ selectedOrder.shippingAddress.street }}</p>
            <p class="text-on-surface-variant">{{ selectedOrder.shippingAddress.city }}, {{ selectedOrder.shippingAddress.state }} {{ selectedOrder.shippingAddress.zip }}</p>
            <p class="text-on-surface-variant">{{ selectedOrder.shippingAddress.country }}</p>
          </div>
          <div class="space-y-1">
            <h4 class="font-bold text-on-surface-variant uppercase tracking-wider text-[10px]">Payment Method</h4>
            <p class="flex items-center gap-1.5 font-semibold">
              <span class="material-symbols-outlined text-[18px]">credit_card</span>
              {{ selectedOrder.paymentMethod.type }} {{ selectedOrder.paymentMethod.number }}
            </p>
            <p class="text-on-surface-variant mt-2">Billing Address same as shipping address</p>
          </div>
        </div>

        <!-- Cost Summary -->
        <div class="pt-4 space-y-2">
          <h4 class="font-bold text-on-surface-variant uppercase tracking-wider text-[10px]">Billing Summary</h4>
          <div class="flex justify-between text-on-surface-variant">
            <span>Subtotal</span>
            <span>${{ (selectedOrder.total - 15).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-on-surface-variant">
            <span>Shipping (Standard)</span>
            <span>$15.00</span>
          </div>
          <div class="flex justify-between font-bold text-sm text-on-surface pt-2 border-t border-outline-variant/30">
            <span>Total charged</span>
            <span class="text-primary">${{ selectedOrder.total.toFixed(2) }}</span>
          </div>
        </div>
      </div>
      <!-- Foot -->
      <div class="bg-surface-container-low border-t border-outline-variant px-6 py-4 text-right">
        <button @click="isDetailsModalOpen = false" class="bg-primary text-on-primary px-5 py-1.5 rounded-lg font-bold text-xs hover:bg-primary-container">
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- TRACKING MODAL -->
  <div v-if="isTrackingModalOpen && trackingOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-md w-full shadow-2xl overflow-hidden flex flex-col">
      <!-- Head -->
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <div>
          <h3 class="font-title-lg text-on-surface text-base leading-tight font-bold">Track Shipment</h3>
          <p class="text-[10px] text-on-surface-variant">Order #{{ trackingOrder.id }}</p>
        </div>
        <button @click="isTrackingModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <!-- Stepper -->
      <div class="p-6 space-y-6">
        <div class="space-y-4">
          <div v-for="(step, idx) in trackingOrder.trackingSteps" :key="idx" class="flex gap-4 relative">
            <!-- Line indicator -->
            <div v-if="idx < trackingOrder.trackingSteps.length - 1"
                 class="absolute left-3 top-6 bottom-0 w-0.5 -translate-x-1/2"
                 :class="step.done && trackingOrder.trackingSteps[idx + 1].done ? 'bg-primary' : 'bg-outline-variant'"></div>
            
            <!-- Point -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0 z-10"
                 :class="step.done ? 'bg-primary text-on-primary' : 'bg-surface-container-high border border-outline-variant text-on-surface-variant'">
              <span class="material-symbols-outlined text-[14px]">{{ step.done ? 'done' : 'hourglass_empty' }}</span>
            </div>
            <!-- Info -->
            <div class="text-left text-xs">
              <p class="font-bold" :class="step.done ? 'text-on-surface' : 'text-on-surface-variant'">{{ step.name }}</p>
              <p class="text-[10px] text-on-surface-variant mt-0.5">{{ step.date }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Foot -->
      <div class="bg-surface-container-low border-t border-outline-variant px-6 py-4 text-right">
        <button @click="isTrackingModalOpen = false" class="bg-primary text-on-primary px-5 py-1.5 rounded-lg font-bold text-xs hover:bg-primary-container">
          Done
        </button>
      </div>
    </div>
  </div>

  <!-- ADDRESS MODAL -->
  <div v-if="isAddressModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <h3 class="font-title-lg text-on-surface text-base font-bold">{{ addressForm.id ? 'Edit Address' : 'Add New Address' }}</h3>
        <button @click="isAddressModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form @submit.prevent="saveAddress" class="p-6 space-y-4 text-xs">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Address Type</label>
            <select v-model="addressForm.type" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none">
              <option>Home</option>
              <option>Office</option>
              <option>Billing</option>
              <option>Other</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Recipient Name *</label>
            <input v-model="addressForm.recipient" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Street Address *</label>
          <input v-model="addressForm.street" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
        </div>
        <div class="grid grid-cols-3 gap-2">
          <div class="space-y-1 col-span-2">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">City *</label>
            <input v-model="addressForm.city" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">State</label>
            <input v-model="addressForm.state" type="text" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">ZIP / Postal Code *</label>
            <input v-model="addressForm.zip" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Country *</label>
            <input v-model="addressForm.country" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Phone number</label>
          <input v-model="addressForm.phone" type="text" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
        </div>
        <div class="bg-surface-container-low border-t border-outline-variant -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isAddressModalOpen = false" class="border border-outline text-on-surface px-4 py-2 rounded-lg font-bold hover:bg-surface-container-high transition-all">Cancel</button>
          <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-bold hover:bg-primary-container transition-all">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- SAVED CARD MODAL -->
  <div v-if="isCardModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-sm w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <h3 class="font-title-lg text-on-surface text-base font-bold">Add Payment Card</h3>
        <button @click="isCardModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form @submit.prevent="saveCard" class="p-6 space-y-4 text-xs">
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Card Provider</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input type="radio" v-model="cardForm.type" value="Visa" class="accent-primary" />
              <span>Visa</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input type="radio" v-model="cardForm.type" value="Mastercard" class="accent-primary" />
              <span>Mastercard</span>
            </label>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Cardholder Name *</label>
          <input v-model="cardForm.holder" type="text" placeholder="e.g. JOHN DOE" required
                 class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Card Number *</label>
          <input v-model="cardForm.number" type="text" placeholder="16-digit card number" required
                 class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Expiry Date *</label>
            <input v-model="cardForm.expiry" type="text" placeholder="MM/YY" required
                   class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">CVV *</label>
            <input v-model="cardForm.cvv" type="password" maxlength="3" placeholder="•••" required
                   class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" />
          </div>
        </div>
        <div class="bg-surface-container-low border-t border-outline-variant -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isCardModalOpen = false" class="border border-outline text-on-surface px-4 py-2 rounded-lg font-bold hover:bg-surface-container-high transition-all">Cancel</button>
          <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-bold hover:bg-primary-container transition-all">Add Card</button>
        </div>
      </form>
    </div>
  </div>

  <!-- REVIEW MODAL -->
  <div v-if="isReviewModalOpen && reviewProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <h3 class="font-title-lg text-on-surface text-base font-bold">Write a Review</h3>
        <button @click="isReviewModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form @submit.prevent="submitReview" class="p-6 space-y-4 text-xs">
        <div class="flex items-center gap-3">
          <img :src="reviewProduct.image" :alt="reviewProduct.name" class="w-12 h-12 object-cover rounded border bg-white p-1" />
          <div>
            <p class="font-semibold text-on-surface leading-snug">{{ reviewProduct.name }}</p>
          </div>
        </div>
        
        <!-- Star selection -->
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Overall Rating</label>
          <div class="flex gap-1 text-star-rating cursor-pointer">
            <span v-for="star in 5" :key="star" @click="reviewRating = star"
                  class="material-symbols-outlined text-[24px]"
                  :style="star <= reviewRating ? 'font-variation-settings: \'FILL\' 1;' : ''">
              star
            </span>
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Comments</label>
          <textarea v-model="reviewComment" rows="4" placeholder="What did you think of the product?" required
                    class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
        </div>

        <div class="bg-surface-container-low border-t border-outline-variant -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isReviewModalOpen = false" class="border border-outline text-on-surface px-4 py-2 rounded-lg font-bold hover:bg-surface-container-high transition-all">Cancel</button>
          <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-bold hover:bg-primary-container transition-all">Submit Review</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Scrollbar removal helper */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Animations */
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}

.animate-slide-down {
  animation: slideDown 0.2s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
    overflow: hidden;
  }
  to {
    opacity: 1;
    max-height: 200px;
  }
}
</style>
