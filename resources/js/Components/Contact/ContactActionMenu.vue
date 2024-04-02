<script setup lang="ts">
import IconContactAdd from '@/Components/Icon/IconContactAdd.vue';
import { useContactStore } from '@/Stores/contactStore';
import { Link } from '@inertiajs/vue3';
import { IconPencil, IconTrash } from '@tabler/icons-vue';
import { storeToRefs } from 'pinia';
import useStickyTop from "@/Composables/useStickyTop"

interface Props {
  contacts: Object
}

const props = defineProps<Props>()

const contactStore = useContactStore()

const { navClasses } = useStickyTop();

const {
  selectedContacts
} = storeToRefs(contactStore)
</script>

<template>
  <nav
    class="flex items-center w-full h-16 gap-6 px-8 dark:text-white dark:border-gray-700"
    :class="navClasses"
      v-if="!selectedContacts.length">

    <Link
      as="button"
      :href="route('contacts.create')"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconContactAdd class="w-5 h-5 stroke-current" /> <span>New contact</span>
    </Link>

  </nav>

  <nav
    class="flex items-center w-full h-16 gap-6 px-8 dark:text-white dark:border-gray-700"
    :class="navClasses"
    v-if="selectedContacts.length && Object.keys(props.contacts).length">

    <Link
      as="button"
      :href="route('contacts.edit', selectedContacts[0])"
      v-if="selectedContacts.length === 1 && $page.url === '/contacts'"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconPencil class="w-5 h-5 stroke-current" /> <span class="hidden md:inline-flex">Edit</span>
    </Link>

    <Link
      as="button"
      method="delete"
      :href="route('contacts.destroy', { ids: selectedContacts } as any)"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconTrash class="w-5 h-5 stroke-current" /> <span class="hidden md:inline-flex">Delete</span>
    </Link>

  </nav>
</template>
