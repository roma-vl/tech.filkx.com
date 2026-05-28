<script setup>
import { ref, onMounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import AppCard from "@/components/application/ui/Data/AppCard.vue";

const { t } = useI18n();

const activeTab = ref("frontend");
const content = ref("");
const loading = ref(false);

const tabs = [
  { id: "frontend", label: "Користувацька документація" },
  { id: "admin", label: "Адміністративна документація" },
];

const parseMarkdown = (text) => {
  if (!text) return "";

  let html = text
    .replace(/^# (.*$)/gim, '<h1 class="text-3xl font-bold mt-8 mb-4">$1</h1>')
    .replace(
      /^## (.*$)/gim,
      '<h2 class="text-2xl font-semibold mt-6 mb-3">$1</h2>',
    )
    .replace(/\*\*(.*)\*\*/gim, "<strong>$1</strong>")
    .replace(/^\- (.*$)/gim, '<li class="ml-4 list-disc">$1</li>')
    .replace(
      /^---$/gim,
      '<hr class="my-8 border-gray-200 dark:border-gray-700">',
    )
    .split(/\n\n/g)
    .map((p) => {
      if (p.trim().startsWith("<")) return p;
      return `<p class="mb-4 text-gray-600 dark:text-gray-300">${p}</p>`;
    })
    .join("\n");
  return html;
};

const fetchDocs = async () => {
  loading.value = true;
  try {
    const filename =
      activeTab.value === "frontend" ? "user-guide.uk.md" : "admin-guide.uk.md";
    const response = await fetch(`/docs/${filename}?t=${Date.now()}`);
    if (!response.ok) throw new Error("Failed to load docs");
    const text = await response.text();
    content.value = parseMarkdown(text);
  } catch (e) {
    console.error(e);
    content.value =
      "<p class='text-red-500'>Не вдалося завантажити документацію. Перевірте наявність файлів.</p>";
  } finally {
    loading.value = false;
  }
};

const print = () => {
  window.print();
};

watch(activeTab, fetchDocs);

onMounted(() => {
  fetchDocs();
});
</script>

<template>
  <div class="p-6 max-w-5xl mx-auto admin-docs-page">
    <div class="flex justify-between items-center mb-6 no-print">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ t("admin.docs.title", "Документація") }}
      </h1>
      <AppButton
        variant="secondary"
        @click="print"
      >
        Експорт в PDF / Друк
      </AppButton>
    </div>

    <div
      class="flex space-x-1 mb-6 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl w-fit no-print"
    >
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
        :class="
          activeTab === tab.id
            ? 'bg-white dark:bg-gray-700 text-primary-600 shadow-sm'
            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'
        "
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <AppCard class="print-content">
      <div
        v-if="loading"
        class="p-8 text-center text-gray-500"
      >
        Завантаження...
      </div>
      <div
        v-else
        class="prose dark:prose-invert max-w-none p-6 md:p-10"
        v-html="content"
      />
    </AppCard>
  </div>
</template>

<style>
@media print {
  @page {
    size: auto;
    margin: 20mm;
  }

  body {
    visibility: hidden;
    background: white !important;
  }

  nav,
  aside,
  header,
  footer,
  .no-print {
    display: none !important;
  }

  .admin-docs-page {
    visibility: visible;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    min-width: 100%;
    margin: 0 !important;
    padding: 0 !important;
    max-width: none !important;
    background: white;
    z-index: 9999;
  }

  .admin-docs-page * {
    visibility: visible;
  }

  .print-content {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: none !important;
  }

  /* Reset prose constraints */
  .prose {
    max-width: none !important;
    width: 100% !important;
    color: black !important;
  }

  .prose h1,
  .prose h2,
  .prose h3,
  .prose p,
  .prose ul,
  .prose li {
    color: black !important;
    page-break-inside: avoid;
  }
}
</style>
