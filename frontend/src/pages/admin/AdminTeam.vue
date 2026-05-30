<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4"
    >
      <AppButton
        variant="primary"
        size="lg"
        @click="showInviteModal = true"
      >
        <template #prefix>
          <UserPlusIcon class="w-5 h-5 mr-3" />
        </template>
        {{ t("admin.team.invite_admin") }}
      </AppButton>
    </div>

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="t('admin.team.loading')"
      />

      <div
        class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
      >
        <TeamStats
          v-model:view-mode="viewMode"
          :stats="stats"
          :loading="loading"
          @refresh="fetchTeam"
        />

        <div class="relative">
          <TeamMemberGrid
            v-if="viewMode === 'grid' && team.length > 0"
            :members="team"
            :loading="loading"
            @toggle-status="toggleStatus"
          />

          <div
            v-else-if="viewMode === 'list' && team.length > 0"
            class="flex flex-col"
          >
            <TeamMemberListRow
              v-for="member in team"
              :key="member.email"
              :member="member"
              @toggle-status="toggleStatus"
            />
          </div>

          <div
            v-else-if="!loading && team.length === 0"
            class="py-24 text-center"
          >
            <div
              class="w-24 h-24 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-dashed border-gray-200 dark:border-gray-800"
            >
              <UsersIcon class="w-12 h-12 text-gray-300 dark:text-gray-700" />
            </div>
            <p
              class="text-gray-500 dark:text-gray-400 font-black text-sm uppercase tracking-widest"
            >
              {{ t("admin.team.no_members") }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <TeamInviteModal
      :is-open="showInviteModal"
      :form="inviteForm"
      :roles="roles"
      :loading="inviting"
      @close="showInviteModal = false"
      @submit="inviteAdmin"
      @update:name="inviteForm.name = $event"
      @update:email="inviteForm.email = $event"
      @update:role-id="inviteForm.roleId = $event"
    />
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import { UserPlusIcon, UsersIcon } from "@heroicons/vue/24/outline";
import axios from "@/services/api";

import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import TeamStats from "@/components/admin/features/team/TeamStats.vue";
import TeamMemberGrid from "@/components/admin/features/team/TeamMemberGrid.vue";
import TeamMemberListRow from "@/components/admin/features/team/TeamMemberListRow.vue";
import TeamInviteModal from "@/components/admin/features/team/TeamInviteModal.vue";

const { t } = useI18n();
const toast = useToast();

const team = ref([]);
const stats = ref({ total: 0, owners: 0 });
const roles = ref([]);
const loading = ref(true);
const inviting = ref(false);
const showInviteModal = ref(false);
const viewMode = ref("grid");

const inviteForm = ref({
  name: "",
  email: "",
  roleId: "",
});

const fetchTeam = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/admin/team");
    team.value = response.data.data.team;
    stats.value = response.data.data.stats;
  } catch (error) {
    toast.error(t("admin.team.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get("/admin/team/roles");
    roles.value = response.data.data.roles;
  } catch (error) {
    console.error("Failed to load roles");
  }
};

const inviteAdmin = async () => {
  inviting.value = true;
  try {
    await axios.post("/admin/team/invite", inviteForm.value);
    toast.success(t("admin.team.alerts.inviteSuccess"));
    showInviteModal.value = false;
    inviteForm.value = { name: "", email: "", roleId: "" };
    fetchTeam();
  } catch (error) {
    const message =
      error.response?.data?.message || t("admin.team.alerts.inviteError");
    toast.error(message);
  } finally {
    inviting.value = false;
  }
};

const toggleStatus = async (member) => {
  try {
    const response = await axios.post(`/admin/team/${member.id}/toggle-status`);
    member.status = response.data.data.status;
    toast.success(
      response.data.data.message || t("admin.team.alerts.statusToggleSuccess"),
    );
  } catch (error) {
    toast.error(
      error.response?.data?.message || t("admin.team.alerts.statusToggleError"),
    );
  }
};

onMounted(() => {
  fetchTeam();
  fetchRoles();
});
</script>
