<script setup lang="ts">
import { useNotificationStore } from '@/Stores/notificationStore';
import { Link, router } from "@inertiajs/vue3"
import { IconBookmarkPlus, IconTrash } from '@tabler/icons-vue';
import axios from "axios"

const props = defineProps<{

  notification: App.Data.NotificationData

}>()

const notificationStore = useNotificationStore()
const { deleteNotification, markNotificationAsRead, fetchNotifications } = notificationStore

const markRead = () => {

  markNotificationAsRead(props.notification)

}

const remove = () => {

  deleteNotification(props.notification)

}

const fetchTask = () => {

  const $footprint = props.notification.read_at
    ? route('tasks.show', { task: props.notification.data.comment.task_tid })
    : route('tasks.show', { task: props.notification.data.comment.task_tid, notification: props.notification.id })

  router
    .get($footprint, {}, {
      onSuccess: () => {

        fetchNotifications()

      }
    })

}
</script>

<template>
  <li
    class="w-full p-4 text-gray-500 bg-white rounded-lg group dark:bg-gray-800 dark:text-gray-400"
    role="alert">
    <div class="flex">
      <img
        class="w-8 h-8 rounded-full"
        :src="notification.data.user.avatar_url"
        alt="notices.data.user.name"
      />

      <div class="text-sm font-normal ms-3">
        <section class="flex items-center gap-4">
          <div
            class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">
            {{ notification.data.user.name }}
          </div>

          <div
            class="relative z-40 hidden gap-2 py-1 text-sm font-thin rounded-md -top-1 dark:bg-gray-700 group-hover:inline-flex dark:text-gray-200"
            v-tooltip="{
              global: true,
              theme: {
                placement: 'bottom',
                width: 'max-content',
                padding: '0.5em',
              },
            }"
            :class="notification.data.comment.read_at ? 'px-2' : 'px-1'">

            <button
              v-tooltip="'Delete'"
              @click.prevent="remove">
              <IconTrash class="w-4 h-4" />
            </button>

            <button
              method="patch"
              preserve-scroll
              v-if="! notification.read_at"
              v-tooltip="'Mark as read'"
              @click.prevent="markRead">
              <IconBookmarkPlus class="w-4 h-4" />
            </button>

          </div>

        </section>

        <button
          class="mb-2 space-y-1 text-sm font-normal text-left"
          @click.prevent="fetchTask(notification.data.comment.tid)">
          <span>Commented on a task</span>

          <blockquote
            class="relative prose-sm border-s-4 ps-4 sm:ps-6 dark:border-neutral-700 line-clamp-3"
            v-html="notification.data.comment.body">
          </blockquote>
        </button>
      </div>

      <span
        class="ms-auto -mx-1.5 my-1 text-xs justify-center items-center flex-shrink-0 text-gray-400 rounded-lg dark:text-gray-500">
        {{ notification.data.comment.created_at }}
      </span>
    </div>

  </li>
</template>
