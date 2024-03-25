<script setup lang="ts">
import type { Phone } from '@/types'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { usePage } from '@inertiajs/vue3'
import { IconTrash } from '@tabler/icons-vue'
import MazPhoneNumberInput from 'maz-ui/components/MazPhoneNumberInput'
import { onMounted } from 'vue'
import InputError from './InputError.vue'

interface InputProps {
  modelValue: Phone[]
}

const props = withDefaults(defineProps<InputProps>(), {
  modelValue: () => [],
})

const emit = defineEmits(['update:modelValue'])

const page = usePage()

function onPhoneAdd(phone: string) {
  emit('update:modelValue', [...props.modelValue, {
    number: '',
    type: phone,
    country_code: 'MW',
  }])
}

function onPhoneRemove(index: Number) {
  const updatedEmails = props.modelValue.filter((_, i) => i !== index)
  emit('update:modelValue', updatedEmails)
}

onMounted(() => {
  if (props.modelValue.length <= 0) {
    emit('update:modelValue', [...props.modelValue, {
      number: '', type: 'mobile', country_code: 'MW',
    }])
  }
})
</script>

<template>
  <div v-for="(phone, idx) in modelValue" :key="idx" class="relative mb-4 space-y-2 group first-letter:uppercase">
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
      {{ phone.type }} phone
    </label>

    <MazPhoneNumberInput
      v-model:country-code="phone.country_code"
      v-model="phone.number"
      country-selector-display-name
      :only-countries="['MW', 'ZA', 'ZM', 'ZW']"
      show-code-on-list
      color="success"
      no-flags
    />

    <InputError :message="page.props.errors[`phones.${idx}.type`]" />
    <InputError :message="page.props.errors[`phones.${idx}.number`]" />
    <InputError :message="page.props.errors[`phones.${idx}.country_code`]" />

    <button
      v-if="modelValue.length > 1" type="button"
      class="absolute z-10 hidden w-4 h-4 text-gray-500 dark:text-gray-300 group-hover:inline-flex top-9 right-3 hover:text-rose-500"
      @click="onPhoneRemove(idx)"
    >
      <IconTrash class="w-5 h-5 stroke-current" />
    </button>
  </div>

  <div>
    <Menu as="div" class="relative inline-flex">
      <MenuButton
        class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
          fill="none" stroke-linecap="round" stroke-linejoin="round"
        >
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 5l0 14" />
          <path d="M5 12l14 0" />
        </svg>
        <span>Add phone</span>
      </MenuButton>

      <transition
        enter-active-class="transition duration-100 ease-out transform" enter-from-class="scale-90 opacity-0"
        enter-to-class="scale-100 opacity-100" leave-active-class="transition duration-100 ease-in transform"
        leave-from-class="scale-100 opacity-100" leave-to-class="scale-90 opacity-0"
      >
        <MenuItems
          class="absolute left-0 z-20 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg focus:outline-none"
        >
          <MenuItem v-slot="{ active }" @click="onPhoneAdd('home')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Home</span>
          </MenuItem>

          <MenuItem v-slot="{ active }" @click="onPhoneAdd('work')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Work</span>
          </MenuItem>

          <MenuItem v-slot="{ active }" @click="onPhoneAdd('mobile')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Mobile</span>
          </MenuItem>

          <MenuItem v-slot="{ active }" @click="onPhoneAdd('fax')">
            <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Fax</span>
          </MenuItem>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
