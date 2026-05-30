<template>
  <div
    class="p-8 border-t border-gray-100 dark:border-gray-700/50 bg-white dark:bg-gray-800"
  >
    <div class="relative group">
      <textarea
        ref="textareaRef"
        :value="message"
        :placeholder="
          isInternal
            ? t('admin.support.chat.type_note')
            : t('admin.support.chat.type_response')
        "
        :class="[
          'w-full px-6 py-5 pb-24 rounded-[2rem] border outline-none resize-none text-sm font-medium transition-all duration-300 shadow-sm',
          isInternal
            ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-900/30 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 text-amber-900 dark:text-amber-100'
            : 'bg-gray-50/50 dark:bg-gray-900 border-gray-100 dark:border-gray-700/50 focus:ring-4 focus:ring-primary-500/10 focus:bg-white dark:focus:bg-gray-800/50 focus:border-primary-500 group-hover:border-gray-200 dark:group-hover:border-gray-600',
        ]"
        rows="3"
        @input="handleInput"
        @keydown.enter.prevent="handleSend"
      />
      <div class="absolute bottom-6 left-6 right-6 flex flex-col gap-5">
        <div
          v-if="selectedFile"
          class="flex items-center gap-3 p-3 bg-primary-50 dark:bg-primary-900/10 rounded-2xl text-xs border border-primary-100 dark:border-primary-900/30 backdrop-blur-sm animate-in slide-in-from-bottom-2 duration-300"
        >
          <span
            class="truncate max-w-[250px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-tighter"
          >{{ selectedFile.name }}</span>
          <AppButton
            variant="ghost"
            class="!text-rose-500 !font-black !ml-auto !p-1.5 hover:!bg-rose-50 dark:hover:!bg-rose-900/20 !rounded-xl !transition-all hover:scale-110"
            @click="$emit('update:selectedFile', null)"
          >
            ✕
          </AppButton>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex gap-4">
            <input
              ref="fileInput"
              type="file"
              class="hidden"
              accept="image/*,video/*"
              @change="handleFileUpload"
            >
            <AppButton
              variant="white"
              class="!p-3 !text-gray-400 hover:!text-primary-600 hover:!bg-primary-50 dark:hover:!bg-primary-900/10 !rounded-2xl !transition-all !border-none !shadow-none"
              :title="t('admin.support.chat.attach_file')"
              @click="$refs.fileInput.click()"
            >
              <PaperClipIcon class="w-5 h-5" />
            </AppButton>

            <div class="relative">
              <AppButton
                variant="white"
                class="!p-3 !text-gray-400 hover:!text-primary-600 hover:!bg-primary-50 dark:hover:!bg-primary-900/10 !rounded-2xl !transition-all !border-none !shadow-none"
                :title="t('admin.support.chat.snippets')"
                @click.stop="showSnippets = !showSnippets"
              >
                <CommandLineIcon class="w-5 h-5" />
              </AppButton>

              <!-- Snippets Dropdown -->
              <div
                v-if="showSnippets"
                v-click-outside="() => (showSnippets = false)"
                class="absolute bottom-full left-0 mb-4 w-72 max-h-[400px] overflow-y-auto bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-[1.5rem] shadow-2xl z-50 p-3 space-y-1.5 animate-in fade-in slide-in-from-bottom-2 duration-300 custom-scrollbar"
                @click.stop
              >
                <div
                  v-if="snippets.length === 0"
                  class="p-8 text-center"
                >
                  <CommandLineIcon class="w-8 h-8 text-gray-200 mx-auto mb-2" />
                  <p
                    class="text-[10px] uppercase font-black text-gray-400 tracking-widest leading-none"
                  >
                    {{ t("admin.support.chat.no_snippets") }}
                  </p>
                </div>
                <AppButton
                  v-for="snippet in snippets"
                  :key="snippet.id"
                  variant="white"
                  class="!w-full !text-left !p-3.5 !rounded-xl hover:!bg-gray-50 dark:hover:!bg-gray-900 !border !border-transparent hover:!border-gray-100 dark:hover:!border-gray-700 !transition-all !shadow-none !justify-start !block group"
                  @click.stop.prevent="applySnippet(snippet)"
                >
                  <p
                    class="text-[10px] font-black uppercase text-gray-400 group-hover:text-primary-600 tracking-widest mb-1"
                  >
                    {{ snippet.title }}
                  </p>
                  <p
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate leading-relaxed"
                  >
                    {{ snippet.content }}
                  </p>
                </AppButton>
              </div>
            </div>

            <AppButton
              variant="white"
              class="!p-3 !rounded-2xl !transition-all !px-4 !border !shadow-none"
              :class="
                isInternal
                  ? '!bg-amber-500  !border-amber-600 !shadow-lg !shadow-amber-500/20 !text-gray-200'
                  : 'hover:!text-amber-600 hover:!bg-amber-50 dark:hover:!bg-amber-900/10 !text-gray-400 !border-transparent'
              "
              :title="t('admin.support.chat.internal_note')"
              @click="isInternal = !isInternal"
            >
              <template #prefix>
                <EyeSlashIcon class="w-4 h-4 mr-2" />
              </template>
              <span class="text-[10px] font-black uppercase tracking-widest">{{
                t("admin.support.chat.note")
              }}</span>
            </AppButton>
          </div>
          <AppButton
            :disabled="(!message && !selectedFile) || loading"
            :loading="loading"
            class="!px-8 !py-3.5 !rounded-2xl !text-[11px] !font-black !transition-all disabled:!opacity-30 disabled:!grayscale !uppercase !tracking-widest !shadow-xl hover:!scale-105 active:!scale-95 group"
            :variant="isInternal ? 'danger' : 'primary'"
            :class="
              isInternal
                ? '!bg-amber-600 !text-white !shadow-amber-500/25 hover:!bg-amber-700'
                : '!bg-primary-600 !text-white !shadow-primary-500/25 hover:!bg-primary-700'
            "
            @click="handleSend"
          >
            <template #prefix>
              <PaperAirplaneIcon
                class="w-4 h-4 -rotate-12 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform mr-2"
              />
            </template>
            {{
              isInternal
                ? t("admin.support.actions.save_note")
                : t("admin.support.actions.send_reply")
            }}
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick, ref, watch } from "vue";
import {
  CommandLineIcon,
  EyeSlashIcon,
  PaperAirplaneIcon,
  PaperClipIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();
const toast = useToast();

const props = defineProps({
  message: {
    type: String,
    default: "",
  },
  loading: {
    type: Boolean,
    default: false,
  },
  snippets: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:message", "send", "file-selected"]);

const textareaRef = ref(null);
const fileInput = ref(null);
const selectedFile = ref(null);
const isInternal = ref(false);
const showSnippets = ref(false);

const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener("click", el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener("click", el.clickOutsideEvent);
  },
};

const autoResize = () => {
  if (textareaRef.value) {
    textareaRef.value.style.height = "auto";
    const newHeight = Math.min(
      Math.max(textareaRef.value.scrollHeight, 80),
      300,
    );
    textareaRef.value.style.height = newHeight + "px";
  }
};

const handleInput = (event) => {
  emit("update:message", event.target.value);
  nextTick(() => autoResize());
};

watch(
  () => props.message,
  () => {
    nextTick(() => autoResize());
  },
);

const handleSend = () => {
  emit("send", isInternal.value);
  isInternal.value = false;
};

const applySnippet = (snippet) => {
  emit("update:message", snippet.content);
  // Close dropdown after a small delay to ensure the event is processed
  setTimeout(() => {
    showSnippets.value = false;
  }, 50);
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const isImg = file.type.startsWith("image/");
  const isVid = file.type.startsWith("video/");

  if (isImg && file.size > 5 * 1024 * 1024) {
    toast.error(t("admin.support.alerts.imageSizeError"));
    return;
  }

  if (isVid && file.size > 100 * 1024 * 1024) {
    toast.error(t("admin.support.alerts.videoSizeError"));
    return;
  }

  emit("update:selectedFile", file);
};
</script>
