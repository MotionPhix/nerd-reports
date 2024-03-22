<script setup>
import { useDark } from '@vueuse/core';

const isDark = useDark()

</script>

<template>
  <div class="flex flex-col items-center w-full h-screen overflow-y-auto bg-gray-100 dark:bg-gray-900">
    <slot />
  </div>
</template>
