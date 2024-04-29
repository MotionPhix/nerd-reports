<script setup lang="ts">
import { IconDownload, IconTrash, IconDots, IconFileCheck } from "@tabler/icons-vue"
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { Link, usePage } from "@inertiajs/vue3"

const props = defineProps<{
  comment: App.Data.CommentData
}>()

const { user } = usePage().props.auth

</script>

<template>
  <li class="flex items-start gap-2.5" v-if="user.email === props.comment.user.email">

    <img class="w-8 h-8 rounded-full"
         :src="props.comment.user.avatar_url"
         :alt="props.comment.user.name">

    <div class="flex flex-col gap-1 w-full  mr-[2.5rem] relative group">

      <div class="flex items-center space-x-2 rtl:space-x-reverse">

        <span class="text-sm font-semibold text-gray-900 dark:text-white">
          {{ props.comment.user.name }}
        </span>

        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
          {{ props.comment.created_at }}
        </span>

      </div>

      <div
           class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 rounded-e-xl rounded-es-xl dark:bg-gray-700">

        <article class="prose font-normal dark:prose-invert"
                 v-html="props.comment.body" />

        <div class="grid gap-4 my-2.5"
             v-if="props.comment.files.length"
             :class="{ 'grid-cols-2': props.comment.files.length > 1 }">

          <div class="group relative h-48"
               v-for="file in props.comment.files"
               :key="file.id">

            <div
                 class="absolute w-full h-full bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg flex items-center justify-center">

              <a download
                 :href="route('file.downloads', file.fid)"
                 class="inline-flex items-center justify-center rounded-full h-8 w-8 bg-white/30 hover:bg-white/50 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50">

                <IconDownload class="w-5 h-5 text-white"
                              stroke="2.5" />

              </a>

            </div>

            <img :src="`${file.full_url}`"
                 class="rounded-lg object-cover h-full w-full"
                 :alt="file.full_url">

          </div>

        </div>

      </div>

      <Menu as="div"
            class="invisible group-hover:visible">

        <MenuButton class="items-center flex justify-center w-5 h-5 text-gray-500 dark:text-gray-100">

          <button class="absolute right-2 top-8 inline-flex self-center items-center p-1.5 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800 dark:focus:ring-gray-600"
                  type="button">
            <IconDots class="w-4 h-4 text-gray-500 dark:text-gray-400"
                      stroke="2.5" />
          </button>

        </MenuButton>

        <transition enter-active-class="transition duration-100 ease-out transform"
                    enter-from-class="scale-90 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-100 ease-in transform"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-90 opacity-0">

          <MenuItems
                     class="absolute right-0 w-40 overflow-hidden origin-top-left bg-white border-gray-300 border rounded-md shadow-lg top-5 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:outline-none">

            <MenuItem>

            <button class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">
              <IconFileCheck stroke="2.5"
                             class="w-4 h-4" />
              <span>Add file</span>
            </button>

            </MenuItem>

            <MenuItem>

            <Link as="button"
                  preserve-scroll
                  :href="route('comments.destroy', props.comment)"
                  class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600"
                  method="delete">
            <IconTrash stroke="2.5"
                       class="w-4 h-4" />
            <span>Delete</span>
            </Link>

            </MenuItem>

          </MenuItems>

        </transition>

      </Menu>

    </div>
  </li>


  <li class="flex items-start gap-2.5" v-else>

    <div class="flex flex-col gap-1 w-full ml-[2.5rem] relative group">

      <div class="flex items-center space-x-2 rtl:space-x-reverse justify-end">

        <span class="text-sm font-semibold text-gray-900 dark:text-white">
          {{ props.comment.user.name }}
        </span>

        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
          {{ props.comment.created_at }}
        </span>

      </div>

      <div
           class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 rounded-s-xl rounded-ee-xl dark:bg-gray-700">

        <article
          class="prose font-normal dark:prose-invert"
          v-html="props.comment.body" />

        <div class="grid gap-4 my-2.5"
             v-if="props.comment.files.length"
             :class="{ 'grid-cols-2': props.comment.files.length > 1 }">

          <div class="group relative h-48"
               v-for="file in props.comment.files"
               :key="file.id">

            <div
                 class="absolute w-full h-full bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg flex items-center justify-center">

              <a download
                 :href="route('file.downloads', file.fid)"
                 class="inline-flex items-center justify-center rounded-full h-8 w-8 bg-white/30 hover:bg-white/50 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50">

                <IconDownload class="w-5 h-5 text-white"
                              stroke="2.5" />

              </a>

            </div>

            <img :src="`${file.full_url}`"
                 class="rounded-lg object-cover h-full w-full"
                 :alt="file.full_url">

          </div>

        </div>

      </div>

      <Menu as="div"
            class="invisible group-hover:visible">

        <MenuButton class="items-center flex justify-center w-5 h-5 text-gray-500 dark:text-gray-100">

          <button class="absolute right-2 top-8 inline-flex self-center items-center p-1.5 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800 dark:focus:ring-gray-600"
                  type="button">
            <IconDots class="w-4 h-4 text-gray-500 dark:text-gray-400"
                      stroke="2.5" />
          </button>

        </MenuButton>

        <transition enter-active-class="transition duration-100 ease-out transform"
                    enter-from-class="scale-90 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-100 ease-in transform"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-90 opacity-0">

          <MenuItems
                     class="absolute right-0 w-40 overflow-hidden origin-top-left bg-white border-gray-300 border rounded-md shadow-lg top-5 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:outline-none">

            <MenuItem>

            <button class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">
              <IconFileCheck stroke="2.5"
                             class="w-4 h-4" />
              <span>Add file</span>
            </button>

            </MenuItem>

            <MenuItem>

            <Link as="button"
                  preserve-scroll
                  :href="route('comments.destroy', props.comment)"
                  class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600"
                  method="delete">
            <IconTrash stroke="2.5"
                       class="w-4 h-4" />
            <span>Delete</span>
            </Link>

            </MenuItem>

          </MenuItems>

        </transition>

      </Menu>

    </div>

    <img
      class="w-8 h-8 rounded-full"
      :src="props.comment.user.avatar_url"
      :alt="props.comment.user.name">

  </li>
</template>

<style scoped></style>
