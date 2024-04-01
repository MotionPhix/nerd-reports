<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { nextTick, ref } from 'vue'
import type { Project } from '@/types'
import toast from '@/Stores/toast'

const props = defineProps<{
  project: Project
}>()

const form = useForm({
  name: props.project.name,
})

const isEditing = ref(false)
const input = ref()

// watch(() => form.name, async () => {
//   await nextTick()
// })

async function edit() {
  isEditing.value = true
  await nextTick()
  input.value.select()
}

function onSubmit() {
  isEditing.value = false
  form.put(route('projects.rename', props.project.id), {
    onError: (err) => {
      form.reset()

      toast.add({
        title: 'Missing project detail',
        type: 'danger',
        message: err.name,
      })
    },

    onSuccess: () => {
      form.reset()
    },
  })
}
</script>

<template>
  <div class="relative flex flex-col items-start">
    <h3
      class="text-xl border whitespace-pre w-full overflow-hidden text-ellipsis border-transparent font-thin leading-none tracking-tight text-gray-900 dark:text-white px-3 py-1.5 hover:bg-gray-400 hover:text-gray-900 cursor-pointer rounded-md transition duration-300"
      :class="[isEditing ? 'invisible' : '']"
      @click="edit()"
    >
      {{ form.name ?? ' ' }}
    </h3>

    working

    <form v-show="isEditing" @submit.prevent="onSubmit()" @focusout="onSubmit()">
      <input ref="input" v-model="form.name" type="text" placeholder="Project name" class="absolute inset-0 px-3 text-xl font-thin placeholder-gray-500 py-1.5 rounded-md focus:ring-2 focus:ring-lime-600">
    </form>
  </div>
</template>
