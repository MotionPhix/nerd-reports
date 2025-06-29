<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';

interface Props {
  href: string
}

const props = defineProps<Props>()
</script>

<template>
  <Link
    class="flex items-center gap-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
    :href="href"
    as="button"
  >
    <slot />
  </Link>
</template>
