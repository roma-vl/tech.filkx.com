<template>
  <div class="rich-editor rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-0.5 px-3 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
      <!-- Text formatting -->
      <ToolbarButton
        :active="editor?.isActive('bold')"
        title="Жирний"
        @click="editor.chain().focus().toggleBold().run()"
      >
        <BoldIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('italic')"
        title="Курсив"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        <ItalicIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('underline')"
        title="Підкреслений"
        @click="editor.chain().focus().toggleUnderline().run()"
      >
        <UnderlineIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('strike')"
        title="Закреслений"
        @click="editor.chain().focus().toggleStrike().run()"
      >
        <StrikeIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('highlight')"
        title="Підсвічування"
        @click="editor.chain().focus().toggleHighlight().run()"
      >
        <HighlightIcon />
      </ToolbarButton>

      <div class="w-px h-5 bg-gray-200 dark:bg-gray-600 mx-1" />

      <!-- Headings -->
      <ToolbarButton
        v-for="level in [1, 2, 3]"
        :key="level"
        :active="editor?.isActive('heading', { level })"
        :title="`Заголовок ${level}`"
        class="text-xs font-bold"
        @click="editor.chain().focus().toggleHeading({ level }).run()"
      >
        H{{ level }}
      </ToolbarButton>

      <div class="w-px h-5 bg-gray-200 dark:bg-gray-600 mx-1" />

      <!-- Lists -->
      <ToolbarButton
        :active="editor?.isActive('bulletList')"
        title="Маркований список"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        <ListBulletIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('orderedList')"
        title="Нумерований список"
        @click="editor.chain().focus().toggleOrderedList().run()"
      >
        <ListOrderedIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('blockquote')"
        title="Цитата"
        @click="editor.chain().focus().toggleBlockquote().run()"
      >
        <QuoteIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive('codeBlock')"
        title="Код"
        @click="editor.chain().focus().toggleCodeBlock().run()"
      >
        <CodeIcon />
      </ToolbarButton>

      <div class="w-px h-5 bg-gray-200 dark:bg-gray-600 mx-1" />

      <!-- Alignment -->
      <ToolbarButton
        :active="editor?.isActive({ textAlign: 'left' })"
        title="По лівому"
        @click="editor.chain().focus().setTextAlign('left').run()"
      >
        <AlignLeftIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive({ textAlign: 'center' })"
        title="По центру"
        @click="editor.chain().focus().setTextAlign('center').run()"
      >
        <AlignCenterIcon />
      </ToolbarButton>
      <ToolbarButton
        :active="editor?.isActive({ textAlign: 'right' })"
        title="По правому"
        @click="editor.chain().focus().setTextAlign('right').run()"
      >
        <AlignRightIcon />
      </ToolbarButton>

      <div class="w-px h-5 bg-gray-200 dark:bg-gray-600 mx-1" />

      <!-- Link -->
      <ToolbarButton
        :active="editor?.isActive('link')"
        title="Посилання"
        @click="setLink"
      >
        <LinkIcon />
      </ToolbarButton>

      <!-- Image upload -->
      <label
        class="toolbar-btn cursor-pointer"
        title="Вставити зображення"
      >
        <ImageIcon />
        <input
          type="file"
          accept="image/*"
          class="sr-only"
          @change="insertImage"
        >
      </label>

      <div class="w-px h-5 bg-gray-200 dark:bg-gray-600 mx-1" />

      <!-- Undo / Redo -->
      <ToolbarButton
        title="Скасувати"
        @click="editor.chain().focus().undo().run()"
      >
        <UndoIcon />
      </ToolbarButton>
      <ToolbarButton
        title="Повторити"
        @click="editor.chain().focus().redo().run()"
      >
        <RedoIcon />
      </ToolbarButton>

      <ToolbarButton
        title="Роздільник"
        @click="editor.chain().focus().setHorizontalRule().run()"
      >
        <HrIcon />
      </ToolbarButton>
    </div>

    <!-- Editor content -->
    <EditorContent
      :editor="editor"
      class="prose prose-sm dark:prose-invert max-w-none min-h-[320px] px-5 py-4 text-gray-800 dark:text-gray-100 focus-within:outline-none"
    />

    <!-- Word count -->
    <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex items-center justify-between">
      <span class="text-xs text-gray-400">
        {{ editor?.storage.characterCount?.words() ?? 0 }} слів ·
        {{ editor?.storage.characterCount?.characters() ?? 0 }} символів
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount, defineComponent, h } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Highlight from '@tiptap/extension-highlight';
import Placeholder from '@tiptap/extension-placeholder';

const props = defineProps({
  modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'upload-image']);

const editor = useEditor({
  content: props.modelValue || '',
  extensions: [
    StarterKit,
    Underline,
    TextAlign.configure({ types: ['heading', 'paragraph'] }),
    Image.configure({ inline: false, allowBase64: true }),
    Link.configure({ openOnClick: false }),
    Highlight,
    Placeholder.configure({ placeholder: 'Почніть писати тут...' }),
  ],
  onUpdate({ editor }) {
    emit('update:modelValue', editor.getHTML());
  },
  editorProps: {
    attributes: {
      class: 'outline-none',
    },
  },
});

watch(() => props.modelValue, (val) => {
  const current = editor.value?.getHTML();
  if (val !== current) {
    editor.value?.commands.setContent(val || '', false);
  }
});

const setLink = () => {
  const prev = editor.value?.getAttributes('link').href || '';
  const url = prompt('URL посилання:', prev);
  if (url === null) return;
  if (!url) {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
  } else {
    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
  }
};

const insertImage = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  emit('upload-image', file, (url) => {
    editor.value?.chain().focus().setImage({ src: url }).run();
  });
  e.target.value = '';
};

onBeforeUnmount(() => {
  editor.value?.destroy();
});

// ─── SVG icon components ──────────────────────────────────────────────────────
const makeIcon = (paths) => defineComponent({ render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', class: 'w-4 h-4' }, paths.map(d => h('path', { d }))) });
const BoldIcon = makeIcon(['M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z', 'M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z']);
const ItalicIcon = makeIcon(['M19 4h-9M14 20H5M15 4L9 20']);
const UnderlineIcon = makeIcon(['M6 3v7a6 6 0 0 0 12 0V3', 'M4 21h16']);
const StrikeIcon = makeIcon(['M16 4H9a3 3 0 0 0-2.83 4', 'M14 12a4 4 0 0 1 0 8H6', 'M4 12h16']);
const HighlightIcon = makeIcon(['m9 11-6 6v3h3l6-6', 'm22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4']);
const ListBulletIcon = makeIcon(['M8 6h13', 'M8 12h13', 'M8 18h13', 'M3 6h.01', 'M3 12h.01', 'M3 18h.01']);
const ListOrderedIcon = makeIcon(['M10 6h11', 'M10 12h11', 'M10 18h11', 'M4 6h1v4', 'M4 10H6', 'M6 18H4c0-1 2-2 2-3s-1-1.5-2-1']);
const QuoteIcon = makeIcon(['M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z', 'M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z']);
const CodeIcon = makeIcon(['m16 18 6-6-6-6', 'm8 6-6 6 6 6']);
const AlignLeftIcon = makeIcon(['M15 12H3', 'M17 6H3', 'M13 18H3']);
const AlignCenterIcon = makeIcon(['M17 12H7', 'M19 6H5', 'M15 18H9']);
const AlignRightIcon = makeIcon(['M21 12H9', 'M21 6H7', 'M21 18H11']);
const LinkIcon = makeIcon(['M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71', 'M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71']);
const ImageIcon = makeIcon(['M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7', 'm16 2 6 6', 'M22 2l-6 6', 'M15 22l5-5', 'M3 15l5-5']);
const UndoIcon = makeIcon(['M3 7v6h6', 'M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13']);
const RedoIcon = makeIcon(['M21 7v6h-6', 'M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3L21 13']);
const HrIcon = makeIcon(['M5 12h14']);

// ─── ToolbarButton helper component ──────────────────────────────────────────
const ToolbarButton = defineComponent({
  props: { active: Boolean },
  emits: ['click'],
  setup(props, { slots, emit }) {
    return () => h('button', {
      type: 'button',
      onClick: () => emit('click'),
      class: [
        'toolbar-btn',
        props.active ? 'toolbar-btn--active' : '',
      ].join(' '),
    }, slots.default?.());
  },
});
</script>

<style scoped>
.toolbar-btn {
  @apply flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 hover:text-gray-800 dark:hover:text-gray-200 transition-colors text-sm font-semibold;
}
.toolbar-btn--active {
  @apply bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400;
}
</style>

<style>
/* Tiptap prose styles */
.ProseMirror {
  outline: none;
  min-height: 320px;
}
.ProseMirror p.is-editor-empty:first-child::before {
  color: #9ca3af;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
.ProseMirror h1 { @apply text-2xl font-bold mb-3 mt-5; }
.ProseMirror h2 { @apply text-xl font-bold mb-2 mt-4; }
.ProseMirror h3 { @apply text-lg font-bold mb-2 mt-3; }
.ProseMirror p  { @apply mb-3 leading-relaxed; }
.ProseMirror ul { @apply list-disc pl-5 mb-3 space-y-1; }
.ProseMirror ol { @apply list-decimal pl-5 mb-3 space-y-1; }
.ProseMirror blockquote {
  @apply border-l-4 border-emerald-400 pl-4 italic text-gray-500 dark:text-gray-400 my-4;
}
.ProseMirror pre {
  @apply bg-gray-900 text-gray-100 rounded-xl p-4 font-mono text-sm mb-4 overflow-x-auto;
}
.ProseMirror code { @apply bg-gray-100 dark:bg-gray-700 text-rose-500 dark:text-rose-400 rounded px-1.5 py-0.5 text-xs font-mono; }
.ProseMirror pre code { @apply bg-transparent text-inherit; }
.ProseMirror hr { @apply border-gray-200 dark:border-gray-600 my-5; }
.ProseMirror img { @apply rounded-xl max-w-full my-4 mx-auto; }
.ProseMirror a { @apply text-emerald-600 dark:text-emerald-400 underline; }
mark { @apply bg-yellow-200 dark:bg-yellow-800 rounded px-0.5; }
</style>
