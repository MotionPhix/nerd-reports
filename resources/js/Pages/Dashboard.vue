<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import { IconPlus } from "@tabler/icons-vue"
import useStickyTop from "@/Composables/useStickyTop"

const { navClasses } = useStickyTop()

const props = defineProps<{
  dashboardData: Object
}>()

defineOptions({
  layout: AuthenticatedLayout
})
</script>

<template>
  <Head title="Dashboard" />

  <nav
    class="flex items-center h-16 max-w-5xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses">
    <h2
      class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">

      <span>Dashboard</span>

    </h2>

    <span class="flex-1"></span>

    <Link
      as="button"
      :href="route('projects.create')"
      class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200">
      <IconPlus stroke="2.5" class="w-4 h-4" />
      <span>Create</span>
    </Link>
  </nav>

  <section class="max-w-4xl px-6 py-12 mx-auto">

    <!-- Card Section -->
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
      <!-- Grid -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
          <div class="p-4 md:p-5 flex gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>

            <div class="grow">

              <div class="flex items-center gap-x-2">

                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                  Total contacts
                </p>

                <div v-tooltip="'The number of contacts (not companies)'">

                  <svg class="flex-shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <path d="M12 17h.01" />
                  </svg>

                </div>

              </div>

              <div class="mt-1 flex items-center gap-x-2">

                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalContacts }}
                </h3>

              </div>

            </div>

          </div>

        </div>
        <!-- End Card -->

        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
          <div class="p-4 md:p-5 flex gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 22h14" />
                <path d="M5 2h14" />
                <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
                <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
              </svg>
            </div>

            <div class="grow">
              <div class="flex items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                  Total Projects
                </p>
              </div>

              <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalProjects }}
                </h3>
              </div>

<!--              <dl class="flex justify-center items-center divide-x divide-gray-200 dark:divide-neutral-800">-->
<!--                <dt class="pe-3">-->
<!--                  <span class="text-green-600">-->
<!--                    <svg class="inline-block size-4 self-center" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">-->
<!--                      <path fill-rule="evenodd" d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/>-->
<!--                    </svg>-->
<!--                    <span class="inline-block text-sm">-->
<!--                      1.7%-->
<!--                    </span>-->
<!--                  </span>-->

<!--                  <span class="block text-sm text-gray-500 dark:text-neutral-500">change</span>-->
<!--                </dt>-->
<!--                -->
<!--                <dd class="text-start ps-3">-->
<!--                  <span class="text-sm font-semibold text-gray-800 dark:text-neutral-200">5</span>-->
<!--                  <span class="block text-sm text-gray-500 dark:text-neutral-500">last week</span>-->
<!--                </dd>-->
<!--              </dl>-->

            </div>
          </div>
        </div>
        <!-- End Card -->

        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
          <div class="p-4 md:p-5 flex gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" />
                <path d="m12 12 4 10 1.7-4.3L22 16Z" />
              </svg>
            </div>

            <div class="grow">
              <div class="flex items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                  Total Tasks
                </p>
              </div>
              <div class="mt-1 flex items-center gap-x-2">

                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalTasks }}
                </h3>

              </div>
            </div>
          </div>
        </div>
        <!-- End Card -->

      </div>
      <!-- End Grid -->
    </div>
    <!-- End Card Section -->

  </section>

</template>
