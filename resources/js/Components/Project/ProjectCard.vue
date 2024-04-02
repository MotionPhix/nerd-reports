<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { IconBuilding, IconBuildingBroadcastTower, IconTrash } from "@tabler/icons-vue"

interface Props {
  project: App.Data.ProjectData
}

defineProps<Props>()
</script>

<template>
  <li class="flex items-start p-6 space-x-6">
    <!-- <img
      :src="project.image"
      alt=""
      width="60"
      height="88"
      class="flex-none rounded-md bg-slate-100"
    /> -->

    <div>
      <IconBuildingBroadcastTower
        width="60"
        height="88"
        class="flex-none border border-gray-400 rounded-md dark:border-gray-600 bg-slate-100 dark:bg-slate-700 text-slate-300" />
    </div>

    <div class="relative flex-auto min-w-0 dark:text-gray-300">
      <Link
        class="pr-20 text-2xl font-semibold capitalize truncate text-slate-900 dark:text-slate-300"
        :href="route('projects.show', { project: project.pid })"
        as="button">
        {{ project.name }}
      </Link>

      <dl class="flex flex-wrap mt-2 text-sm font-medium leading-6">
        <div class="absolute top-0 right-0 flex items-center space-x-1">
          <dt class="text-sky-500">
            <span class="sr-only">Progress</span>
            <svg width="16" height="20" fill="currentColor">
              <path
                d="M7.05 3.691c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.372 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118L.98 9.483c-.784-.57-.381-1.81.587-1.81H5.03a1 1 0 00.95-.69L7.05 3.69z"
              />
            </svg>
          </dt>

          <dd>{{ project.status }}</dd>
        </div>

        <div>
          <dt class="sr-only">Closing date</dt>
          <dd class="px-1.5 ring-1 ring-slate-300 dark:ring-slate-600 rounded">
            Deadline
          </dd>
        </div>

        <div class="ml-2">
          <dt class="sr-only">Deadline</dt>
          <dd>{{ project.due_date }}</dd>
        </div>

        <div v-if="!! project.contact?.firm?.name">
          <dt class="sr-only">Firm</dt>
          <dd class="flex items-center">
            <svg
              width="2"
              height="2"
              fill="currentColor"
              class="mx-2 text-slate-300"
              aria-hidden="true"
            >
              <circle cx="1" cy="1" r="1" />
            </svg>
            {{ project.contact?.firm?.name }}
          </dd>
        </div>

        <div>
          <dt class="sr-only">Contact person</dt>
          <dd class="flex items-center">
            <svg
              width="2"
              height="2"
              fill="currentColor"
              class="mx-2 text-slate-300"
              aria-hidden="true"
            >
              <circle cx="1" cy="1" r="1" />
            </svg>
            {{ `${project.contact.first_name} ${project.contact.last_name}` }}
          </dd>
        </div>

        <!-- <div class="flex-none w-full mt-2 font-normal">
          <dt class="sr-only">Team</dt>
          <dd class="text-slate-400">{{ project.team }}</dd>
        </div> -->
      </dl>
    </div>
  </li>
</template>
