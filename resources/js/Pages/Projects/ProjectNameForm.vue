<script setup lang="ts">
import { nextTick, ref } from "vue";
import { useForm } from "@inertiajs/vue3"
import { useProjectStore } from "@/Stores/projectStore";
import { storeToRefs } from "pinia";
import { useToastStore } from "@/Stores/toastStore"

const props = withDefaults(defineProps<{
  shouldBeShort?: boolean
}>(), {
  shouldBeShort: false
})

const projectStore = useProjectStore()

const toastStore = useToastStore();

const { project } = storeToRefs(projectStore);

const { notify } = toastStore

const form = useForm({
  name: project.value.name
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

  if (project.value.name === form.name) return

  form.patch(
    route('projects.update', {project: project.value.pid}),
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

      onSuccess: (data: any) => {

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

    <h3
      class="hover:bg-white/20 w-full overflow-hidden border border-transparent rounded-md cursor-pointer px-3 py-1.5 text-5xl dark:text-white font-semibold"
      :class="{ 'text-ellipsis': props.shouldBeShort, 'invisible': isEditing }"
      @click="edit()">
      {{ form.name ? form.name : ' ' }}
      <!-- whitespace-pre -->
    </h3>

    <form
      v-show="isEditing"
      @focusout="onSubmit()"
      @submit.prevent="onSubmit()"
      class="w-full">

      <textarea
        ref="input"
        v-model="form.name"
        class="absolute inset-0 text-5xl max-w-full font-semibold placeholder-gray-400 px-3 py-1.5 rounded-md focus:ring-2 focus:ring-blue-900 dark:text-gray-800"
        placeholder="Project name"
        type="text">
      </textarea>

    </form>
  </div>
</template>
