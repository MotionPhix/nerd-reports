<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { IconPlus } from "@tabler/icons-vue"

import { computed, nextTick, onMounted, ref } from "vue"

import { useFormStore } from "@/Stores/formStore"

import { storeToRefs } from "pinia"

import InputError from '@/Components/InputError.vue'

import TextInput from "@/Components/TextInput.vue"

import TipTap from "@/Components/TipTap.vue"

import SelectInput from "@/Components/SelectInput.vue"
import axios from "axios"
import { watch } from 'vue';

const props = defineProps({
  board: Object
});

const formStore = useFormStore()

const {
  currentBoardId
} = storeToRefs(formStore);

const {
  setCurrentBoardId,
  unSetCurrentBoardId,
} = formStore

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

  await nextTick();

  if (!! newBoardId) {

    inputNameRef.value.focus();

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
      // form.reset()

      console.log(errors)

      // for (const prop in errors) {
      //   toast.add({
      //     title: 'Resolve errors',
      //     type: 'warning',
      //     message: errors[prop],
      //   })
      // }

    },

    onSuccess: () => {
      form.reset()
      inputNameRef.value.focus();
      unSetCurrentBoardId()
      emit('created')
    },
  })

}

onMounted(() => {

  axios
    .get(`/api/users/${props.board.project_id}`)
    .then((response) => {
      users.value = response.data.users;
    })

})
</script>

<template>
  <form
    class="grid grid-cols-2 gap-6 bg-gray-700 py-4 p-2 z-30 rounded-md fixed bottom-5 right-5 max-w-sm"
    @keydown.esc="unSetCurrentBoardId()"
    @submit.prevent="onSubmit()"
    v-if="isShowingForm">

<!--    start-->

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

    <div class="col-span-2 grid grid-cols-2 gap-2">
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

      <TipTap v-model="form.description" height="h-54" />

      <InputError :message="form.errors.description" />
    </div>

<!--    <div class="sticky bottom-0 z-50 flex justify-between col-span-2 gap-2 px-2 py-2 mt-2 bg-gray-500 rounded-full dark:bg-gray-700">-->
<!--      <button-->
<!--        type="submit"-->
<!--        class="flex items-center gap-2 px-3 py-2 font-semibold text-white transition duration-300 rounded-full bg-white/10 hover:bg-white/20">-->
<!--        <IconCheck class="w-5 h-5" />-->
<!--        <span>-->
<!--          Create task-->
<!--        </span>-->
<!--      </button>-->

<!--      <button-->
<!--        type="button"-->
<!--        class="px-3 py-2 font-semibold text-white transition duration-300 rounded-md hover:text-white/60"-->
<!--        @click="close">-->
<!--        <span>Cancel</span>-->
<!--      </button>-->
<!--    </div>-->

<!--    end-->

    <div class="flex justify-between col-span-2">
      <button
        class="px-4 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-500 rounded-md shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
        type="submit">
        Add task
      </button>

      <button
        class="px-4 py-2 text-sm dark:text-gray-300 font-medium text-gray-700 hover:text-black rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
        type="button"
        @click="unSetCurrentBoardId()">
        Cancel
      </button>
    </div>
  </form>
</template>
