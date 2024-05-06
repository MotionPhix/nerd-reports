<script setup lang="ts">
import NavTabs from '@/Components/NavTabs.vue';
import PrimaryButtonLink from '@/Components/PrimaryButtonLink.vue';
import InteractionsTab from '@/Components/Tab/InteractionsTab.vue';
import OverviewTab from '@/Components/Tab/OverviewTab.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useContactStore } from '@/Stores/contactStore';
import { Head, Link, router } from '@inertiajs/vue3';
import { IconMailFast, IconPencil, IconPhone, IconTrash } from '@tabler/icons-vue';
import { storeToRefs } from 'pinia';

interface Props {
  contact: App.Data.ContactFullData
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

// function getRandomPhoneNumber(phones?: App.Data.PhoneData[]) {
//   const validPhones = phones?.filter(phone => phone.type !== 'fax');
//   return sample(validPhones)?.number || ''; // Ensure there's a fallback in case the array is empty
// }

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
  <Head :title="`${props.contact.first_name} ${props.contact.last_name}`" />

  <div class="flex max-w-3xl gap-6 pt-12 mx-auto mb-10 lg:mb-0 sm:px-6">

    <section
      class="w-full mx-2 mt-12 mb-4 space-y-4 text-sm text-gray-900 dark:text-gray-100">

      <div class="p-2 sm:p-6 empty:hidden">
        <section class="sticky z-50 flex items-center gap-6 bg-gray-100 top-10 dark:bg-gray-900">
          <div
            class="items-center justify-center hidden text-5xl font-bold rounded-full w-36 h-36 sm:flex shrink-0 bg-lime-500 text-lime-900">
            <span>
              {{ `${props.contact.first_name[0]}${props.contact.last_name[0]}` }}
            </span>
          </div>

          <div class="flex flex-col w-full gap-1">
            <h3 class="text-3xl flex items-center gap-2">
              <span class="capitalize" v-if="props.contact.title">{{ props.contact.title }}</span>
              <span>{{ `${props.contact.first_name} ${props.contact.last_name}` }}</span>
            </h3>

            <span>{{ props.contact?.job_title }}</span>

            <div class="flex items-center w-full gap-2 font-semibold sm:gap-6">
              <PrimaryButtonLink
                href="#"
                class="rounded-2xl"
                @click="onCheckSelection">
                <IconMailFast class="h-7" />
                <span>Email</span>
              </PrimaryButtonLink>

              <span class="flex-1"></span>

              <a
                :href="`tel:${(contact?.phones.filter(phone => phone.is_primary_phone)[0] || {}).number}`"
                :style="{ 'pointer-events': (contact?.phones.filter(phone => phone.is_primary_phone)[0] || {}).number ? 'auto' : 'none' }"
                v-if="contact?.phones?.length">
                <IconPhone class="h-7" />
              </a>

              <Link
                as="button"
                class="transition duration-300 hover:opacity-70"
                :href="route('contacts.edit', contact.cid)">
                <IconPencil class="h-7"/>
              </Link>

              <Link
                as="button"
                class="transition duration-300 text-rose-500 hover:opacity-70"
                :href="route('contacts.destroy', {ids: [contact.cid]} as any)"
                method="delete">
                <IconTrash class="h-7" />
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
