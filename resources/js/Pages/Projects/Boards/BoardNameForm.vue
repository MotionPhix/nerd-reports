<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import {nextTick, ref} from "vue";

const props = defineProps<{
  board: App.Data.BoardData
}>();

const form = useForm({

  name: props.board.name

});

const input = ref();

const isEditing = ref(false);

async function edit() {

  isEditing.value = true;

  await nextTick();

  input.value.select();

}

function onSubmit() {

  isEditing.value = false;

  if (props.board.name === form.name) return

  form.patch(

    route('boards.update', { project: props.board.project_id, board: props.board.id }),
    {

      onError: () => { form.reset() },

      preserveScroll: true

    },

  );
}
</script>

<template>
  <div class="relative flex flex-col items-start max-w-full">
    <h1
      :class="[isEditing ? 'invisible': '']"
      class="hover:bg-white/20 whitespace-pre w-full overflow-hidden text-ellipsis border border-transparent rounded-md cursor-pointer px-3 py-1.5 text-xl dark:text-white font-bold"
      @click="edit()">
      {{ form.name ? form.name : ' ' }}
    </h1>

    <form
      v-show="isEditing"
      @focusout="onSubmit()"
      @submit.prevent="onSubmit()"
      class="w-full">

      <input
        ref="input"
        v-model="form.name"
        class="absolute inset-0 text-xl max-w-full dark:text-gray-800 font-bold placeholder-gray-400 px-3 py-1.5 rounded-md focus:ring-2 focus:ring-blue-900"
        placeholder="board name"
        type="text">

    </form>
  </div>
</template>
