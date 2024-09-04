<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import {
  IconPlus,
  IconReceipt,
} from "@tabler/icons-vue";

import Pagination from "@/Components/Project/Pagination.vue";

import useStickyTop from "@/Composables/useStickyTop";

import ProjectCard from "@/Components/Project/ProjectCard.vue";

import ProjectGrid from "@/Components/Project/ProjectGrid.vue";

import ProjectGridCard from "@/Components/Project/ProjectGridCard.vue";

import CardList from "@/Components/CardList.vue";

import { useProjectStore } from "@/Stores/projectStore";

import { storeToRefs } from "pinia";

import { onMounted } from "vue";

const projectStore = useProjectStore()

const { fetchProjects } = projectStore

const { projects } = storeToRefs(projectStore)

const { navClasses } = useStickyTop();

onMounted(async () => {
  await fetchProjects()
})

defineOptions({
  layout: AuthenticatedLayout,
});
</script>

<template>
  <Head title="Explore Projects" />

  <nav
    class="flex items-center h-16 max-w-3xl gap-6 px-6 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses">
    <h2
      class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">
      <span>Explore Projects</span>

      <span class="text-gray-400 dark:text-gray-600">
        ({{ projects.total }})
      </span>
    </h2>

    <span class="flex-1"></span>

    <Link
      as="button"
      :href="route('projects.create')"
      class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200">
      <IconPlus stroke="2.5" class="w-4 h-4" />
      <span class="hidden sm:inline-block">Create</span>
    </Link>
  </nav>

  <section class="max-w-3xl px-6 py-12 mx-auto">

    <ProjectGrid
      v-if="projects.total">

      <ProjectGridCard
        :project="project"
        v-for="project in projects.data"
        :key="project.id" />

    </ProjectGrid>

    <div
      v-else
      class="flex flex-col items-center justify-center flex-1 px-8">
      <div
        class="flex flex-col items-center w-full gap-2 py-12 mx-auto sm:px-6 lg:px-8">
        <IconReceipt class="text-gray-400 h-36 w-36" />

        <h2
          class="text-2xl font-semibold leading-none text-center dark:text-gray-400">
          No projects found!
        </h2>

        <p class="text-sm text-center text-gray-500">
          You don't have projects yet.
        </p>

        <div>
          <Link
            as="button"
            class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
            :href="route('projects.create')">
            <IconPlus class="w-5 h-5" stroke="2.5" />
            <span>Add project</span>
          </Link>
        </div>
      </div>
    </div>
  </section>
</template>
