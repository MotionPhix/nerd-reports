<script setup lang="ts">
import ContactActionMenu from '@/Components/ContactActionMenu.vue'
import ContactCard from '@/Components/ContactCard.vue'
import NoContactFound from '@/Components/NoContactFound.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { IconPlus } from '@tabler/icons-vue'

interface Props {
  contacts: Object
}

const props = defineProps<Props>()

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
  <Head title="Explore Contacts" />

  <ContactActionMenu :contacts="props.contacts" />

  <div class="py-12">
    <div class="flex max-w-5xl px-6 mx-auto">
      <!-- <template v-if="Object.keys(baseGroup).length"> -->
        <!-- <section> -->
          <!-- <div
            v-for="(contactsArray, group) in baseGroup"
            :key="group" class="pt-12 space-y-4"
          >
            <span
              class="flex items-center justify-center w-8 h-8 p-1 mb-4 ml-20 font-bold text-white bg-gray-600 rounded dark:bg-gray-200 dark:text-gray-900"
            >
              {{ group }}
            </span> -->

            <ul v-if="props.contacts.length" role="list" class="divide-y divide-gray-200 dark:divide-gray-500">
              <ContactCard
                v-for="(contact) in props.contacts"
                :key="contact.id"
                :contact="contact"
              />
            </ul>
          <!-- </div> -->
        <!-- </section> -->
      <!-- </template> -->

      <article v-else class="w-full py-12 mt-12">

        <NoContactFound>

          <div>

            <Link
              :href="route('contacts.create')"
              class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
              as="button">

              <IconPlus class="w-5 h-5" stroke-width="3.5" />

              <span>Create contact</span>

            </Link>

          </div>

        </NoContactFound>

      </article>
    </div>
  </div>
</template>
