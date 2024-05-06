<script setup lang="ts">
import { useProjectStore } from '@/Stores/projectStore';
import { Link, router } from '@inertiajs/vue3';
import { IconTrash } from '@tabler/icons-vue';
import axios from 'axios';


const props = defineProps<{
  project: App.Data.ProjectData
}>()

const projectStore = useProjectStore()

const { setProject } = projectStore

const fetchProject = () => {

  axios
    .get(route('projects.show', { project: props.project.pid }))
    .then((resp) => {

      setProject(resp.data)

      setTimeout(() => {

        router.get(route('projects.show', { project: props.project.pid }))

      }, 50)

    })
    .catch((err) => {

      console.log(err);

    })

}

</script>

<template>
<div
  class="relative overflow-hidden flex flex-col gap-y-3 lg:gap-y-5 p-4 md:p-5 bg-white shadow rounded-lg dark:bg-neutral-900 group">

  <div class="inline-flex items-center gap-2">

    <img
        class="w-8 h-8 rounded-full"
        :src="props.project.author.avatar_url"
        alt="notices.data.user.name"
      />

    <span class="font-semibold text-gray-600 dark:text-neutral-400">
      {{ props.project.author.name }}
    </span>

  </div>

  <button
    @click="fetchProject"
    class="text-xl block font-thin text-gray-800 dark:text-neutral-200 text-left">
    <div class="line-clamp-3">

      {{ props.project.name }}

    </div>
  </button>

  <div class="flex-1 justify-end flex flex-col gap-2 divide-y divide-gray-200 dark:divide-neutral-800">

    <div>
      <span class="block text-xs dark:text-gray-600">
        Due by
      </span>

      <span class="block text-sm text-gray-500 dark:text-neutral-500">
        {{ props.project.due_date }}
      </span>
    </div>

    <div class="text-start pt-2">
      <span class="block text-xs dark:text-gray-600">
        Project for
      </span>

      <span class="block font-semibold text-sm text-gray-500 dark:text-neutral-500">
        {{ props.project.contact?.firm?.name ?? `${project.contact.first_name} ${project.contact.last_name}` }}
      </span>
    </div>

  </div>

  <div class="absolute hidden -bottom-2 -right-2 dark:bg-gray-600 bg-gray-300 h-12 w-12 rounded-md items-center justify-center group-hover:flex transition duration-300">

    <Link
      :href="route('projects.destroy', { ids: project.pid })"
      class="relative -left-1 -top-1 flex items-center justify-center text-gray-800 hover:text-rose-500 transition duration-300"
      method="delete"
      preserve-scroll
      as="button">

      <IconTrash class="h-6 w-6 align-bottom" />

    </Link>

  </div>

</div>

</template>
