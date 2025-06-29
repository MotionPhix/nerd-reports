<script lang="ts" setup>
import type { Contact } from '@/types/index';
import { Link } from '@inertiajs/vue3';
import { IconAddressBook, IconPlus } from '@tabler/icons-vue';

interface Props {
  contact: Contact
}

const props = defineProps<Props>()
</script>

<template>
  <!-- <div v-if="contact?.interactions[0]?.id">
    Here are the interactions
  </div> -->

  <div class="empty:hidden flex flex-col items-center gap-3 text-gray-500">
    <IconAddressBook class="text-gray-400 h-48 w-48" stroke-width="1" />

    <h2 class="text-xl font-semibold leading-none text-center mt-6">
      No interactions found!
    </h2>

    <p class="text-sm text-center">
      Start interacting with <strong>{{ contact.first_name }}</strong>.
    </p>

    <div>
      <Link as="button"
        class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
        :href="route('contacts.edit', contact.cid)" preserve-scroll>

        <IconPlus class="w-5 h-5" />
        <span>Add interaction</span>

      </Link>
    </div>
  </div>
</template>
