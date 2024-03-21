<script setup lang="ts">
import IconContactAdd from '@/Components/Icon/IconContactAdd.vue';
import IconContactRemove from '@/Components/Icon/IconContactRemove.vue';
import { useContactStore } from '@/Stores/contactStore';
import { Link, usePage } from '@inertiajs/vue3';
import { IconArrowCapsule, IconHeartMinus, IconHeartPlus, IconMailForward, IconPencil, IconTrash } from '@tabler/icons-vue';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';

interface Props {
  contacts: Object
}

const props = defineProps<Props>()

const url = ref(usePage().url)

const contactStore = useContactStore()

const { expungeSelectedContacts } = contactStore

const {
  selectedContacts
} = storeToRefs(contactStore)

if (!url.value.startsWith('/m/compose') && selectedContacts.value.length) {
  expungeSelectedContacts()
}
</script>

<template>
  <section
    class="sticky top-0 flex items-center w-full h-16 gap-6 p-6 pl-8 border-b rounded-md dark:text-white dark:border-gray-700"
      v-if="!selectedContacts.length">

    <Link
      as="button"
      :href="route('contacts.create')"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconContactAdd class="w-5 h-5 stroke-current" /> <span>New contact</span>
    </Link>

  </section>

  <section
    class="flex items-center w-full h-16 gap-6 p-6 pl-8 border-b dark:text-white dark:border-gray-700"
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
      method="patch"
      v-if="Object.keys(props.contacts).length && (!$page.url.startsWith('/contacts/deleted') && !$page.url.startsWith('/contacts/favourites'))"
      :href="route('contacts.favourite', { ids: selectedContacts } as any)"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconHeartPlus class="w-5 h-5 stroke-current" /> <span class="hidden md:inline-flex">Add to favourites</span>
    </Link>

    <Link
      as="button"
      method="patch"
      v-if="Object.keys(contactBase).length && $page.url.startsWith('/contacts/favourites')"
      :href="route('contacts.favourite', { ids: selectedContacts } as any)"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconHeartMinus class="w-5 h-5 stroke-current" /> <span class="hidden md:inline-flex">Remove from favourites</span>
    </Link>

    <Link
      as="button"
      method="delete"
      v-if="$page.url === '/contacts' || $page.url.startsWith('/tags')"
      :href="route('contacts.destroy', { ids: selectedContacts } as any)"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconTrash class="w-5 h-5 stroke-current" /> <span class="hidden md:inline-flex">Delete</span>
    </Link>

    <Link
      as="button"
      method="delete"
      v-if="$page.url.startsWith('/contacts/deleted')"
      :href="route('contacts.destroy', { ids: selectedContacts } as any)"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70">
      <IconContactRemove class="w-5 h-5 stroke-current text-rose-500" /> <span class="hidden md:inline-flex">Delete</span>
    </Link>

    <Link
      as="button"
      method="put"
      v-if="$page.url.startsWith('/contacts/deleted')"
      :href="route('contacts.restore', { ids: selectedContacts } as any)"
      class="flex items-center gap-2 font-semibold transition duration-300 hover:opacity-70"
      preserve-scroll>

      <IconArrowCapsule class="w-5 h-5 stroke-current" />
      <span class="hidden md:inline-flex">
        Restore
      </span>

    </Link>

    <Link
      :href="route('mail.compose')"
      class="flex items-center gap-2 px-1.5 font-semibold rounded-md"
      v-if="!$page.url.startsWith('/contacts/deleted')"
      preserve-scroll
      as="button">

      <IconMailForward class="w-5 h-5" />
      <span class="hidden md:inline-flex">Send Email</span>

    </Link>

  </section>
</template>
