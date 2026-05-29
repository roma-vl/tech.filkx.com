<template>
  <div class="h-[calc(100vh-120px)] flex gap-6">
    <div
      class="w-full lg:w-1/4 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col"
    >
      <div class="p-5 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/50">
        <h2 class="text-xs font-black uppercase tracking-widest text-gray-400">
          {{ $t("admin.logs.server_logs.title") }}
        </h2>
      </div>

      <div class="overflow-y-auto flex-1 p-3 space-y-2 custom-scrollbar">
        <AppButton
          v-for="file in files"
          :key="file.name"
          variant="white"
          class="!w-full !justify-between !px-4 !py-3 !rounded-2xl !text-sm !border-none !shadow-none transition-all duration-200"
          :class="
            selectedFile?.name === file.name
              ? '!bg-primary-50 !text-primary-600 !ring-1 !ring-primary-200 dark:!bg-primary-900/10 dark:!text-primary-400 dark:!ring-primary-800/50'
              : 'hover:!bg-gray-50 dark:hover:!bg-gray-700/50 !text-gray-700 dark:!text-gray-300'
          "
          @click="selectFile(file)"
        >
          <div class="flex items-center gap-3 min-w-0">
            <DocumentTextIcon
              class="w-4 h-4 flex-shrink-0"
              :class="selectedFile?.name === file.name ? 'text-primary-500' : 'text-gray-400'"
            />
            <span class="truncate font-medium">{{ file.name }}</span>
          </div>
          <template #suffix>
            <span
              class="text-[10px] font-black text-gray-400 uppercase tracking-tighter bg-gray-100 dark:bg-gray-700/50 px-2 py-1 rounded-lg"
            >
              {{ formatSize(file.size) }}
            </span>
          </template>
        </AppButton>

        <div v-if="loadingList" class="flex flex-col gap-2 p-2">
           <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 dark:bg-gray-800/50 rounded-2xl animate-pulse"></div>
        </div>
      </div>
    </div>
    <div
      class="flex-1 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col"
    >
      <div
        class="p-4 border-b border-gray-100 dark:border-gray-700/50 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50"
      >
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
            <CommandLineIcon class="w-5 h-5 text-gray-400" />
          </div>
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white leading-tight">
              <span v-if="selectedFile">{{ selectedFile.name }}</span>
              <span v-else class="text-gray-400">
                {{ $t("admin.logs.server_logs.select_file") }}
              </span>
            </h3>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-0.5" v-if="selectedFile">
               {{ formatSize(selectedFile.size) }} &bull; {{ selectedFile.updated_at || 'Recently updated' }}
            </p>
          </div>
        </div>

        <div
          v-if="selectedFile"
          class="flex items-center gap-2"
        >
          <AppButton
            variant="secondary"
            :loading="loadingDetails"
            @click="refreshLog"
          >
            <template #prefix>
              <ArrowPathIcon class="w-3.5 h-3.5 mr-2" />
            </template>
            {{ $t("admin.logs.server_logs.refresh") }}
          </AppButton>

          <AppButton
            variant="danger"
            :loading="clearingLog"
            @click="clearLog"
          >
            <template #prefix>
              <TrashIcon class="w-3.5 h-3.5 mr-2" />
            </template>
            {{ $t("admin.logs.server_logs.clear_log") }}
          </AppButton>
        </div>
      </div>

      <div
        class="flex-1 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 font-mono text-sm relative"
      >
        <div
          v-if="!selectedFile"
          class="h-full flex flex-col items-center justify-center text-gray-400"
        >
          <InboxIcon class="w-12 h-12 mb-3 opacity-20" />
          <p>{{ $t("admin.logs.server_logs.select_file_desc") }}</p>
        </div>

        <div
          v-else-if="loadingDetails"
          class="h-full flex items-center justify-center"
        />

        <div
          v-else
          class="space-y-4"
        >
          <div
            v-for="(entry, index) in parsedLogs"
            :key="index"
            class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50 relative overflow-hidden group transition-all hover:shadow-md"
          >
            <div
              class="absolute left-0 top-0 bottom-0 w-1"
              :class="getBgColor(entry.level)"
            ></div>

            <div class="flex items-start justify-between gap-4 mb-3">
              <div class="flex items-center gap-3 flex-wrap">
                <span
                  class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider shadow-sm"
                  :class="getBadgeColor(entry.level)"
                >
                  {{ entry.level }}
                </span>
                <div class="flex items-center gap-1.5 text-gray-400">
                  <ClockIcon class="w-3.5 h-3.5" />
                  <span class="text-xs font-semibold">{{ entry.date }}</span>
                </div>
                <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-gray-50 dark:bg-gray-700/30 text-gray-400 uppercase tracking-widest">{{ entry.env }}</span>
              </div>
            </div>

            <div class="relative">
              <p
                class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed whitespace-pre-wrap break-words font-medium"
              >
                {{ entry.message }}
              </p>
            </div>

            <div
              v-if="entry.stack"
              class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-700/50"
            >
              <AppButton
                variant="secondary"
                class="!px-3 !py-1.5 !text-[10px] !font-black !uppercase !tracking-widest"
                @click="entry.expanded = !entry.expanded"
              >
                <template #prefix>
                  <ChevronRightIcon
                    class="w-3.5 h-3.5 mr-2 transition-transform duration-200"
                    :class="{ 'rotate-90': entry.expanded }"
                  />
                </template>
                {{
                  entry.expanded
                    ? $t("common.hide_stack")
                    : $t("common.show_stack")
                }}
              </AppButton>

              <div
                v-show="entry.expanded"
                class="mt-3 p-4 bg-gray-900 rounded-2xl text-gray-300 text-[11px] overflow-x-auto whitespace-pre font-mono custom-scrollbar leading-relaxed ring-1 ring-inset ring-white/10"
              >
                {{ entry.stack }}
              </div>
            </div>
          </div>

          <div
            v-if="parsedLogs.length === 0"
            class="text-center text-gray-500 py-10"
          >
            {{ $t("admin.logs.server_logs.empty_file") }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";
import {
  InboxIcon,
  ChevronRightIcon,
  TrashIcon,
  DocumentTextIcon,
  CommandLineIcon,
  ArrowPathIcon,
  ClockIcon
} from "@heroicons/vue/24/outline";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const files = ref([]);
const selectedFile = ref(null);
const logContent = ref("");
const loadingList = ref(false);
const loadingDetails = ref(false);
const clearingLog = ref(false);

const fetchFiles = async () => {
  loadingList.value = true;
  try {
    const { data } = await api.get("/admin/server-logs");
    files.value = data.data;
  } catch (e) {
    console.error("Failed to fetch log files", e);
  } finally {
    loadingList.value = false;
  }
};

const selectFile = async (file) => {
  selectedFile.value = file;
  await fetchLogContent(file.name);
};

const refreshLog = () => {
  if (selectedFile.value) {
    fetchLogContent(selectedFile.value.name);
  }
};

const clearLog = async () => {
  if (!selectedFile.value) return;

  if (!confirm(t("admin.logs.server_logs.clear_confirm"))) return;

  clearingLog.value = true;
  try {
    await api.delete(`/admin/server-logs/${selectedFile.value.name}`);
    logContent.value = "";
    const file = files.value.find((f) => f.name === selectedFile.value.name);
    if (file) file.size = 0;
  } catch (e) {
    console.error("Failed to clear log content", e);
    alert(t("admin.logs.server_logs.clear_error"));
  } finally {
    clearingLog.value = false;
  }
};

const fetchLogContent = async (filename) => {
  loadingDetails.value = true;
  try {
    const { data } = await api.get(`/admin/server-logs/${filename}`);
    logContent.value = data.data.content;
  } catch (e) {
    console.error("Failed to fetch log content", e);
    logContent.value = "";
  } finally {
    loadingDetails.value = false;
  }
};

const formatSize = (bytes) => {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const getBgColor = (level) => {
  const colors = {
    ERROR: "bg-red-500",
    CRITICAL: "bg-red-700",
    WARNING: "bg-amber-500",
    INFO: "bg-primary-500",
    DEBUG: "bg-gray-500",
    EMERGENCY: "bg-purple-600",
    ALERT: "bg-pink-600",
    NOTICE: "bg-indigo-500",
  };
  return colors[level] || "bg-gray-300";
};

const getBadgeColor = (level) => {
  const colors = {
    ERROR: "bg-red-50 text-red-600 dark:bg-red-900/10 dark:text-red-400",
    CRITICAL: "bg-red-100 text-red-700 dark:bg-red-800/40 dark:text-red-300",
    WARNING:
      "bg-amber-50 text-amber-600 dark:bg-amber-900/10 dark:text-amber-400",
    INFO: "bg-primary-50 text-primary-600 dark:bg-primary-900/10 dark:text-primary-400",
    DEBUG: "bg-gray-50 text-gray-600 dark:bg-gray-700/50 dark:text-gray-300",
    EMERGENCY:
      "bg-purple-50 text-purple-600 dark:bg-purple-900/10 dark:text-purple-400",
    ALERT: "bg-pink-50 text-pink-600 dark:bg-pink-900/10 dark:text-pink-400",
    NOTICE:
      "bg-indigo-50 text-indigo-600 dark:bg-indigo-900/10 dark:text-indigo-400",
  };
  return colors[level] || "bg-gray-100 text-gray-600";
};

const parsedLogs = computed(() => {
  if (!logContent.value) return [];

  const standardLogPattern =
    /^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+): ([\s\S]*?)(?=(^\[\d{4}-\d{2}-\d{2})|$)/gm;

  const schedulerLogPattern =
    /^ {2}(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) ([\s\S]*?)(?=(^ {2}\d{4}-\d{2}-\d{2})|$)/gm;

  const entries = [];

  let match;
  let hasStandardMatches = false;

  while ((match = standardLogPattern.exec(logContent.value)) !== null) {
    hasStandardMatches = true;
    processMatch(match[1], match[2], match[3], match[4]);
  }

  if (!hasStandardMatches) {
    while ((match = schedulerLogPattern.exec(logContent.value)) !== null) {
      processMatch(match[1], "scheduler", "INFO", match[2]);
    }

    if (entries.length === 0) {
      const lines = logContent.value.split("\n");
      lines.forEach((line) => {
        if (line.trim()) {
          const dateMatch = line.match(
            /^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/,
          );
          if (dateMatch) {
            processMatch(dateMatch[1], "system", "INFO", line.substring(19));
          }
        }
      });
    }
  }

  function processMatch(date, env, level, fullMessage) {
    fullMessage = fullMessage.trim();
    let message = fullMessage;
    let stack = "";

    const stackStart = fullMessage.indexOf("[stacktrace]");
    if (stackStart !== -1) {
      message = fullMessage.substring(0, stackStart).trim();
      stack = fullMessage.substring(stackStart).trim();
    } else {
      const fallBackStack = fullMessage.match(/\n#0 /);
      if (fallBackStack) {
        message = fullMessage.substring(0, fallBackStack.index).trim();
        stack = fullMessage.substring(fallBackStack.index).trim();
      }
    }

    entries.unshift({
      date: date,
      env: env,
      level: level,
      message,
      stack,
      expanded: false,
    });
  }

  return entries;
});

onMounted(() => {
  fetchFiles();
});
</script>
