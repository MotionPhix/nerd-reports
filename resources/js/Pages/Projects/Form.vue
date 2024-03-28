<script setup lang="ts">
import {
ComboboxInput,
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, onMounted, reactive } from 'vue'
import InputError from '@/Components/InputError.vue'
import AutosizeTextarea from '@/Components/AutosizeTextarea.vue'
import { debounce } from 'lodash'
import ContactSelector from '@/Components/ContactSelector.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
  project: Project
}>()

defineOptions({
    layout: AuthenticatedLayout,
})

const form = useForm({
  name: props.project.name,
  description: props.project.description,
  status: props.project.status,
  contact_id: props.project.id ? { id: props.project.contact_id } : {},
  due_date: props.project.due_date ?? new Date(),
})

const loadFirms = debounce((query: string, setOptions: Function) => {
    axios.get(query ? `/api/companies/${query}` : '/api/companies')
        .then((resp) => {
            setOptions(
                resp.data.map((company: App.Data.FirmData) => company),
            )
        })
}, 500)

function createFirm(option: Partial<{ name: string }>, setSelected: Function) {
    axios.post('/api/companies', {
        name: option.name,
    }, {
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then((resp) => {
            setSelected({
                fid: resp.data.fid,
                name: resp.data.name,
            })
        })
        .catch((err) => {
            error.value = err.response.data.message
        })
}

function submit() {
  if (props.project.pid) {
    form.patch(`/projects/${props.project.pid}`, {

      preserveScroll: true,

      onSuccess: () => {
        // form.reset()
        // handleClose()
      },
    })
  }

  form.post('/projects', {

    preserveScroll: true,

    onSuccess: () => {
      // form.reset()
      // handleClose()
    },
  })

}

async function fetchContacts() {
  form.reset('contact_id')

  Object.assign(contacts, null)

  filtered_contacts.splice(0)

  await axios.get(`/api/contacts`).then((response) => {
    Object.assign(contacts, response.data.contacts)

    response.data.contacts.forEach((contact: ContactApiResponse) => {
      filtered_contacts.push({
        id: contact.id,
        name: `${contact.first_name} ${contact.last_name}`,
      })
    })
  })
}
</script>

<template>

  <Head :title="props.project.pid ? `Edit ${props.project.name}` : 'New project'" />



  <nav
        class="sticky z-50 flex items-center w-full h-16 gap-6 p-6 pl-8 mt-4 bg-gray-100 border rounded-full dark:bg-gray-900 top-4 dark:text-white dark:border-gray-700">

        <SecondaryButton
            class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 rounded-full dark:text-lime-300 hover:text-blue-500"
            v-if="! hasFirm && ! form.firm_keys?.fid"
            @click="toggleField('hasFirm')">
            <IconPlus class="w-6 h-6" /> <span>Add company</span>
        </SecondaryButton>

        <h2 v-else class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-gray-200">
            <Link
                :href="route('contacts.index')" as="button">
                <IconArrowLeft stroke="2.5" class="w-6 h-6" />
            </Link>
            <span>{{ form.firm_keys?.name ?? props.contact.firm.name }}</span>
        </h2>

        <span class="flex-1"></span>

        <PrimaryButton @click.prevent="onSubmit" type="submit" :disabled="form.processing" class="gap-2 rounded-full">

            <IconPlus stroke="2.5" class="w-6 h-6 fill-current" />

            <span>
                {{ contact.id ? 'Update' : 'Create' }}
            </span>

            <Spinner v-if="form.processing" />

        </PrimaryButton>

        <Link as="button" :href="route('contacts.index')"
            class="py-2.5 text-gray-800 font-semibold dark:text-white hover:text-opacity-40 transition duration-300 inline-flex items-center border-gray-700 hover:border-opacity-40 focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-full px-5 text-center border dark:border-gray-600 dark:hover:border-gray-700 dark:focus:ring-gray-800">
            Cancel
        </Link>

    </nav>


<section>
                <div class="flex items-center justify-between pb-4 mb-4 border-b rounded-t sm:mb-5 dark:border-gray-600">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ project.id ? 'Edit' : 'Add' }} project
                  </h3>

                  <button
                    type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                    @click="handleClose"
                  >
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    <span class="sr-only">Close modal</span>
                  </button>
                </div>

              <form
                @submit.prevent="submit"
              >
                <div class="grid gap-4 mb-4 sm:grid-cols-2">
                  <div class="col-span-2">
                    <label
                      for="name"
                      class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >
                      Project name
                    </label>

                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-lime-600 focus:border-lime-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-lime-500 dark:focus:border-lime-500"
                      placeholder="Type project's name"
                    >

                    <InputError :message="form.errors.name" />
                  </div>

                  <div>
                    <label for="company" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Contact person</label>
                    <ContactSelector
                      v-model="form.contact_id"
                      placeholder="Select project's company" />
                  </div>

                  <div class="col-span-2">
                    <label
                      for="description"
                      class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >
                      Description
                    </label>

                    <AutosizeTextarea id="description" v-model="form.description" placeholder="Type project's name" />

                    <InputError :message="form.errors.description" />
                  </div>

                  <div class="flex items-center justify-end col-span-2 gap-4 pt-4">
                    <button
                      type="submit"
                      :disabled="form.processing"
                      class="text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800"
                    >
                      <svg class="w-6 h-6 mr-1 -ml-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                      </svg>

                      {{ project.id ? 'Update ' : 'Create ' }} project
                    </button>
                  </div>
                </div>
              </form>
            </section>
</template>
