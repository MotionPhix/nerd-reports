<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import {
  IconPlus,
  IconUsers,
  IconClipboardList,
  IconBriefcase,
  IconTrendingUp,
  IconCalendarEvent,
  IconChevronRight,
  IconActivity,
  IconChartPie,
  IconChartLine,
  IconRefresh
} from "@tabler/icons-vue"
import useStickyTop from "@/Composables/useStickyTop"
import { computed, onMounted, ref, watch } from "vue"

const { navClasses } = useStickyTop()

const props = defineProps<{
  dashboardData: {
    totalContacts: number,
    totalTasks: number,
    totalProjects: number,
    totalFirms: number,
    chart_data: {
      current_month: Array<{ date: string, count: number }>,
      previous_month: Array<{ date: string, count: number }>
    },
    projectsByStatus?: Array<{ status: string, count: number }>,
    recentProjects?: Array<{ id: number, name: string, created_at: string, status: string }>,
    upcomingDeadlines?: Array<{ id: number, name: string, due_date: string }>,
    recentContacts?: Array<{ id: number, first_name: string, last_name: string, created_at: string }>,
    tasksByPriority?: Array<{ priority: string, count: number }>,
    projectProgress?: { completed: number, inProgress: number, pending: number },
    monthlyGrowth?: { contacts: number, projects: number, tasks: number }
  }
}>()

// Reactive state for animations and interactions
const isLoading = ref(false)
const hoveredCard = ref<string | null>(null)
const selectedChart = ref<'line' | 'pie'>('line')

// Chart configuration with proper dark mode support
const isDark = ref(document.documentElement.classList.contains('dark'))

// Watch for dark mode changes
watch(() => document.documentElement.classList.contains('dark'), (newVal) => {
  isDark.value = newVal
}, { immediate: true })

// Chart data preparation
const categories = computed(() => {
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

const series = computed(() => [
  {
    name: "Current Month",
    data: currentMonthSeries.value
  },
  {
    name: "Previous Month",
    data: previousMonthSeries.value
  }
])

const chartOptions = computed(() => ({
  chart: {
    id: 'projects-comparison',
    type: 'line',
    toolbar: { show: false },
    background: 'transparent',
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800
    }
  },
  theme: {
    mode: isDark.value ? 'dark' : 'light'
  },
  xaxis: {
    categories: categories.value,
    type: 'category',
    labels: {
      rotate: -45,
      style: {
        colors: isDark.value ? '#9CA3AF' : '#6B7280'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: isDark.value ? '#9CA3AF' : '#6B7280'
      }
    }
  },
  stroke: {
    width: 3,
    curve: 'smooth'
  },
  colors: ['#3B82F6', '#10B981'],
  grid: {
    borderColor: isDark.value ? '#374151' : '#E5E7EB'
  },
  tooltip: {
    theme: isDark.value ? 'dark' : 'light'
  },
  dataLabels: { enabled: false },
  legend: {
    position: 'top',
    labels: {
      colors: isDark.value ? '#D1D5DB' : '#374151'
    }
  }
}))

const pieOptions = computed(() => ({
  chart: {
    type: 'pie',
    background: 'transparent'
  },
  theme: {
    mode: isDark.value ? 'dark' : 'light'
  },
  labels: props.dashboardData.projectsByStatus?.map(s => s.status) || [],
  colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
  legend: {
    position: 'bottom',
    labels: {
      colors: isDark.value ? '#D1D5DB' : '#374151'
    }
  },
  tooltip: {
    theme: isDark.value ? 'dark' : 'light'
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#FFFFFF']
    }
  }
}))

const pieSeries = computed(() =>
  props.dashboardData.projectsByStatus?.map(s => s.count) || []
)

// Stats cards configuration
const statsCards = computed(() => [
  {
    id: 'contacts',
    title: 'Total Contacts',
    value: props.dashboardData.totalContacts,
    icon: IconUsers,
    color: 'blue',
    growth: props.dashboardData.monthlyGrowth?.contacts || 0,
    route: 'contacts.index'
  },
  {
    id: 'projects',
    title: 'Total Projects',
    value: props.dashboardData.totalProjects,
    icon: IconBriefcase,
    color: 'green',
    growth: props.dashboardData.monthlyGrowth?.projects || 0,
    route: 'projects.index'
  },
  {
    id: 'tasks',
    title: 'Total Tasks',
    value: props.dashboardData.totalTasks,
    icon: IconClipboardList,
    color: 'purple',
    growth: props.dashboardData.monthlyGrowth?.tasks || 0,
    route: 'tasks.index'
  },
  {
    id: 'firms',
    title: 'Total Firms',
    value: props.dashboardData.totalFirms || 0,
    icon: IconActivity,
    color: 'amber',
    growth: 0,
    route: 'firms.index'
  }
])

const refreshData = () => {
  isLoading.value = true
  // Simulate API call
  setTimeout(() => {
    isLoading.value = false
  }, 1000)
}

defineOptions({
  layout: AuthenticatedLayout
})
</script>

<template>
  <Head title="Dashboard" />

  <!-- Header Navigation -->
  <nav
    class="sticky top-0 z-10 flex items-center h-16 max-w-7xl gap-6 px-4 mx-auto bg-white/80 backdrop-blur-md dark:bg-gray-900/80 border-b border-gray-200 dark:border-gray-700"
    :class="navClasses">
    <h2 class="flex items-center gap-3 text-2xl font-bold text-gray-900 dark:text-white">
      <IconChartLine class="w-8 h-8 text-blue-600 dark:text-blue-400" />
      <span>Dashboard</span>
    </h2>

    <div class="flex-1"></div>

    <button
      @click="refreshData"
      :disabled="isLoading"
      class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors disabled:opacity-50">
      <IconRefresh :class="['w-5 h-5', { 'animate-spin': isLoading }]" />
    </button>

    <Link
      as="button"
      :href="route('projects.create')"
      class="inline-flex items-center gap-2 px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
      <IconPlus class="w-4 h-4" />
      <span>New Project</span>
    </Link>
  </nav>

  <main class="max-w-7xl px-4 py-8 mx-auto space-y-8">
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="card in statsCards"
        :key="card.id"
        @mouseenter="hoveredCard = card.id"
        @mouseleave="hoveredCard = null"
        class="relative p-6 bg-white rounded-2xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 cursor-pointer group">

        <!-- Card Background Gradient -->
        <div class="absolute inset-0 bg-gradient-to-r opacity-0 group-hover:opacity-10 transition-opacity duration-300 rounded-2xl"
             :class="{
               'from-blue-500 to-blue-600': card.color === 'blue',
               'from-green-500 to-green-600': card.color === 'green',
               'from-purple-500 to-purple-600': card.color === 'purple',
               'from-amber-500 to-amber-600': card.color === 'amber'
             }"></div>

        <div class="relative flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
              {{ card.title }}
            </p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
              {{ card.value.toLocaleString() }}
            </p>
            <div v-if="card.growth !== 0" class="flex items-center mt-2">
              <IconTrendingUp
                :class="[
                  'w-4 h-4 mr-1',
                  card.growth > 0 ? 'text-green-500' : 'text-red-500'
                ]" />
              <span
                :class="[
                  'text-sm font-medium',
                  card.growth > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                ]">
                {{ card.growth > 0 ? '+' : '' }}{{ card.growth }}%
              </span>
            </div>
          </div>

          <div
            :class="[
              'p-3 rounded-full transition-all duration-300',
              {
                'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': card.color === 'blue',
                'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400': card.color === 'green',
                'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400': card.color === 'purple',
                'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': card.color === 'amber'
              },
              hoveredCard === card.id ? 'scale-110' : 'scale-100'
            ]">
            <component :is="card.icon" class="w-6 h-6" />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Line Chart -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Project Trends
          </h3>
          <div class="flex items-center space-x-2">
            <button
              @click="selectedChart = 'line'"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                selectedChart === 'line'
                  ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]">
              Line
            </button>
            <button
              @click="selectedChart = 'pie'"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                selectedChart === 'pie'
                  ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]">
              Status
            </button>
          </div>
        </div>

        <div v-show="selectedChart === 'line'">
          <apexchart
            width="100%"
            height="300"
            type="line"
            :options="chartOptions"
            :series="series" />
        </div>

        <div v-show="selectedChart === 'pie'" v-if="pieSeries.length > 0">
          <apexchart
            width="100%"
            height="300"
            type="pie"
            :options="pieOptions"
            :series="pieSeries"
            />
        </div>
      </div>
    </div>
  </main>
</template>
