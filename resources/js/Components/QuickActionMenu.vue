<script setup>
import { useMenuStore } from '@/Stores/menuStore';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { Link, router } from '@inertiajs/vue3';
import { IconLogout, IconSettingsBolt } from '@tabler/icons-vue';

const menuControl = useMenuStore()
const { toggleOpen } = menuControl

const openPath = (path) => {
  toggleOpen()
  router.get(route(path))
}

</script>

<template>
  <Menu
    as="div"
    class="relative z-10 inline-flex">
    <MenuButton class="my-2">
      <img
        :src="$page.props.auth.avatar"
        class="rounded-full w-7 h-7">

    </MenuButton>

    <transition
      enter-active-class="transition duration-100 ease-out transform"
      enter-from-class="scale-90 opacity-0"
      enter-to-class="scale-100 opacity-100"
      leave-active-class="transition duration-100 ease-in transform"
      leave-from-class="scale-100 opacity-100"
      leave-to-class="scale-90 opacity-0"
    >
      <MenuItems
        class="absolute w-40 overflow-hidden overflow-y-auto origin-bottom-left bg-white divide-y divide-gray-100 rounded-lg shadow -left-2 dark:border dark:border-gray-500 bottom-full dark:bg-gray-700 dark:divide-gray-600"
      >
        <!-- <MenuItem>
          <button
            @click="openPath('profile.edit')"
            class="block w-full px-4 py-2 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 hover:text-lime-500">
            <div
              class="flex items-center gap-2"
              >
              <IconUserSquareRounded /> <span>Profile</span>
            </div>
          </button>
        </MenuItem> -->

        <MenuItem>
          <button
            @click="openPath('settings.profile')"
            class="block w-full px-4 py-2 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 hover:text-lime-500">
            <div
              class="flex items-center gap-2">
              <IconSettingsBolt /> <span>Settings</span>
            </div>
          </button>
        </MenuItem>

        <MenuItem>
          <Link
            as="button"
            method="post"
            href="/logout"
            class="block w-full px-4 py-2 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 hover:text-lime-500">
            <div
              class="flex items-center gap-2">
              <IconLogout /> <span>Sign out</span>
            </div>
          </Link>
        </MenuItem>

      </MenuItems>

    </transition>
  </Menu>

  <span class="font-semibold text-gray-700 dark:text-gray-400 text-wrap">
    {{ $page.props.auth.user.full_name }}
  </span>

</template>
