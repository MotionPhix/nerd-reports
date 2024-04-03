<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

import { IconCheck } from '@tabler/icons-vue'

import axios from 'axios'

import { onMounted, ref, watch } from 'vue'

import toast from '@/Stores/toast'

import InputError from '@/Components/InputError.vue'

import MazCheckbox from 'maz-ui/components/MazCheckbox'

import TextInput from "@/Components/TextInput.vue"

import TipTap from "@/Components/TipTap.vue"

import SelectInput from "@/Components/SelectInput.vue"

import { storeToRefs } from "pinia"

import { useTaskStore } from "@/Stores/taskStore"

import { useBoardStore } from '@/Stores/boardStore'

const emit = defineEmits(['created'])

const taskStore = useTaskStore();

const boardStore = useBoardStore();

const {
  task,
  isEditing
} = storeToRefs(taskStore);

const {
  board
} = storeToRefs(boardStore);

const { unSet } = taskStore;

const { unSetBoard } = boardStore;

const users = ref<App.Data.UserData[]>([])

const titleRef = ref()
function focusInput() {
  titleRef.value.focus()
}

const priorities = [
  {
    value: 'normal',
    label: 'Normal'
  },

  {
    value: 'medium',
    label: 'Medium'
  },

  {
    value: 'high',
    label: 'High'
  }
]

const form = useForm({
  name: task.value?.name ?? '',

  description: task.value?.description ?? '',

  assigned_to: task.value?.assigned_to ?? null,

  is_completed: task.value?.is_completed ?? false,

  priority: task.value?.priority ?? 'normal',

  board_id: board.value?.id,
})

function onSubmit() {
  form.transform((data) => {

    let formData: Partial<App.Data.TaskData> = {
      name: data.name,
      priority: data.priority,
      is_completed: data.is_completed,
      assigned_to: data.assigned_to,
      board_id: data.board_id,
    };

    if (!! form.description) {
      formData.description = form.description
    }

    return formData

  })

  if (task.value.id) {

    form.patch(route('tasks.update', { task: task.value }), {
      onError: (errors) => {

        for (const prop in errors) {
          toast.add({
            title: 'Resolve errors',
            type: 'warning',
            message: errors[prop],
          })
        }

      },

      onSuccess: () => {
        form.reset()
        focusInput()
        unSet()
        emit('created')
      },
    })

    return
  }

  form.post(route('tasks.store'), {
    onError: (errors) => {
      // form.reset()

      for (const prop in errors) {
        toast.add({
          title: 'Resolve errors',
          type: 'warning',
          message: errors[prop],
        })
      }

    },

    onSuccess: () => {
      form.reset()
      focusInput()
      unSet()
      emit('created')
    },
  })
}

function close() {
  unSet()
  form.reset()
}

watch(() => task.value, newValue => {

  form.name = newValue?.name ?? ''

  form.description = newValue?.description ?? ''

  form.assigned_to = newValue?.assigned_to ?? null

  form.is_completed = newValue?.is_completed ?? false

  form.priority = newValue?.priority ?? 'normal'

  form.board_id = board.value?.id

}, { immediate: true })

onMounted(() => {

  const projectId = board.value?.project_id;

  axios
    .get(`/api/users/${projectId}`)
    .then((response) => {
      users.value = response.data.users;
    })

})
</script>

<template>
  <section>
  <div class="fixed inset-0 z-10 bg-gray-500 bg-opacity-25" v-if="isEditing" />

  <form
    v-if="isEditing"
    class="fixed grid grid-cols-2 gap-6 border bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700 z-40 overflow-y-auto max-h-[60dvh] scrollbar-none p-4 mb-4 rounded-lg w-[22em] right-10 bottom-4 shadow-lg"
    @keydown.esc="unSet()"
    @submit.prevent="onSubmit()">
    <div class="col-span-2">
      <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Task
      </label>

      <TextInput
        id="title"
        ref="titleRef"
        v-model="form.name"
        type="text"
        placeholder="Enter a task" />

      <InputError :message="form.errors.name" />
    </div>

    <div class="col-span-2">
      <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign task</label>

      <SelectInput
        placeholder="Select a person"
        v-model="form.assigned_to"
        :options="users"
      />

      <InputError :message="form.errors.assigned_to" />
    </div>

    <div class="col-span-2">
      <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Priority
      </label>

      <SelectInput
        placeholder="Set task priority"
        v-model="form.priority"
        :options="priorities"
      />

      <InputError :message="form.errors.priority" />
    </div>

    <div class="sm:col-span-2">
      <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>

      <TipTap v-model="form.description" />

      <InputError :message="form.errors.description" />
    </div>

    <div class="sm:col-span-2">
      <MazCheckbox
        v-model="form.is_completed"
        color="success">
        Task is completed
      </MazCheckbox>

      <InputError :message="form.errors.is_completed" />
    </div>

    <div class="sticky bottom-0 z-50 flex justify-between col-span-2 gap-2 px-2 py-2 mt-2 bg-gray-500 rounded-full dark:bg-gray-700">
      <button
        type="submit"
        class="flex items-center gap-2 px-3 py-2 font-semibold text-white transition duration-300 rounded-full bg-white/10 hover:bg-white/20">
        <IconCheck class="w-5 h-5" />
        <span>
          {{ task?.id ? 'Update' : 'Create' }} task
        </span>
      </button>

      <button
        type="button"
        class="px-3 py-2 font-semibold text-white transition duration-300 rounded-md hover:text-white/60"
        @click="close">
        <span>Cancel</span>
      </button>
    </div>
  </form>

  <template v-else>
    <slot />
  </template>
</section>
</template>
