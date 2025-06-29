<script setup>
import {
  IconAlignCenter,
  IconAlignLeft,
  IconAlignRight,
  IconBaseline,
  IconBold,
  IconColorSwatchOff,
  IconH1,
  IconH2,
  IconH3,
  IconItalic,
  IconLetterP,
  IconListDetails,
  IconListNumbers,
  IconMinus,
  IconStrikethrough,
  IconUnderline,
} from "@tabler/icons-vue"

import Placeholder from "@tiptap/extension-placeholder"

import { useEditor, EditorContent } from "@tiptap/vue-3"

import StarterKit from "@tiptap/starter-kit"

import TextAlign from "@tiptap/extension-text-align"

import { onBeforeUnmount } from "vue"

import Underline from "@tiptap/extension-underline"

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },

  height: {
    type: String,
    default: ''
  },

  hasControls: {
    type: Boolean,
    default: true
  }
})

const placeholder = defineModel('placeholder', { default: 'Write some notes' })

const emits = defineEmits(["update:modelValue"])

const editor = useEditor({
  content: props.modelValue,

  onUpdate: () => {
    emits("update:modelValue", editor.value.getHTML())
  },

  editorProps: {
    attributes: {
      class:
        `border max-w-full border-gray-300 rounded-md dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 prose dark:prose-headings:text-gray-300 prose-headings:text-gray-800 mx-auto focus:outline-none p-4 shadow-sm scrollbar-none text-gray-900 overflow-y-auto scroll-smooth scrollbar-none`,
    },
  },

  extensions: [
    StarterKit.configure({
      horizontalRule: {
        HTMLAttributes: {
          class: 'border-gray-300 dark:border-gray-700',
        },
      }
    }),

    Underline,

    Placeholder.configure({
      placeholder: ({ node }) => {
        if (node.type.name === "heading") {
          return "Enter a title for the note"
        }

        return placeholder.value ?? "Write some notes"
      },
    }),

    TextAlign.configure({
      types: ["heading", "paragraph"],
    }),
  ],
})

onBeforeUnmount(() => {
  editor.value.destroy()
})
</script>

<template>
  <section
    v-if="editor && hasControls"
    class="flex mb-4 items-center justify-start gap-6 overflow-x-auto scrollbar-thin scroll-smooth"
  >
    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleBulletList().run()"
      :class="{
          'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('bulletList')
      }">
      <IconListDetails class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleOrderedList().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('orderedList')
      }"
    >
      <IconListNumbers class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleBold().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('bold')
      }"
    >
      <IconBold class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleItalic().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('italic')
      }"
    >
      <IconItalic class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleStrike().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('strike')
      }"
    >
      <IconStrikethrough class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleUnderline().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('underline')
      }"
    >
      <IconBaseline class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().setHorizontalRule().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('underline')
      }">
      <IconMinus class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().setParagraph().run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('paragraph')
      }"
    >
      <IconLetterP class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('heading', { level: 1 })
      }"
    >
      <IconH1 class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('heading', { level: 2 })
      }"
    >
      <IconH2 class="w-6 h-6" />
    </button>

    <button
      type="button"
      class="dark:text-gray-400"
      @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive('heading', { level: 3 })
      }"
    >
      <IconH3 class="w-6 h-6" />
    </button>

    <span class="flex-1"></span>

    <button
      class="dark:text-gray-400"
      @click="editor.chain().focus().setTextAlign('left').run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive({ textAlign: 'left' })
      }"
      type="button"
    >
      <IconAlignLeft :stroke="{ 2.5: editor.isActive({ textAlign: 'left' }) }" class="w-6 h-6" />
    </button>

    <button
      class="dark:text-gray-400"
      @click="editor.chain().focus().setTextAlign('center').run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive({ textAlign: 'center' })
      }"
      type="button"
    >
      <IconAlignCenter class="w-6 h-6" />
    </button>

    <button
      class="dark:text-gray-400"
      @click="editor.chain().focus().setTextAlign('right').run()"
      :class="{
        'bg-gray-300 dark:bg-gray-800 dark:text-gray-300 rounded-lg p-1': editor.isActive({ textAlign: 'right' })
      }"
      type="button"
    >
      <IconAlignRight class="w-6 h-6" />
    </button>
  </section>

  <EditorContent :editor="editor" />
</template>
