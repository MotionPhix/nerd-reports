<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue"
import Dropdown from "@/Components/Dropdown.vue"
import DropdownLink from "@/Components/DropdownLink.vue"
import NavLink from "@/Components/NavLink.vue"
import NotificationList from "@/Components/NotificationList.vue"
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue"
import { useProjectStore } from "@/Stores/projectStore"
import { Link, usePage } from "@inertiajs/vue3"
import { IconBell, IconMoon, IconSun } from "@tabler/icons-vue"
import { UseDark } from "@vueuse/components"
import { ref } from "vue"
import { useTaskStore } from "@/Stores/taskStore"
import { useNotificationStore } from "@/Stores/notificationStore"
import { storeToRefs } from "pinia"
import { twi } from "tw-to-css"

const showingNavigationDropdown = ref(false)

const { user } = usePage().props.auth

const projectStore = useProjectStore()

const { reFetchProject } = projectStore

const taskStore = useTaskStore()

const { reFetchTask } = taskStore

const notificationStore = useNotificationStore()

const { fetchNotifications } = notificationStore

const { unreadNotifications } = storeToRefs(notificationStore)

window.Echo
  .private(`App.Models.User.${user.id}`)
  .notification((notification) => {

    switch (notification.type) {

      case 'App\\Notifications\\CommentAdded':

        fetchNotifications()

        if (usePage().url.startsWith('/projects/s')) {

          reFetchProject()

        }

        if (usePage().url.startsWith('/tasks/s')) {

          reFetchTask()

        }

        break;

    }

  })

const styleInline = twi(`bg-white mx-auto`);

console.log(styleInline)
</script>

<template>
  <nav
    class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">

    <div class="max-w-5xl px-4 mx-auto sm:px-6 lg:px-8">

      <div class="flex justify-between h-16">

        <div class="flex">

          <div class="flex items-center shrink-0">
            <Link :href="route('dashboard')">
              <ApplicationLogo
                class="block w-auto text-gray-800 fill-current h-9 dark:text-gray-200" />
            </Link>
          </div>

          <!-- Navigation starts -->

          <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
              Dashboard
            </NavLink>

            <NavLink :href="route('contacts.index')" :active="route().current('contacts.*')">
              Contacts
            </NavLink>

            <NavLink :href="route('projects.index')" :active="route().current('projects.*')">
              Projects
            </NavLink>

            <NavLink :href="route('reports.index')" :active="route().current('reports.*')">
              Reports
            </NavLink>

          </div>

          <!-- Navigation ends -->

        </div>

        <div class="hidden sm:flex sm:items-center sm:ms-6">

          <Dropdown align="right" width="96" :can-close="false">

            <template #trigger>

              <button
                type="button"
                class="relative mr-6 text-gray-700 w-7 h-7 hover:opacity-70 dark:text-white">

                <IconBell class="w-5 h-5" />

                <span
                  class="flex absolute top-0 end-0 h-5 w-5 -mt-1.5 -me-1.5"
                  v-if="unreadNotifications.length">

                  <span
                    class="absolute inline-flex w-5 h-5 rounded-full opacity-75 bg-rose-400 animate-ping dark:bg-yellow-500"></span>

                  <span
                    class="relative inline-flex items-center justify-center w-5 h-5 text-sm text-white bg-blue-500 rounded-full dark:bg-red-500">

                    {{ unreadNotifications.length }}

                  </span>

                </span>

              </button>

            </template>

            <template #content>

              <NotificationList />

            </template>

          </Dropdown>

          <UseDark v-slot="{ isDark, toggleDark }">
            <button class="text-gray-700 hover:opacity-70 dark:text-white" @click="toggleDark()">
              <IconSun class="w-5 h-5" stroke="2" v-if="isDark" />
              <IconMoon class="w-5 h-5" v-else />
            </button>
          </UseDark>

          <!-- Settings Dropdown -->
          <div class="relative ms-3">
            <Dropdown align="right" width="48">
              <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                        {{ $page.props.auth.user.name }}

                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                  clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
              </template>

              <template #content>
                <DropdownLink :href="route('profile.edit')"> Profile</DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button">
                  Log Out
                </DropdownLink>
              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Hamburger -->
        <div class="flex items-center -me-2 sm:hidden">
          <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                  class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400">
            <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
              <path :class="{
                            hidden: showingNavigationDropdown,
                            'inline-flex': !showingNavigationDropdown,
                        }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
              <path :class="{
                            hidden: !showingNavigationDropdown,
                            'inline-flex': showingNavigationDropdown,
                        }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
         class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
          Dashboard
        </ResponsiveNavLink>

        <ResponsiveNavLink :href="route('contacts.index')" :active="route().current('contacts.*')">
          Contacts
        </ResponsiveNavLink>

        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
          Projects
        </ResponsiveNavLink>
      </div>

      <!-- Responsive Settings Options -->
      <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
        <div class="px-4">
          <div class="text-base font-medium text-gray-800 dark:text-gray-200">
            {{ $page.props.auth.user.name }}
          </div>

          <div class="text-sm font-medium text-gray-500">
            {{ $page.props.auth.user.email }}
          </div>
        </div>

        <div class="mt-3 space-y-1">
          <ResponsiveNavLink :href="route('profile.edit')">
            Profile
          </ResponsiveNavLink>

          <ResponsiveNavLink :href="route('logout')" method="post" as="button">
            Log Out
          </ResponsiveNavLink>
        </div>
      </div>
    </div>
  </nav>

  <!-- Page Heading -->
  <header class="sticky top-0 bg-white shadow dark:bg-gray-800" v-if="$slots.header">
    <div class="max-w-5xl px-4 py-6 mx-auto sm:px-6 lg:px-8">
      <slot name="header" />
    </div>
  </header>

  <!-- Page Content -->
  <main>
    <slot />
  </main>
</template>
