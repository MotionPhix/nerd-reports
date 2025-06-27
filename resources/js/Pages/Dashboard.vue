<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import { IconPlus } from "@tabler/icons-vue"
import useStickyTop from "@/Composables/useStickyTop"
import { LineChart } from '@opd/g2plot-vue'
import { computed, onMounted, reactive, ref } from "vue";
import { Line } from '@antv/g2plot';
import { twi, twj } from "tw-to-css"

const { navClasses } = useStickyTop()

const props = defineProps<{
  dashboardData: {
    totalContacts: number,
    totalTasks: number,
    totalProjects: number,
    chart_data: {
      current_month: Array<{ date: string, count: number }>,
      previous_month: Array<{ date: string, count: number }>
    }
  }
}>()

const chartColor = ref(twj(`text-gray-500 dark:text-gray-200`))

// Prepare chart data for ApexCharts
const categories = computed(() => {
  // Get all unique dates from both months, sorted
  const dates = [
    ...props.dashboardData.chart_data.current_month.map(d => d.date),
    ...props.dashboardData.chart_data.previous_month.map(d => d.date)
  ]
  return Array.from(new Set(dates)).sort()
})

const currentMonthSeries = computed(() => {
  const map = Object.fromEntries(props.dashboardData.chart_data.current_month.map(d => [d.date, d.count]))
  return categories.value.map(date => map[date] ?? 0)
})

const previousMonthSeries = computed(() => {
  const map = Object.fromEntries(props.dashboardData.chart_data.previous_month.map(d => [d.date, d.count]))
  return categories.value.map(date => map[date] ?? 0)
})

const series = [
  {
    name: "Projects (Current Month)",
    data: currentMonthSeries.value
  },
  {
    name: "Projects (Previous Month)",
    data: previousMonthSeries.value
  }
]

const options = {
  chart: {
    id: 'projects-comparison',
    type: 'line',
    toolbar: { show: false }
  },
  xaxis: {
    categories: categories.value,
    type: 'category',
    labels: {
      rotate: -45,
      formatter: val => val
    }
  },
  stroke: {
    width: 3,
    curve: 'smooth'
  },
  title: {
    text: 'Monthly Project Comparison',
    align: 'left',
    style: {
      fontSize: "16px",
      color: chartColor.value.color,
    }
  },
  dataLabels: { enabled: false },
  legend: { position: 'top' }
}

const pieSeries = computed(() =>
  props.dashboardData.projectsByStatus.map(s => s.count)
)
const pieLabels = computed(() =>
  props.dashboardData.projectsByStatus.map(s => s.status)
)
const pieOptions = {
  chart: {
    type: 'pie',
    background: 'transparent'
  },
  labels: pieLabels.value,
  legend: { position: 'bottom', labels: { colors: [chartColor.value.color] } },
  title: {
    text: 'Projects by Status',
    align: 'left',
    style: {
      fontSize: "16px",
      color: chartColor.value.color,
    }
  },
  dataLabels: { enabled: true },
  theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' }
}

defineOptions({
  layout: AuthenticatedLayout
})
</script>

<template>
  <Head title="Dashboard" />

  <nav
    class="flex items-center h-16 max-w-3xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
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
      <span>New Project</span>
    </Link>
  </nav>

  <section class="max-w-3xl px-8 py-12 mx-auto">

    <!-- Card Section -->
    <div class="max-w-[85rem] py-10 lg:py-14 mx-auto">
      <!-- Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800 border-neutral-300">
          <div class="flex p-4 md:p-5 gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-200 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 text-gray-600 size-5 dark:text-neutral-400"
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

                <p class="text-xs tracking-wide text-gray-500 uppercase dark:text-neutral-500">
                  Total <br> contacts
                </p>

                <div v-tooltip="'The number of contacts (not companies)'">

                  <svg class="flex-shrink-0 text-gray-500 size-4 dark:text-neutral-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <path d="M12 17h.01" />
                  </svg>

                </div>

              </div>

              <div class="flex items-center mt-1 gap-x-2">

                <h3 class="text-xl font-medium text-gray-800 sm:text-2xl dark:text-neutral-200">
                  {{ props.dashboardData.totalContacts }}
                </h3>

              </div>

            </div>

          </div>

        </div>
        <!-- End Card -->

        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800 border-neutral-300">
          <div class="flex p-4 md:p-5 gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-200 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 text-gray-600 size-5 dark:text-neutral-400"
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
                <p class="text-xs tracking-wide text-gray-500 uppercase dark:text-neutral-500">
                  Total <br> Projects
                </p>
              </div>

              <div class="flex items-center mt-1 gap-x-2">
                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalProjects }}
                </h3>
              </div>

            </div>
          </div>
        </div>
        <!-- End Card -->

        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800 border-neutral-300">
          <div class="flex p-4 md:p-5 gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-200 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 text-gray-600 size-5 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" />
                <path d="m12 12 4 10 1.7-4.3L22 16Z" />
              </svg>
            </div>

            <div class="grow">
              <div class="flex items-center gap-x-2">
                <p class="text-xs tracking-wide text-gray-500 uppercase dark:text-neutral-500">
                  Total <br> Tasks
                </p>
              </div>
              <div class="flex items-center mt-1 gap-x-2">

                <h3 class="text-xl font-medium text-gray-800 sm:text-2xl dark:text-neutral-200">
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

    <apexchart
      width="100%"
      type="line"
      height="350"
      :options="options"
      :series="series">
    </apexchart>

    <!-- Projects by Status Pie Chart -->
    <div class="mt-8">
      <apexchart
        width="100%"
        type="pie"
        height="300"
        :options="pieOptions"
        :series="pieSeries"
      />
    </div>

    <div class="mt-8">
      <h3 class="mb-2 text-lg font-semibold">Recent Projects</h3>

      <ul>
        <li v-for="project in props.dashboardData.recentProjects" :key="project.id" class="mb-1">
          <span class="font-medium">{{ project.name }}</span>
          <span class="text-xs text-gray-500">({{ project.created_at }})</span>
        </li>
      </ul>
    </div>

    <div class="mt-8 text-gray-500 dark:text-gray-400">
      <h3 class="mb-2 text-lg font-semibold">Upcoming Project Deadlines</h3>
      <ul>
        <li
          v-for="project in props.dashboardData.upcomingDeadlines"
          :key="project.id"
          class="flex items-center justify-between mb-1"
        >
          <span class="font-medium">{{ project.name }}</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ project.due_date }}
          </span>
        </li>
        <li v-if="!props.dashboardData.upcomingDeadlines.length" class="text-sm text-gray-400 dark:text-gray-600">
          No upcoming deadlines.
        </li>
      </ul>
    </div>

    <!-- Recent Contacts Widget -->
    <div class="mt-8">
      <h3 class="mb-2 text-lg font-semibold">Recent Contacts</h3>
      <ul>
        <li
          v-for="contact in props.dashboardData.recentContacts"
          :key="contact.id"
          class="flex items-center justify-between mb-1"
        >
          <span class="font-medium">{{ contact.first_name }} {{ contact.last_name }}</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ contact.created_at }}
          </span>
        </li>
        <li v-if="!props.dashboardData.recentContacts.length" class="text-sm text-gray-400 dark:text-gray-600">
          No recent contacts.
        </li>
      </ul>
    </div>

  </section>

</template>
