<template>
  <div
    class="group flex flex-col sm:flex-row sm:items-center gap-4 p-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200"
  >
    <div class="flex items-center gap-4 flex-1 min-w-0">
      <div class="relative flex-shrink-0">
        <img
          :src="member.avatar || avatarPlaceholder"
          class="w-12 h-12 rounded-full border-2 border-gray-100 dark:border-gray-700 object-cover group-hover:scale-110 transition-transform"
        >
        <div
          class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-800"
          :class="member.status === 'Active' ? 'bg-green-500' : 'bg-red-500'"
        />
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-0.5">
          <h4
            class="font-bold text-gray-900 dark:text-white truncate group-hover:text-primary-500 transition-colors"
          >
            {{ member.name }}
          </h4>
          <span
            class="px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-[9px] font-black uppercase tracking-widest border border-primary-200 dark:border-primary-800/50"
          >
            {{ member.role }}
          </span>
        </div>
        <div
          class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 font-medium"
        >
          <div class="flex items-center gap-1">
            <MailIcon class="w-3.5 h-3.5 opacity-60" />
            {{ member.email }}
          </div>
          <div class="flex items-center gap-1">
            <ClockIcon class="w-3.5 h-3.5 opacity-60" />
            {{ t("admin.team.member.last_active") }}: {{ member.lastActive }}
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3 self-end sm:self-auto">
      <AppButton
        variant="ghost"
        class="!px-4 !py-2 !text-[10px] !font-black !uppercase !tracking-widest !rounded-xl !border !shadow-sm"
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
</template>

<script setup>
import { useI18n } from "vue-i18n";
import { ClockIcon, MailIcon } from "lucide-vue-next";
import avatarPlaceholder from "@/assets/images/avatars/info/empty.png";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

defineProps({
  member: {
    type: Object,
    required: true,
  },
});

defineEmits(["toggleStatus"]);
</script>
