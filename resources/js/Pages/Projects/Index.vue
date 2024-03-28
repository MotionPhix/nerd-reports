<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

import { formatTimeAgo } from '@vueuse/core'

// import { format } from 'date-fns'


import PageHeader from '@/Components/PageHeader.vue'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import Nav from '@/Shared/Nav.vue'
import { IconList, IconPlus, IconTrash } from '@tabler/icons-vue'

interface Props {
  projects: App.Data.ProjectData
}

defineProps<Props>()

function formatDate(dateString: string) {
  const date = new Date(dateString)
  return formatTimeAgo(date /* , 'dd MMM, yyyy HH:mm' */)
  // return format(date, 'dd MMM, yyyy HH:mm')
}

defineOptions({
  layout: AuthenticatedLayout,
})

function stringToColor(str: string) {
  let hash = 0
  for (let i = 0; i < str.length; i++)
    hash = str.charCodeAt(i) + ((hash << 5) - hash)

  const c = (hash & 0x00FFFFFF).toString(16).toUpperCase()
  return '00000'.substring(0, 6 - c.length) + c
}

function getProjectColor(name: string) {
  const color = stringToColor(name)
  return `#${color}`
}
</script>

<template>
  <Head title="Explore Projects" />

  <PageHeader>
    <h2 class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">
      Explore Projects <span class="text-gray-400 dark:text-gray-600">({{ projects.total }})</span>
    </h2>

    <Link
      v-if="projects.total" as="button"
      :href="route('projects.create')"
      class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200">
      <IconPlus class="w-4 h-4" />
      <span>Create project</span>
    </Link>

    <span class="flex-1" />

    <section v-if="projects.total" class="px-6 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">
      <Nav :pagination="projects" model-type="projects" />
    </section>
  </PageHeader>

  <section class="h-full pt-12">

    <div class="flex gap-8 mx-auto overflow-hidden shadow-sm max-w-7xl sm:px-6 lg:px-8">

      <section class="max-w-80 h-[80vh] overflow-y-auto scrollbar-none">

        <ul v-if="projects.total" class="grid grid-cols-1 gap-6">
          <Link
            v-for="project in projects.data" :key="project.id"
            as="li" class="relative flex-shrink-0 overflow-hidden transition duration-500 bg-orange-500 rounded-lg shadow-lg cursor-pointer group hover:bg-opacity-50"
            :style="{ backgroundColor: getProjectColor(project.name) }"
            :href="route('projects.show', project.pid)"
          >
            <Link
              as="button"
              method="delete"
              :href="route('projects.destroy', project.id)"
              class="absolute z-50 hidden p-2 transition duration-500 rounded-lg right-5 top-3 bg-slate-400 group-hover:grid place-content-center"
            >
              <IconTrash />
            </Link>

            <svg class="absolute bottom-0 left-0 mb-8" viewBox="0 0 375 283" fill="none" style="transform: scale(1.5); opacity: 0.1;">
              <rect x="159.52" y="175" width="152" height="152" rx="8" transform="rotate(-45 159.52 175)" fill="white" />
              <rect y="107.48" width="152" height="152" rx="8" transform="rotate(-45 0 107.48)" fill="white" />
            </svg>

            <div
              class="relative px-6 pt-6 text-sm text-gray-100"
            >
              <div
                class="absolute top-0 left-0 block w-48 h-48"
              />

              {{ project.contact?.company?.name }}
            </div>

            <div class="relative px-6 pb-6 mt-6 text-white">
              <span class="block -mb-1 opacity-75">
                {{ `${project.contact?.first_name} ${project.contact?.last_name}` }}
              </span>

              <div class="flex justify-between">
                <span class="block text-2xl font-thin leading-none">
                  {{ project.name }}
                </span>
              </div>
            </div>

            <div class="relative flex items-center px-6 bg-indigo-600 bg-opacity-80">
              <span class="block my-2 font-semibold text-gray-200 opacity-75">
                {{ `${project.deadline}` }}
              </span>
            </div>
          </Link>

        </ul>

        <div v-else class="flex flex-col items-center justify-center p-6 sm:px-6 lg:px-8">
          <div class="flex flex-col items-center w-full gap-2 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <IconList class="text-gray-400" />

            <h2 class="text-lg font-semibold leading-none text-center text-gray-500">
              No projects found!
            </h2>

            <p class="text-sm text-center text-gray-500">
              You don't have projects yet.
            </p>

            <div>
              <Link
                as="button"
                class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
                :href="route('projects.index', 'modal')"
              >
                <IconPlus />
                <span>Add project</span>
              </Link>
            </div>
          </div>
        </div>

      </section>

      <section class="h-[80vh] overflow-y-104 scrollbar-none flex-1">

        First project

      </section>
    </div>

    <template>
      <!-- <FormProject :action="null" width="max-w-2xl" /> -->
    </template>
  </section>
</template>
