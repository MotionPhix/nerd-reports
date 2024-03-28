<script setup lang="ts">
import { useContactStore } from '@/Stores/contactStore';
import { Link, router } from '@inertiajs/vue3';
import { IconMailForward } from '@tabler/icons-vue';
import MazCheckbox from 'maz-ui/components/MazCheckbox';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import ContactEmail from './ContactEmail.vue';

const props = defineProps<{
  contact: App.Data.ContactData,
}>()

const contactStore = useContactStore()

const full_name = computed(() =>
  `${props.contact?.first_name} ${props.contact?.last_name}`,
)

const param: any = computed(() => route().params)

const {
  selectedContacts
} = storeToRefs(contactStore)

const { setSelectedContacts, unsetSelectedContacts, expungeSelectedContacts } = contactStore

function isSelected(contactId?: string) {
  return (selectedContacts.value.includes(contactId));
}

function onContactSelect(contactId?: string) {

  if (isSelected(contactId)) {
    unsetSelectedContacts(contactId)
  } else {
    setSelectedContacts(contactId)
  }
}

router.on('navigate', (e) => {

  if (e.detail.page.component !== 'Component/Compose' && selectedContacts.value.length > 0) {
    expungeSelectedContacts()
  }

})
</script>

<template>
  <li
    class="relative flex px-4 py-3 transition duration-300 ease-in-out border border-gray-300 rounded-full dark:border-gray-700 sm:py-4 hover:bg-gray-200 dark:hover:bg-gray-800"
    :class="{ 'bg-gray-300 dark:bg-gray-700': contact.cid === param.contact }">
    <div
      class="absolute z-20 flex items-center justify-center flex-shrink-0 w-10 h-10 font-semibold transition duration-300 rounded-full cursor-pointer hover:bg-transparent group"
      :class="selectedContacts.length ? '' : 'bg-lime-500 text-lime-900'">

      <span
        v-if="!selectedContacts.length"
        class="transition duration-300 empty:hidden group-hover:hidden">
        {{ `${contact.first_name[0]}${contact.last_name[0]}` }}
      </span>

      <span
        class="transition duration-300 group-hover:inline-flex"
        :class="selectedContacts.length ? 'inline-flex' : 'hidden'">
        <MazCheckbox
          @click="onContactSelect(contact.cid)"
          :model-value="isSelected(contact.cid)"
          color="success" />
      </span>
    </div>

    <Link
      class="flex items-center w-full gap-6 pl-16 text-left"
      :href="route('contacts.show', contact.cid)" as="button"
      preserve-scroll>

      <div class="flex-1 min-w-0">

        <p class="text-2xl font-medium text-gray-900 truncate text-balance dark:text-white">
            {{ full_name }}
        </p>

        <section class="flex items-center gap-2">

            <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                <ContactEmail :emails="contact?.emails" />
            </p>

            <span class="w-px h-4 bg-rose-500" v-if="props.contact?.job_title"></span>

            <p class="text-sm text-gray-500 truncate dark:text-gray-400" v-if="props.contact?.job_title">
               <strong>
                    {{ contact?.job_title }}
               </strong>
            </p>

        </section>
      </div>

    </Link>

    <div class="relative top-0 right-0 z-20 flex items-center justify-end cursor-pointer">

        <p class="text-sm text-gray-500 dark:text-gray-400">
            <IconMailForward class="w-6 h-6" />
        </p>

    </div>

  </li>
</template>
