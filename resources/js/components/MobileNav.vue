<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { ScrollArea } from '@/components/ui/scroll-area'

defineProps<{
  navigation: Array<{
    name: string
    href: string
    icon: any
  }>
}>()
</script>

<template>
  <div class="flex h-full flex-col bg-white dark:bg-gray-900">
    <div class="p-6">
      <ApplicationLogo class="h-8 w-auto" />
    </div>
    <ScrollArea class="flex-1">
      <div class="space-y-1 px-3">
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          :class="[
            route().current(item.href)
              ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800',
            'group flex items-center rounded-md px-3 py-2 text-sm font-medium'
          ]"
        >
          <component
            :is="item.icon"
            :class="[
              route().current(item.href)
                ? 'text-gray-900 dark:text-white'
                : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
          />
          {{ item.name }}
        </Link>
      </div>
    </ScrollArea>
  </div>
</template>
