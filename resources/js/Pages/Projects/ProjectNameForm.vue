<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { nextTick, ref } from 'vue'
import type { Project } from '@/types'
import toast from '@/Stores/toast'
import TextInput from "@/Components/TextInput.vue"

const props = defineProps<{
  project: Project
}>()

const form = useForm({
  name: props.project.name,
})

const isEditing = ref(false)

const input = ref()

const emit = defineEmits(['saved'])

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

  form.transform((data) => {

    const { boards, contact, ...formData } = props.project;

    formData.name = data.name

    return formData;
  })

  form.patch(route('projects.update', props.project.pid), {
    onError: (err) => {
      form.reset()

      toast.add({
        title: 'Missing project detail',
        type: 'danger',
        message: err.name,
      })
    },

    onSuccess: (resp) => {
      form.name = resp.props.project.name
      emit('saved', resp.props.project.name)
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

    <form v-show="isEditing" @submit.prevent="onSubmit()" @focusout="onSubmit()">
      <input
          ref="input"
          v-model="form.name"
          type="text"
          placeholder="Project name"
          class="absolute inset-0 dark:bg-gray-800 px-3 text-xl font-thin placeholder-gray-500 py-1.5 rounded-md focus:ring-2 focus:ring-lime-600" />
    </form>
  </div>
</template>
