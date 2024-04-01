<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { IconBuildingArch, IconMail, IconMap2, IconNote, IconNotes, IconPhone, IconTag } from '@tabler/icons-vue';
import HeadlessTags from "@/Components/HeadlessTags.vue"

interface Props {
  contact: App.Data.ContactFullData
}

const props = defineProps<Props>()

</script>

<template>
  <section
    class="grid grid-cols-2 gap-10 py-6 lg:flex-row">

    <div class="space-y-8">

      <section
        class="flex gap-6">
        <IconMail class="h-7 shrink-0" />

        <div>
          <h3 class="mb-4 text-lg font-semibold">Email(s)</h3>

          <p v-for="(email) in props.contact.emails" :key="email.id">
            <strong class="truncate">
              {{ email.email }}
            </strong>
          </p>

        </div>
      </section>

      <section
        class="flex gap-6">
        <IconPhone class="h-7 shrink-0" />

        <div class="w-full space-y-2">
          <h3 class="mb-4 text-lg font-semibold">Phone(s)</h3>

          <p
            v-for="(phone) in props.contact.phones"
            class="flex items-center justify-between pb-2 border-b last:border-b-0"
            :key="phone.id">
            <strong>
              {{ phone.formatted }}
            </strong>

            <small class="capitalize">
              {{ phone.type }}
            </small>
          </p>
        </div>
      </section>

      <section
        class="flex gap-6"
        v-if="props.contact.firm?.address?.id">
        <IconMap2 class="shrink-0 h-7" />

        <div class="w-full space-y-2">
          <h3 class="mb-4 text-lg font-semibold">Address</h3>

          <p
            class="flex flex-col gap-2 pb-2 border-b last:border-b-0"
            :key="props.contact.firm.address.id">
            <div class="flex flex-col">

              <small class="capitalize">
                {{ props.contact.firm.address.type }} address
              </small>

              <strong>
                {{ props.contact.firm.address.street }}
              </strong>
            </div>

            <div>
              {{ props.contact.firm.address.city }}

              <span v-if="props.contact.firm.address.state" class="empty:hidden">
                , {{ props.contact.firm.address.state }}
              </span>
            </div>

            <strong class="text-blue-500">
              {{ props.contact.firm.address.country }}
            </strong>
          </p>
        </div>
      </section>
    </div>

    <div class="space-y-8">

      <section
        class="flex gap-6"
        v-if="props.contact.firm?.id">
        <IconBuildingArch class="shrink-0 h-7" />

        <div>
          <h3 class="mb-4 text-lg font-semibold">Company</h3>

          <p
            class="flex flex-col gap-1">
            <div class="mb-2">
              <strong class="block">
                {{ props.contact.firm.name }}
              </strong>

              <small
                class="block text-gray-500 empty:hidden dark:text-gray-400"
                v-if="props.contact.firm.slogan">
                {{ props.contact.firm.slogan }}
              </small>
            </div>

            <div
              class="flex items-center mb-1"
              v-if="props.contact.firm?.url">
              <span class="font-semibold text-gray-400 me-2">
                <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 21 20">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.487 1.746c0 4.192 3.592 1.66 4.592 5.754 0 .828 1 1.5 2 1.5s2-.672 2-1.5a1.5 1.5 0 0 1 1.5-1.5h1.5m-16.02.471c4.02 2.248 1.776 4.216 4.878 5.645C10.18 13.61 9 19 9 19m9.366-6h-2.287a3 3 0 0 0-3 3v2m6-8a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
              </span>

              <a
                :href="props.contact.firm.url"
                class="text-blue-600 dark:text-blue-500 hover:underline"
                target="_blank">

                {{ props.contact.firm.url }}
              </a>
            </div>
          </p>
        </div>
      </section>

      <section
        class="flex gap-6"
        v-if="!! props.contact?.firm">
        <IconTag class="shrink-0 h-7" />

        <div>
          <h3 class="mb-4 text-lg font-semibold">Tags</h3>

          <HeadlessTags v-model="props.contact.firm.tags" :contact="props.contact" />
        </div>
      </section>

    </div>

    <article
        class="flex col-span-2 gap-6 mt-6">
        <IconNote class="shrink-0 h-7" />

        <div class="w-full">
          <h3 class="mb-4 text-lg font-semibold">Notes</h3>

          <section v-html="props.contact.bio" v-if="props.contact.bio" class="empty:hidden prose prose-invert pt-6" />

          <div class="flex flex-col items-center gap-3 text-gray-500 empty:hidden" v-else>
            <IconNote class="w-48 h-48 text-gray-400" stroke-width="1" />

            <h2 class="mt-6 text-xl font-semibold leading-none text-center">
              No notes found!
            </h2>

            <p class="text-sm text-center">
              Add <strong>{{ props.contact.first_name }}</strong>'s notes here.
            </p>

            <div>
              <Link as="button"
                class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
                :href="route('contacts.edit', props.contact.cid)" preserve-scroll>

                <IconNotes class="w-5 h-5" />
                <span>Add notes</span>

              </Link>
            </div>
          </div>
        </div>
      </article>

  </section>
</template>
