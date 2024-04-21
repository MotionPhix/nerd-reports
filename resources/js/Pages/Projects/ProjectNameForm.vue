<script setup lang="ts">
import { nextTick, ref } from "vue";
import { useForm } from "@inertiajs/vue3"
import { useNotificationStore } from '@/Stores/notificationStore'

const props = defineProps<{
  project: App.Data.ProjectFullData
}>();

const toastStore = useNotificationStore();

const { notify } = toastStore

const emit = defineEmits(['saved'])

const form = useForm({
  name: props.project.name
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

  if (props.project.name === form.name) return

  form.patch(
    route('projects.update', {project: props.project.pid}),
    {
      preserveScroll: true,

      onError: (errors) => {

        form.reset()

        for (const prop in errors) {

          notify({
            title:  'Resolve errors',
            type: 'warning',
            message: errors[prop]
          })

        }

      },

      onSuccess: (resp) => {

        emit('saved', resp.props.project)

        notify({
          title:  true,
          message: 'Project was renamed!'
        })

      },

    }

  );

}
</script>

<template>
  <div class="relative flex flex-col items-start max-w-full">

    <h1
      :class="[isEditing ? 'invisible': '']"
      class="hover:bg-white/20 whitespace-pre w-full overflow-hidden text-ellipsis border border-transparent rounded-md cursor-pointer px-3 py-1.5 text-2xl dark:text-white font-semibold"
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
        class="absolute inset-0 text-2xl max-w-full font-semibold placeholder-gray-400 px-3 py-1.5 rounded-md focus:ring-2 focus:ring-blue-900 dark:text-gray-800"
        placeholder="Project name"
        type="text">

    </form>
  </div>
</template>
