<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue"
import Dropdown from "@/Components/Dropdown.vue"
import DropdownLink from "@/Components/DropdownLink.vue"
import NotificationList from "@/Components/NotificationList.vue"
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue"
import { useProjectStore } from "@/Stores/projectStore"
import { Link, usePage } from "@inertiajs/vue3"
import { IconAddressBook, IconBell, IconFileStack, IconMoon, IconSmartHome, IconSun, IconTags } from "@tabler/icons-vue"
import { UseDark } from "@vueuse/components"
import { computed, ref } from "vue"
import { useTaskStore } from "@/Stores/taskStore"
import SearchInput from "@/Components/SearchInput.vue"

const props = defineProps({

  header: String,

  errors: {
    type: Object,
    default: () => {}
  },

  auth: {
    type: Object,
    default: () => {}
  },

})

const showingNavigationDropdown = ref(false)

const { user } = usePage().props.auth

const projectStore = useProjectStore()

const { reFetchProject } = projectStore

const taskStore = useTaskStore()

const { reFetchTask } = taskStore

console.log(props.header);

window.Echo
  .private(`App.Models.User.${user.id}`)
  .notification((notification) => {

    switch (notification.type) {

      case 'App\\Notifications\\CommentAdded':

        console.log('here we have the notification of a comment addition');

        if (usePage().url.startsWith('/projects/s')) {

          reFetchProject()

        }

        if (usePage().url.startsWith('/tasks/s')) {

          reFetchTask()

        }

        break;

      case 'App\\Notifications\\CommentRemoved':

        console.log('here we have the notification of a comment addition');

        if (usePage().url.startsWith('/projects/s')) {

          reFetchProject()

        }

        if (usePage().url.startsWith('/tasks/s')) {

          reFetchTask()

        }

        break;

    }

  })

  const active = (path) => {
    return usePage().url.startsWith(path)
    ? 'border-l-2 border-blue-500 bg-gray-200 dark:bg-gray-900 dark:text-gray-300 text-gray-900'
    : 'border-l-2 border-transparent'
  }
</script>

<template>

  <nav
    class="sticky top-0 bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700 z-40 shadow">

    <div class="max-w-5xl px-4 mx-auto sm:px-6 lg:px-8">

      <div class="flex justify-between h-16">

        <div class="flex flex-1">

          <div class="flex items-center shrink-0">

            <Link :href="route('dashboard')">

              <ApplicationLogo
                class="block w-auto text-gray-800 fill-current h-9 dark:text-gray-200" />

            </Link>

          </div>

          <!-- Navigation starts -->

          <div class="hidden py-3 sm:-my-px sm:ms-10 sm:flex w-full">

            <SearchInput />

          </div>

          <!-- Navigation ends -->

        </div>

        <div class="hidden sm:flex sm:items-center sm:ms-6">

          <Dropdown align="right" width="96" :can-close="false">

            <template #trigger>

              <button
                type="button"
                class="relative mr-6 text-gray-700 w-7 h-7 hover:opacity-70 dark:text-white">

                <IconBell class="w-6 h-6" />

                <span
                  class="flex absolute top-0 end-0 h-5 w-5 -mt-1.5 -me-1.5"
                  v-if="$page.props.auth.unreadNotifications">

                  <span
                    class="absolute inline-flex w-5 h-5 rounded-full opacity-75 bg-rose-400 animate-ping dark:bg-yellow-500"></span>

                  <span
                    class="relative inline-flex items-center justify-center w-5 h-5 text-sm text-white bg-blue-500 rounded-full dark:bg-red-500">

                    {{ $page.props.auth.unreadNotifications }}

                  </span>

                </span>

              </button>

            </template>

            <template #content>

              <NotificationList />

            </template>

          </Dropdown>

          <UseDark v-slot="{ isDark, toggleDark }">
            <button
              class="text-gray-700 hover:opacity-70 dark:text-white"
              @click="toggleDark()">
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
          <button
            @click="showingNavigationDropdown = !showingNavigationDropdown"
            class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400">
            <svg
              class="w-6 h-6"
              stroke="currentColor"
              fill="none"
              viewBox="0 0 24 24">
              <path
                :class="{
                  hidden: showingNavigationDropdown,
                  'inline-flex': !showingNavigationDropdown,
                }"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
              <path
                :class="{
                  hidden: !showingNavigationDropdown,
                  'inline-flex': showingNavigationDropdown,
                }"
                stroke-linecap="round"
                stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

      </div>

    </div>

    <!-- Responsive Navigation Menu -->
    <div
      :class="{
        block: showingNavigationDropdown,
        hidden: !showingNavigationDropdown
      }"
      class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <ResponsiveNavLink
          :href="route('dashboard')"
          :active="route().current('dashboard')">
          Dashboard
        </ResponsiveNavLink>

        <ResponsiveNavLink
          :href="route('contacts.index')"
          :active="route().current('contacts.*')">
          Contacts
        </ResponsiveNavLink>

        <ResponsiveNavLink
          :href="route('dashboard')"
          :active="route().current('dashboard')">
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


  <section class="flex h-[92vh]">

<!-- Menu Section -->
<aside class="w-72 py-12 sticky top-0 overflow-y-auto max-w-5xl">

  <nav class="flex flex-col gap-4 dark:text-white dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md">

    <h2 class="text-xl font-semibold leading-tight text-gray-900 dark:text-white px-4 mb-4 mt-4">
      {{ header }}
    </h2>

    <ul class="block py-2 text-gray-700 dark:text-gray-200 space-y-2">

      <li>
        <Link preserve-scroll :href="route('dashboard')" :class="active('/dashboard')" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
          <IconSmartHome class="w-5 h-5" stroke="2" />
          <span>Dashboard</span>
        </Link>
      </li>

      <li>
        <Link preserve-scroll :class="active('/contacts')" :href="route('contacts.index')" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
          <IconAddressBook class="w-5 h-5" stroke="2" />
          <span>Contacts</span>
        </Link>
      </li>

      <li>
        <Link preserve-scroll :class="active('/projects')" :href="route('projects.index')" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
          <IconFileStack class="w-5 h-5" stroke="2" />
          <span>Projects</span>
        </Link>
      </li>

      <li>
        <Link preserve-scroll :class="active('/tasks')" :href="route('projects.index')" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
          <IconTags class="w-5 h-5" stroke="2" />
          <span>Tasks</span>
        </Link>
      </li>

    </ul>

  </nav>

</aside>

<!-- Main Content Section -->
<main class="flex-1 overflow-y-auto max-w-full mx-auto col-span-2">

  <slot />

</main>

</section>


</template>
