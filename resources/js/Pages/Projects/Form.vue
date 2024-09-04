<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

import InputError from "@/Components/InputError.vue";

import ContactSelector from "@/Components/Contact/ContactSelector.vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { IconPlus } from "@tabler/icons-vue";

import Spinner from "@/Components/Spinner.vue";

import PrimaryButton from "@/Components/PrimaryButton.vue";

import TextInput from "@/Components/TextInput.vue";

import useStickyTop from "@/Composables/useStickyTop";

import { UseDark } from "@vueuse/components";

import { DatePicker } from 'v-calendar'

import { ref } from "vue";

import 'v-calendar/style.css'

import TipTap from "@/Components/TipTap.vue";

const props = defineProps<{
  project: App.Data.ProjectFullData;
}>();

defineOptions({
  layout: AuthenticatedLayout,
});

const form = useForm({
  name: props.project.name ?? '',
  description: props.project.description ?? '',
  status: props.project.status ?? 'in_progress',
  contact_id: props.project?.contact_id ?? '',
  due_date: props.project.due_date ?? new Date(),
});

function submit() {

  form.transform((data) => {

    let formData: Partial<App.Data.ProjectFullData> = {
      name: data.name,
      status: data.status,
      due_date: data.due_date,
      contact_id: data.contact_id,
    };

    if (!! data.description) {
      formData.description = data.description
    }

    return formData;

  })

  if (props.project.pid) {

    form.patch(`/projects/${props.project.pid}`, {
      preserveScroll: true,

      onSuccess: () => {
        form.reset()
      },
    });

    return;

  }

  form.post(route('projects.store'), {
    preserveScroll: true,

    onSuccess: () => {
      form.reset()
    },
  });
}

const { navClasses } = useStickyTop();

const disabledDates = ref([
  {
    repeat: {
      weekdays: [1, 7],
    },
  },
])
</script>

<template>
    <Head
      :title="props.project.pid ? `Edit ${props.project.name}` : 'New project'"
    />

  <article class="sm:px-6 lg:px-8">

    <nav
      class="flex items-center max-w-4xl h-16 gap-6 px-4 mx-auto dark:text-white dark:border-gray-700"
      :class="navClasses"
    >
      <h2
        class="font-semibold text-gray-800 dark:text-gray-300 hidden sm:inline-block"
      >
        New Project
      </h2>

      <span class="flex-1 hidden sm:inline-block"></span>

      <PrimaryButton
        type="button"
        @click.prevent="submit"
        :disabled="form.processing"
        class="gap-2 rounded-lg"
      >
        <IconPlus stroke="2" class="w-6 h-6" />

        <span>
          {{ props.project.pid ? "Update" : "Create" }}
        </span>

        <Spinner v-if="form.processing" />

      </PrimaryButton>

      <span class="flex-1 sm:hidden"></span>

      <Link
        as="button"
        :href="route('projects.index')"
        class="py-2.5 text-gray-800 font-semibold dark:text-white hover:text-opacity-40 transition duration-300 inline-flex items-center border-gray-700 hover:border-opacity-40 focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-lg px-5 text-center border dark:border-gray-600 dark:hover:border-gray-700 dark:focus:ring-gray-800"
      >
        Cancel
      </Link>
    </nav>

    <section class="max-w-3xl px-6 py-12 mx-auto">

      <form>
        <div class="grid gap-4 sm:gap-8 mb-4 grid-cols-1 sm:grid-cols-2">
          <div class="col-span-2">
            <label
              for="name"
              class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
            >
              Project name
            </label>

            <TextInput
              id="name"
              v-model="form.name"
              placeholder="Type project's name"
              class="w-full"
              type="text"
            />

            <InputError :message="form.errors.name" />
          </div>

          <section class="col-span-2 grid sm:grid-cols-2 gap-8">

            <div>
              <label
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Due date
              </label>

              <UseDark v-slot="{ isDark }">

                <DatePicker
                  v-model="form.due_date" is-required
                  :masks="{
                    input: 'DD-MM-YYYY',
                  }"
                  class="min-w-full"
                  :disabled-dates="disabledDates"
                  :is-dark="isDark">
                  <template v-slot="{ inputValue, inputEvents }">
                    <input
                      class="w-full py-4 border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                      :value="inputValue"
                      v-on="inputEvents"
                    />
                  </template>
                </DatePicker>

              </UseDark>

              <InputError :message="form.errors.due_date" />
            </div>

            <div>
              <label
                for="company"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Contact person
              </label>

              <ContactSelector
                v-model="form.contact_id"
                placeholder="Pick a project's contact person" />

              <InputError :message="form.errors.contact_id" />
            </div>

          </section>

          <div class="col-span-2">
            <label
              for="description"
              class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
              Description
            </label>

            <TipTap
              v-model="form.description"
              placeholder="Say a few things worthy noting about the project" />

            <InputError :message="form.errors.description" />
          </div>

        </div>
      </form>

    </section>

  </article>
</template>
