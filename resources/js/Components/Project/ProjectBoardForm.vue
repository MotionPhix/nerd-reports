<script setup lang="ts">
import toast from '@/Stores/toast'
import type { Project } from '@/types'
import { useForm } from '@inertiajs/vue3'
import { IconPlus } from '@tabler/icons-vue'
import { nextTick, ref } from 'vue'
import TextInput from '@/Components/TextInput.vue'

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

  form.post(route('boards.store', props.project.pid), {
    onError: (err) => {
      // form.reset()

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
  <form
    v-if="formShowing"
    ref="formRef"
    @submit.prevent="onSubmit()"
    @keydown.esc="formShowing = false">
    <TextInput
      ref="nameRef"
      v-model="form.name"
      type="text"
      placeholder="Enter a board name" />

    <div class="flex items-center justify-between gap-2 mt-2">
      <button type="submit" class="flex items-center justify-center w-full gap-2 px-3 py-3 font-semibold transition duration-300 bg-gray-300 rounded-md dark:text-white dark:bg-white/10 hover:bg-white/20">
        <IconPlus class="w-5 h-5" />
        <span>Create</span>
      </button>

      <button type="button" class="w-full px-3 py-3 font-semibold transition duration-300 border border-gray-300 rounded t dark:border-gray-700 dark:text-white hover:text-white/20" @click="formShowing = false">
        <span>Cancel</span>
      </button>
    </div>
  </form>

  <button
    v-else
    class="flex flex-col items-center justify-center h-full py-4 text-xl font-semibold rounded-md bg-lime-500 text-lime-900"
    @click="showForm">
    <IconPlus class="w-16 h-16" />
    <span>Add board</span>
  </button>
</template>
