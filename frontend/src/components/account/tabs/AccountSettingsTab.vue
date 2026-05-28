<script setup>
import { ref, reactive, computed } from 'vue';
import { store } from '@/store.js';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const userName  = computed(() => authStore.user?.name  || 'Клієнт');
const userEmail = computed(() => authStore.user?.email || '');

// ── Profile ────────────────────────────────────────────────────────────────
const profileForm = reactive({ name: userName.value, email: userEmail.value, phone: '+380 (50) 123-4567', language: 'Українська' });

const saveProfile = () => {
  if (authStore.user) authStore.user.name = profileForm.name;
  store.addToast('Профіль успішно оновлено!', 'success');
};

// ── Password ───────────────────────────────────────────────────────────────
const passwordForm = reactive({ current: '', new: '', confirm: '' });

const updatePassword = () => {
  if (!passwordForm.current || !passwordForm.new || !passwordForm.confirm) {
    store.addToast('Будь ласка, заповніть усі поля пароля.', 'warning'); return;
  }
  if (passwordForm.new !== passwordForm.confirm) {
    store.addToast('Нові паролі не співпадають.', 'error'); return;
  }
  store.addToast('Пароль успішно змінено!', 'success');
  passwordForm.current = ''; passwordForm.new = ''; passwordForm.confirm = '';
};

// ── Addresses ──────────────────────────────────────────────────────────────
const addressesList = ref([
  { id: 1, type: 'Дім', recipient: 'Роман Шевченко', street: 'вул. Хрещатик 22, кв. 14', city: 'Київ', state: 'Київська область', zip: '01001', country: 'Україна', phone: '+380 (50) 123-4567', isDefault: true },
  { id: 2, type: 'Офіс', recipient: 'Роман Шевченко', street: 'вул. Дегтярівська 10, оф. 104', city: 'Київ', state: 'Київська область', zip: '04050', country: 'Україна', phone: '+380 (67) 987-6543', isDefault: false },
]);
const isAddressModalOpen = ref(false);
const addressForm = reactive({ id: null, type: 'Дім', recipient: '', street: '', city: '', state: '', zip: '', country: 'Україна', phone: '' });

const openAddressModal = (address = null) => {
  if (address) { Object.assign(addressForm, address); }
  else { Object.assign(addressForm, { id: null, type: 'Дім', recipient: '', street: '', city: '', state: '', zip: '', country: 'Україна', phone: '' }); }
  isAddressModalOpen.value = true;
};
const saveAddress = () => {
  if (!addressForm.recipient || !addressForm.street || !addressForm.city || !addressForm.zip) {
    store.addToast('Будь ласка, заповніть усі обов\'язкові поля.', 'warning'); return;
  }
  if (addressForm.id) {
    const idx = addressesList.value.findIndex(a => a.id === addressForm.id);
    if (idx !== -1) addressesList.value[idx] = { ...addressForm };
    store.addToast('Адресу оновлено!', 'success');
  } else {
    const newId = Math.max(...addressesList.value.map(a => a.id), 0) + 1;
    addressesList.value.push({ ...addressForm, id: newId, isDefault: addressesList.value.length === 0 });
    store.addToast('Нову адресу збережено!', 'success');
  }
  isAddressModalOpen.value = false;
};
const deleteAddress = (id) => {
  const idx = addressesList.value.findIndex(a => a.id === id);
  if (idx !== -1) {
    const wasDefault = addressesList.value[idx].isDefault;
    addressesList.value.splice(idx, 1);
    if (wasDefault && addressesList.value.length > 0) addressesList.value[0].isDefault = true;
    store.addToast('Адресу видалено.', 'info');
  }
};
const setAddressDefault = (id) => { addressesList.value.forEach(a => a.isDefault = a.id === id); store.addToast('Адресу за замовчуванням оновлено.', 'success'); };

// ── Payment Cards ──────────────────────────────────────────────────────────
const cardsList = ref([
  { id: 1, type: 'Visa',       number: '•••• •••• •••• 4242', holder: 'ROMAN SHEVCHENKO', expiry: '12/28', isDefault: true },
  { id: 2, type: 'Mastercard', number: '•••• •••• •••• 9876', holder: 'ROMAN SHEVCHENKO', expiry: '06/27', isDefault: false },
]);
const isCardModalOpen = ref(false);
const cardForm = reactive({ number: '', holder: '', expiry: '', cvv: '', type: 'Visa' });

const openCardModal = () => { Object.assign(cardForm, { number: '', holder: '', expiry: '', cvv: '', type: 'Visa' }); isCardModalOpen.value = true; };
const saveCard = () => {
  if (!cardForm.number || !cardForm.holder || !cardForm.expiry || !cardForm.cvv) {
    store.addToast('Будь ласка, заповніть усі дані картки.', 'warning'); return;
  }
  const digits = cardForm.number.replace(/\D/g, '');
  const lastFour = digits.substring(digits.length - 4) || '1111';
  const newId = Math.max(...cardsList.value.map(c => c.id), 0) + 1;
  cardsList.value.push({ id: newId, type: cardForm.type, number: `•••• •••• •••• ${lastFour}`, holder: cardForm.holder.toUpperCase(), expiry: cardForm.expiry, isDefault: cardsList.value.length === 0 });
  store.addToast('Картку додано успішно!', 'success');
  isCardModalOpen.value = false;
};
const deleteCard = (id) => {
  const idx = cardsList.value.findIndex(c => c.id === id);
  if (idx !== -1) {
    const wasDefault = cardsList.value[idx].isDefault;
    cardsList.value.splice(idx, 1);
    if (wasDefault && cardsList.value.length > 0) cardsList.value[0].isDefault = true;
    store.addToast('Картку видалено.', 'info');
  }
};
const setCardDefault = (id) => { cardsList.value.forEach(c => c.isDefault = c.id === id); store.addToast('Основну картку оновлено.', 'success'); };

const inputClass = 'w-full bg-zinc-55 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none';
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Profile + Password -->
      <div class="lg:col-span-2 space-y-6">
        <section class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
          <h3 class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3">Особисті дані</h3>
          <form @submit.prevent="saveProfile" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Повне ім'я</label><input v-model="profileForm.name" type="text" required :class="inputClass" /></div>
            <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-455 dark:text-zinc-500 uppercase tracking-wider">Електронна пошта</label><input v-model="profileForm.email" type="email" required :class="inputClass" /></div>
            <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Номер телефону</label><input v-model="profileForm.phone" type="text" :class="inputClass" /></div>
            <div class="space-y-1.5">
              <label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Мова інтерфейсу</label>
              <select v-model="profileForm.language" :class="inputClass"><option>Українська</option><option>Англійська</option></select>
            </div>
            <div class="md:col-span-2 pt-2 text-right">
              <button type="submit" class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-3 rounded-lg font-extrabold text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm">Зберегти профіль</button>
            </div>
          </form>
        </section>

        <section class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
          <h3 class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3">Зміна пароля</h3>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Поточний пароль</label><input v-model="passwordForm.current" type="password" required :class="inputClass" /></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Новий пароль</label><input v-model="passwordForm.new" type="password" required :class="inputClass" /></div>
              <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Підтвердження пароля</label><input v-model="passwordForm.confirm" type="password" required :class="inputClass" /></div>
            </div>
            <div class="pt-2 text-right">
              <button type="submit" class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-3 rounded-lg font-extrabold text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm">Оновити пароль</button>
            </div>
          </form>
        </section>
      </div>

      <!-- Right: Addresses + Cards -->
      <div class="space-y-6">
        <!-- Address Book -->
        <section class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <h3 class="font-extrabold text-base text-zinc-900 dark:text-white">Книга адрес</h3>
            <button @click="openAddressModal()" class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-extrabold hover:underline flex items-center gap-0.5"><span class="material-symbols-outlined text-[16px] md:text-[18px]">add</span> Додати</button>
          </div>
          <div class="space-y-3">
            <div v-for="address in addressesList" :key="address.id" class="border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 text-xs md:text-sm hover:bg-zinc-50 dark:hover:bg-zinc-850/50 transition-colors">
              <div class="flex items-center justify-between mb-2">
                <span class="font-extrabold text-zinc-800 dark:text-zinc-200">{{ address.type }}</span>
                <span v-if="address.isDefault" class="bg-[#00a046]/10 text-[#00a046] text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded border border-[#00a046]/20">Основна</span>
              </div>
              <p class="text-zinc-800 dark:text-zinc-200 font-extrabold">{{ address.recipient }}</p>
              <p class="text-zinc-550 dark:text-zinc-400 mt-1">{{ address.street }}</p>
              <p class="text-zinc-550 dark:text-zinc-400">м. {{ address.city }}, {{ address.state }} {{ address.zip }}</p>
              <div class="mt-4 flex gap-3 border-t border-zinc-100 dark:border-zinc-800 pt-3">
                <button @click="openAddressModal(address)" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors font-extrabold">Редагувати</button>
                <button @click="deleteAddress(address.id)" class="text-zinc-500 hover:text-rose-500 transition-colors font-extrabold">Видалити</button>
                <button v-if="!address.isDefault" @click="setAddressDefault(address.id)" class="text-[#00a046] hover:underline ml-auto font-extrabold text-[10px] uppercase">Встановити як основну</button>
              </div>
            </div>
          </div>
        </section>

        <!-- Payment Cards -->
        <section class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <h3 class="font-extrabold text-base text-zinc-900 dark:text-white">Збережені картки</h3>
            <button @click="openCardModal()" class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-extrabold hover:underline flex items-center gap-0.5"><span class="material-symbols-outlined text-[16px] md:text-[18px]">add</span> Додати</button>
          </div>
          <div class="space-y-4">
            <div v-for="card in cardsList" :key="card.id"
                 class="rounded-lg p-5 border relative overflow-hidden text-white flex flex-col justify-between aspect-[1.58/1] shadow-sm select-none"
                 :class="card.isDefault ? 'bg-gradient-to-tr from-emerald-955 via-[#00a046] to-emerald-800 border-[#00a046]/30' : 'bg-gradient-to-tr from-zinc-900 via-zinc-800 to-zinc-700 border-zinc-750'">
              <div class="flex justify-between items-start">
                <div><p class="text-[9px] font-black uppercase tracking-widest opacity-80">Картка {{ card.type }}</p><p v-if="card.isDefault" class="text-[10px] font-bold mt-1">ОСНОВНИЙ МЕТОД</p></div>
                <span class="material-symbols-outlined text-2xl opacity-90">{{ card.type === 'Visa' ? 'credit_card' : 'payments' }}</span>
              </div>
              <p class="font-mono text-base tracking-widest my-2">{{ card.number }}</p>
              <div class="flex justify-between items-end">
                <div><p class="text-[8px] uppercase tracking-wider opacity-60">Власник</p><p class="font-bold text-[11px] tracking-wide">{{ card.holder }}</p></div>
                <div><p class="text-[8px] uppercase tracking-wider opacity-60">Дійсна до</p><p class="font-bold text-[11px]">{{ card.expiry }}</p></div>
              </div>
              <div class="absolute inset-0 bg-black/85 backdrop-blur-sm opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                <button v-if="!card.isDefault" @click="setCardDefault(card.id)" class="bg-white text-black font-extrabold text-[10px] px-3.5 py-2 rounded-lg uppercase tracking-wider hover:bg-zinc-200 transition-colors">Основна</button>
                <button @click="deleteCard(card.id)" class="bg-rose-600 text-white font-extrabold text-[10px] px-3.5 py-2 rounded-lg uppercase tracking-wider hover:bg-rose-500 transition-colors">Видалити</button>
              </div>
            </div>
          </div>
        </section>
      </div>

    </div>
  </div>

  <!-- Address Modal -->
  <div v-if="isAddressModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-md w-full shadow-2xl overflow-hidden">
      <div class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-5 flex justify-between items-center">
        <h3 class="font-black text-base md:text-lg text-zinc-900 dark:text-white">{{ addressForm.id ? 'Редагувати адресу' : 'Додати нову адресу' }}</h3>
        <button @click="isAddressModalOpen = false" class="text-zinc-400 hover:text-zinc-650"><span class="material-symbols-outlined">close</span></button>
      </div>
      <form @submit.prevent="saveAddress" class="p-6 space-y-4 text-xs md:text-sm">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Тип адреси</label>
            <select v-model="addressForm.type" class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"><option>Дім</option><option>Офіс</option><option>Інше</option></select>
          </div>
          <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Отримувач *</label><input v-model="addressForm.recipient" type="text" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        </div>
        <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Вулиця, будинок, квартира *</label><input v-model="addressForm.street" type="text" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        <div class="grid grid-cols-3 gap-2">
          <div class="space-y-1.5 col-span-2"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Місто *</label><input v-model="addressForm.city" type="text" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
          <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Область</label><input v-model="addressForm.state" type="text" class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Поштовий індекс *</label><input v-model="addressForm.zip" type="text" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
          <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Країна *</label><input v-model="addressForm.country" type="text" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        </div>
        <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Номер телефону</label><input v-model="addressForm.phone" type="text" class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        <div class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isAddressModalOpen = false" class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2.5 rounded-lg font-extrabold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-xs md:text-sm">Скасувати</button>
          <button type="submit" class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg font-extrabold hover:bg-[#00b050] transition-all text-xs md:text-sm">Зберегти</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Card Modal -->
  <div v-if="isCardModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-sm w-full shadow-2xl overflow-hidden">
      <div class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-5 flex justify-between items-center">
        <h3 class="font-black text-base md:text-lg text-zinc-900 dark:text-white">Додати платіжну картку</h3>
        <button @click="isCardModalOpen = false" class="text-zinc-400 hover:text-zinc-650"><span class="material-symbols-outlined">close</span></button>
      </div>
      <form @submit.prevent="saveCard" class="p-6 space-y-4 text-xs md:text-sm">
        <div class="space-y-1.5">
          <label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Платіжна система</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer font-extrabold"><input type="radio" v-model="cardForm.type" value="Visa" class="accent-[#00a046]" /><span>Visa</span></label>
            <label class="flex items-center gap-2 cursor-pointer font-extrabold"><input type="radio" v-model="cardForm.type" value="Mastercard" class="accent-[#00a046]" /><span>Mastercard</span></label>
          </div>
        </div>
        <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Ім'я власника картки *</label><input v-model="cardForm.holder" type="text" placeholder="напр. ROMAN SHEVCHENKO" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Номер картки *</label><input v-model="cardForm.number" type="text" placeholder="16-значний номер картки" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Термін дії *</label><input v-model="cardForm.expiry" type="text" placeholder="ММ/РР" required class="w-full bg-zinc-55 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
          <div class="space-y-1.5"><label class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">CVV *</label><input v-model="cardForm.cvv" type="password" maxlength="3" placeholder="•••" required class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none" /></div>
        </div>
        <div class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6">
          <button type="button" @click="isCardModalOpen = false" class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2.5 rounded-lg font-extrabold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-xs md:text-sm">Скасувати</button>
          <button type="submit" class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg font-extrabold hover:bg-[#00b050] transition-all text-xs md:text-sm">Додати картку</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
