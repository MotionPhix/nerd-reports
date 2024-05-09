<script setup lang="ts">
import { createVNode, h, onMounted, ref } from "vue"
import { debounce } from "lodash"
import axios from "axios"
import { Head, Link } from "@inertiajs/vue3"
import CommentShell from "@/Pages/Projects/Comments/CommentShell.vue"
import CommentItem from "@/Pages/Projects/Comments/CommentItem.vue"
import CommentForm from "@/Pages/Projects/Comments/CommentForm.vue"
import { IconClock, IconUser } from "@tabler/icons-vue"
import Priority from "@/Components/Priority.vue"
import { useTaskStore } from "@/Stores/taskStore"
import { storeToRefs } from "pinia"
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"

const props = defineProps<{

  localTask: App.Data.TaskData

}>()

const messageContainer = ref(null);

const taskStore = useTaskStore()

const { task } = storeToRefs(taskStore)

const { setTask } = taskStore

setTask(props.localTask)

const scrollToBottom = () => {

  messageContainer.value.scrollTop = messageContainer.value.scrollHeight;

}

const cancelComment = () => {

  scrollToBottom()

}

onMounted(() => {

  scrollToBottom();

})

defineOptions({

  layout: AuthenticatedLayout

})
</script>

<template>

  <Head :title="task.name" />

  <section class="flex-1 max-w-5xl mx-auto relative px-4 sm:px-6 lg:px-8">

    <article
      class="grid gap-6 sm:grid-cols-2 h-[100vh] scrollbar-none overflow-y-auto py-12"
      ref="messageContainer">

      <div class="sm:col-span-2">

        <p class="text-gray-900 text-3xl font-thin block max-w-sm py-2.5 dark:text-white">
          {{ task.name }}
        </p>

      </div>

      <section
        class="grid gap-6 sm:grid-cols-2 sm:col-span-2">

        <div
          class="relative p-2 overflow-hidden border border-gray-300 rounded-lg dark:border-gray-700">

          <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">

            <span>Assigned to</span>

            <IconUser
              stroke="2"
              class="absolute h-36 w-36 -top-8 -right-10 rotate-12 -z-0" />

          </label>

          <p class="relative text-gray-900 font-thin block w-full py-2.5 dark:text-white">
            {{ task?.user?.name }}
          </p>
        </div>

        <div
          class="relative px-6 overflow-hidden border border-gray-300 rounded-lg dark:border-gray-700">
          <label
            class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">

            <span>Priority</span>

            <IconClock
              stroke="2"
              class="absolute h-36 w-36 -top-8 -right-10 rotate-12 -z-0" />

          </label>

          <p class="relative capitalize py-2.5 max-w-[20%]">

            <Priority
              :priority="task.priority"
              font-size="text-md font-thin">
              {{ task.priority }}
            </Priority>

          </p>

        </div>

      </section>

      <div
        class="sm:col-span-2"
        v-if="!! task.description">

        <label
          class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">
          Description
        </label>

        <article
          v-html="task.description"
          class="block py-2.5 w-full prose dark:prose-invert rounded-lg">
        </article>

      </div>

      <div
        class="sm:col-span-2">

        <label
          class="block backdrop-filter backdrop-blur mb-2 text-2xl font-medium bg-gray-100/50 pt-16 text-gray-400 dark:text-gray-500 sticky -top-12 z-10 dark:bg-gray-900/50">
          Discussions
        </label>

        <section class="mb-8 p-10">

          <CommentShell>

            <CommentItem
              v-for="comment in task.comments"
              :comment="comment"
              :key="comment.id" />

          </CommentShell>

        </section>

      </div>

    </article>

    <article class="sticky bottom-0 z-40">

      <CommentForm
        :task="task"
        :cancel="cancelComment" />

    </article>

  </section>

</template>
