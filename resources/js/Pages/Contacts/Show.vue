<script setup lang="ts">
import ContactActionMenu from '@/Components/ContactActionMenu.vue';
import ContactCard from '@/Components/ContactCard.vue';
import IconContacts from '@/Components/Icon/IconContacts.vue';
import NavTabs from '@/Components/NavTabs.vue';
import PrimaryButtonLink from '@/Components/PrimaryButtonLink.vue';
import InteractionsTab from '@/Components/Tab/InteractionsTab.vue';
import OverviewTab from '@/Components/Tab/OverviewTab.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useContactStore } from '@/Stores/contactStore';
import type { Contact, ContactsBase, Phone } from '@/types/index';
import { Head, Link, router } from '@inertiajs/vue3';
import { IconMailFast, IconPencil, IconPhone, IconTrash, IconUserMinus } from '@tabler/icons-vue';
import { sample } from 'lodash';
import { storeToRefs } from 'pinia';

interface Props {
  baseGroup: ContactsBase
  contact: Contact
}

const props = defineProps<Props>()

const contactStore = useContactStore()

const {
  selectedContacts
} = storeToRefs(contactStore)

const { setSelectedContacts } = contactStore

function onCheckSelection() {

  if (!selectedContacts.value.length) setSelectedContacts(props.contact.cid)

}

const onSendMail = () => {
  router.get(route('mail.compose'))
}

function getRandomPhoneNumber(phones?: Phone[]) {
  const validPhones = phones?.filter(phone => phone.type !== 'fax');
  return sample(validPhones)?.number || ''; // Ensure there's a fallback in case the array is empty
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
  <Head :title="contact.first_name + ' ' + contact.last_name" />

  <ContactActionMenu :contactBase="baseGroup" />

  <div class="flex max-w-6xl gap-6 pt-12 mx-auto mb-10 lg:mb-0 lg:h-screen sm:px-6">

    <section
      class="hidden w-full max-w-sm pt-12 space-y-4 overflow-y-auto scrollbar-thin lg:block">
      <div
        v-for="(contactsArray, group) in baseGroup"
        :key="group"
        class="pb-6 mr-4">
        <span
          class="flex items-center justify-center w-8 h-8 p-1 mb-4 ml-20 font-bold text-white bg-gray-600 rounded dark:bg-gray-200 dark:text-gray-900">
          {{ group }}
        </span>

        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-500">
          <ContactCard
            v-for="(contact) in contactsArray"
            :contact="contact"
            :key="contact.id" />
        </ul>
      </div>
    </section>

    <section
      class="scrollbar-thin mx-2 sm:mx-0 lg:overflow-y-auto mt-12 mb-4 space-y-4 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      <div
        class="flex flex-col items-center justify-center h-full empty:hidden"
        v-if="selectedContacts.length > 1">
        <div class="flex flex-col items-center gap-3 text-gray-500">
          <IconContacts class="w-48 h-48 text-gray-400" stroke-width="1" />

          <h2 class="mt-6 text-xl font-semibold leading-none text-center">
            {{ selectedContacts.length }} contacts selected
          </h2>

          <span class="block w-full h-px mt-4 mb-1 bg-rose-500"></span>

          <div class="flex gap-6">
            <button
              @click="onSendMail()"
              class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
              type="button">

              <IconMailFast class="w-5 h-5" />
              <span>Send mail</span>

            </button>

            <Link
              as="button"
              class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
              :href="route('contacts.destroy', { ids: [selectedContacts] } as any)" preserve-scroll>

              <IconUserMinus class="w-5 h-5" />
              <span>Delete</span>

            </Link>
          </div>
        </div>
      </div>

      <div v-else class="p-2 sm:p-6 empty:hidden">
        <section class="flex items-center gap-6">
          <div
            class="items-center justify-center hidden w-24 h-24 text-3xl font-bold rounded-full sm:flex shrink-0"
            :class="contact.is_favorite ? 'bg-blue-500 text-blue-50' : 'bg-lime-500 text-lime-900'">
            <span>
              {{ contact.first_name[0] }}{{ contact.last_name[0] }}
            </span>
          </div>

          <div class="flex flex-col w-full gap-1">
            <h3 class="text-3xl">
              {{ contact.first_name + ' ' + contact.last_name }}
            </h3>

            <span>{{ contact.last_company?.pivot?.job_title }}</span>

            <div class="flex items-center w-full gap-2 font-semibold sm:gap-6">
              <PrimaryButtonLink
                :href="route('mail.compose')"
                @click="onCheckSelection">
                <IconMailFast />
                <span>Email</span>
              </PrimaryButtonLink>

              <span class="flex-1"></span>

              <a
                :href="`tel:${getRandomPhoneNumber(contact?.phones)}`"
                v-if="contact?.phones?.length">
                <IconPhone />
              </a>

              <Link
                as="button"
                class="transition duration-300 hover:opacity-70"
                :href="route('contacts.edit', contact.cid)">
                <IconPencil />
              </Link>

              <Link
                as="button"
                class="transition duration-300 text-rose-500 hover:opacity-70"
                :href="route('contacts.destroy', {ids: [contact.cid]} as any)"
                method="delete">
                <IconTrash />
              </Link>
            </div>
          </div>
        </section>

        <NavTabs>

          <template #tab_1>

            <OverviewTab :contact="contact" />

          </template>

          <template #tab_2>

            <InteractionsTab :contact="contact" />

          </template>

        </NavTabs>

      </div>
    </section>

  </div>
</template>
