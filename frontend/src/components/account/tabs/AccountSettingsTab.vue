<script setup>
import { ref, reactive, computed } from 'vue';
import { store } from '@/store.js';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const userName  = computed(() => authStore.user?.name  || 'User');
const userEmail = computed(() => authStore.user?.email || '');

// ── Profile ────────────────────────────────────────────────────────────────
const profileForm = reactive({ name: userName.value, email: userEmail.value, phone: '+1 (555) 019-2834', language: 'English' });

const saveProfile = () => {
  if (authStore.user) authStore.user.name = profileForm.name;
  store.addToast('Profile updated successfully!', 'success');
};

// ── Password ───────────────────────────────────────────────────────────────
const passwordForm = reactive({ current: '', new: '', confirm: '' });

const updatePassword = () => {
  if (!passwordForm.current || !passwordForm.new || !passwordForm.confirm) {
    store.addToast('Please fill out all password fields.', 'warning'); return;
  }
  if (passwordForm.new !== passwordForm.confirm) {
    store.addToast('New passwords do not match.', 'error'); return;
  }
  store.addToast('Password changed successfully!', 'success');
  passwordForm.current = ''; passwordForm.new = ''; passwordForm.confirm = '';
};

// ── Addresses ──────────────────────────────────────────────────────────────
const addressesList = ref([
  { id: 1, type: 'Home', recipient: 'John Doe', street: '123 Emerald Ave, Suite 400', city: 'Seattle', state: 'WA', zip: '98101', country: 'United States', phone: '+1 (555) 019-2834', isDefault: true },
  { id: 2, type: 'Office', recipient: 'John Doe', street: '500 Tech Plaza, Floor 12', city: 'San Francisco', state: 'CA', zip: '94105', country: 'United States', phone: '+1 (555) 019-7788', isDefault: false },
]);
const isAddressModalOpen = ref(false);
const addressForm = reactive({ id: null, type: 'Home', recipient: '', street: '', city: '', state: '', zip: '', country: 'United States', phone: '' });

const openAddressModal = (address = null) => {
  if (address) { Object.assign(addressForm, address); }
  else { Object.assign(addressForm, { id: null, type: 'Home', recipient: '', street: '', city: '', state: '', zip: '', country: 'United States', phone: '' }); }
  isAddressModalOpen.value = true;
};
const saveAddress = () => {
  if (!addressForm.recipient || !addressForm.street || !addressForm.city || !addressForm.zip) {
    store.addToast('Please fill in required fields.', 'warning'); return;
  }
  if (addressForm.id) {
    const idx = addressesList.value.findIndex(a => a.id === addressForm.id);
    if (idx !== -1) addressesList.value[idx] = { ...addressForm };
    store.addToast('Address updated!', 'success');
  } else {
    const newId = Math.max(...addressesList.value.map(a => a.id), 0) + 1;
    addressesList.value.push({ ...addressForm, id: newId, isDefault: addressesList.value.length === 0 });
    store.addToast('New address saved!', 'success');
  }
  isAddressModalOpen.value = false;
};
const deleteAddress = (id) => {
  const idx = addressesList.value.findIndex(a => a.id === id);
  if (idx !== -1) {
    const wasDefault = addressesList.value[idx].isDefault;
    addressesList.value.splice(idx, 1);
    if (wasDefault && addressesList.value.length > 0) addressesList.value[0].isDefault = true;
    store.addToast('Address deleted.', 'info');
  }
};
const setAddressDefault = (id) => { addressesList.value.forEach(a => a.isDefault = a.id === id); store.addToast('Default address updated.', 'success'); };

// ── Payment Cards ──────────────────────────────────────────────────────────
const cardsList = ref([
  { id: 1, type: 'Visa',       number: '•••• •••• •••• 4242', holder: 'JOHN DOE', expiry: '12/28', isDefault: true },
  { id: 2, type: 'Mastercard', number: '•••• •••• •••• 9876', holder: 'JOHN DOE', expiry: '06/27', isDefault: false },
]);
const isCardModalOpen = ref(false);
const cardForm = reactive({ number: '', holder: '', expiry: '', cvv: '', type: 'Visa' });

const openCardModal = () => { Object.assign(cardForm, { number: '', holder: '', expiry: '', cvv: '', type: 'Visa' }); isCardModalOpen.value = true; };
const saveCard = () => {
  if (!cardForm.number || !cardForm.holder || !cardForm.expiry || !cardForm.cvv) {
    store.addToast('Please fill in all card details.', 'warning'); return;
  }
  const digits = cardForm.number.replace(/\D/g, '');
  const lastFour = digits.substring(digits.length - 4) || '1111';
  const newId = Math.max(...cardsList.value.map(c => c.id), 0) + 1;
  cardsList.value.push({ id: newId, type: cardForm.type, number: `•••• •••• •••• ${lastFour}`, holder: cardForm.holder.toUpperCase(), expiry: cardForm.expiry, isDefault: cardsList.value.length === 0 });
  store.addToast('Card added!', 'success');
  isCardModalOpen.value = false;
};
const deleteCard = (id) => {
  const idx = cardsList.value.findIndex(c => c.id === id);
  if (idx !== -1) {
    const wasDefault = cardsList.value[idx].isDefault;
    cardsList.value.splice(idx, 1);
    if (wasDefault && cardsList.value.length > 0) cardsList.value[0].isDefault = true;
    store.addToast('Card removed.', 'info');
  }
};
const setCardDefault = (id) => { cardsList.value.forEach(c => c.isDefault = c.id === id); store.addToast('Default payment method updated.', 'success'); };

const inputClass = 'w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none';
</script>

<template>
  <div class="space-y-gutter animate-fade">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">

      <!-- Left: Profile + Password -->
      <div class="lg:col-span-2 space-y-gutter">
        <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
          <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Profile Information</h3>
          <form @submit.prevent="saveProfile" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Full Name</label><input v-model="profileForm.name" type="text" required :class="inputClass" /></div>
            <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Email Address</label><input v-model="profileForm.email" type="email" required :class="inputClass" /></div>
            <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Phone Number</label><input v-model="profileForm.phone" type="text" :class="inputClass" /></div>
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Language</label>
              <select v-model="profileForm.language" :class="inputClass"><option>English</option><option>Ukrainian</option></select>
            </div>
            <div class="md:col-span-2 pt-2 text-right">
              <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-bold text-xs hover:bg-primary-container transition-all uppercase tracking-wider">Save Profile</button>
            </div>
          </form>
        </section>

        <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
          <h3 class="font-title-lg text-on-surface text-base border-b border-outline-variant pb-2">Change Password</h3>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Current Password</label><input v-model="passwordForm.current" type="password" required :class="inputClass" /></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">New Password</label><input v-model="passwordForm.new" type="password" required :class="inputClass" /></div>
              <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Confirm New Password</label><input v-model="passwordForm.confirm" type="password" required :class="inputClass" /></div>
            </div>
            <div class="pt-2 text-right">
              <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-bold text-xs hover:bg-primary-container transition-all uppercase tracking-wider">Update Password</button>
            </div>
          </form>
        </section>
      </div>

      <!-- Right: Addresses + Cards -->
      <div class="space-y-gutter">
        <!-- Address Book -->
        <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-outline-variant pb-2">
            <h3 class="font-title-lg text-on-surface text-base">Address Book</h3>
            <button @click="openAddressModal()" class="text-primary text-[11px] font-bold hover:underline flex items-center gap-0.5"><span class="material-symbols-outlined text-[16px]">add</span> Add</button>
          </div>
          <div class="space-y-3">
            <div v-for="address in addressesList" :key="address.id" class="border border-outline-variant rounded-lg p-4 text-xs hover:bg-surface-container-low/55 transition-colors">
              <div class="flex items-center justify-between mb-2">
                <span class="font-bold text-on-surface">{{ address.type }}</span>
                <span v-if="address.isDefault" class="bg-primary/10 text-primary text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded border border-primary/20">DEFAULT</span>
              </div>
              <p class="text-on-surface font-medium">{{ address.recipient }}</p>
              <p class="text-on-surface-variant mt-1">{{ address.street }}</p>
              <p class="text-on-surface-variant">{{ address.city }}, {{ address.state }} {{ address.zip }}</p>
              <div class="mt-4 flex gap-3 border-t border-outline-variant pt-2">
                <button @click="openAddressModal(address)" class="text-on-surface-variant hover:text-primary transition-colors font-semibold">Edit</button>
                <button @click="deleteAddress(address.id)" class="text-on-surface-variant hover:text-error transition-colors font-semibold">Delete</button>
                <button v-if="!address.isDefault" @click="setAddressDefault(address.id)" class="text-primary hover:underline ml-auto font-bold text-[10px] uppercase">Set default</button>
              </div>
            </div>
          </div>
        </section>

        <!-- Payment Cards -->
        <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-outline-variant pb-2">
            <h3 class="font-title-lg text-on-surface text-base">Saved Cards</h3>
            <button @click="openCardModal()" class="text-primary text-[11px] font-bold hover:underline flex items-center gap-0.5"><span class="material-symbols-outlined text-[16px]">add</span> Add</button>
          </div>
          <div class="space-y-4">
            <div v-for="card in cardsList" :key="card.id"
                 class="rounded-xl p-5 border relative overflow-hidden text-white flex flex-col justify-between aspect-[1.58/1] shadow-sm select-none"
                 :class="card.isDefault ? 'bg-gradient-to-tr from-teal-950 via-primary to-emerald-800 border-primary/30' : 'bg-gradient-to-tr from-slate-900 via-slate-800 to-slate-700 border-slate-700'">
              <div class="flex justify-between items-start">
                <div><p class="text-[9px] font-black uppercase tracking-widest opacity-80">{{ card.type }} Card</p><p v-if="card.isDefault" class="text-[10px] font-bold mt-1">DEFAULT METHOD</p></div>
                <span class="material-symbols-outlined text-2xl opacity-90">{{ card.type === 'Visa' ? 'credit_card' : 'payments' }}</span>
              </div>
              <p class="font-mono text-base tracking-widest my-2">{{ card.number }}</p>
              <div class="flex justify-between items-end">
                <div><p class="text-[8px] uppercase tracking-wider opacity-60">Card Holder</p><p class="font-bold text-[11px] tracking-wide">{{ card.holder }}</p></div>
                <div><p class="text-[8px] uppercase tracking-wider opacity-60">Expires</p><p class="font-bold text-[11px]">{{ card.expiry }}</p></div>
              </div>
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

  <!-- Address Modal -->
  <div v-if="isAddressModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <h3 class="font-title-lg text-on-surface text-base font-bold">{{ addressForm.id ? 'Edit Address' : 'Add New Address' }}</h3>
        <button @click="isAddressModalOpen = false"><span class="material-symbols-outlined text-on-surface-variant">close</span></button>
      </div>
      <form @submit.prevent="saveAddress" class="p-6 space-y-4 text-xs">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Address Type</label>
            <select v-model="addressForm.type" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none"><option>Home</option><option>Office</option><option>Billing</option><option>Other</option></select>
          </div>
          <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Recipient *</label><input v-model="addressForm.recipient" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        </div>
        <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Street Address *</label><input v-model="addressForm.street" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        <div class="grid grid-cols-3 gap-2">
          <div class="space-y-1 col-span-2"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">City *</label><input v-model="addressForm.city" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
          <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">State</label><input v-model="addressForm.state" type="text" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">ZIP *</label><input v-model="addressForm.zip" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
          <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Country *</label><input v-model="addressForm.country" type="text" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        </div>
        <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Phone</label><input v-model="addressForm.phone" type="text" class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        <div class="bg-surface-container-low border-t border-outline-variant -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isAddressModalOpen = false" class="border border-outline text-on-surface px-4 py-2 rounded-lg font-bold hover:bg-surface-container-high transition-all">Cancel</button>
          <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-bold hover:bg-primary-container transition-all">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Card Modal -->
  <div v-if="isCardModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl max-w-sm w-full shadow-2xl overflow-hidden">
      <div class="bg-surface-container-low border-b border-outline-variant px-6 py-4 flex justify-between items-center">
        <h3 class="font-title-lg text-on-surface text-base font-bold">Add Payment Card</h3>
        <button @click="isCardModalOpen = false"><span class="material-symbols-outlined text-on-surface-variant">close</span></button>
      </div>
      <form @submit.prevent="saveCard" class="p-6 space-y-4 text-xs">
        <div class="space-y-1">
          <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Card Provider</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" v-model="cardForm.type" value="Visa" class="accent-primary" /><span>Visa</span></label>
            <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" v-model="cardForm.type" value="Mastercard" class="accent-primary" /><span>Mastercard</span></label>
          </div>
        </div>
        <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Cardholder Name *</label><input v-model="cardForm.holder" type="text" placeholder="e.g. JOHN DOE" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Card Number *</label><input v-model="cardForm.number" type="text" placeholder="16-digit card number" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Expiry *</label><input v-model="cardForm.expiry" type="text" placeholder="MM/YY" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
          <div class="space-y-1"><label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">CVV *</label><input v-model="cardForm.cvv" type="password" maxlength="3" placeholder="•••" required class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:ring-1 focus:ring-primary outline-none" /></div>
        </div>
        <div class="bg-surface-container-low border-t border-outline-variant -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isCardModalOpen = false" class="border border-outline text-on-surface px-4 py-2 rounded-lg font-bold hover:bg-surface-container-high transition-all">Cancel</button>
          <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-lg font-bold hover:bg-primary-container transition-all">Add Card</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
