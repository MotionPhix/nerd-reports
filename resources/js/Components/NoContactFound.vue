<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import IconContacts from '@/Components/Icon/IconContacts.vue';
import IconContactList from './Icon/IconContactList.vue';

const { url } = usePage()

const context = computed(() => {
  if (url.startsWith('/contacts/favourites')) {
    return {
      title: 'favourites',
      description: 'You haven\'t added anyone to your favourites yet'
    }
  }

  if (url.startsWith('/contacts/deleted')) {
    return {
      title: 'deleted contacts',
      description: 'All good! You don\'t have deleted any contacts.'
    }
  }

  if (url.startsWith('/g/i')) {
    return {
      title: 'contact groups',
      description: 'You don\'t have contact groups yet.'
    }
  }

  return {
    title: 'contacts',
    description: 'You haven\'t added any contacts yet.'
  }

})
</script>

<template>
  <div class="flex flex-col items-center gap-3">
    <IconContactList v-if="$page.url.startsWith('/g')" class="w-48 h-48 text-gray-400" stroke-width="1" />
    <IconContacts v-else class="w-48 h-48 text-gray-400" stroke-width="1" />

    <h2 class="mt-6 text-xl font-semibold leading-none text-center text-gray-500">
      No {{ context.title }} found!
    </h2>

    <p class="text-sm text-center text-gray-500">
      {{ context.description }}
    </p>

    <slot />
  </div>
</template>
