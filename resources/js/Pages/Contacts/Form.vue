<script setup lang="ts">
import AddressRepeater from '@/Components/AddressRepeater.vue'
import AutosizeTextarea from '@/Components/AutosizeTextarea.vue'
import Combobox from '@/Components/Combobox.vue'
import EmailRepeater from '@/Components/EmailRepeater.vue'
import InputError from '@/Components/InputError.vue'
import PhoneRepeater from '@/Components/PhoneRepeater.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Spinner from '@/Components/Spinner.vue'
import TextInput from '@/Components/TextInput.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useFieldStore } from '@/Stores/fieldStore'
import { useNotificationStore } from '@/Stores/notificationStore'
import type { Address, Company, Email, Phone } from '@/types'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { IconPlus } from '@tabler/icons-vue'
import axios from 'axios'
import { debounce } from 'lodash'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'

interface FormData {
  first_name: string;
  last_name: string;
  middle_name?: string;
  nickname?: string;
  bio?: string;
  title?: string;
  job_title?: string;
  phones?: Phone[];
  emails?: Email[];
  firm?: Company;
  addresses?: Address[];
}

const props = defineProps<{
  contact: Object
}>()

defineOptions({
  layout: AuthenticatedLayout,
})

console.log(props.contact);

const error = ref()

const toastStore = useNotificationStore();

const fieldStore = useFieldStore()

const { notify } = toastStore

const {
  hasMiddleName,
  hasNickname,
  hasTitle,
  hasJobTitle,
  hasLocation,
  hasDepartment,
  hasAddresses,
  hasSlogan,
  hasUrl,
} = storeToRefs(fieldStore)

const { toggleField, unSet } = fieldStore

const form = useForm({
  first_name: props.contact.first_name,
  last_name: props.contact.last_name,
  bio: props.contact.bio ?? '',
  middle_name: props.contact.middle_name,
  title: props.contact.title,
  job_title: props.contact.job_title,
  nickname: props.contact.nickname,
  emails: props.contact.emails,
  phones: props.contact.phones,
  addresses: props.contact?.addresses,
  firm: props.contact.firm
})

const loadCompanies = debounce((query: string, setOptions: any) => {
  axios.get(query ? `/api/companies/${query}` : '/api/companies')
    .then((resp) => {
      setOptions(
        resp.data.map((company: Company) => company),
      )
    })
}, 500)

function createCompany(option: Partial<{ label?: string }>, setSelected: Function) {
  axios.post('/api/companies', {
    name: option.label,
  }, {
    headers: {
      'Content-Type': 'application/json',
    },
  })
    .then((resp) => {
      setSelected({
        value: resp.data.id,
        label: resp.data.name,
      })
    })
    .catch((err) => {
      error.value = err.response.data.message
    }).finally(() => error.value = null)
}

function onSubmit() {
  form.transform((data) => {

    const formData: FormData = {
      first_name: data.first_name,
      last_name: data.last_name,
      phones: data.phones,
      emails: data.emails,
      firm: data.firm
    }

    // Include optional fields only if they are filled
    if (hasTitle.value || !!data.title)
      formData.title = data.title

    if (hasMiddleName.value || !!data.middle_name)
      formData.middle_name = data.middle_name

    if (hasNickname.value || !!data.nickname)
      formData.nickname = data.nickname

    if (hasJobTitle.value || !!data.job_title)
        formData.job_title = data.job_title

    if (data.bio?.length || !!data.bio?.charAt(5))
      formData.bio = data.bio

    if (
      data.addresses && (data.addresses[0].id
        || form?.addresses[0].street
        || data.addresses[0].city
        || data.addresses[0].state
        || data.addresses[0].country)
    )
      formData.addresses = data.addresses

    if (data.firm?.value.id) {
      formData.firm = {
        id: data.firm.id,
      }

      if (hasSlogan.value || !!data.firm?.slogan)
        formData.firm.slogan = data.firm.slogan

      if (hasUrl.value || !!data.firm?.url)
        formData.firm.url = data.firm.url

      if (hasLocation.value || !!data.firm?.address)
        formData.firm.address = data.firm.address
    }

    return formData
  })

  if (props.contact.cid) {

    form.patch(route('contacts.update', props.contact.cid), {
      preserveScroll: true,
      onSuccess: () => {

        unSet()

        form.reset()

        notify({ title: true, message: 'Contact was updated!' })
      }
    })

    return

  }

  form.post(route('contacts.store'), {
    preserveScroll: true,
    onSuccess: () =>  {

      unSet()

      form.reset()

      notify({ title: true })

    }
  })
}
</script>

<template>
  <Head :title="contact.cid ? `Edit ${contact.first_name} ${contact.last_name}` : 'Create new contact'" />

  <form class="flex flex-col w-full gap-6 px-4 pb-16 my-16 mb-4 sm:pb-0 sm:px-8 md:mx-auto" @submit.prevent="onSubmit">
    <section class="flex w-full gap-6">
      <div v-if="hasTitle || !!form.title" class="w-full">
        <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          Title
        </label>

        <TextInput
          id="title"
          v-model="form.title" type="text"
          placeholder="Mr, Mrs, Ms" />

        <InputError :message="$page.props.errors.title" />
      </div>

      <div class="flex-1">
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          First name
        </label>

        <TextInput
          id="name"
          v-model="form.first_name" type="text"
          class="w-full"
          placeholder="Enter first name" />

        <InputError :message="$page.props.errors.first_name" />
      </div>
    </section>

    <div v-if="hasMiddleName || !!form.middle_name">
      <label for="middle_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Middle name
      </label>

      <TextInput
        id="middle_name"
        v-model="form.middle_name" type="text"
        placeholder="Enter middle name" />

      <InputError :message="$page.props.errors.middle_name" />
    </div>

    <div v-if="hasJobTitle || !!form.job_title">
        <label for="job_title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Job title
        </label>

        <TextInput
            id="job_title"
            v-model="form.job_title" type="text"
            placeholder="Enter job title" />

        <InputError :message="$page.props.errors['job_title']" />
    </div>

    <div>
      <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Surname
      </label>

      <TextInput
        id="last_name"
        v-model="form.last_name" type="text"
        placeholder="Type surname" />

      <InputError :message="$page.props.errors.last_name" />
    </div>

    <div v-if="hasNickname || !!form.nickname">
      <label for="nickname" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Nickname
      </label>

      <TextInput
        id="nickname"
        v-model="form.nickname" type="text"
        placeholder="Enter nickname" />

      <InputError :message="$page.props.errors.nickname" />
    </div>

    <div v-if="fieldStore.showTag">
      <Menu as="div" class="relative z-10 inline-flex">
        <MenuButton
          class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 5l0 14" />
            <path d="M5 12l14 0" />
          </svg>
          <span>Add field</span>
        </MenuButton>

        <transition enter-active-class="transition duration-100 ease-out transform" enter-from-class="scale-90 opacity-0"
          enter-to-class="scale-100 opacity-100" leave-active-class="transition duration-100 ease-in transform"
          leave-from-class="scale-100 opacity-100" leave-to-class="scale-90 opacity-0">
          <MenuItems
            class="absolute left-0 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg focus:outline-none">
            <MenuItem v-slot="{ active }" @click="toggleField('hasTitle')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">
              Title
            </span>
            </MenuItem>

            <MenuItem v-slot="{ active }" @click="toggleField('hasMiddleName')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Middle name</span>
            </MenuItem>

            <MenuItem v-slot="{ active }" @click="toggleField('hasNickname')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Nick name</span>
            </MenuItem>
          </MenuItems>
        </transition>
      </Menu>
    </div>

    <div>
      <EmailRepeater v-model="form.emails" />
    </div>

    <div>
      <PhoneRepeater v-model="form.phones" />
    </div>

    <div v-if="hasAddresses || !!form.addresses">
      <AddressRepeater v-model="form.addresses" />
    </div>

    <div>
      <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Company
      </label>

      <Combobox v-model="form.firm.id" :create-option="createCompany" :load-options="loadCompanies" />

      <InputError :message="error" />

      <InputError :message="$page.props.errors['firm.id']" />
    </div>

    <div v-if="hasLocation || !!form.firm.address">
      <label for="company_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Address
      </label>

      <TextInput
        id="company_address"
        v-model="form.firm.address" type="text"
        placeholder="Enter work address" />

      <InputError :message="$page.props.errors['firm.address']" />
    </div>

    <div v-if="hasUrl || !!form.firm.url">
      <label for="company_website" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Website
      </label>

      <TextInput
        id="company_website"
        v-model="form.firm.url" type="text"
        placeholder="Enter office website e.g. https://www.example.com" />

      <InputError :message="$page.props.errors['firm.url']" />
    </div>

    <div v-if="hasSlogan || !!form.firm.slogan">
      <label for="company_slogan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Slogan
      </label>

      <TextInput
        id="company_slogan"
        v-model="form.firm.slogan" type="text"
        placeholder="Enter slogan" />

      <InputError :message="$page.props.errors['firm.slogan']" />
    </div>

    <div class="col-span-2">
      <Menu as="div" class="relative">
        <MenuButton
          class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">
          <IconPlus /> <span>Add work field</span>
        </MenuButton>

        <transition enter-active-class="transition duration-100 ease-out transform" enter-from-class="scale-90 opacity-0"
          enter-to-class="scale-100 opacity-100" leave-active-class="transition duration-100 ease-in transform"
          leave-from-class="scale-100 opacity-100" leave-to-class="scale-90 opacity-0">
          <MenuItems
            class="absolute left-0 z-10 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg -top-44 focus:outline-none">

            <MenuItem v-slot="{ active }" @click="hasJobTitle = !hasJobTitle">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Job title</span>
            </MenuItem>

            <MenuItem v-slot="{ active }" @click="hasLocation = !hasLocation">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Address</span>
            </MenuItem>

            <MenuItem v-slot="{ active }" @click="hasUrl = !hasUrl">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Office website</span>
            </MenuItem>

            <MenuItem v-slot="{ active }" @click="hasSlogan = !hasSlogan">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Motto</span>
            </MenuItem>

          </MenuItems>
        </transition>
      </Menu>
    </div>

    <div class="mt-4">
      <label for="bio" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Notes
      </label>

      <section>
        <AutosizeTextarea v-model="form.bio" placeholder="Write down some notes" />
      </section>

      <InputError :message="$page.props.errors.bio" />
    </div>

    <div class="flex items-center justify-end col-span-4 gap-4 pt-4">
      <PrimaryButton type="submit" :disabled="form.processing" class="gap-2">

        <IconPlus class="w-6 h-6 fill-current" />

        <span>
          {{ contact.id ? 'Update' : 'Create' }}
        </span>

        <Spinner v-if="form.processing" />

      </PrimaryButton>

      <Link as="button" :href="route('contacts.index')"
        class="py-2.5 text-gray-800 font-semibold dark:text-white hover:text-opacity-40 transition duration-300 inline-flex items-center border-gray-700 hover:border-opacity-40 focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-lg px-5 text-center border dark:border-gray-600 dark:hover:border-gray-700 dark:focus:ring-gray-800">
        Cancel
      </Link>
    </div>
  </form>
</template>

<style>

.Vue-Toastification__toast--default {
  @apply p-0 bg-transparent
}

</style>
