<script setup lang="ts">
import toast from '@/Stores/toast'
import type { Project } from '@/types'
import { useForm } from '@inertiajs/vue3'
import { IconPlus } from '@tabler/icons-vue'
import { nextTick, ref } from 'vue'

const props = defineProps<{
  project: Project
}>()

const formShowing = ref(false)
const nameRef = ref()
const formRef = ref()

function focusInput() {
  nameRef.value.focus()
  formRef.value.scrollIntoView()
}

async function showForm() {
  formShowing.value = true
  await nextTick()
  focusInput()
}

const form = useForm({
  name: '',
})

function onSubmit() {
  form.post(route('boards.store', props.project.id), {
    onError: (err) => {
      form.reset()

      toast.add({
        title: 'Resolve errors',
        type: 'warning',
        message: err.name,
      })
    },

    onSuccess: () => {
      form.reset()
      focusInput()
    },
  })
}
</script>

<template>
  <form v-if="formShowing" ref="formRef" @submit.prevent="onSubmit()" @keydown.esc="formShowing = false">
    <input
      ref="nameRef"
      v-model="form.name"
      type="text"
      placeholder="Type board name"
      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-lime-600 focus:border-lime-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-lime-500 dark:focus:border-lime-500"
    >

    <div class="flex items-center justify-between gap-2 mt-2">
      <button type="submit" class="flex items-center justify-center w-full gap-2 px-3 py-2 font-semibold text-white transition duration-300 rounded-md bg-white/10 hover:bg-white/20">
        <IconPlus class="w-5 h-5" />
        <span>Create</span>
      </button>

      <button type="button" class="w-full px-3 py-2 font-semibold text-white transition duration-300 hover:text-white/20" @click="formShowing = false">
        <span>Cancel</span>
      </button>
    </div>
  </form>

  <button v-else class="flex items-center w-full gap-2 px-3 py-2 font-semibold text-white transition duration-300 rounded-md bg-white/10 hover:bg-white/20" @click="showForm">
    <IconPlus class="w-5 h-5" />
    <span>Add board</span>
  </button>
</template>
