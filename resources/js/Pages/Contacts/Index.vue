<script setup lang="ts">
import ContactActionMenu from "@/Components/Contact/ContactActionMenu.vue";
import ContactCard from "@/Components/Contact/ContactCard.vue";
import NoContactFound from "@/Components/Contact/NoContactFound.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { IconPlus } from "@tabler/icons-vue";

interface Props {
  contacts: App.Data.ContactData[];
}

const props = defineProps<Props>();

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
  <Head title="Explore Contacts" />

  <ContactActionMenu :contacts="props.contacts" />

  <article class="py-12 max-w-3xl px-6 mx-auto">

    <section v-if="props.contacts.length">

      <ul role="list" class="w-full space-y-6">
        <ContactCard
          v-for="contact in props.contacts"
          :key="contact.cid"
          :contact="contact"
        />
      </ul>

    </section>

    <section v-else class="w-full py-12 mt-12">
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
    </section>

  </article>
</template>
