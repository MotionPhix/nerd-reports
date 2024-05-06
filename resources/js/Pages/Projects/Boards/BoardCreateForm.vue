<script setup>
import { useForm } from '@inertiajs/vue3';
import {IconPlus} from '@tabler/icons-vue';
import {nextTick, ref} from "vue";
import { useToastStore } from '@/Stores/toastStore';
import { useProjectStore } from '@/Stores/projectStore';
import { storeToRefs } from 'pinia';

const projectStore = useProjectStore();

const { project } = storeToRefs(projectStore)

const toastStore = useToastStore()

const inputNameRef = ref();

const formRef = ref();

const isShowingForm = ref(false);

const { notify } = toastStore
const { reFetchProject } = projectStore;

const form = useForm({
  name: ''
});
async function showForm() {
  isShowingForm.value = true;

  await nextTick();

  inputNameRef.value.focus();
}

function onSubmit() {

  form.post(
    route('boards.store', project.value.pid),
    {

      preserveScroll: true,

      onError: (err) => {
        form.reset()

        inputNameRef.value.focus();

        notify({
          title: 'An error ocurred',
          type: 'warning',
          message: err.name,
        })
      },

      onSuccess: () => {
        form.reset()

        reFetchProject()

        isShowingForm.value = false

        formRef.value.scrollIntoView();
      },

    }
  )

}
</script>
<template>
  <form
    ref="formRef"
    v-if="isShowingForm"
    @submit.prevent="onSubmit()"
    class="fixed w-64 p-3 space-y-4 bg-gray-200 rounded-md bottom-5 right-5"
  >
    <input
      v-model="form.name"
      ref="inputNameRef"
      class="block w-full py-3 text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-400 focus:ring-blue-400"
      placeholder="Enter board name..."
      type="text">

    <div class="flex items-center justify-between">
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm bg-rose-600 hover:bg-rose-500 focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none">
        Add board
      </button>

      <button
        type="button"
        @click="isShowingForm = false"
        class="px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-black focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none">
        Cancel
      </button>
    </div>
  </form>

  <button
    v-if="!isShowingForm"
    @click="showForm()"
    class="fixed z-40 flex items-center justify-center w-16 h-16 p-2 text-white bg-gray-700 bottom-5 right-5 hover:bg-gray-400 dark:hover:bg-white/20 rounded-3xl">
    <IconPlus class="w-10 h-10"/>
  </button>
</template>
