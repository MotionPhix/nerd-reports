<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue'
import Tooltip from '@/Components/Tooltip.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { paginationStore } from '@/Stores/pagination'
import type { Company, Project, ProjectApiResponse } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { IconArrowLeft, IconClockUp, IconLayoutCards } from '@tabler/icons-vue'
import { computed } from 'vue'
import BoardList from './BoardList.vue'
import CreateBoardForm from './CreateBoardForm.vue'
import ProjectNameForm from './ProjectNameForm.vue'

const props = defineProps<Props>()

interface Props {
  projects?: ProjectApiResponse
  project: Project
  companies?: Company[]
  canOpen?: boolean
}

defineOptions({
  layout: AuthenticatedLayout,
})

const title = computed(() => props.project.contact?.company?.name)
const options = computed(() => paginationStore.page > 1 ? { page: paginationStore.page } : {})
</script>

<template>
  <Head :title="title" />

  <PageHeader>
    <Link
      :href="route('projects.index', options)"
      as="button"
      preserve-state
      preserve-scroll
    >
      <IconArrowLeft />
    </Link>

    <h2 class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">
      <span>{{ project.contact?.company?.name }}</span> <span>|</span> <span class="font-thin"> {{ `${project.contact?.first_name} ${project.contact?.last_name}` }}</span>
    </h2>

    <span class="flex-1" />

    <div class="flex -space-x-3">
      <Tooltip v-for="user in project.users?.slice(0, 3)" :key="user.id" :content="user.id === $page.props.auth.user.id ? 'Me' : user.name" position="bottom">
        <div class="relative w-8 h-8 overflow-hidden bg-gray-100 border-2 border-white rounded-full dark:border-gray-800 dark:bg-gray-600">
          <svg class="absolute w-10 h-10 text-gray-400 -left-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
        </div>
      </Tooltip>

      <button
        v-if="project.users?.length > 3"
        class="relative z-10 flex items-center justify-center w-8 h-8 font-medium text-white bg-gray-700 border-2 border-white rounded-full text-xxs hover:bg-gray-600 dark:border-gray-800"
      >
        {{ `+${project.users?.length - 3}` }}
      </button>
    </div>
  </PageHeader>

  <div class="h-[calc(90vh-20px)] pt-6">
    <div class="flex flex-col h-full mx-auto overflow-hidden max-w-7xl sm:px-6 lg:px-8">
      <div class="p-4">
        <section class="flex items-center gap-2">
          <IconLayoutCards class="dark:text-white" />
          <ProjectNameForm :project="project" />
        </section>

        <section class="mt-5 dark:text-white">
          <div>
            <p>
              Start date
            </p>

            <p>
              <IconClockUp />
              <span>
                {{ project.deadline }}
              </span>
            </p>
          </div>
        </section>
      </div>

      <!-- start -->

      <div class="flex-1 overflow-x-auto">
        <div class="inline-flex items-start h-full gap-4">
          <BoardList
            v-for="board in project.boards"
            :key="board.id"
            :board="board"
            class="flex flex-col max-h-full bg-gray-200 rounded-md w-72"
          />

          <article class="w-72">
            <CreateBoardForm :project="project" />
          </article>
        </div>
      </div>

      <!-- finish -->
    </div>
  </div>
</template>
