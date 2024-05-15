<script setup lang="ts">
import AddressInput from "@/Components/AddressInput.vue";

import EmailRepeater from "@/Components/EmailRepeater.vue";

import FirmInput from "@/Components/FirmInput.vue";

import InputError from "@/Components/InputError.vue";

import PhoneRepeater from "@/Components/PhoneRepeater.vue";

import PrimaryButton from "@/Components/PrimaryButton.vue";

import SecondaryButton from "@/Components/SecondaryButton.vue";

import Spinner from "@/Components/Spinner.vue";

import TextInput from "@/Components/TextInput.vue";

import MazRadioButtons from 'maz-ui/components/MazRadioButtons'

import MazAvatar from 'maz-ui/components/MazAvatar'

import TipTap from "@/Components/TipTap.vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { useFieldStore } from "@/Stores/fieldStore";

import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";

import { Head, Link, useForm } from "@inertiajs/vue3";

import { IconArrowLeft, IconPlus } from "@tabler/icons-vue";

import useStickyTop from "@/Composables/useStickyTop";

import axios from "axios";

import { debounce } from "lodash";

import { storeToRefs } from "pinia";

import { computed, h, ref } from "vue"

import { useToastStore } from "@/Stores/toastStore";

interface FormData {
  first_name: string;
  last_name: string;
  middle_name?: string;
  nickname?: string;
  bio?: string;
  title?: string;
  job_title?: string;
  phones?: App.Data.PhoneData[];
  emails?: App.Data.EmailData[];
  firm?: {
    id?: number | null;
    fid?: string | null;
    job_title?: string;
    slogan?: string;
    url?: string;
    address?: {
      id?: number;
      city?: string;
      street?: string;
      state?: string;
      country?: string;
    };
  };
}

const props = defineProps<{

  contact: App.Data.ContactFullData;

}>();

const error = ref();

const toastStore = useToastStore();

const fieldStore = useFieldStore();

const { notify } = toastStore;

const {
  hasMiddleName,
  hasNickname,
  hasTitle,
  hasJobTitle,
  hasAddress,
  hasSlogan,
  hasUrl,
  hasFirm,
} = storeToRefs(fieldStore);

const { toggleField, unSet } = fieldStore;

const titles = [
  {
    value: "mr",
    label: "Mr",
  },
  {
    value: "mrs",
    label: "Mrs",
  },
  {
    value: "ms",
    label: "Ms",
  },
  {
    value: "sr",
    label: "Sir",
  },
  {
    value: "dr",
    label: "Dr",
  },
  {
    value: "prof",
    label: "Professor",
  }
]

const form = useForm({
  first_name: props.contact.first_name,
  last_name: props.contact.last_name,
  bio: props.contact.bio ?? "",
  middle_name: props.contact.middle_name ?? "",
  title: props.contact.title ?? "",
  job_title: props.contact.job_title ?? "",
  nickname: props.contact.nickname ?? "",
  emails: props.contact.emails,
  phones: props.contact.phones,
  firm_keys: props.contact.firm?.fid
    ? { fid: props.contact?.firm?.fid, name: props.contact?.firm?.name }
    : { name: "", fid: null },
  firm_name: props.contact.firm?.name ?? "",
  firm_url: props.contact.firm?.url ?? "",
  firm_slogan: props.contact.firm?.slogan ?? "",
  firm_address: {
    id: props.contact.firm?.address?.id ?? null,
    type: props.contact.firm?.address?.type ?? "work",
    city: props.contact.firm?.address?.city ?? "",
    street: props.contact.firm?.address?.street ?? "",
    state: props.contact.firm?.address?.state ?? "",
    country: props.contact.firm?.address?.country ?? "",
  },
});

const loadFirms = debounce((query: string, setOptions: Function) => {

  axios
    .get(query ? route('api.firms.index', { q: query }) : route('api.firms.index'))
    .then((resp) => {

      setOptions(resp.data.map((company: App.Data.FirmData) => company));

    });

}, 500);

function createFirm(option: Partial<{ name: string }>, setSelected: Function) {
  axios
    .post(
      route('api.firms.index'),
      {
        name: option.name,
      },
      {
        headers: {
          "Content-Type": "application/json",
        },
      },
    )
    .then((resp) => {
      setSelected({
        fid: resp.data.fid,
        name: resp.data.name,
      });
    })
    .catch((err) => {
      error.value = err.response.data.message;
    });
}

function onSubmit() {
  form.transform((data) => {
    let formData: Partial<FormData> = {
      first_name: data.first_name,
      last_name: data.last_name,
      phones: data.phones,
      emails: data.emails,
    };

    // Include optional fields only if they are filled
    if (hasTitle.value || !!data?.title) formData.title = data?.title;

    if (hasMiddleName.value || !!data?.middle_name)
      formData.middle_name = data?.middle_name;

    if (hasNickname.value || !!data?.nickname)
      formData.nickname = data?.nickname;

    if (data.bio?.length || !!data?.bio?.charAt(5)) formData.bio = data.bio;

    if (hasFirm.value || !!data?.firm_keys?.fid) {
      if (data.firm_keys) {
        const firm: Partial<FormData["firm"]> = { fid: null };

        formData.firm = firm;

        formData.firm.fid = data?.firm_keys?.fid;

        if (hasJobTitle.value || !!data?.job_title)
          formData.job_title = data?.job_title;

        if (hasSlogan.value || !!data?.firm_slogan)
          formData.firm.slogan = data?.firm_slogan;

        if (hasUrl.value || !!data?.firm_url)
          formData.firm.url = data?.firm_url;

        if (hasAddress.value || !!data?.firm_address?.city)
          formData.firm.address = data?.firm_address;
      }
    }

    return formData;
  });

  if (props.contact.cid) {
    form.patch(route("contacts.update", props.contact.cid), {
      preserveScroll: true,

      onSuccess: () => {
        unSet();

        form.reset();

        notify({ title: true, message: "Contact was updated!" });
      },
    });

    return;
  }

  form.post(route("contacts.store"), {
    preserveScroll: true,

    onSuccess: () => {
      unSet();

      form.reset();

      notify({ title: true });
    },
  });
}

const { navClasses } = useStickyTop();

const placeholder = computed(
  () => form.firm_name
    ? `A few things to note about ${form.first_name}`
    : 'Enter some notes'
)

defineOptions({

  layout: AuthenticatedLayout,

});
</script>

<template>
  <Head
    :title="
      props.contact.cid
        ? `Edit ${contact.first_name} ${contact.last_name}`
        : 'Update new contact'
    "
  />

  <nav
    class="flex items-center w-full max-w-4xl h-16 gap-6 px-2 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses">
    <SecondaryButton
      class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 rounded-full dark:text-lime-300 hover:text-blue-500"
      v-if="!hasFirm && !form.firm_keys?.fid"
      @click="toggleField('hasFirm')">
      <IconPlus class="w-6 h-6" /> <span>Add Company</span>
    </SecondaryButton>

    <h2
      v-else
      class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-gray-200">
      <Link :href="route('contacts.index')" as="button">
        <IconArrowLeft stroke="2.5" class="w-6 h-6" />
      </Link>
      <span>{{ form.firm_keys?.name ?? props.contact.firm.name }}</span>
    </h2>

    <span class="flex-1"></span>

    <PrimaryButton
      @click.prevent="onSubmit"
      type="submit"
      :disabled="form.processing"
      class="gap-2 rounded-full">

      <IconPlus stroke="2.5" class="w-6 h-6 fill-current" />

      <span>
        {{ props.contact.id ? "Update" : "Create" }}
      </span>

      <Spinner v-if="form.processing" />
    </PrimaryButton>

    <Link
      as="button"
      :href="route('contacts.index')"
      class="py-2.5 text-gray-800 font-semibold dark:text-white hover:text-opacity-40 transition duration-300 inline-flex items-center border-gray-700 hover:border-opacity-40 focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-full px-5 text-center border dark:border-gray-600 dark:hover:border-gray-700 dark:focus:ring-gray-800">
      Cancel
    </Link>
  </nav>

  <article class="pb-12">

    <form
      class="flex flex-col max-w-3xl gap-6 px-4 pb-16 my-16 sm:pb-0 sm:px-8 md:mx-auto">
      <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
          Contact profile
        </h2>

        <p class="text-sm text-gray-600 dark:text-gray-400">
          Manage contact information, and company data settings.
        </p>
      </div>

      <div v-if="hasTitle || !!form.title" class="flex-1">
        <label
          for="title"
          class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          Title
        </label>

        <MazRadioButtons
          v-model="form.title"
          :options="titles"
          color="success">
          <template #default="{ option, selected }">

            <div style="display: flex;">
              <MazAvatar
                v-if="form.first_name"
                :caption="`${form.first_name} ${form.last_name}`"
                style="margin-right: 16px;"
                size="0.8rem" />

              <div style="display: flex; flex-direction: column;">
                <span class="dark:text-gray-200" :class="{ 'text-gray-800': selected }">
                  {{ option.label }}
                </span>

                <span v-if="form.first_name" :class="{ 'maz-text-muted': !selected }" class="text-xs capitalize">
                  {{ `${option.value} ${form.first_name} ${form.last_name}` }}
                </span>
              </div>

            </div>

          </template>
        </MazRadioButtons>

        <InputError :message="$page.props.errors.title" />
      </div>

      <div class="flex-1">
        <label
          for="name"
          class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          First name
        </label>

        <TextInput
          id="name"
          v-model="form.first_name"
          type="text"
          placeholder="Enter first name"
        />

        <InputError :message="$page.props.errors.first_name" />
      </div>

      <div class="flex-1" v-if="hasMiddleName || !!form.middle_name">
        <label
          for="middle_name"
          class="flex-1 mb-2 text-sm font-medium text-gray-900 dark:text-white">
          Middle name
        </label>

        <TextInput
          id="middle_name"
          v-model="form.middle_name"
          type="text"
          placeholder="Enter middle name"
        />

        <InputError :message="$page.props.errors.middle_name" />
      </div>

      <div class="flex-1">
        <label
          for="last_name"
          class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          Surname
        </label>

        <TextInput
          id="last_name"
          v-model="form.last_name"
          type="text"
          placeholder="Type surname"
        />

        <InputError :message="$page.props.errors.last_name" />
      </div>

      <div v-if="hasNickname || !!form.nickname" class="flex-1">
        <label
          for="nickname"
          class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          Nickname
        </label>

        <TextInput
          id="nickname"
          v-model="form.nickname"
          type="text"
          placeholder="Enter nickname"
        />

        <InputError :message="$page.props.errors.nickname" />
      </div>

      <div v-if="fieldStore.showTag">
        <Menu as="div" class="relative z-10 inline-flex">
          <MenuButton
            class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6"
              viewBox="0 0 24 24"
              stroke-width="2"
              stroke="currentColor"
              fill="none"
              stroke-linecap="round"
              stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M12 5l0 14" />
              <path d="M5 12l14 0" />
            </svg>
            <span>Add field</span>
          </MenuButton>

          <transition
            enter-active-class="transition duration-100 ease-out transform"
            enter-from-class="scale-90 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition duration-100 ease-in transform"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-90 opacity-0">
            <MenuItems
              class="absolute left-0 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg focus:outline-none">
              <MenuItem v-slot="{ active }" @click="toggleField('hasTitle')">
                <span
                  :class="{ 'bg-gray-100': active }"
                  class="block px-4 py-2 text-sm text-gray-700">
                  Title
                </span>
              </MenuItem>

              <MenuItem v-slot="{ active }" @click="toggleField('hasMiddleName')">
                <span
                  :class="{ 'bg-gray-100': active }"
                  class="block px-4 py-2 text-sm text-gray-700">
                  Middle name
                </span>
              </MenuItem>

              <MenuItem v-slot="{ active }" @click="toggleField('hasNickname')">
                <span
                  :class="{ 'bg-gray-100': active }"
                  class="block px-4 py-2 text-sm text-gray-700">
                  Nick name
                </span>
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

      <section
        v-if="hasFirm || !!form.firm_keys?.fid"
        class="flex flex-col gap-6">
        <div>
          <label
            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Company
          </label>

          <FirmInput
            v-model="form.firm_keys"
            :create-option="createFirm"
            :load-options="loadFirms"
            placeholder="Pick a company"
          />

          <InputError :message="error" />

          <InputError :message="$page.props.errors['firm.id']" />
        </div>

        <div v-if="hasJobTitle || !!form.job_title">
          <label
            for="job_title"
            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Job title
          </label>

          <TextInput
            id="job_title"
            v-model="form.job_title"
            type="text"
            placeholder="Enter job title"
          />

          <InputError :message="$page.props.errors['job_title']" />
        </div>

        <div v-if="hasAddress || !!form.firm_address.id">
          <AddressInput v-model="form.firm_address" />
        </div>

        <div v-if="hasUrl || !!form.firm_url">
          <label
            for="company_website"
            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Website
          </label>

          <TextInput
            id="company_website"
            v-model="form.firm_url"
            type="url"
            placeholder="Enter office website e.g. https://www.example.com"
          />

          <InputError :message="$page.props.errors['firm.url']" />
        </div>

        <div
          v-if="hasSlogan || !!form.firm_slogan">
          <label
            for="company_slogan"
            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Slogan
          </label>

          <TextInput
            id="company_slogan"
            v-model="form.firm_slogan"
            type="text"
            placeholder="Enter slogan"
          />

          <InputError :message="$page.props.errors['firm.slogan']" />
        </div>

        <div class="col-span-2">
          <Menu as="div" class="relative">
            <MenuButton
              class="flex items-center w-full gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">
              <IconPlus class="w-6 h-6" /> <span>Add work field</span>
            </MenuButton>

            <transition
              enter-active-class="transition duration-100 ease-out transform"
              enter-from-class="scale-90 opacity-0"
              enter-to-class="scale-100 opacity-100"
              leave-active-class="transition duration-100 ease-in transform"
              leave-from-class="scale-100 opacity-100"
              leave-to-class="scale-90 opacity-0">
              <MenuItems
                class="absolute left-0 z-10 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg -top-44 focus:outline-none">
                <MenuItem
                  v-slot="{ active }"
                  @click="toggleField('hasJobTitle')">
                  <span
                    :class="{ 'bg-gray-100': active }"
                    class="block px-4 py-2 text-sm text-gray-700">
                    Job title
                  </span>
                </MenuItem>

                <MenuItem
                  v-slot="{ active }"
                  @click="toggleField('hasAddress')">
                  <span
                    :class="{ 'bg-gray-100': active }"
                    class="block px-4 py-2 text-sm text-gray-700">
                    Address
                  </span>
                </MenuItem>

                <MenuItem
                  v-slot="{ active }"
                  @click="toggleField('hasUrl')">
                  <span
                    :class="{ 'bg-gray-100': active }"
                    class="block px-4 py-2 text-sm text-gray-700">
                    Office website
                  </span>
                </MenuItem>

                <MenuItem
                  v-slot="{ active }"
                  @click="toggleField('hasSlogan')">
                  <span
                    :class="{ 'bg-gray-100': active }"
                    class="block px-4 py-2 text-sm text-gray-700">
                    Motto
                  </span>
                </MenuItem>
              </MenuItems>
            </transition>
          </Menu>
        </div>
      </section>

      <div class="mt-4">
        <label
          for="bio"
          class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
          Notes
        </label>

        <section>
          <TipTap
            v-model="form.bio"
            v-model:placeholder="placeholder"
          />
        </section>

        <InputError :message="$page.props.errors.bio" />
      </div>
    </form>

  </article>
</template>

<style>
.Vue-Toastification__toast--default {
  @apply p-0 bg-transparent;
}
</style>
