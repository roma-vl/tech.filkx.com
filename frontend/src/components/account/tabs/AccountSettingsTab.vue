<script setup lang="ts">
import { ref, reactive, computed, onMounted } from "vue";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import api from "@/services/api.js";

const authStore = useAuthStore();
const cartStore = useCartStore();
interface AddressItem {
  id: number;
  type: string;
  recipient: string;
  street: string;
  city: string;
  state: string;
  zip: string;
  country: string;
  phone: string;
  isDefault: boolean;
}

interface CardItem {
  id: number;
  type: string;
  number: string;
  holder: string;
  expiry: string;
  isDefault: boolean;
}

// Accordion Expand/Collapse State
const expandedSections = reactive<Record<string, boolean>>({
  profile: true,
  password: false,
  addresses: false,
  cards: false,
});

const toggleSection = (section: string) => {
  expandedSections[section] = !expandedSections[section];
};

// Form states
const profileForm = reactive({
  name: "",
  email: "",
  phone: "",
  language: "Українська",
});

const passwordForm = reactive({
  current: "",
  new: "",
  confirm: "",
});

const addressesList = ref<AddressItem[]>([]);
const cardsList = ref<CardItem[]>([]);

// Modals
const isAddressModalOpen = ref(false);
const addressForm = reactive<{
  id: number | null;
  type: string;
  recipient: string;
  street: string;
  city: string;
  state: string;
  zip: string;
  country: string;
  phone: string;
}>({
  id: null,
  type: "Дім",
  recipient: "",
  street: "",
  city: "",
  state: "",
  zip: "",
  country: "Україна",
  phone: "",
});

const isCardModalOpen = ref(false);
const cardForm = reactive({
  number: "",
  holder: "",
  expiry: "",
  cvv: "",
  type: "Visa",
});

// Initialization
const initData = () => {
  if (authStore.user) {
    const user = authStore.user as any;
    profileForm.name = user.name || "";
    profileForm.email = user.email || "";
    profileForm.phone = user.phone || "";
    profileForm.language = user.language || "Українська";

    addressesList.value = user.addresses || [];
    cardsList.value = user.cards || [];
  }
};

onMounted(async () => {
  await authStore.fetchUser();
  initData();
});

// Save actions
const isSavingProfile = ref(false);
const saveProfile = async () => {
  isSavingProfile.value = true;
  try {
    const response = await api.put("/user/profile", {
      name: profileForm.name,
      email: profileForm.email,
      phone: profileForm.phone,
      language: profileForm.language,
      addresses: addressesList.value,
      cards: cardsList.value,
    });
    if (response.data.success) {
      await authStore.fetchUser();
      cartStore.addToast("Профіль успішно оновлено!", "success");
    }
  } catch (e: any) {
    const msg = e.response?.data?.message || "Не вдалося оновити профіль.";
    cartStore.addToast(msg, "error");
  } finally {
    isSavingProfile.value = false;
  }
};

const isUpdatingPassword = ref(false);
const updatePassword = async () => {
  if (!passwordForm.current || !passwordForm.new || !passwordForm.confirm) {
    cartStore.addToast("Будь ласка, заповніть усі поля пароля.", "warning");
    return;
  }
  if (passwordForm.new !== passwordForm.confirm) {
    cartStore.addToast("Нові паролі не співпадають.", "error");
    return;
  }
  isUpdatingPassword.value = true;
  try {
    const response = await api.put("/user/password", {
      currentPassword: passwordForm.current,
      newPassword: passwordForm.new,
    });
    if (response.data.success) {
      cartStore.addToast("Пароль успішно змінено!", "success");
      passwordForm.current = "";
      passwordForm.new = "";
      passwordForm.confirm = "";
    }
  } catch (e: any) {
    const msg = e.response?.data?.message || "Поточний пароль вказано невірно.";
    cartStore.addToast(msg, "error");
  } finally {
    isUpdatingPassword.value = false;
  }
};

// Sync helpers for lists (addresses, cards)
const syncSettingsWithBackend = async () => {
  try {
    await api.put("/user/profile", {
      name: profileForm.name,
      email: profileForm.email,
      phone: profileForm.phone,
      language: profileForm.language,
      addresses: addressesList.value,
      cards: cardsList.value,
    });
    await authStore.fetchUser();
  } catch (e) {
    console.error("Failed to sync settings with backend:", e);
    cartStore.addToast("Не вдалося зберегти зміни на сервері.", "error");
  }
};

// Address Book Handlers
const openAddressModal = (address: AddressItem | null = null) => {
  if (address) {
    Object.assign(addressForm, address);
  } else {
    Object.assign(addressForm, {
      id: null,
      type: "Дім",
      recipient: "",
      street: "",
      city: "",
      state: "",
      zip: "",
      country: "Україна",
      phone: "",
    });
  }
  isAddressModalOpen.value = true;
};

const saveAddress = async () => {
  if (
    !addressForm.recipient ||
    !addressForm.street ||
    !addressForm.city ||
    !addressForm.zip
  ) {
    cartStore.addToast(
      "Будь ласка, заповніть усі обов'язкові поля.",
      "warning",
    );
    return;
  }
  if (addressForm.id) {
    const idx = addressesList.value.findIndex((a) => a.id === addressForm.id);
    if (idx !== -1) {
      addressesList.value[idx] = { ...addressForm } as any;
    }
    cartStore.addToast("Адресу оновлено!", "success");
  } else {
    const newId = addressesList.value.length
      ? Math.max(...addressesList.value.map((a) => a.id), 0) + 1
      : 1;
    addressesList.value.push({
      ...addressForm,
      id: newId,
      isDefault: addressesList.value.length === 0,
    } as any);
    cartStore.addToast("Нову адресу збережено!", "success");
  }
  isAddressModalOpen.value = false;
  await syncSettingsWithBackend();
};

const deleteAddress = async (id: number) => {
  const idx = addressesList.value.findIndex((a) => a.id === id);
  if (idx !== -1) {
    const wasDefault = addressesList.value[idx].isDefault;
    addressesList.value.splice(idx, 1);
    if (wasDefault && addressesList.value.length > 0) {
      addressesList.value[0].isDefault = true;
    }
    cartStore.addToast("Адресу видалено.", "info");
    await syncSettingsWithBackend();
  }
};

const setAddressDefault = async (id: number) => {
  addressesList.value.forEach((a) => (a.isDefault = a.id === id));
  cartStore.addToast("Адресу за замовчуванням оновлено.", "success");
  await syncSettingsWithBackend();
};

// Saved Cards Handlers
const openCardModal = () => {
  Object.assign(cardForm, {
    number: "",
    holder: "",
    expiry: "",
    cvv: "",
    type: "Visa",
  });
  isCardModalOpen.value = true;
};

const saveCard = async () => {
  if (
    !cardForm.number ||
    !cardForm.holder ||
    !cardForm.expiry ||
    !cardForm.cvv
  ) {
    cartStore.addToast("Будь ласка, заповніть усі дані картки.", "warning");
    return;
  }
  const digits = cardForm.number.replace(/\D/g, "");
  const lastFour = digits.substring(digits.length - 4) || "1111";
  const newId = cardsList.value.length
    ? Math.max(...cardsList.value.map((c) => c.id), 0) + 1
    : 1;
  cardsList.value.push({
    id: newId,
    type: cardForm.type,
    number: `•••• •••• •••• ${lastFour}`,
    holder: cardForm.holder.toUpperCase(),
    expiry: cardForm.expiry,
    isDefault: cardsList.value.length === 0,
  });
  cartStore.addToast("Картку додано успішно!", "success");
  isCardModalOpen.value = false;
  await syncSettingsWithBackend();
};

const deleteCard = async (id: number) => {
  const idx = cardsList.value.findIndex((c) => c.id === id);
  if (idx !== -1) {
    const wasDefault = cardsList.value[idx].isDefault;
    cardsList.value.splice(idx, 1);
    if (wasDefault && cardsList.value.length > 0) {
      cardsList.value[0].isDefault = true;
    }
    cartStore.addToast("Картку видалено.", "info");
    await syncSettingsWithBackend();
  }
};

const setCardDefault = async (id: number) => {
  cardsList.value.forEach((c) => (c.isDefault = c.id === id));
  cartStore.addToast("Основну картку оновлено.", "success");
  await syncSettingsWithBackend();
};

// Summary computations for collapsed state
const addressesSummary = computed(() => {
  if (!addressesList.value.length) return "Немає збережених адрес";
  const def = addressesList.value.find((a) => a.isDefault);
  if (def) return `Основна: м. ${def.city}, ${def.street}`;
  return `${addressesList.value.length} збережені адреси`;
});

const cardsSummary = computed(() => {
  if (!cardsList.value.length) return "Немає збережених карт";
  const def = cardsList.value.find((c) => c.isDefault);
  if (def) return `${def.type} ${def.number}`;
  return `${cardsList.value.length} збережені картки`;
});

const inputClass =
  "w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none transition-all";
</script>

<template>
  <div class="space-y-4 animate-fade font-sans select-none pb-12">
    <!-- 1. PERSONAL PROFILE ACCORDION -->
    <div
      class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
    >
      <button
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-850/30 transition-colors"
        @click="toggleSection('profile')"
      >
        <div class="flex items-center gap-4">
          <div
            class="w-10 h-10 rounded-lg bg-[#00a046]/10 text-[#00a046] flex items-center justify-center shrink-0"
          >
            <span class="material-symbols-outlined text-[22px]">person</span>
          </div>
          <div>
            <h3
              class="font-black text-sm md:text-base text-zinc-900 dark:text-white"
            >
              Особисті дані
            </h3>
            <p
              v-if="!expandedSections.profile"
              class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold"
            >
              {{ profileForm.name || "Не вказано ім'я" }} &bull;
              {{ profileForm.email }}
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined text-zinc-400 transition-transform duration-300"
          :class="{ 'rotate-180': expandedSections.profile }"
          >keyboard_arrow_down</span
        >
      </button>

      <div
        v-show="expandedSections.profile"
        class="border-t border-zinc-100 dark:border-zinc-800 p-6 bg-zinc-50/20 dark:bg-zinc-900/40"
      >
        <form
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
          @submit.prevent="saveProfile"
        >
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >Повне ім'я</label
            >
            <input
              v-model="profileForm.name"
              type="text"
              required
              :class="inputClass"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >Електронна пошта</label
            >
            <input
              v-model="profileForm.email"
              type="email"
              required
              :class="inputClass"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >Номер телефону</label
            >
            <input
              v-model="profileForm.phone"
              type="text"
              placeholder="+380..."
              :class="inputClass"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >Мова інтерфейсу</label
            >
            <select v-model="profileForm.language" :class="inputClass">
              <option>Українська</option>
              <option>Англійська</option>
            </select>
          </div>
          <div class="md:col-span-2 pt-2 text-right">
            <button
              type="submit"
              :disabled="isSavingProfile"
              class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm flex items-center justify-center gap-2 ml-auto"
            >
              <span
                v-if="isSavingProfile"
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              />
              {{ isSavingProfile ? "Збереження..." : "Зберегти профіль" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- 2. SECURITY & PASSWORD ACCORDION -->
    <div
      class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
    >
      <button
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-850/30 transition-colors"
        @click="toggleSection('password')"
      >
        <div class="flex items-center gap-4">
          <div
            class="w-10 h-10 rounded-lg bg-[#00a046]/10 text-[#00a046] flex items-center justify-center shrink-0"
          >
            <span class="material-symbols-outlined text-[22px]">lock</span>
          </div>
          <div>
            <h3
              class="font-black text-sm md:text-base text-zinc-900 dark:text-white"
            >
              Безпека та пароль
            </h3>
            <p
              v-if="!expandedSections.password"
              class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold"
            >
              Оновлення пароля вашого акаунту
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined text-zinc-400 transition-transform duration-300"
          :class="{ 'rotate-180': expandedSections.password }"
          >keyboard_arrow_down</span
        >
      </button>

      <div
        v-show="expandedSections.password"
        class="border-t border-zinc-100 dark:border-zinc-800 p-6 bg-zinc-50/20 dark:bg-zinc-900/40"
      >
        <form class="space-y-4" @submit.prevent="updatePassword">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >Поточний пароль</label
            >
            <input
              v-model="passwordForm.current"
              type="password"
              required
              :class="inputClass"
            />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label
                class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
                >Новий пароль</label
              >
              <input
                v-model="passwordForm.new"
                type="password"
                required
                :class="inputClass"
              />
            </div>
            <div class="space-y-1.5">
              <label
                class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
                >Підтвердження пароля</label
              >
              <input
                v-model="passwordForm.confirm"
                type="password"
                required
                :class="inputClass"
              />
            </div>
          </div>
          <div class="pt-2 text-right">
            <button
              type="submit"
              :disabled="isUpdatingPassword"
              class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm flex items-center justify-center gap-2 ml-auto"
            >
              <span
                v-if="isUpdatingPassword"
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              />
              {{ isUpdatingPassword ? "Оновлення..." : "Оновити пароль" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- 3. ADDRESS BOOK ACCORDION -->
    <div
      class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
    >
      <button
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-850/30 transition-colors"
        @click="toggleSection('addresses')"
      >
        <div class="flex items-center gap-4">
          <div
            class="w-10 h-10 rounded-lg bg-[#00a046]/10 text-[#00a046] flex items-center justify-center shrink-0"
          >
            <span class="material-symbols-outlined text-[22px]">home_pin</span>
          </div>
          <div>
            <h3
              class="font-black text-sm md:text-base text-zinc-900 dark:text-white"
            >
              Книга адрес
            </h3>
            <p
              v-if="!expandedSections.addresses"
              class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold truncate max-w-[280px] md:max-w-md"
            >
              {{ addressesSummary }}
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined text-zinc-400 transition-transform duration-300"
          :class="{ 'rotate-180': expandedSections.addresses }"
          >keyboard_arrow_down</span
        >
      </button>

      <div
        v-show="expandedSections.addresses"
        class="border-t border-zinc-100 dark:border-zinc-800 p-6 bg-zinc-50/20 dark:bg-zinc-900/40"
      >
        <div class="flex items-center justify-between mb-4">
          <span
            class="text-xs font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest"
          >
            Збережені адреси доставки
          </span>
          <button
            class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-black hover:underline flex items-center gap-0.5"
            @click="openAddressModal()"
          >
            <span class="material-symbols-outlined text-[16px] md:text-[18px]"
              >add</span
            >
            Додати
          </button>
        </div>

        <div
          v-if="addressesList.length"
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
          <div
            v-for="address in addressesList"
            :key="address.id"
            class="border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 bg-white dark:bg-zinc-900 text-xs md:text-sm hover:shadow-sm transition-all flex flex-col justify-between"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="font-extrabold text-zinc-800 dark:text-zinc-200">{{
                  address.type
                }}</span>
                <span
                  v-if="address.isDefault"
                  class="bg-[#00a046]/10 text-[#00a046] text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded border border-[#00a046]/20"
                  >Основна</span
                >
              </div>
              <p class="text-zinc-800 dark:text-zinc-200 font-extrabold">
                {{ address.recipient }}
              </p>
              <p class="text-zinc-500 dark:text-zinc-400 mt-1">
                {{ address.street }}
              </p>
              <p class="text-zinc-500 dark:text-zinc-400">
                м. {{ address.city }}, {{ address.state }} {{ address.zip }}
              </p>
            </div>

            <div
              class="mt-4 flex flex-wrap items-center gap-3 border-t border-zinc-100 dark:border-zinc-800 pt-3"
            >
              <button
                class="text-zinc-450 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors font-extrabold"
                @click="openAddressModal(address)"
              >
                Редагувати
              </button>
              <button
                class="text-zinc-450 hover:text-rose-500 transition-colors font-extrabold"
                @click="deleteAddress(address.id)"
              >
                Видалити
              </button>
              <button
                v-if="!address.isDefault"
                class="text-[#00a046] hover:underline ml-auto font-black text-[10px] uppercase"
                @click="setAddressDefault(address.id)"
              >
                Зробити основною
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-6 text-zinc-400 dark:text-zinc-555">
          У вас немає збережених адрес. Додайте першу адресу для швидкого
          оформлення замовлення.
        </div>
      </div>
    </div>

    <!-- 4. SAVED CARDS ACCORDION -->
    <div
      class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
    >
      <button
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-850/30 transition-colors"
        @click="toggleSection('cards')"
      >
        <div class="flex items-center gap-4">
          <div
            class="w-10 h-10 rounded-lg bg-[#00a046]/10 text-[#00a046] flex items-center justify-center shrink-0"
          >
            <span class="material-symbols-outlined text-[22px]"
              >credit_card</span
            >
          </div>
          <div>
            <h3
              class="font-black text-sm md:text-base text-zinc-900 dark:text-white"
            >
              Збережені картки
            </h3>
            <p
              v-if="!expandedSections.cards"
              class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold truncate max-w-[280px]"
            >
              {{ cardsSummary }}
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined text-zinc-400 transition-transform duration-300"
          :class="{ 'rotate-180': expandedSections.cards }"
          >keyboard_arrow_down</span
        >
      </button>

      <div
        v-show="expandedSections.cards"
        class="border-t border-zinc-100 dark:border-zinc-800 p-6 bg-zinc-50/20 dark:bg-zinc-900/40"
      >
        <div class="flex items-center justify-between mb-4">
          <span
            class="text-xs font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest"
          >
            Ваші платіжні методи
          </span>
          <button
            class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-black hover:underline flex items-center gap-0.5"
            @click="openCardModal()"
          >
            <span class="material-symbols-outlined text-[16px] md:text-[18px]"
              >add</span
            >
            Додати
          </button>
        </div>

        <div
          v-if="cardsList.length"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
        >
          <div
            v-for="card in cardsList"
            :key="card.id"
            class="rounded-lg p-5 border relative overflow-hidden text-white flex flex-col justify-between aspect-[1.58/1] shadow-sm select-none"
            :class="
              card.isDefault
                ? 'bg-gradient-to-tr from-emerald-950 via-[#00a046] to-emerald-800 border-[#00a046]/35'
                : 'bg-gradient-to-tr from-zinc-900 via-zinc-800 to-zinc-700 border-zinc-750'
            "
          >
            <div class="flex justify-between items-start">
              <div>
                <p
                  class="text-[9px] font-black uppercase tracking-widest opacity-80"
                >
                  Картка {{ card.type }}
                </p>
                <p
                  v-if="card.isDefault"
                  class="text-[10px] font-bold mt-1 text-emerald-300"
                >
                  ОСНОВНИЙ МЕТОД
                </p>
              </div>
              <span class="material-symbols-outlined text-2xl opacity-90">{{
                card.type === "Visa" ? "credit_card" : "payments"
              }}</span>
            </div>
            <p class="font-mono text-base tracking-widest my-2 text-center">
              {{ card.number }}
            </p>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-[8px] uppercase tracking-wider opacity-60">
                  Власник
                </p>
                <p class="font-bold text-[11px] tracking-wide">
                  {{ card.holder }}
                </p>
              </div>
              <div>
                <p class="text-[8px] uppercase tracking-wider opacity-60">
                  Дійсна до
                </p>
                <p class="font-bold text-[11px]">
                  {{ card.expiry }}
                </p>
              </div>
            </div>
            <div
              class="absolute inset-0 bg-black/85 backdrop-blur-sm opacity-0 hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-3"
            >
              <button
                v-if="!card.isDefault"
                class="bg-white text-black font-extrabold text-[10px] px-3.5 py-2 rounded-lg uppercase tracking-wider hover:bg-zinc-200 transition-colors"
                @click="setCardDefault(card.id)"
              >
                Основна
              </button>
              <button
                class="bg-rose-600 text-white font-extrabold text-[10px] px-3.5 py-2 rounded-lg uppercase tracking-wider hover:bg-rose-500 transition-colors"
                @click="deleteCard(card.id)"
              >
                Видалити
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-6 text-zinc-400 dark:text-zinc-555">
          У вас немає збережених платіжних карток.
        </div>
      </div>
    </div>
  </div>

  <!-- Address Modal -->
  <div
    v-if="isAddressModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-md w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-150 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <h3
          class="font-black text-base md:text-lg text-zinc-900 dark:text-white"
        >
          {{ addressForm.id ? "Редагувати адресу" : "Додати нову адресу" }}
        </h3>
        <button
          class="text-zinc-400 hover:text-zinc-650"
          @click="isAddressModalOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form
        class="p-6 space-y-4 text-xs md:text-sm"
        @submit.prevent="saveAddress"
      >
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Тип адреси</label
            >
            <select
              v-model="addressForm.type"
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            >
              <option>Дім</option>
              <option>Офіс</option>
              <option>Інше</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Отримувач *</label
            >
            <input
              v-model="addressForm.recipient"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Вулиця, будинок, квартира *</label
          >
          <input
            v-model="addressForm.street"
            type="text"
            required
            class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
          />
        </div>
        <div class="grid grid-cols-3 gap-2">
          <div class="space-y-1.5 col-span-2">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Місто *</label
            >
            <input
              v-model="addressForm.city"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Область</label
            >
            <input
              v-model="addressForm.state"
              type="text"
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Поштовий індекс *</label
            >
            <input
              v-model="addressForm.zip"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Країна *</label
            >
            <input
              v-model="addressForm.country"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Номер телефону</label
          >
          <input
            v-model="addressForm.phone"
            type="text"
            class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
          />
        </div>
        <div
          class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-150 dark:border-zinc-800 -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6"
        >
          <button
            type="button"
            class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg font-extrabold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-xs"
            @click="isAddressModalOpen = false"
          >
            Скасувати
          </button>
          <button
            type="submit"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2 rounded-lg font-extrabold transition-all text-xs"
          >
            Зберегти
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Card Modal -->
  <div
    v-if="isCardModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-sm w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-150 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <h3
          class="font-black text-base md:text-lg text-zinc-900 dark:text-white"
        >
          Додати платіжну картку
        </h3>
        <button
          class="text-zinc-400 hover:text-zinc-650"
          @click="isCardModalOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form class="p-6 space-y-4 text-xs md:text-sm" @submit.prevent="saveCard">
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Платіжна система</label
          >
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer font-extrabold"
              ><input
                v-model="cardForm.type"
                type="radio"
                value="Visa"
                class="accent-[#00a046]"
              /><span>Visa</span></label
            >
            <label class="flex items-center gap-2 cursor-pointer font-extrabold"
              ><input
                v-model="cardForm.type"
                type="radio"
                value="Mastercard"
                class="accent-[#00a046]"
              /><span>Mastercard</span></label
            >
          </div>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Ім'я власника картки *</label
          >
          <input
            v-model="cardForm.holder"
            type="text"
            placeholder="напр. ROMAN SHEVCHENKO"
            required
            class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
          />
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Номер картки *</label
          >
          <input
            v-model="cardForm.number"
            type="text"
            placeholder="16-значний номер картки"
            required
            class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >Термін дії *</label
            >
            <input
              v-model="cardForm.expiry"
              type="text"
              placeholder="ММ/РР"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >CVV *</label
            >
            <input
              v-model="cardForm.cvv"
              type="password"
              maxlength="3"
              placeholder="•••"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div
          class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-150 dark:border-zinc-800 -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6"
        >
          <button
            type="button"
            class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg font-extrabold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-xs"
            @click="isCardModalOpen = false"
          >
            Скасувати
          </button>
          <button
            type="submit"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2 rounded-lg font-extrabold transition-all text-xs"
          >
            Додати картку
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
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
</style>
