<script setup lang="ts">
import { ref, reactive, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import api from "@/shared/services/api/apiClient";
import TwoFactorSection from "@/widgets/Account/components/TwoFactorSection.vue";
import UiDropdown from "@/shared/ui/UiDropdown.vue";

const authStore = useAuthStore();
const cartStore = useCartStore();
const { t } = useI18n();
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

// Accordion Expand/Collapse State
const expandedSections = reactive<Record<string, boolean>>({
  profile: true,
  password: false,
  twoFactor: false,
  addresses: false,
});

const toggleSection = (section: string) => {
  expandedSections[section] = !expandedSections[section];
};

// Form states
const profileForm = reactive({
  name: "",
  email: "",
  phone: "",
  language: "uk",
});

const languageOptions = computed(() => [
  { value: "uk", label: t("account.settings.profile.languageUk") },
  { value: "en", label: t("account.settings.profile.languageEn") },
]);

// Older accounts may still have the pre-fix free-text label ("Українська"/
// "Англійська") stored as their language setting, from when the native
// <select> had no `value` attributes and sent its visible text instead of a
// real locale code - normalize those to a real code so the dropdown can
// actually find a matching option, rather than rendering blank.
const normalizeLanguage = (value?: string | null): string => {
  if (value === "uk" || value === "en") return value;
  if (value === "Англійська" || value === "English") return "en";
  return "uk";
};

const passwordForm = reactive({
  current: "",
  new: "",
  confirm: "",
});

const addressesList = ref<AddressItem[]>([]);

// Address type is persisted as its literal Ukrainian label (see AddressItem's
// `type` field and how it's sent straight through in saveAddress/syncSettingsWithBackend),
// so the option values below must stay untranslated to avoid changing what's
// stored — only the dropdown's and the address card's displayed labels are localized.
const ADDRESS_TYPE_VALUES = ["Дім", "Офіс", "Інше"] as const;

const addressTypeLabelKeys: Record<string, string> = {
  Дім: "account.settings.addresses.types.home",
  Офіс: "account.settings.addresses.types.office",
  Інше: "account.settings.addresses.types.other",
};

const addressTypeOptions = computed(() =>
  ADDRESS_TYPE_VALUES.map((value) => ({
    value,
    label: t(addressTypeLabelKeys[value]),
  })),
);

const getAddressTypeLabel = (type: string) =>
  addressTypeLabelKeys[type] ? t(addressTypeLabelKeys[type]) : type;

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

// Initialization
const initData = () => {
  if (authStore.user) {
    const user = authStore.user as any;
    profileForm.name = user.name || "";
    profileForm.email = user.email || "";
    profileForm.phone = user.phone || "";
    profileForm.language = normalizeLanguage(user.language);

    addressesList.value = user.addresses || [];
  }
};

onMounted(async () => {
  await authStore.fetchUser();
  initData();
});

// Avatar Upload Logic
const userInitials = computed(() => {
  const name = authStore.user?.name || "";
  return (
    name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .substring(0, 2)
      .toUpperCase() || t("account.sidebar.customerInitial")
  );
});

const isUploadingAvatar = ref(false);
const avatarFileInput = ref<HTMLInputElement | null>(null);

const triggerAvatarUpload = () => {
  avatarFileInput.value?.click();
};

const handleAvatarFileChange = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  if (!file.type.startsWith("image/")) {
    cartStore.addToast(
      t("account.settings.toasts.avatarInvalidType"),
      "warning",
    );
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    cartStore.addToast(t("account.settings.toasts.avatarTooLarge"), "warning");
    return;
  }

  isUploadingAvatar.value = true;
  const formData = new FormData();
  formData.append("avatar", file);

  try {
    const response = await api.post("/user/avatar", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    if (response.data && response.data.status === "success") {
      authStore.user = response.data.data;
      cartStore.addToast(t("account.settings.toasts.avatarUpdated"), "success");
    }
  } catch (error: any) {
    console.error("Avatar upload failed:", error);
    const msg =
      error.response?.data?.message ||
      t("account.settings.toasts.avatarUploadFailed");
    cartStore.addToast(msg, "error");
  } finally {
    isUploadingAvatar.value = false;
    if (avatarFileInput.value) {
      avatarFileInput.value.value = "";
    }
  }
};

const deleteAvatarAction = async () => {
  if (!confirm(t("account.settings.deleteAvatarConfirm"))) return;

  isUploadingAvatar.value = true;
  try {
    const response = await api.delete("/user/avatar");
    if (response.data && response.data.status === "success") {
      authStore.user = response.data.data;
      cartStore.addToast(t("account.settings.toasts.avatarDeleted"), "info");
    }
  } catch (error: any) {
    console.error("Failed to delete avatar:", error);
    const msg =
      error.response?.data?.message ||
      t("account.settings.toasts.avatarDeleteFailed");
    cartStore.addToast(msg, "error");
  } finally {
    isUploadingAvatar.value = false;
  }
};

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
    });
    if (response.data.success) {
      await authStore.fetchUser();
      cartStore.addToast(
        t("account.settings.toasts.profileUpdated"),
        "success",
      );
    }
  } catch (e: any) {
    const msg =
      e.response?.data?.message ||
      t("account.settings.toasts.profileUpdateFailed");
    cartStore.addToast(msg, "error");
  } finally {
    isSavingProfile.value = false;
  }
};

const isUpdatingPassword = ref(false);
const updatePassword = async () => {
  if (!passwordForm.current || !passwordForm.new || !passwordForm.confirm) {
    cartStore.addToast(
      t("account.settings.toasts.fillPasswordFields"),
      "warning",
    );
    return;
  }
  if (passwordForm.new !== passwordForm.confirm) {
    cartStore.addToast(t("account.settings.toasts.passwordMismatch"), "error");
    return;
  }
  isUpdatingPassword.value = true;
  try {
    const response = await api.put("/user/password", {
      currentPassword: passwordForm.current,
      newPassword: passwordForm.new,
    });
    if (response.data.success) {
      cartStore.addToast(
        t("account.settings.toasts.passwordUpdated"),
        "success",
      );
      passwordForm.current = "";
      passwordForm.new = "";
      passwordForm.confirm = "";
    }
  } catch (e: any) {
    const msg =
      e.response?.data?.message ||
      t("account.settings.toasts.passwordUpdateFailed");
    cartStore.addToast(msg, "error");
  } finally {
    isUpdatingPassword.value = false;
  }
};

// Sync helper for the address book list
const syncSettingsWithBackend = async () => {
  try {
    await api.put("/user/profile", {
      name: profileForm.name,
      email: profileForm.email,
      phone: profileForm.phone,
      language: profileForm.language,
      addresses: addressesList.value,
    });
    await authStore.fetchUser();
  } catch (e) {
    console.error("Failed to sync settings with backend:", e);
    cartStore.addToast(
      t("account.settings.toasts.settingsSyncFailed"),
      "error",
    );
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
      t("account.settings.toasts.fillRequiredAddressFields"),
      "warning",
    );
    return;
  }
  if (addressForm.id) {
    const idx = addressesList.value.findIndex((a) => a.id === addressForm.id);
    if (idx !== -1) {
      addressesList.value[idx] = { ...addressForm } as any;
    }
    cartStore.addToast(t("account.settings.toasts.addressUpdated"), "success");
  } else {
    const newId = addressesList.value.length
      ? Math.max(...addressesList.value.map((a) => a.id), 0) + 1
      : 1;
    addressesList.value.push({
      ...addressForm,
      id: newId,
      isDefault: addressesList.value.length === 0,
    } as any);
    cartStore.addToast(t("account.settings.toasts.addressAdded"), "success");
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
    cartStore.addToast(t("account.settings.toasts.addressDeleted"), "info");
    await syncSettingsWithBackend();
  }
};

const setAddressDefault = async (id: number) => {
  addressesList.value.forEach((a) => (a.isDefault = a.id === id));
  cartStore.addToast(
    t("account.settings.toasts.addressDefaultUpdated"),
    "success",
  );
  await syncSettingsWithBackend();
};

// Summary computation for collapsed state
const addressesSummary = computed(() => {
  if (!addressesList.value.length)
    return t("account.settings.addresses.noneSaved");
  const def = addressesList.value.find((a) => a.isDefault);
  if (def)
    return t("account.settings.addresses.defaultSummary", {
      city: def.city,
      street: def.street,
    });
  return t("account.settings.addresses.countSummary", {
    count: addressesList.value.length,
  });
});

const inputClass =
  "w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-800 dark:text-zinc-100 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none transition-all";
</script>

<template>
  <div class="space-y-4 animate-fade font-sans pb-12">
    <!-- 1. PERSONAL PROFILE ACCORDION -->
    <div
      class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
    >
      <button
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors"
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
              {{ t("account.settings.profile.title") }}
            </h3>
            <p
              v-if="!expandedSections.profile"
              class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold"
            >
              {{
                profileForm.name ||
                t("account.settings.profile.collapsedFallbackName")
              }}
              &bull;
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
        <!-- Avatar Section -->
        <div
          class="flex flex-col sm:flex-row items-center gap-5 pb-6 mb-6 border-b border-zinc-150 dark:border-zinc-800/80"
        >
          <div class="relative group">
            <img
              v-if="authStore.user?.avatarUrl"
              :src="authStore.user.avatarUrl"
              class="w-24 h-24 rounded-full object-cover border-2 border-[#00a046]"
            />
            <div
              v-else
              class="w-24 h-24 rounded-full bg-emerald-500/10 text-[#00a046] flex items-center justify-center text-3xl font-black border-2 border-emerald-500/20 select-none"
            >
              {{ userInitials }}
            </div>

            <div
              v-if="isUploadingAvatar"
              class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center"
            >
              <div
                class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"
              />
            </div>
          </div>

          <div class="flex flex-col gap-2 text-center sm:text-left">
            <h4 class="font-extrabold text-sm text-zinc-800 dark:text-zinc-200">
              {{ t("account.settings.profile.avatarTitle") }}
            </h4>
            <p
              class="text-xs text-zinc-450 dark:text-zinc-500 font-medium max-w-xs leading-normal"
            >
              {{ t("account.settings.profile.avatarHint") }}
            </p>
            <div
              class="flex flex-wrap gap-2.5 mt-2 justify-center sm:justify-start"
            >
              <input
                ref="avatarFileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleAvatarFileChange"
              />
              <button
                type="button"
                class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2 rounded-lg font-black text-xs transition-colors shadow-sm"
                @click="triggerAvatarUpload"
              >
                {{ t("account.settings.profile.uploadPhoto") }}
              </button>
              <button
                v-if="authStore.user?.avatarUrl"
                type="button"
                class="border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-rose-500 px-4 py-2 rounded-lg font-black text-xs transition-colors"
                @click="deleteAvatarAction"
              >
                {{ t("account.settings.profile.deletePhoto") }}
              </button>
            </div>
          </div>
        </div>

        <form
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
          @submit.prevent="saveProfile"
        >
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >{{ t("account.settings.profile.nameLabel") }}</label
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
              >{{ t("account.settings.profile.emailLabel") }}</label
            >
            <input
              v-model="profileForm.email"
              type="email"
              required
              disabled
              :class="[
                inputClass,
                '!bg-zinc-100 dark:!bg-zinc-800/60 opacity-60 cursor-not-allowed',
              ]"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >{{ t("account.settings.profile.phoneLabel") }}</label
            >
            <input
              v-model="profileForm.phone"
              type="text"
              :placeholder="t('account.settings.profile.phonePlaceholder')"
              :class="inputClass"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
              >{{ t("account.settings.profile.languageLabel") }}</label
            >
            <UiDropdown
              v-model="profileForm.language"
              :options="languageOptions"
              trigger-class="w-full"
            />
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
              {{
                isSavingProfile
                  ? t("account.settings.profile.saving")
                  : t("account.settings.profile.save")
              }}
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
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors"
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
              {{ t("account.settings.password.title") }}
            </h3>
            <p
              v-if="!expandedSections.password"
              class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold"
            >
              {{ t("account.settings.password.collapsedSubtitle") }}
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
              >{{ t("account.settings.password.currentLabel") }}</label
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
                >{{ t("account.settings.password.newLabel") }}</label
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
                >{{ t("account.settings.password.confirmLabel") }}</label
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
              {{
                isUpdatingPassword
                  ? t("account.settings.password.updating")
                  : t("account.settings.password.submit")
              }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- 2b. TWO-FACTOR AUTHENTICATION ACCORDION -->
    <TwoFactorSection
      :expanded="expandedSections.twoFactor"
      @toggle="toggleSection('twoFactor')"
    />

    <!-- 3. ADDRESS BOOK ACCORDION -->
    <div
      class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
    >
      <button
        class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors"
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
              {{ t("account.settings.addresses.title") }}
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
            {{ t("account.settings.addresses.savedTitle") }}
          </span>
          <button
            class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-black hover:underline flex items-center gap-0.5"
            @click="openAddressModal()"
          >
            <span class="material-symbols-outlined text-[16px] md:text-[18px]"
              >add</span
            >
            {{ t("account.settings.addresses.add") }}
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
                  getAddressTypeLabel(address.type)
                }}</span>
                <span
                  v-if="address.isDefault"
                  class="bg-[#00a046]/10 text-[#00a046] text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded border border-[#00a046]/20"
                  >{{ t("account.settings.addresses.defaultBadge") }}</span
                >
              </div>
              <p class="text-zinc-800 dark:text-zinc-200 font-extrabold">
                {{ address.recipient }}
              </p>
              <p class="text-zinc-500 dark:text-zinc-400 mt-1">
                {{ address.street }}
              </p>
              <p class="text-zinc-500 dark:text-zinc-400">
                {{ t("account.settings.addresses.cityPrefix")
                }}{{ address.city }}, {{ address.state }} {{ address.zip }}
              </p>
            </div>

            <div
              class="mt-4 flex flex-wrap items-center gap-3 border-t border-zinc-100 dark:border-zinc-800 pt-3"
            >
              <button
                class="text-zinc-450 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors font-extrabold"
                @click="openAddressModal(address)"
              >
                {{ t("account.settings.addresses.edit") }}
              </button>
              <button
                class="text-zinc-450 hover:text-rose-500 transition-colors font-extrabold"
                @click="deleteAddress(address.id)"
              >
                {{ t("account.settings.addresses.delete") }}
              </button>
              <button
                v-if="!address.isDefault"
                class="text-[#00a046] hover:underline ml-auto font-black text-[10px] uppercase"
                @click="setAddressDefault(address.id)"
              >
                {{ t("account.settings.addresses.makeDefault") }}
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-6 text-zinc-400 dark:text-zinc-555">
          {{ t("account.settings.addresses.empty") }}
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
        class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-150 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <h3
          class="font-black text-base md:text-lg text-zinc-900 dark:text-white"
        >
          {{
            addressForm.id
              ? t("account.settings.addresses.modal.editTitle")
              : t("account.settings.addresses.modal.addTitle")
          }}
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
              >{{ t("account.settings.addresses.modal.typeLabel") }}</label
            >
            <UiDropdown
              v-model="addressForm.type"
              :options="addressTypeOptions"
              trigger-class="w-full"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.settings.addresses.modal.recipientLabel") }}</label
            >
            <input
              v-model="addressForm.recipient"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >{{ t("account.settings.addresses.modal.streetLabel") }}</label
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
              >{{ t("account.settings.addresses.modal.cityLabel") }}</label
            >
            <input
              v-model="addressForm.city"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.settings.addresses.modal.stateLabel") }}</label
            >
            <input
              v-model="addressForm.state"
              type="text"
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.settings.addresses.modal.zipLabel") }}</label
            >
            <input
              v-model="addressForm.zip"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.settings.addresses.modal.countryLabel") }}</label
            >
            <input
              v-model="addressForm.country"
              type="text"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
            />
          </div>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >{{ t("account.settings.addresses.modal.phoneLabel") }}</label
          >
          <input
            v-model="addressForm.phone"
            type="text"
            class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] outline-none"
          />
        </div>
        <div
          class="bg-zinc-50 dark:bg-zinc-800 border-t border-zinc-150 dark:border-zinc-800 -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6"
        >
          <button
            type="button"
            class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg font-extrabold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-xs"
            @click="isAddressModalOpen = false"
          >
            {{ t("account.settings.addresses.modal.cancel") }}
          </button>
          <button
            type="submit"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2 rounded-lg font-extrabold transition-all text-xs"
          >
            {{ t("account.settings.addresses.modal.save") }}
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
