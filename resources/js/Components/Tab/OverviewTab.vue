<script lang="ts" setup>
import type { Contact } from '@/types/index';
import { Link } from '@inertiajs/vue3';
import { IconAlbum, IconBuildingArch, IconMail, IconMap2, IconNote, IconNotes, IconPhone, IconTag } from '@tabler/icons-vue';
import HeadlessTags from '../HeadlessTags.vue';

interface Props {
  contact: Contact
}

const props = defineProps<Props>()

</script>

<template>
  <section
    class="flex flex-col flex-1 flex-grow gap-10 py-6 xl:flex-row">

    <div class="space-y-8">

      <section
        class="flex gap-6">
        <IconMail class="shrink-0" />

        <div>
          <h3 class="mb-4 text-lg font-semibold">Email(s)</h3>

          <p v-for="(email) in contact.emails" :key="email.id">
            <strong class="truncate">
              {{ email.email }}
            </strong>
          </p>

        </div>
      </section>

      <section
        class="flex gap-6">
        <IconPhone class="shrink-0" />

        <div class="w-full space-y-2">
          <h3 class="mb-4 text-lg font-semibold">Phone(s)</h3>

          <p
            v-for="(phone, idx) in contact.phones"
            class="flex items-center justify-between pb-2 border-b last:border-b-0"
            :key="phone.id">
            <strong>
              {{ phone.formatted }}
            </strong>

            <small>
              {{ phone.type }}
            </small>
          </p>
        </div>
      </section>

      <section
        class="flex gap-6"
        v-if="contact.addresses?.length">
        <IconMap2 class="shrink-0" />

        <div class="w-full space-y-2">
          <h3 class="mb-4 text-lg font-semibold">Address(es)</h3>

          <p
            v-for="(address, idx) in contact.addresses"
            class="flex flex-col gap-2 pb-2 border-b last:border-b-0"
            :key="address.id">
            <div class="flex flex-col">

              <small class="capitalize">
                {{ address.type }} address
              </small>

              <strong>
                {{ address.street }}
              </strong>
            </div>

            <div>
              {{ address.city }}

              <span v-if="address.state" class="empty:hidden">
                , {{ address.state }}
              </span>
            </div>

            <strong class="text-blue-500">
              {{ address.country }}
            </strong>
          </p>
        </div>
      </section>

      <section
        class="flex gap-6"
        v-if="contact.last_company?.id">
        <IconNote class="shrink-0" />

        <div class="w-full">
          <h3 class="mb-4 text-lg font-semibold">Notes</h3>

          <section v-html="contact.bio" v-if="contact.bio" class="empty:hidden" />

          <div class="flex flex-col items-center gap-3 text-gray-500 empty:hidden" v-else>
            <IconNote class="w-48 h-48 text-gray-400" stroke-width="1" />

            <h2 class="mt-6 text-xl font-semibold leading-none text-center">
              No notes found!
            </h2>

            <p class="text-sm text-center">
              Add <strong>{{ contact.first_name }}</strong>'s notes here.
            </p>

            <div>
              <Link as="button"
                class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
                :href="route('contacts.edit', contact.cid)" preserve-scroll>

                <IconNotes class="w-5 h-5" />
                <span>Add notes</span>

              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div class="space-y-8">

      <section
        class="flex gap-6"
        v-if="contact.last_company?.id">
        <IconBuildingArch class="shrink-0" />

        <div>
          <h3 class="mb-4 text-lg font-semibold">Company</h3>

          <p
            class="flex flex-col gap-1">
            <div class="mb-2">
              <strong class="block">
                {{ contact.last_company.name }}
              </strong>

              <small
                class="block empty:hidden"
                v-if="contact.last_company.slogan">
                {{ contact.last_company.slogan }}
              </small>
            </div>

            <p
              class="mb-3 text-sm font-normal"
              v-if="contact.last_company?.pivot?.department">
              {{ contact.last_company.pivot.department }}
            </p>

            <div
              class="flex items-start mb-1 empty:hidden"
              v-if="contact.last_company.address">
              <span class="font-semibold text-gray-400 me-2">
                <IconAlbum class="w-3.5 h-3.5" />
              </span>

              <span class="-mt-1">
                {{ contact.last_company.address }}
              </span>
            </div>

            <div
              class="flex items-center mb-1"
              v-if="contact.last_company.url">
              <span class="font-semibold text-gray-400 me-2">
                <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 21 20">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.487 1.746c0 4.192 3.592 1.66 4.592 5.754 0 .828 1 1.5 2 1.5s2-.672 2-1.5a1.5 1.5 0 0 1 1.5-1.5h1.5m-16.02.471c4.02 2.248 1.776 4.216 4.878 5.645C10.18 13.61 9 19 9 19m9.366-6h-2.287a3 3 0 0 0-3 3v2m6-8a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
              </span>

              <a
                :href="contact.last_company.url"
                class="text-blue-600 dark:text-blue-500 hover:underline"
                target="_blank">

                {{ contact.last_company.url }}
              </a>
            </div>
          </p>
        </div>
      </section>

      <section
        class="flex gap-6">
        <IconTag class="shrink-0" />

        <div>
          <h3 class="mb-4 text-lg font-semibold">Categories</h3>

          <HeadlessTags v-model="contact.tags" :contact="contact" />
        </div>
      </section>

    </div>

  </section>
</template>
