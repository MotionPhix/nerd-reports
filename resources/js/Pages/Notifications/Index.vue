<script setup lang="ts">
import { onMounted, ref } from "vue"
import { debounce } from "lodash"
import axios from "axios"
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import useStickyTop from "@/Composables/useStickyTop"
import { IconBookmarkPlus, IconChecks, IconReceipt, IconReceipt2, IconTrash } from "@tabler/icons-vue"

const props = defineProps<{
  notifications: Object
}>()

const notices = ref(props.notifications)

const { navClasses } = useStickyTop();

onMounted(() => {
  window.addEventListener("scroll", debounce(() => {
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
  )
})

defineOptions({
  layout: AuthenticatedLayout
})
</script>

<template>

  <Head title="Notifications" />

  <nav class="flex items-center h-16 max-w-5xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
       :class="navClasses">
    <h2 class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">
      <span>Notifications</span>

      <span class="text-gray-400 dark:text-gray-600">
        ({{ props.notifications.total }})
      </span>
    </h2>

    <span class="flex-1"></span>

    <!-- <Link
      as="button"
      :href="route('projects.create')"
      class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200">
      <IconPlus stroke="2.5" class="w-4 h-4" />
      <span>Create project</span>
    </Link> -->
  </nav>

  <section class="max-w-3xl px-6 py-12 mx-auto">

    <ul class="divide-y divide-gray-200">

      <li
        v-for="notice in notices.data"
        :key="notice.id"
        class="p-4">

        <section class="flex items-start gap-4">

          <img :src="notice.data.user.avatar_url"
               alt="notices.data.user.name"
               class="flex-shrink-0 w-8 h-8 rounded-full mb-2">

          <div class="flex flex-col group">

            <p
              class="mb-2 font-bold flex items-center gap-2">
              <span>{{ notice.data.user.name }}</span>

              <span class="text-gray-400 text-sm font-thin pl-2.5">
                {{ notice.data.comment.created_at }}
              </span>

              <Link
                as="button"
                class="text-gray-400 text-sm font-thin items-center gap-1 pl-2.5 hidden group-hover:inline-flex"
                href="#">
                <IconTrash class="w-4 h-4" />
                <span>Delete</span>
              </Link>

              <Link
                as="button"
                class="text-gray-400 text-sm font-thin items-center gap-1 pl-2.5 hidden group-hover:inline-flex"
                href="#">
                <IconBookmarkPlus class="w-4 h-4" />
                <span>Mark as read</span>
              </Link>

            </p>

            <Link
              as="button"
              :href="route('notifications')"
              class="text-left">

              <div
                v-html="notice.data.comment.body"
                class="prose-sm dark:prose-invert line-clamp-3">
              </div>

            </Link>

          </div>

        </section>

      </li>

    </ul>

  </section>
</template>
