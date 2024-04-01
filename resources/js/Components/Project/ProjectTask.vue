<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

import { UseDark } from '@vueuse/components'

import axios from 'axios'

import { DatePicker } from 'v-calendar'

import { computed, nextTick, onMounted, reactive, ref } from 'vue'

import { IconCheck, IconPencil } from '@tabler/icons-vue'

import toast from '@/Stores/toast'

import InputError from '@/Components/InputError.vue'

import AutosizeTextarea from '@/Components/AutosizeTextarea.vue'

const props = defineProps<{
  task: Task
}>()

const users = reactive<User[]>([])

const formShowing = computed(() => openForm.value.edit_task_form_id === props.task.id)

const titleRef = ref()

function focusInput() {
  titleRef.value.focus()
  titleRef.value.select()
}

async function showForm() {
  openForm.value.edit_task_form_id = props.task.id
  await nextTick()
  focusInput()
}

const form = useForm({
  title: props.task.title,
  description: props.task.description,
  cost: props.task.cost,
  status: props.task.status,
  user_id: props.task.user_id,
  board_id: props.task.board_id,
  start_date: props.task.start_date,
  end_date: props.task.end_date,
})

const statuses = reactive<{ label: string; value: string }[]>([
  { label: 'New', value: 'new' },
  { label: 'In progress', value: 'in_progress' },
  { label: 'Done', value: 'done' },
  { label: 'Cancelled', value: 'cancelled' },
])

function onSubmit() {
  form.patch(route('tasks.update', props.task), {
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
      openForm.value.edit_task_form_id = 0
    },
  })
}

function close() {
  openForm.value.edit_task_form_id = 0
  form.reset()
}

const disabledDates = ref([
  {
    repeat: {
      weekdays: [1, 7],
    },
  },
])

onMounted(async () => {
  Object.assign(users, null)

  await axios.get(`/assignable/${props.task.project_id}`).then((response) => {
    Object.assign(users, response.data.users)
  })
})
</script>

<template>
  <li class="relative group">
    <div>
      <div class="max-w-sm p-3 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-2 text-2xl font-thin leading-none tracking-tight text-gray-900 dark:text-white">
          {{ task.name }}
        </h5>

        <p class="text-sm font-normal text-gray-700 dark:text-gray-400">
          {{ task.description }}
        </p>
      </div>

      <button v-if="!formShowing" class="absolute hidden w-8 h-8 text-gray-600 transition duration-300 rounded-md group-hover:grid bg-gray-50 right-2 top-2 place-content-center hover:text-gray-800 hover:bg-gray-200 dark:hover:bg-gray-400" @click="showForm">
        <IconPencil class="w-4 h-4" />
      </button>

      <form
        v-if="formShowing"
        class="fixed max-h-[60dvh] overflow-y-auto scrollbar-none grid z-20 shadow-2xl grid-cols-2 gap-4 p-4 mb-4 bg-gray-500 border rounded-md w-[22em] dark:border-gray-700 right-10 bottom-4 dark:bg-gray-800"
        @keydown.esc="openForm.edit_task_form_id = 0"
        @submit.prevent="onSubmit()"
      >
        <div class="col-span-2">
          <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Task name</label>
          <input
            id="title"
            ref="titleRef"
            v-model="form.title"
            type="text"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Type task title"
          >

          <InputError :message="form.errors.title" />
        </div>

<!--        <div class="col-span-2">-->
<!--          <label for="cost" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>-->
<!--          <BaseList-->
<!--            id="status"-->
<!--            v-model="form.status"-->
<!--            placeholder="Task status"-->
<!--            :options="statuses"-->
<!--            :error="form.errors.status"-->
<!--          />-->
<!--        </div>-->

        <div class="col-span-2">
          <label for="user_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">User</label>
          <select id="user_id" v-model="form.user_id" placeholder="Assign a user" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="" disabled selected>
              Assign task to someone
            </option>

            <option :value="$page.props.auth.user.id">
              Me
            </option>

            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>

          <InputError :message="form.errors.user_id" />
        </div>

        <div class="sm:col-span-2">
          <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
          <AutosizeTextarea
            id="description"
            v-model="form.description"
            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Explain a bit about the task"
          />

          <InputError :message="form.errors.description" />
        </div>

        <div class="sticky z-50 flex justify-between col-span-2 gap-2 px-2 py-1 mt-2 bg-blue-600 rounded-lg -bottom-4 -left-0 -right-0">
          <button type="submit" class="flex items-center gap-2 px-3 py-2 font-semibold text-white transition duration-300 rounded-md bg-white/10 hover:bg-white/20">
            <IconCheck class="w-5 h-5" />
            <span>Update task</span>
          </button>

          <button type="button" class="px-3 py-2 font-semibold text-white transition duration-300 rounded-md hover:text-white/60" @click="close">
            <span>Cancel</span>
          </button>
        </div>
      </form>
      <div />
    </div>
  </li>
</template>

<style scoped>
.drag {
  cursor: move;
}

.drag > div {
  transform: rotate(5deg);
}

.list-group-item {
  cursor: move;
}

.ghost {
  @apply bg-rose-600/40 rounded-lg
}

.ghost > div {
  opacity: 0.75;
}
</style>
