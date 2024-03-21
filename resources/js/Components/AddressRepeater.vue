<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Array, String],
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const page = usePage()

onMounted(() => {
  if (props.modelValue.length <= 0) {
    emit('update:modelValue', [...props.modelValue, {
      street: '',
      city: '',
      state: '',
      country: '',
      type: 'office'
    }])
  }
})

function onAddressAdd(addressType) {
  emit('update:modelValue', [...props.modelValue, {
    street: '',
    city: '',
    state: '',
    country: '',
    type: addressType
  }])
}

function onAddressRemove(index) {
  const updatedAddresses = props.modelValue.filter((_, i) => i !== index)
  emit('update:modelValue', updatedAddresses)
}
</script>

<template>
  <div v-for="(address, idx) in modelValue" :key="idx" class="relative mb-4 space-y-2 group first-letter:uppercase">
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
      {{ address.type }} address
    </label>

    <section class="grid grid-cols-2 gap-6">

      <div class="col-span-2 md:col-span-1">
        <TextInput
          v-model="address.street"
          placeholder="Enter street name" type="text" />

        <InputError :message="page.props.errors[`addresses.${idx}.street`]" />
      </div>

      <div class="col-span-2 md:col-span-1">
        <TextInput
          v-model="address.city"
          placeholder="Enter city name" type="text" />

        <InputError :message="page.props.errors[`addresses.${idx}.city`]" />
      </div>

      <div>
        <TextInput
          v-model="address.state"
          placeholder="Enter state/region name" type="text" />

        <InputError :message="page.props.errors[`addresses.${idx}.state`]" />
      </div>

      <div>
        <TextInput
          v-model="address.country"
          placeholder="Enter country name" type="text" />

        <InputError :message="page.props.errors[`addresses.${idx}.country`]" />
      </div>

    </section>

    <InputError :message="page.props.errors[`addresses.${idx}.type`]" />

    <button v-if="modelValue.length > 1" type="button"
      class="absolute hidden w-4 h-4 text-gray-300 group-hover:inline-flex top-9 right-3 hover:text-rose-500"
      @click="onAddressRemove(idx)">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-current" viewBox="0 0 24 24" stroke-width="2"
        fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M4 7l16 0" />
        <path d="M10 11l0 6" />
        <path d="M14 11l0 6" />
        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
      </svg>
    </button>
  </div>

  <div>
    <Menu as="div" class="relative inline-flex">
      <MenuButton
        class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
          fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 5l0 14" />
          <path d="M5 12l14 0" />
        </svg>
        <span>Add address</span>
      </MenuButton>

      <transition enter-active-class="transition duration-100 ease-out transform" enter-from-class="scale-90 opacity-0"
        enter-to-class="scale-100 opacity-100" leave-active-class="transition duration-100 ease-in transform"
        leave-from-class="scale-100 opacity-100" leave-to-class="scale-90 opacity-0">
        <MenuItems
          class="absolute left-0 z-20 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg focus:outline-none">

          <MenuItem v-slot="{ active }" @click="onAddressAdd('home')">
          <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Home</span>
          </MenuItem>

          <MenuItem v-slot="{ active }" @click="onAddressAdd('office')">
          <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Office</span>
          </MenuItem>

        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
