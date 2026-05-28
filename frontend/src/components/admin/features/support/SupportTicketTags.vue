<template>
  <div class="flex flex-wrap items-center gap-2">
    <!-- Tag List -->
    <div
      v-for="tag in (tags || [])"
      :key="tag"
      class="group relative flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/50 rounded-xl shadow-sm hover:border-primary-500/30 transition-all duration-300"
    >
      <TagIcon class="w-3.5 h-3.5 text-primary-500 opacity-50"/>
      <span
        class="text-[9px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 group-hover:text-primary-500 transition-colors"
      >{{ tag }}</span>
      <AppButton
        variant="ghost"
        class="!ml-1.5 !p-0 !min-h-0 !h-auto !text-gray-300 hover:!text-rose-500 !transition-colors"
        @click.stop="removeTag(tag)"
      >
        <span class="text-xs">×</span>
      </AppButton>
    </div>

    <!-- Add Tag Dropdown -->
    <div class="relative">
      <AppButton
        variant="white"
        class="!w-8 !h-8 !flex !items-center !justify-center !p-0 !bg-white dark:!bg-gray-800 !rounded-lg !border !border-gray-100 dark:!border-gray-700/50 hover:!border-primary-500 hover:!text-primary-600 !transition-all !text-gray-300 group !shadow-sm active:!scale-90"
        @click.stop="showTagActions = !showTagActions"
      >
        <PlusIcon
          class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300"
        />
      </AppButton>

      <div
        v-if="showTagActions"
        v-click-outside="() => (showTagActions = false)"
        class="absolute top-full sm:right-0 mt-3 w-64 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-[2rem] shadow-2xl z-50 p-4 space-y-4 animate-in fade-in slide-in-from-top-2 duration-300"
      >
        <!-- Custom Tag Input -->
        <div class="space-y-2">
          <p class="text-[8px] font-black uppercase tracking-[0.2em] text-gray-400 px-1">New Tag</p>
          <div class="flex gap-2">
            <AppInput
              v-model="customTag"
              type="text"
              placeholder="Type tag..."
              @keyup.enter="addCustomTag"
              @click.stop
            />
            <AppButton
              variant="success"
              :disabled="!customTag.trim()"
              @click.stop="addCustomTag"
            >
              <PlusIcon class="w-4 h-4" />
            </AppButton>
          </div>
        </div>

        <div class="h-px bg-gray-100 dark:bg-gray-700" />

        <div class="space-y-1">
          <p class="text-[8px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 px-1">Presets</p>
          <div class="max-h-40 overflow-y-auto custom-scrollbar flex flex-wrap gap-1">
            <AppButton
              v-for="availableTag in availableTags"
              :key="availableTag"
              variant="white"
              :disabled="tags?.includes(availableTag)"
              @click.stop="addTag(availableTag)"
            >
              {{ availableTag }}
            </AppButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { PlusIcon, TagIcon } from "@heroicons/vue/24/outline";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";

const props = defineProps({
  tags: {
    type: Array,
    default: () => [],
  },
  availableTags: {
    type: Array,
    default: () => ["Bug", "Billing", "Feedback", "Urgent", "Question", "Feature", "Low Priority"],
  },
});

const emit = defineEmits(["update:tags"]);

const showTagActions = ref(false);
const customTag = ref("");

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

const addTag = (tag) => {
  const newTags = [...(props.tags || [])];
  if (!newTags.includes(tag)) {
    newTags.push(tag);
    emit("update:tags", newTags);
  }
  showTagActions.value = false;
};

const addCustomTag = () => {
  if (!customTag.value.trim()) return;
  addTag(customTag.value.trim());
  customTag.value = "";
};

const removeTag = (tag) => {
  const newTags = (props.tags || []).filter((t) => t !== tag);
  emit("update:tags", newTags);
};
</script>
