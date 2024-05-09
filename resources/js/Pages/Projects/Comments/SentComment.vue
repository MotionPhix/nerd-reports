<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { IconDots, IconDownload, IconFileCheck, IconTrash } from '@tabler/icons-vue';

const props = defineProps<{
  comment: App.Data.CommentData,
  deleteComment: Function
}>()

</script>

<template>
  <li class="max-w-lg flex gap-x-2 sm:gap-x-4 me-11 group">
    <img
      class="inline-block size-9 rounded-full"
      :src="props.comment.user.avatar_url"
      :alt="props.comment.user.name">

    <div>

      <Menu
        as="div"
        class="relative">

        <MenuButton
          class="items-center flex justify-center w-5 h-5 text-gray-500 dark:text-gray-100">

          <button
            class="inline-flex self-center items-center p-1.5 text-sm font-medium text-center text-gray-900 dark:text-white"
            type="button">

            <IconDots
              class="w-4 h-4 text-gray-500 dark:text-gray-400"
              stroke="3" />

          </button>

        </MenuButton>

        <transition
          enter-active-class="transition duration-100 ease-out transform"
          enter-from-class="scale-90 opacity-0"
          enter-to-class="scale-100 opacity-100"
          leave-active-class="transition duration-100 ease-in transform"
          leave-from-class="scale-100 opacity-100"
          leave-to-class="scale-90 opacity-0">

          <MenuItems
            class="absolute w-24 overflow-hidden origin-top-right bg-white border-gray-300 border rounded-md shadow-lg top-5 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:outline-none">

            <MenuItem
              v-if="props.comment.user_id !== $page.props.auth.user.id">

              <button
                class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">

                <IconFileCheck
                  stroke="2.5"
                  class="w-4 h-4" />

                <span>Reply</span>

              </button>

            </MenuItem>

            <MenuItem
              v-if="props.comment.user_id === $page.props.auth.user.id">

              <button
                @click.prevent="props.deleteComment"
                class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">

                <IconTrash
                  stroke="2.5"
                  class="w-4 h-4" />

                <span>Delete</span>

              </button>

            </MenuItem>

          </MenuItems>

        </transition>

      </Menu>

      <!-- Card -->
      <div
        class="bg-white border border-gray-200 rounded-lg p-4 space-y-3 dark:bg-neutral-900 dark:border-neutral-700">

        <div
          class="prose prose-sm dark:prose-invert"
          v-html="props.comment.body">
        </div>

        <!-- Files -->
        <div
          class="grid gap-2 my-2.5"
          v-if="props.comment.files.length"
          :class="{ 'grid-cols-2': props.comment.files.length > 1 }">

          <div
            class="group relative h-48"
            v-for="file in props.comment.files"
            :key="file.id">

            <div
              class="absolute w-full h-full bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg flex items-center justify-center">

              <a
                download
                :href="route('file.downloads', file.fid)"
                class="inline-flex items-center justify-center rounded-full h-8 w-8 bg-white/30 hover:bg-white/50 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50">

                <IconDownload
                  class="w-5 h-5 text-white"
                  stroke="2.5" />

              </a>

            </div>

            <img
              :src="`${file.full_url}`"
              class="rounded-lg object-cover h-full w-full"
              :alt="file.full_url">

          </div>

        </div>
        <!-- End Files -->

      </div>
      <!-- End Card -->

      <span
        class="mt-1.5 flex items-center gap-x-1 text-xs text-gray-500 dark:text-neutral-500">
        <svg
          class="flex-shrink-0 size-3"
          xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6 7 17l-5-5"></path>
          <path d="m22 10-7.5 7.5L13 16"></path>
        </svg>
        {{ props.comment.created_at }}
      </span>
    </div>

  </li>
</template>
