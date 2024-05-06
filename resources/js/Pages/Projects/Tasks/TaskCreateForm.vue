<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { computed, nextTick, onMounted, ref } from "vue"

import { useFormStore } from "@/Stores/formStore"

import { useToastStore } from "@/Stores/toastStore"

import { storeToRefs } from "pinia"

import InputError from '@/Components/InputError.vue'

import TextInput from "@/Components/TextInput.vue"

import TipTap from "@/Components/TipTap.vue"

import SelectInput from "@/Components/SelectInput.vue"

import axios from "axios"

import { watch } from 'vue';

import { useProjectStore } from '@/Stores/projectStore';

const props = defineProps<{
  board: App.Data.BoardData
}>();

const formStore = useFormStore()

const toastStore = useToastStore();

const projectStore = useProjectStore();

const { setProject } = projectStore;

const {
  currentBoardId
} = storeToRefs(formStore);

const {
  setCurrentBoardId,
  unSetCurrentBoardId,
} = formStore

const { notify } = toastStore

const emit = defineEmits(['created']);

const inputNameRef = ref();

const isShowingForm = computed(() => props.board.id === currentBoardId.value);

const users = ref<App.Data.UserData[]>([])

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
  name: '',

  description: '',

  assigned_to: null,

  priority: 'normal',

  board_id: props.board.id,
});

watch(() => currentBoardId.value, async (newBoardId, oldBoardId) => {

  if (!! newBoardId) {

    await nextTick();

    inputNameRef.value?.focus();

  }

}, { immediate: true })

async function showForm() {

  setCurrentBoardId(props.board.id)

}

function onSubmit() {

  form.transform((data) => {

    let formData: Partial<App.Data.TaskData> = {
      name: data.name,
      priority: data.priority,
      assigned_to: data.assigned_to,
      board_id: data.board_id,
    };

    if (!! form.description) {
      formData.description = form.description
    }

    return formData

  })

  form.post(route('tasks.store'), {

    preserveScroll: true,

    onError: (errors) => {

      for (const prop in errors) {

        notify({
          title:  'Resolve errors',
          type: 'warning',
          message: errors[prop]
        })

      }

    },

    onSuccess: (data: any) => {

      setProject(data.props.project)

      form.reset()

      inputNameRef.value.focus();

      unSetCurrentBoardId()

      emit('created')
    },
  })

}

onMounted(() => {

  axios
    .get('/api/users')
    .then((response) => {
      users.value = response.data.users;
    })

})

const placeholder = ref('Describe the task')
</script>

<template>
  <form
    class="fixed z-50 grid max-w-sm grid-cols-2 gap-6 p-2 py-4 bg-gray-100 border border-gray-300 rounded-md shadow-lg dark:border-gray-600 dark:bg-gray-700 bottom-5 right-5"
    @keydown.esc="unSetCurrentBoardId()"
    @submit.prevent="onSubmit()"
    v-if="isShowingForm">

    <div class="col-span-2">
      <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Task
      </label>

      <TextInput
        id="title"
        ref="inputNameRef"
        v-model="form.name"
        type="text"
        placeholder="Enter a task" />

      <InputError :message="form.errors.name" />
    </div>

    <div class="grid grid-cols-2 col-span-2 gap-2">
      <div class="col-span-1">
        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign task</label>

        <SelectInput
          placeholder="Select a person"
          v-model="form.assigned_to"
          :options="users"
        />

        <InputError :message="form.errors.assigned_to" />
      </div>

      <div class="col-span-1">
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
    </div>

    <div class="sm:col-span-2">
      <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>

      <TipTap v-model="form.description" height="h-54" v-model:placeholder="placeholder" />

      <InputError :message="form.errors.description" />
    </div>

    <div class="flex justify-between col-span-2">
      <button
        class="px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm bg-rose-600 hover:bg-rose-500 focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
        type="submit">
        Add task
      </button>

      <button
        class="px-4 py-2 text-sm font-medium text-gray-700 rounded-md dark:text-gray-300 hover:text-black focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
        type="button"
        @click="unSetCurrentBoardId()">
        Cancel
      </button>
    </div>
  </form>
</template>
