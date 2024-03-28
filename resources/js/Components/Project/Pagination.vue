<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import type { ContactApiResponse, ProjectApiResponse, TaskApiResponse } from '@/types'
import { setPage } from '@/Stores/pagination'

const props = defineProps<{
  pagination: ProjectApiResponse | ContactApiResponse | TaskApiResponse
  modelType: string
}>()

onMounted(() => {
  const activeLink = props.pagination?.links?.find(element => element.active === true)
  setPage(activeLink?.label)
})

function activePage(page: number) {
  setPage(page)
}
</script>

<template>
  <nav class="flex items-center justify-between gap-4">
    <div>
      <!-- Show the current page and total number of pages -->
      <span class="text-sm text-gray-700 dark:text-gray-300">
        Showing <strong>{{ pagination.from }}</strong> to <strong>{{ pagination.to }}</strong> of {{ pagination.total }} <strong>{{ modelType }}</strong>
      </span>
    </div>

    <div v-if="pagination?.links?.length > 3">
      <div class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in pagination.links">
          <div
            v-if="link.url === null" :key="key"
            class="px-3 py-2 mb-1 mr-1 text-sm leading-4 text-gray-400 border rounded-lg dark:border-gray-700" v-html="link.label"
          />

          <Link
            v-else
            :key="`link-${key}`"
            as="button"
            preserve-scroll
            class="p-2 px-3 mb-1 mr-1 text-sm leading-4 border rounded-lg dark:border-gray-700 focus:text-indigo-500 hover:bg-white focus:border-indigo-500"
            :class="{ 'dark:bg-white font-semibold bg-gray-600 text-gray-100 dark:text-gray-600': link.active }"
            :href="link.url"
            @click="activePage(link.label)"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </nav>
</template>
