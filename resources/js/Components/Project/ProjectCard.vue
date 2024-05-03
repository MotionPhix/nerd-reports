<script setup lang="ts">
import { useProjectStore } from "@/Stores/projectStore";
import { Link, router } from "@inertiajs/vue3";
import { IconBuilding, IconBuildingBroadcastTower, IconTrash } from "@tabler/icons-vue"
import axios from "axios";

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
      <button
        class="pr-20 text-2xl font-semibold capitalize truncate text-slate-900 dark:text-slate-300"
        @click="fetchProject">
        {{ project.name }}
      </button>

      <dl class="flex flex-wrap mt-2 text-sm font-medium leading-6">
        <div class="absolute top-0 right-0 flex items-center gap-4">

          <Link
            :href="route('projects.destroy', { ids: project.pid })"
            class="inline-flex text-sky-500 hover:text-rose-500 transition duration-300"
            method="delete"
            preserve-scroll
            as="button">

            <IconTrash class="h-4 w-4 align-bottom" />

          </Link>

          <span
            class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2 py-1 rounded dark:bg-blue-900 dark:text-blue-300">
            {{ project.status }}
          </span>
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
