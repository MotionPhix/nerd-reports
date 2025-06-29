<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { usePage } from '@inertiajs/vue3'
import { IconPhoneCheck, IconPlus, IconTrash } from '@tabler/icons-vue'
import MazPhoneNumberInput from 'maz-ui/components/MazPhoneNumberInput'
import { onMounted } from 'vue'
import InputError from './InputError.vue'

const phones = defineModel({ default: [] })

const page = usePage()

function onPhoneAdd(phone: string) {
  phones.value = [...phones.value, {
    number: '',
    type: phone,
    is_primary_phone: false,
    country_code: 'MW',
  }]
}

function onPhoneRemove(index: Number) {
  phones.value = phones.value.filter((_, i) => i !== index)
}

function togglePrimaryPhone(index: number) {
  for (let i = 0; i < phones.value.length; i++) {
    if (i === index) {
      // Set the current phone as the primary phone
      phones.value[i].is_primary_phone = true;
    } else {
      // Set all other phones as non-primary
      phones.value[i].is_primary_phone = false;
    }
  }
}

onMounted(() => {
  if (phones.value.length <= 0) {
    phones.value = [...phones.value, {
      number: '', type: 'mobile', country_code: 'MW', is_primary_phone: true,
    }]
  }
})
</script>

<template>
  <div
    :key="idx"
    v-for="(phone, idx) in phones"
    class="relative mb-4 space-y-2 group first-letter:uppercase">
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
      {{ phone.type }} phone
    </label>

    <!-- :only-countries="['MW', 'ZA', 'ZM', 'ZW']" -->
    <MazPhoneNumberInput
      v-model:country-code="phone.country_code"
      v-model="phone.number"
      country-selector-display-name
      show-code-on-list
      class="w-full"
      :class="{ 'border-r-4 rounded-xl border-r-indigo-600 dark:border-r-yellow-500': phone.is_primary_phone }"
      color="success"
      no-flags />

    <InputError :message="page.props.errors[`phones.${idx}.type`]" />
    <InputError :message="page.props.errors[`phones.${idx}.number`]" />
    <InputError :message="page.props.errors[`phones.${idx}.country_code`]" />

    <section
      class="absolute z-30 items-center hidden h-8 gap-2 px-3 py-1 top-9 group-hover:inline-flex bottom-10 right-4"
      :class="{ 'dark:bg-gray-800 rounded-lg': ! phone.is_primary_phone }">
      <button
        type="button"
        v-if="! phone.is_primary_phone"
        @click="togglePrimaryPhone(idx)"
        class="text-gray-500 dark:text-gray-300 hover:text-gray-900 hover:bg-opacity-10">
        <IconPhoneCheck class="w-5 h-5 transition duration-300 hover:text-lime-500" />
      </button>

      <button
        v-if="phones.length > 1"
        type="button"
        class="text-gray-500 dark:text-gray-300 group-hover:inline-flex"
        @click="onPhoneRemove(idx)">
        <IconTrash class="w-5 h-5 transition duration-300 stroke-current hover:text-rose-500" />
      </button>
    </section>

    <!-- <span
            v-if="phone.is_primary_phone"
            class="absolute inline-flex w-2 h-8 text-gray-500 rounded-lg z-5 -right-1 top-7 bg-lime-500 dark:text-gray-300" /> -->
  </div>

  <div>
    <Menu
      as="div"
      class="relative inline-flex">
      <MenuButton
        class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">

        <IconPlus class="w-6 h-6" />

        <span>Add phone</span>

      </MenuButton>

      <transition
        enter-active-class="transition duration-100 ease-out transform"
        enter-from-class="scale-90 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in transform"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-90 opacity-0">
        <MenuItems
          class="absolute left-0 z-20 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg focus:outline-none">
          <MenuItem
            v-slot="{ active }"
            @click="onPhoneAdd('home')">
            <span
              :class="{ 'bg-gray-100': active }"
              class="block px-4 py-2 text-sm text-gray-700">
              Home
            </span>
          </MenuItem>

          <MenuItem
            v-slot="{ active }"
            @click="onPhoneAdd('work')">
            <span
              :class="{ 'bg-gray-100': active }"
              class="block px-4 py-2 text-sm text-gray-700">
              Work
            </span>
          </MenuItem>

          <MenuItem
            v-slot="{ active }"
            @click="onPhoneAdd('mobile')">
            <span
              :class="{ 'bg-gray-100': active }"
              class="block px-4 py-2 text-sm text-gray-700">
              Mobile
            </span>
          </MenuItem>

          <MenuItem
            v-slot="{ active }"
            @click="onPhoneAdd('fax')">
            <span
              :class="{ 'bg-gray-100': active }"
              class="block px-4 py-2 text-sm text-gray-700">
              Fax
            </span>
          </MenuItem>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
