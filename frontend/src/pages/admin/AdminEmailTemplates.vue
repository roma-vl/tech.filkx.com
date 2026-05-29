<template>
  <div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <EmailCampaignList
          :campaigns="campaigns"
          :selected-id="selectedCampaign?.id"
          @select="handleSelectCampaign"
        />
      </div>

      <EmailBroadcastPanel
        v-model:target-audience="targetAudience"
        :selected-campaign="selectedCampaign"
        :loading="loading"
        :loading-preview="loadingPreview"
        @preview="previewCampaign"
        @broadcast="confirmBroadcast"
      />
    </div>

    <EmailDispatchModal
      :is-open="showModal"
      :campaign="selectedCampaign"
      :target-audience="targetAudience"
      @close="showModal = false"
      @confirm="dispatchCampaign"
    />

    <EmailPreviewModal
      :is-open="showPreviewModal"
      :campaign-name="selectedCampaign?.name"
      :preview-html="previewHtml"
      @close="showPreviewModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/services/api";

import EmailCampaignList from "@/components/admin/features/emails/EmailCampaignList.vue";
import EmailBroadcastPanel from "@/components/admin/features/emails/EmailBroadcastPanel.vue";
import EmailDispatchModal from "@/components/admin/features/emails/EmailDispatchModal.vue";
import EmailPreviewModal from "@/components/admin/features/emails/EmailPreviewModal.vue";

const { t } = useI18n();
const toast = useToast();

const campaigns = ref([]);
const selectedCampaign = ref(null);
const targetAudience = ref("all");
const loading = ref(false);
const loadingPreview = ref(false);
const showModal = ref(false);
const showPreviewModal = ref(false);
const previewHtml = ref("");

const init = async () => {
  try {
    const resp = await api.get("/admin/emails/campaigns");
    campaigns.value = resp.data.data.campaigns;
    if (campaigns.value.length > 0) {
      selectedCampaign.value = campaigns.value[0];
      targetAudience.value = selectedCampaign.value.suggestedAudience;
    }
  } catch (err) {
    console.error("Failed to load campaigns:", err);
    toast.error(t("admin.emails.alerts.loadError"));
  }
};

const handleSelectCampaign = (campaign) => {
  selectedCampaign.value = campaign;
  targetAudience.value = campaign.suggestedAudience;
};

const confirmBroadcast = () => {
  showModal.value = true;
};

const previewCampaign = async () => {
  if (!selectedCampaign.value) return;

  loadingPreview.value = true;
  try {
    const resp = await api.post("/admin/emails/preview", {
      campaignClass: selectedCampaign.value.class,
    });
    previewHtml.value = resp.data;
    showPreviewModal.value = true;
  } catch (err) {
    console.error("Failed to load preview:", err);
    toast.error(t("admin.emails.alerts.previewError"));
  } finally {
    loadingPreview.value = false;
  }
};

const dispatchCampaign = async () => {
  showModal.value = false;
  loading.value = true;
  try {
    const resp = await api.post("/admin/emails/broadcast", {
      campaignClass: selectedCampaign.value.class,
      audience: targetAudience.value,
    });
    toast.success(
      resp.data.message || t("admin.emails.alerts.dispatchSuccess"),
    );
  } catch (err) {
    console.error("Failed to dispatch campaign:", err);
    toast.error(t("admin.emails.alerts.dispatchError"));
  } finally {
    loading.value = false;
  }
};

onMounted(init);
</script>
