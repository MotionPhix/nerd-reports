<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { IconArrowLeft, IconBuildingFortress, IconClockUp, IconClockDown, IconTrash } from '@tabler/icons-vue'
import { computed, ref } from "vue"
import ProjectNameForm from '@/Pages/Projects/ProjectNameForm.vue'
import useStickyTop from "@/Composables/useStickyTop"
import BoardList from '@/Pages/Projects/Boards/BoardList.vue'

const props = defineProps<Props>()

interface Props {
  project: App.Data.ProjectFullData,
}

defineOptions({
  layout: AuthenticatedLayout,
})

const projectName = ref(props.project.name);

const title = computed(() => props.project.contact?.firm?.name ?? props.project.contact.first_name + ' ' + props.project.contact.last_name)

const { navClasses } = useStickyTop();

function updateProjectName (project: App.Data.ProjectFullData) {

  projectName.value = project.name

}
</script>

<template>
  <Head :title="title" />

  <nav
    class="flex items-center w-full h-16 max-w-5xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses">

    <Link
      :href="route('projects.index')"
      as="button"
      preserve-state
      preserve-scroll>
      <IconArrowLeft class="h-7" stroke="2.5" />
    </Link>

    <ProjectNameForm
      :project="props.project"
      @saved="updateProjectName"
    />

    <span class="flex-1"></span>

    <Link
      method="delete"
      :href="route('projects.destroy', { ids: props.project.pid })"
      class="flex items-center gap-2 duration-200 hover:opacity-75"
      preserve-scroll
      as="button">
      <IconTrash class="h-7" />
      <span>Delete</span>
    </Link>

  </nav>

  <section class="relative flex flex-col max-w-3xl gap-24 px-6 pt-12 mx-auto">

    <article class="flex" v-if="!! props.project.contact.firm">

      <div class="flex-none bg-gray-300 dark:bg-gray-700 rounded-xl">
        <IconBuildingFortress
          class="w-72 dark:text-gray-400" stroke="0.75" />
      </div>

      <form class="flex-auto p-6">

        <div class="flex flex-wrap">

          <h1 class="flex-auto font-medium text-slate-900 dark:text-slate-200">
            {{ `${props.project.contact.first_name} ${props.project.contact.last_name}` }}
          </h1>

          <div class="flex-none order-1 w-full mt-2 text-5xl font-bold text-lime-600">
            {{ props.project.contact.firm.name }}
          </div>

          <div class="text-sm font-medium text-slate-400">
            {{ props.project.contact.emails[0].email }}
          </div>
        </div>

        <div class="my-24"></div>

        <div class="flex mb-5 space-x-4 text-sm font-medium">
          <div class="flex flex-auto space-x-4">
            <button
              type="button"
              class="h-10 px-6 font-semibold text-white rounded-full bg-lime-600">
              Upload files
            </button>

            <button
              class="h-10 px-6 font-semibold border rounded-full dark:border-gray-700 dark:text-slate-300 border-slate-200 text-slate-900"
              type="button">
              Delete
            </button>
          </div>

          <button
            type="button"
            class="flex items-center justify-center flex-none bg-gray-400 rounded-full text-lime-100 w-9 h-9 dark:text-lime-600 dark:bg-lime-50"
            aria-label="Like">
            <svg width="20" height="20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" />
            </svg>
          </button>
        </div>

        <p class="text-sm text-slate-500">
          This is the brief information about the company and the contact person
          handling the project. You can get in touch with them should need be.
        </p>
      </form>
    </article>

    <article v-else class="text-3xl dark:text-gray-400">
      For {{ `${props.project.contact.first_name} ${props.project.contact.last_name}` }}
    </article>

    <div class="space-y-6">

      <h2 class="text-5xl font-display dark:text-gray-300">
        {{ projectName }}
      </h2>

      <hr class="dark:border-gray-600">

      <section
        v-html="props.project.description"
        class="pt-8 prose dark:prose-invert empty:hidden"
        v-if="props.project.description" />
    </div>

    <section class="grid grid-cols-1 gap-4 dark:text-white md:grid-cols-2 font-display">

      <div
        class="p-4 space-y-2 border border-gray-400 rounded-md dark:border-gray-700">
        <p class="font-semibold">
          Start date
        </p>

        <p class="flex items-center gap-2">
          <IconClockUp class="h-6" />

          <span>
            {{ project.created_at }}
          </span>
        </p>
      </div>

      <div class="p-4 space-y-2 border border-gray-400 rounded-md dark:border-gray-700">
        <p class="font-semibold">
          Due date
        </p>

        <p class="flex items-center gap-2">
          <IconClockDown class="h-6" />

          <div class="flex flex-col">
            <span>
              {{ project.due_date }}
            </span>

            <span class="font-sans text-xs text-gray-400 dark:text-gray-500">
              Deadline {{ project.deadline.includes('now') ? 'is' : 'was'}} {{ project.deadline }}.
            </span>
          </div>
        </p>
      </div>
    </section>

  </section>

  <section>

    <!-- start -->
  <div class="flex flex-col max-w-3xl gap-24 px-6 py-12 mx-auto">

    <h2
      class="text-3xl dark:text-gray-400 font-display">
      Tasks
    </h2>

  </div>

    <!-- <div class="flex-1 overflow-x-auto scrollbar-thin">
      <div class="grid items-start h-full grid-cols-1 gap-4 sm:grid-cols-2">
        <ProjectBoardList
          v-for="board in project.boards"
          :key="board.id"
          :board="board"
          class="h-full rounded-md"
        />

        <ProjectBoardForm :project="project" />
      </div>
    </div> -->

    <BoardList :project="project" />

  </section>
</template>
