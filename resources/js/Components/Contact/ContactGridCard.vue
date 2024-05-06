<script setup lang="ts">
import { useContactStore } from '@/Stores/contactStore';
import { Link, router } from '@inertiajs/vue3';
import { IconFileExport, IconPencil } from '@tabler/icons-vue';
import MazCheckbox from 'maz-ui/components/MazCheckbox';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import ContactEmail from '@/Components/Contact/ContactEmail.vue';

const props = defineProps<{
  contact: App.Data.ContactData,
}>()

const contactStore = useContactStore()

const full_name = computed(() => `${props.contact?.first_name} ${props.contact?.last_name}`)

const param: any = computed(() => route().params)

const {

  selectedContacts

} = storeToRefs(contactStore)

const { selectContact, unselectContact, resetSelectedContacts } = contactStore

function isSelected(contactId?: string) {

  return (selectedContacts.value.includes(contactId));

}

function onContactSelect(contactId?: string) {

  if (isSelected(contactId)) {

    unselectContact(contactId)

  } else {

    selectContact(contactId)

  }
}

router.on('navigate', (e) => {

  if (e.detail.page.component !== 'Component/Compose' && selectedContacts.value.length > 0) {

    resetSelectedContacts()

  }

})
</script>

<template>
  <li
    class="relative flex px-4 py-3 transition duration-300 ease-in-out border border-gray-300 rounded-xl dark:border-gray-700 sm:py-4 hover:bg-gray-200 dark:hover:bg-gray-800 group"
    :class="{ 'bg-gray-300 dark:bg-gray-700': props.contact.cid === param.contact }">
    <div
      class="absolute z-20 flex items-center justify-center flex-shrink-0 w-10 h-10 font-semibold transition duration-300 rounded-full cursor-pointer hover:bg-transparent group"
      :class="selectedContacts.length ? '' : 'bg-lime-500 text-lime-900'">

      <span
        v-if="!selectedContacts.length"
        class="transition duration-300 empty:hidden group-hover:hidden">
        {{ `${props.contact.first_name[0]}${props.contact.last_name[0]}` }}
      </span>

      <span
        class="transition duration-300 group-hover:inline-flex"
        :class="selectedContacts.length ? 'inline-flex' : 'hidden'">
        <MazCheckbox
          @click="onContactSelect(props.contact.cid)"
          :model-value="isSelected(props.contact.cid)"
          color="success" />
      </span>
    </div>

    <Link
      class="flex items-center w-full gap-6 pl-16 text-left"
      :href="route('contacts.show', props.contact.cid)" as="button"
      preserve-scroll>

      <div class="flex-1 min-w-0">

        <p class="flex items-center gap-2 text-2xl font-medium text-gray-900 truncate text-balance dark:text-white">

          <span class="capitalize">{{ props.contact.title }}</span>

          <span>{{ full_name }}</span>

        </p>

        <strong
          class="block text-sm text-gray-500 truncate dark:text-gray-300"
          v-if="props.contact?.job_title">

          {{ props.contact?.job_title }}

        </strong>

        <span
          class="text-sm text-gray-500 dark:text-gray-400 block">

          <ContactEmail :emails="props.contact?.emails" />

        </span>

      </div>

    </Link>

    <div
      class="hidden group-hover:flex absolute bottom-2 right-2 z-20 items-center justify-end gap-2 cursor-pointer"
      v-tooltip="{
        theme: {
          placement: 'left'
        },
      }">

        <p class="text-sm text-gray-500 dark:text-gray-400">
            <Link
              as="button"
              :href="route('projects.create', props.contact.cid)"
              v-tooltip="`Add project for ${props.contact?.firm?.name ?? props.contact.first_name + ' ' + props.contact.last_name}`">
              <IconFileExport stroke="2.5" class="w-6 h-6" />
            </Link>
        </p>

        <p
          class="text-sm text-gray-500 dark:text-gray-400">
          <Link
            as="button"
            v-tooltip="`Edit ${props.contact?.firm?.name ?? props.contact.first_name + ' ' + props.contact.last_name}`"
            :href="route('contacts.edit', { 'cid': props.contact.cid })">

            <IconPencil stroke="2.5" class="w-6 h-6" />

          </Link>
        </p>

    </div>

  </li>
</template>
