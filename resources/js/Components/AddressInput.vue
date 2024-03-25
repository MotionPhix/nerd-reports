<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { usePage } from '@inertiajs/vue3';
import { IconSwitchHorizontal } from '@tabler/icons-vue';
import { onMounted } from 'vue';

const address = defineModel()

const page = usePage()

onMounted(() => {
  if (! address.value.street) {
    address.value = {
      street: '',
      city: '',
      state: '',
      country: '',
      type: 'work'
    }
  }
})

function onChangeType(type) {
  address.value.type = type;
}
</script>

<template>
  <div class="relative mb-4 space-y-2 group first-letter:uppercase">
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
      {{ address.type }} address
    </label>

    <section class="grid grid-cols-2 gap-6">

      <div class="col-span-2 md:col-span-1">
        <TextInput
          v-model="address.street"
          placeholder="Enter street name" type="text" />

        <InputError :message="page.props.errors[`address.street`]" />
      </div>

      <div class="col-span-2 md:col-span-1">
        <TextInput
          v-model="address.city"
          placeholder="Enter city name" type="text" />

        <InputError :message="page.props.errors[`address.city`]" />
      </div>

      <div>
        <TextInput
          v-model="address.state"
          placeholder="Enter state/region name" type="text" />

        <InputError :message="page.props.errors[`address.state`]" />
      </div>

      <div>
        <TextInput
          v-model="address.country"
          placeholder="Enter country name" type="text" />

        <InputError :message="page.props.errors[`address.country`]" />
      </div>

    </section>

    <InputError :message="page.props.errors[`address.type`]" />
  </div>

  <div>
    <Menu as="div" class="relative inline-flex">
      <MenuButton
        class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500">
        <IconSwitchHorizontal class="w-6 h-6" />

        <span>Change type</span>

      </MenuButton>

      <transition enter-active-class="transition duration-100 ease-out transform" enter-from-class="scale-90 opacity-0"
        enter-to-class="scale-100 opacity-100" leave-active-class="transition duration-100 ease-in transform"
        leave-from-class="scale-100 opacity-100" leave-to-class="scale-90 opacity-0">
        <MenuItems
          class="absolute left-0 z-20 w-48 mt-2 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg focus:outline-none">

          <MenuItem v-slot="{ active }" @click="onChangeType('home')">
          <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Home</span>
          </MenuItem>

          <MenuItem v-slot="{ active }" @click="onChangeType('work')">
          <span :class="{ 'bg-gray-100': active }" class="block px-4 py-2 text-sm text-gray-700">Office</span>
          </MenuItem>

        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
