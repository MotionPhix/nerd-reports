<script setup lang="ts">
import { onMounted, ref } from "vue"
import { debounce } from "lodash"
import axios from "axios"
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import useStickyTop from "@/Composables/useStickyTop"
import CommentShell from "@/Pages/Projects/Comments/CommentShell.vue"
import CommentItem from "@/Pages/Projects/Comments/CommentItem.vue"
import CommentForm from "@/Pages/Projects/Comments/CommentForm.vue"
import {
  IconBookmarkPlus,
  IconChecks,
  IconClock,
  IconReceipt,
  IconReceipt2,
  IconTrash,
  IconUser
} from "@tabler/icons-vue"
import Priority from "@/Components/Priority.vue"
import { useTaskStore } from "@/Stores/taskStore"
import { storeToRefs } from "pinia"

const props = defineProps<{
  localTask: App.Data.TaskData
}>()

const { navClasses } = useStickyTop();

const isCommenting = ref()

const taskStore = useTaskStore()
const { task } = storeToRefs(taskStore)
const { setTask } = taskStore

setTask(props.localTask)

onMounted(() => {
  /*window.addEventListener("scroll", debounce(() => {
    let pixelsFromBottom = document.documentElement.offsetHeight - document.documentElement.scrollTop - window.innerHeight

    if (pixelsFromBottom < 200) {
      axios
        .get(notices.value.nex_page_url)
        .then(resp => {
          notices.value = {
            ...resp.data,
            data: [...notices.value.data, resp.data.data]
          }
        })
    }
  }, 100)
  )*/

})

const cancelComment = () => {
  isCommenting.value = false
}

defineOptions({
  layout: AuthenticatedLayout
})
</script>

<template>

  <Head :title="task.name" />

  <nav
    class="flex items-center h-16 max-w-5xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses">

    <h2 class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">

      <span>
        Task
      </span>

    </h2>

    <span class="flex-1"></span>

  </nav>

  <section class="max-w-3xl px-6 py-12 mx-auto">

    <article
      v-if="! isCommenting"
      class="grid gap-6 mb-4 sm:grid-cols-2">

      <div class="sm:col-span-2">

        <p
          class="text-gray-900 text-3xl font-thin block w-full py-2.5 dark:text-white">
          {{ task.name }}
        </p>

      </div>

      <section class="grid gap-6 sm:grid-cols-2 sm:col-span-2">

        <div class="relative p-2 overflow-hidden border border-gray-300 rounded-lg dark:border-gray-700">

          <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">

            <span>Assigned to</span>

            <IconUser stroke="2" class="absolute h-36 w-36 -top-8 -right-10 rotate-12 -z-0" />

          </label>

          <p
            class="relative text-gray-900 font-thin block w-full py-2.5 dark:text-white">
            {{ task?.user?.name }}
          </p>
        </div>

        <div class="relative px-6 overflow-hidden border border-gray-300 rounded-lg dark:border-gray-700">
          <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">

            <span>Priority</span>

            <IconClock stroke="2" class="absolute h-36 w-36 -top-8 -right-10 rotate-12 -z-0" />

          </label>

          <p
            class="relative capitalize py-2.5 max-w-[20%]">

            <Priority :priority="task.priority" font-size="text-md font-thin">
              {{ task.priority }}
            </Priority>

          </p>

        </div>

      </section>

      <div class="sm:col-span-2" v-if="!! task.description">

        <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">
          Description
        </label>

        <article
          v-html="task.description"
          class="block py-2.5 w-full prose dark:prose-invert rounded-lg">
        </article>

      </div>

      <div class="sm:col-span-2">

        <label class="block backdrop-filter backdrop-blur mt-10 mb-2 text-2xl font-medium bg-gray-100/50 pt-5 text-gray-400 dark:text-gray-500 sticky top-24 z-10 dark:bg-gray-900/50">
          Discussions
        </label>

        <section class="p-10">

          <CommentShell>

            <CommentItem v-for="comment in task.comments" :comment="comment" :key="comment.id" />

          </CommentShell>

        </section>

      </div>

    </article>

    <article>

      <CommentForm :task="task" :cancel="cancelComment" />

    </article>

  </section>
</template>
