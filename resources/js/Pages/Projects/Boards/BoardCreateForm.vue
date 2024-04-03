<script setup>
import { useForm } from '@inertiajs/vue3';
import {IconPlus} from '@tabler/icons-vue';
import {nextTick, ref} from "vue";

const props = defineProps({
  project: Object
});

const inputNameRef = ref();

const formRef = ref();

const isShowingForm = ref(false);

const form = useForm({
  name: ''
});

async function showForm() {
  isShowingForm.value = true;

  await nextTick();

  inputNameRef.value.focus();
}

function onSubmit() {

  form.post(route('boards.store', props.project.pid), {
    onError: (err) => {
      // form.reset()

      console.log(err)

      // toast.add({
      //   title: 'Resolve errors',
      //   type: 'warning',
      //   message: err.name,
      // })
    },

    onSuccess: () => {
      form.reset()
      inputNameRef.value.focus();
      isShowingForm = false
      // formRef.value.scrollIntoView();
    },
  })

}
</script>
<template>
  <form
    ref="formRef"
    v-if="isShowingForm"
    @submit.prevent="onSubmit()"
    class="p-3 bg-gray-200 rounded-md fixed bottom-5 right-5"
  >
    <input
      v-model="form.name"
      ref="inputNameRef"
      class="block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring-blue-400"
      placeholder="Enter list name..."
      type="text">

    <div class="mt-2 space-x-2">
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-500 rounded-md shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none">Add board</button>
      <button
        type="button"
        @click="isShowingForm = false"
        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-black rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none">Cancel</button>
    </div>
  </form>

  <button
    v-if="!isShowingForm"
    @click="showForm()"
    class="fixed bottom-5 right-5 flex bg-gray-700 justify-center items-center h-16 w-16 hover:bg-white/20 text-white p-2 rounded-xl z-50">
    <IconPlus class="w-10 h-10"/>
  </button>
</template>
