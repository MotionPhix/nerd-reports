<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { IconCheck, IconPlus } from '@tabler/icons-vue'
import { UseDark } from '@vueuse/components'
import axios from 'axios'
import { DatePicker } from 'v-calendar'
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import toast from '@/Stores/toast'
import { openForm } from '@/Stores/task-form'
import InputError from '@/Components/InputError.vue'
import AutosizeTextarea from '@/Components/AutosizeTextarea.vue'
import TextInput from "@/Components/TextInput.vue"

const props = defineProps<{
  board: Board
}>()

const emit = defineEmits(['created'])

const formShowing = computed(() => openForm.value.add_task_form_id === props.board.id)

const users = reactive<User[]>([])

const titleRef = ref()
function focusInput() {
  titleRef.value.focus()
}
async function showForm() {
  openForm.value.add_task_form_id = props.board.id
  await nextTick()
  focusInput()
}

const form = useForm({
  name: '',
  description: '',
  board_id: props.board.id,
})

function onSubmit() {
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
      openForm.value.add_task_form_id = 0
      emit('created')
    },
  })
}

function close() {
  openForm.value.add_task_form_id = 0
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

  await axios.get(`/api/users/${props.board.project_id}`).then((response) => {
    Object.assign(users, response.data.users)
  })
})
</script>

<template>
  <form
    v-if="formShowing"
    class="fixed grid grid-cols-2 gap-6 border dark:border-gray-700 z-40 overflow-y-auto max-h-[60dvh] scrollbar-none p-4 mb-4 rounded-lg w-[22em] right-10 bottom-4"
    @keydown.esc="openForm.add_task_form_id = 0"
    @submit.prevent="onSubmit()">
    <div class="col-span-2">
      <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Task name</label>

      <TextInput
        id="title"
        ref="titleRef" v-model="form.name"
        type="text"
        placeholder="Enter task name" />

      <InputError :message="form.errors.name" />
    </div>

    <div class="col-span-2">
      <label for="user_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign task</label>
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
        <span>Create task</span>
      </button>

      <button type="button" class="px-3 py-2 font-semibold text-white transition duration-300 rounded-md hover:text-white/60" @click="close">
        <span>Cancel</span>
      </button>
    </div>
  </form>

  <button
    v-else
    class="inline-flex items-center w-full gap-1 px-2 py-1 font-medium text-gray-600 transition duration-300 rounded-md dark:text-gray-400 dark:hover:text-gray-900 dark:hover:bg-gray-500 hover:text-gray-800 hover:bg-gray-300"
    @click="showForm()">
    <IconPlus class="h-6" stroke="2.5" />
    <span>Add task</span>
  </button>
</template>
