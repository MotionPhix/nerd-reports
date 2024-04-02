<script setup lang="ts">

import { IconDetails, IconListDetails, IconPencil } from '@tabler/icons-vue'

import ProjectTaskForm from "@/Components/Project/ProjectTaskForm.vue"

import { storeToRefs } from 'pinia';

import { useTaskStore } from '@/Stores/taskStore';

import { nextTick, ref } from 'vue';

import { router } from '@inertiajs/vue3';

import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps<{
  board: App.Data.BoardData
  task: App.Data.TaskData
}>()

const taskStore = useTaskStore();

const {
  isEditing,
} = storeToRefs(taskStore);

const { setIsEditing, setTask } = taskStore;

const titleRef = ref()

function focusInput() {
  titleRef.value.focus()
  titleRef.value.select()
}

async function showForm() {
  setTask(props.task)

  setIsEditing()

  await nextTick()

  // focusInput()
}

const showTask = ref(false)

function deleteTask() {
  router.delete(route('boards.destroy', props.board.id), {

    onError: error => console.log(error),

    onSuccess: () => {
      showTask.value = false
    },

  })

  showTask.value = false
}

</script>

<template>
  <li class="relative group">

    <article class="p-3 border border-gray-300 rounded-lg dark:border-gray-700">

      <h5 class="mb-2 text-xl font-thin leading-none tracking-tight text-gray-900 dark:text-white">
        {{ task?.name }}
      </h5>

      <section
        class="text-sm font-normal prose dark:prose-invert line-clamp-5"
        v-html="task?.description" />

      <ProjectTaskForm
          :board="props.board">
          <div class="absolute hidden right-3 top-2 group-hover:inline-block">
            <section class="flex items-center gap-2">

              <button
                v-if="!isEditing" class="flex items-center justify-center w-8 h-8 text-white bg-gray-700 rounded-xl"
                @click="showTask = !showTask">
                <IconListDetails class="p-1 w-7 h-7" />
              </button>

              <button
                v-if="!isEditing" class="flex items-center justify-center w-8 h-8 text-white bg-gray-700 rounded-xl"
                @click="showForm">
                <IconPencil class="p-1 w-7 h-7" />
              </button>

            </section>
          </div>
      </ProjectTaskForm>

    </article>

    <ConfirmDialog
      v-if="showTask"
      @cancel="showTask = false"
      @confirm="deleteTask()">
      <div class="relative max-w-md overflow-y-auto max-h-96 scrollbar-none">
        <div class="relative prose rounded-lg dark:bg-gray-700 dark:prose-invert">
          <h2 class="sticky top-0 z-10 px-4 pt-2 bg-gray-200 rounded-t-lg dark:bg-gray-600">{{ task.name }}</h2>

          <section class="px-4" v-html="task.description" />
        </div>
      </div>

    </ConfirmDialog>

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
