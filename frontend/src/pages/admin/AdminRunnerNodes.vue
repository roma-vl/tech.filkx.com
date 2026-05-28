<template>
  <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6 animate-in fade-in duration-500">
    <div
      class="flex flex-col md:flex-row md:items-end justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white uppercase tracking-tight">
          {{ t("admin.runnerNodes.title") }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          {{ t("admin.runnerNodes.description") }}
        </p>
      </div>
      
      <AppButton
        variant="primary"
        @click="openAddModal"
      >
        <template #prefix>
          <PlusIcon class="w-4 h-4 mr-2" />
        </template>
        {{ t("admin.runnerNodes.add") }}
      </AppButton>
    </div>

    <!-- Nodes Grid -->
    <div 
      v-if="store.loading && store.nodes.length === 0" 
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div v-for="i in 3" :key="i" class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 h-48 animate-pulse">
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
      </div>
    </div>

    <AppEmptyState
      v-else-if="store.nodes.length === 0"
      :title="t('admin.runnerNodes.emptyTitle')"
      :description="t('admin.runnerNodes.emptyDescription')"
    >
      <template #icon>
        <ServerIcon class="w-8 h-8 text-gray-400" />
      </template>
    </AppEmptyState>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="node in store.nodes"
        :key="node.id"
        class="bg-white dark:bg-gray-800 rounded-2xl border p-6 flex flex-col justify-between"
        :class="node.status === 'alive' ? 'border-emerald-500/30 shadow-emerald-500/5' : 'border-red-500/30 shadow-red-500/5'"
      >
        <div>
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                {{ node.name }}
                <div 
                  class="w-2.5 h-2.5 rounded-full" 
                  :class="node.status === 'alive' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'" 
                  title="Ping Status"
                ></div>
              </h3>
              <p class="text-xs text-gray-500 font-mono mt-1">{{ node.ipAddress }}:{{ node.port }}</p>
            </div>
            
            <Dropdown align="right" width="48">
              <template #trigger>
                <button class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                  <MoreHorizontalIcon class="w-5 h-5" />
                </button>
              </template>
              <template #content>
                <div class="p-1.5 space-y-1">
                  <button @click="openEditModal(node)" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <Edit2Icon class="w-4 h-4" /> {{ t('admin.runnerNodes.menu.edit') }}
                  </button>
                  <button @click="pingNode(node)" :disabled="pinging[node.id]" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg">
                    <ActivityIcon class="w-4 h-4" :class="{'animate-spin': pinging[node.id]}" />
                    {{ pinging[node.id] ? t('admin.runnerNodes.card.pinging') : t('admin.runnerNodes.menu.forcePing') }}
                  </button>
                  <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>
                  <button @click="confirmDelete(node)" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                    <TrashIcon class="w-4 h-4" /> {{ t('admin.runnerNodes.menu.delete') }}
                  </button>
                </div>
              </template>
            </Dropdown>
          </div>

          <div class="space-y-3 mb-6">
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-500">{{ t('admin.runnerNodes.card.status') }}</span>
              <span class="font-medium" :class="node.status === 'alive' ? 'text-emerald-600' : 'text-red-500'">
                {{ node.status === 'alive' ? t('admin.runnerNodes.card.alive') : t('admin.runnerNodes.card.unreachable') }}
              </span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-500">{{ t('admin.runnerNodes.card.load') }}</span>
              <span class="font-medium text-gray-900 dark:text-gray-300">
                {{ node.currentActiveStreams || 0 }} / {{ node.maxCapacityStreams || 10 }}
              </span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-500">{{ t('admin.runnerNodes.card.cpu') }}</span>
              <span class="font-medium" :class="node.currentCpu > 80 ? 'text-red-500' : node.currentCpu > 50 ? 'text-amber-500' : 'text-emerald-600'">
                {{ node.currentCpu || 0 }}%
              </span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-500">{{ t('admin.runnerNodes.card.lastChecked') }}</span>
              <span class="font-medium text-gray-900 dark:text-gray-300">
                {{ node.lastPingAt ? formatDate(node.lastPingAt) : t('admin.runnerNodes.card.never') }}
              </span>
            </div>
             <div class="flex justify-between items-center text-sm">
              <span class="text-gray-500">{{ t('admin.runnerNodes.card.activeLabel') }}</span>
              <AppToggle
                :model-value="node.isActive == 1 || node.isActive === true"
                @update:model-value="toggleNode(node)"
              />
            </div>
          </div>
        </div>

        <AppButton 
          variant="secondary" 
          class="w-full font-mono text-xs"
          @click="pingNode(node)"
          :loading="pinging[node.id]"
        >
          {{ t('admin.runnerNodes.card.ping') }}
        </AppButton>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <AppModal
      v-if="showModal"
      @update:model-value="closeModal"
      :model-value="showModal"
      :title="editingNode ? t('admin.runnerNodes.modal.editTitle') : t('admin.runnerNodes.modal.addTitle')"
      max-width="lg"
    >
      <form @submit.prevent="saveNode" class="space-y-4">
        <AppInput
          v-model="form.name"
          :label="t('admin.runnerNodes.modal.name')"
          :placeholder="t('admin.runnerNodes.modal.namePlaceholder')"
          required
        />
        
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2">
            <AppInput
              v-model="form.ipAddress"
              :label="t('admin.runnerNodes.modal.ip')"
              :placeholder="t('admin.runnerNodes.modal.ipPlaceholder')"
              required
            />
          </div>
          <div class="col-span-1">
            <AppInput
              v-model.number="form.port"
              type="number"
              :label="t('admin.runnerNodes.modal.port')"
              placeholder="8080"
              required
            />
          </div>
        </div>
        
        <AppInput
          v-model.number="form.maxCapacityStreams"
          type="number"
          :label="t('admin.runnerNodes.modal.maxCapacity')"
          placeholder="10"
          required
        />

        <div>
          <AppInput
            v-model="form.apiToken"
            :label="t('admin.runnerNodes.modal.token')"
            :type="showToken ? 'text' : 'password'"
            :placeholder="t('admin.runnerNodes.modal.tokenPlaceholder')"
            required
          >
            <template #append>
              <button
                type="button"
                tabindex="-1"
                @click="showToken = !showToken"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
              >
                <EyeOffIcon v-if="showToken" class="w-5 h-5" />
                <EyeIcon v-else class="w-5 h-5" />
              </button>
            </template>
          </AppInput>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 ml-1">{{ t('admin.runnerNodes.modal.tokenHint') }}</p>
        </div>

        <div class="pt-2 flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('admin.runnerNodes.modal.activeImmediately') }}</span>
          <AppToggle v-model="form.isActive" />
        </div>

        <div class="pt-6 flex justify-end gap-3">
          <AppButton type="button" variant="secondary" @click="closeModal">{{ t('admin.runnerNodes.modal.cancel') }}</AppButton>
          <AppButton type="submit" variant="primary" :loading="saving">{{ t('admin.runnerNodes.modal.save') }}</AppButton>
        </div>
      </form>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <AppModal
      v-if="nodeToDelete"
      @update:model-value="nodeToDelete = null"
      :model-value="!!nodeToDelete"
      :title="t('admin.runnerNodes.deleteModal.title')"
      max-width="sm"
    >
      <div class="space-y-4">
        <p class="text-gray-600 dark:text-gray-300" v-html="t('admin.runnerNodes.deleteModal.warning', { name: `<span class='font-bold'>${nodeToDelete.name}</span>` })">
        </p>
        <div class="flex justify-end gap-3 pt-4">
          <AppButton variant="secondary" @click="nodeToDelete = null">{{ t('admin.runnerNodes.deleteModal.cancel') }}</AppButton>
          <AppButton variant="danger" :loading="deleting" @click="executeDelete">{{ t('admin.runnerNodes.deleteModal.confirm') }}</AppButton>
        </div>
      </div>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useRunnerNodesStore } from '@/stores/admin/runnerNodesStore';

import AppButton from '@/components/application/ui/Button/AppButton.vue';
import AppModal from '@/components/application/ui/Overlay/AppModal.vue';
import AppInput from '@/components/application/ui/Form/AppInput.vue';
import AppToggle from '@/components/application/ui/Form/AppToggle.vue';
import AppEmptyState from '@/components/application/ui/Feedback/AppEmptyState.vue';
import Dropdown from '@/components/Dropdown.vue';

import { 
  ServerIcon, 
  PlusIcon, 
  MoreHorizontalIcon, 
  Edit2Icon, 
  TrashIcon, 
  ActivityIcon,
  EyeIcon,
  EyeOffIcon
} from 'lucide-vue-next';

const { t } = useI18n();
const toast = useToast();
const store = useRunnerNodesStore();

const showModal = ref(false);
const editingNode = ref(null);
const saving = ref(false);
const nodeToDelete = ref(null);
const deleting = ref(false);
const pinging = ref({});
const showToken = ref(false);

const form = ref({
  name: '',
  ipAddress: '',
  port: 8080,
  apiToken: '',
  isActive: true,
  maxCapacityStreams: 10,
});

onMounted(() => {
  store.fetchNodes();
});

const openAddModal = () => {
  editingNode.value = null;
  form.value = {
    name: '',
    ipAddress: '',
    port: 8080,
    apiToken: '',
    isActive: true,
    maxCapacityStreams: 10,
  };
  showToken.value = false;
  showModal.value = true;
};

const openEditModal = (node) => {
  editingNode.value = node;
  form.value = {
    name: node.name,
    ipAddress: node.ipAddress,
    port: node.port,
    apiToken: node.apiToken || '', // Sometimes token might be hidden from API responses, but we return it in resource for admin
    isActive: node.isActive == 1 || node.isActive === true,
    maxCapacityStreams: node.maxCapacityStreams || 10,
  };
  showToken.value = false;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingNode.value = null;
};

const saveNode = async () => {
  saving.value = true;

  const payload = {
    name: form.value.name,
    ip_address: form.value.ipAddress,
    port: form.value.port,
    api_token: form.value.apiToken,
    is_active: form.value.isActive,
    max_capacity_streams: form.value.maxCapacityStreams
  };

  try {
    if (editingNode.value) {
      await store.updateNode(editingNode.value.id, payload);
      toast.success(t('admin.runnerNodes.alerts.updated'));
    } else {
      await store.addNode(payload);
      toast.success(t('admin.runnerNodes.alerts.added'));
    }
    closeModal();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save node');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (node) => {
  nodeToDelete.value = node;
};

const executeDelete = async () => {
  deleting.value = true;
  try {
    await store.deleteNode(nodeToDelete.value.id);
    toast.success(t('admin.runnerNodes.alerts.deleted'));
    nodeToDelete.value = null;
  } catch (error) {
    toast.error('Failed to delete node');
  } finally {
    deleting.value = false;
  }
};

const toggleNode = async (node) => {
  // Optimistically toggle UI before network finishes
  const originalState = node.isActive;
  const currentState = node.isActive == 1 || node.isActive === true;
  node.isActive = !currentState;
  
  try {
    await store.toggleNode(node.id);
    toast.success(!originalState ? t('admin.runnerNodes.alerts.activated') : t('admin.runnerNodes.alerts.deactivated'));
  } catch (error) {
    // Revert on error
    node.isActive = originalState;
    toast.error('Failed to toggle node status');
  }
};

const pingNode = async (node) => {
  if (pinging.value[node.id]) return;
  pinging.value[node.id] = true;
  try {
    await store.pingNode(node.id);
    toast.success(t('admin.runnerNodes.alerts.alive', { name: node.name }));
  } catch (error) {
    toast.error(error.response?.data?.message || `Failed to reach ${node.name}`);
  } finally {
    pinging.value[node.id] = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ', ' + date.toLocaleDateString();
};
</script>
