<script setup lang="ts">
import { IconSearch } from '@tabler/icons-vue';

</script>

<template>
  <form class="flex items-center max-w-xl flex-1">

    <label for="q-search" class="sr-only">Search</label>

    <div class="relative w-full">

      <input
        type="text"
        id="q-search"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-3 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        placeholder="Search projects, contacts, tasks..."
        required
      />

      <button
        type="button"
        class="absolute inset-y-0 end-0 flex items-center pe-3">
        <IconSearch
          class="w-5 h-5 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white" />

      </button>

    </div>

  </form>
</template>
