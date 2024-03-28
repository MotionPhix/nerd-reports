<script setup
        lang="ts">
        import { Head, Link } from '@inertiajs/vue3'

        import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

        import { IconList, IconPlus, IconProtocol, IconReceipt, IconTrash } from '@tabler/icons-vue'

        import Pagination from '@/Components/Project/Pagination.vue'

        import { computed } from 'vue'

        import { onMounted } from 'vue'

        import { onUnmounted } from 'vue'

        import { ref } from 'vue'

        import { watchEffect } from 'vue'

        import useStickyTop from '@/Composables/useStickyTop';

        interface Props {
          projects: App.Data.ProjectData
        }

        defineProps<Props>()

        const { sectionClasses } = useStickyTop();

        defineOptions({
          layout: AuthenticatedLayout,
        })
</script>

<template>

  <Head title="Explore Projects" />

  <section class="flex items-center w-full h-16 gap-6 p-6 pl-8 dark:text-white dark:border-gray-700"
           :class="sectionClasses">
    <h2 class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">
      Explore Projects <span class="text-gray-400 dark:text-gray-600">({{ projects.total }})</span>
    </h2>

    <span class="flex-1"></span>

    <Link as="button"
          :href="route('projects.create')"
          class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200">
    <IconPlus stroke="2.5"
              class="w-4 h-4" />
    <span>Create project</span>
    </Link>

    <section v-if="projects.total"
             class="px-6 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">
      <Pagination :pagination="projects"
                  model-type="projects" />
    </section>
  </section>

  <section class="flex pt-12 gap-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

      <ul v-if="projects.total"
          class="grid grid-cols-1 gap-6">
        <Link v-for="project in projects.data"
              :key="project.id"
              as="li"
              class="relative flex-shrink-0 overflow-hidden transition duration-500 bg-orange-500 rounded-lg shadow-lg cursor-pointer group hover:bg-opacity-50"
              :href="route('projects.show', project.pid)">
        <Link as="button"
              method="delete"
              :href="route('projects.destroy', project.id)"
              class="absolute z-50 hidden p-2 transition duration-500 rounded-lg right-5 top-3 bg-slate-400 group-hover:grid place-content-center">
        <IconTrash />
        </Link>

        <svg class="absolute bottom-0 left-0 mb-8"
             viewBox="0 0 375 283"
             fill="none"
             style="transform: scale(1.5); opacity: 0.1;">
          <rect x="159.52"
                y="175"
                width="152"
                height="152"
                rx="8"
                transform="rotate(-45 159.52 175)"
                fill="white" />
          <rect y="107.48"
                width="152"
                height="152"
                rx="8"
                transform="rotate(-45 0 107.48)"
                fill="white" />
        </svg>

        <div class="relative px-6 pt-6 text-sm text-gray-100">
          <div class="absolute top-0 left-0 block w-48 h-48" />

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

      <div
        v-else
        class="flex flex-col items-center justify-center p-6 sm:px-6 lg:px-8 flex-1">
        <div class="flex flex-col items-center w-full gap-2 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <IconReceipt class="text-gray-400 h-36 w-36" />

          <h2 class="text-2xl font-semibold leading-none text-center dark:text-gray-400">
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
              <IconPlus class="h-5 w-5" stroke="2.5" />
              <span>Add project</span>
            </Link>
          </div>
        </div>
      </div>

  </section>
</template>
