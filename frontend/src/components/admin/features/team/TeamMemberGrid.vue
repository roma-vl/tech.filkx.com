<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
    <div
      v-for="member in members"
      :key="member.email"
      class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 hover:border-primary-300 dark:hover:border-primary-900 transition-all duration-300 group relative shadow-sm hover:shadow-xl hover:shadow-primary-500/5"
    >
      <div class="flex items-center gap-4 mb-6">
        <div class="relative">
          <img
            :src="member.avatar || avatarPlaceholder"
            class="w-16 h-16 rounded-2xl border-2 border-primary-50 dark:border-primary-900/30 object-cover group-hover:scale-110 transition-transform duration-500"
          >
          <div
            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-4 border-white dark:border-gray-800"
            :class="member.status === 'Active' ? 'bg-green-500' : 'bg-red-500'"
          />
        </div>
        <div>
          <h4
            class="font-black text-lg text-gray-900 dark:text-white tracking-tight group-hover:text-primary-500 transition-colors"
          >
            {{ member.name }}
          </h4>
          <span
            class="inline-flex px-2 py-0.5 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-[10px] font-black uppercase tracking-widest border border-primary-200 dark:border-primary-800/50"
          >
            {{ member.role }}
          </span>
        </div>
      </div>

      <div class="space-y-4">
        <div class="flex flex-col gap-1">
          <span
            class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
          >{{ t("admin.team.member.email") }}</span>
          <span
            class="text-sm font-bold text-gray-700 dark:text-gray-200 truncate"
            :title="member.email"
          >{{ member.email }}</span>
        </div>

        <div class="flex flex-col gap-1">
          <span
            class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
          >{{ t("admin.team.member.last_active") }}</span>
          <span class="text-sm font-bold text-gray-700 dark:text-gray-200">{{
            member.lastActive
          }}</span>
        </div>
      </div>

      <div
        class="mt-6 pt-6 border-t border-gray-50 dark:border-gray-700/50 flex"
      >
        <AppButton
          variant="ghost"
          class="flex-1 !py-3 !text-[10px] !font-black !uppercase !tracking-widest !rounded-2xl !border !shadow-sm"
          :class="
            member.status === 'Active'
              ? '!bg-red-50 !text-red-600 hover:!bg-red-100 !border-red-100 dark:!bg-red-900/10 dark:!text-red-400 dark:!border-red-900/30'
              : '!bg-green-50 !text-green-600 hover:!bg-green-100 !border-green-100 dark:!bg-green-900/10 dark:!text-green-400 dark:!border-green-900/30'
          "
          @click="$emit('toggleStatus', member)"
        >
          {{
            member.status === "Active"
              ? t("admin.team.member.revoke_access")
              : t("admin.team.member.restore_access")
          }}
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import avatarPlaceholder from "@/assets/images/avatars/info/empty.png";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

defineProps({
  members: {
    type: Array,
    required: true,
  },
});

defineEmits(["toggleStatus"]);
</script>
