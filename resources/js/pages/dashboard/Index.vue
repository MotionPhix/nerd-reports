<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import VueApexCharts from 'vue3-apexcharts';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import {
  ClipboardList,
  TrashIcon as IconTrash,
  PhoneCall,
  RefreshCwIcon as IconRefresh,
  ClipboardList as IconClipboardList,
  Briefcase,
  BarChart,
  CheckCircle2,
  Users as IconUsers,
  FileText as IconFileText,
  AlertTriangle as IconAlertTriangle,
  TrendingUp,
  TrendingDown,
  Calendar,
  Clock,
  Plus,
  ArrowRight,
  Eye,
  Filter,
  MoreHorizontal,
  Target,
  Activity,
  Zap,
  ChevronRight,
  Bell,
  Star,
  Timer,
  MapPin,
  Phone,
  Mail,
  MessageSquare,
  Video,
  Users2,
  Building2,
  Sparkles
} from 'lucide-vue-next';
import { router, usePoll, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { useDark } from '@vueuse/core';
import AppSidebarLayout from '@/layouts/AppLayout.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import QuickStats from '@/components/QuickStats.vue';

// Props
const props = defineProps<{
  dashboardData: any
  quickStats?: any
  user: {
    id: number
    first_name: string
    last_name: string
    email: string
  }
}>();

// Reactive variables
const isLoading = ref(false);
const isRefreshing = ref(false);
const isClearingCache = ref(false);
const isDark = useDark();

// Computed properties
const hasAlerts = computed(() => {
  return (props.dashboardData.overview?.tasks?.overdue > 0) ||
    (props.dashboardData.overview?.projects?.overdue > 0) ||
    (props.dashboardData.overview?.interactions?.follow_ups_overdue > 0);
});

const taskEfficiency = computed(() => {
  const stats = props.dashboardData.tasks?.stats;
  return {
    completion_rate: stats?.completion_rate || 0,
    average_hours: stats?.average_hours_per_task || 0
  };
});

const monthlyProgressPercentage = computed(() =>
  props.dashboardData.productivity?.monthly_goal_progress?.tasks?.percentage || 0
);

// Chart configurations
const taskPriorityOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 280,
    background: 'transparent'
  },
  colors: ['#22c55e', '#3b82f6', '#f97316', '#dc2626'],
  labels: ['Low', 'Medium', 'High', 'Urgent'],
  legend: {
    position: 'bottom',
    fontSize: '14px',
    labels: {
      colors: isDark.value ? '#e5e7eb' : '#374151'
    }
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#fff']
    }
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total Tasks',
            color: isDark.value ? '#e5e7eb' : '#374151'
          }
        }
      }
    }
  },
  theme: {
    mode: isDark.value ? 'dark' : 'light'
  }
}));

const taskPrioritySeries = computed(() => [
  props.dashboardData.tasks?.priority_distribution?.low || 0,
  props.dashboardData.tasks?.priority_distribution?.medium || 0,
  props.dashboardData.tasks?.priority_distribution?.high || 0,
  props.dashboardData.tasks?.priority_distribution?.urgent || 0
]);

const productivityTrendOptions = computed(() => ({
  chart: {
    type: 'area',
    height: 200,
    toolbar: { show: false },
    background: 'transparent'
  },
  stroke: { curve: 'smooth', width: 3 },
  colors: ['#2563eb'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [0, 90, 100]
    }
  },
  grid: {
    borderColor: isDark.value ? '#374151' : '#e5e7eb'
  },
  xaxis: {
    labels: {
      style: {
        colors: isDark.value ? '#9ca3af' : '#6b7280'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: isDark.value ? '#9ca3af' : '#6b7280'
      }
    }
  },
  tooltip: {
    theme: isDark.value ? 'dark' : 'light'
  }
}));

// Utility functions
const getPriorityColor = (priority: string) => {
  const colors = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  };
  return colors[priority?.toLowerCase()] || colors.medium;
};

const getStatusColor = (status: string) => {
  const colors = {
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'in-progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    on_hold: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  };
  return colors[status?.toLowerCase().replace(' ', '_')] || colors.pending;
};

const formatDate = (date: string | null) => {
  if (!date) return 'No date set';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatTime = (timestamp: string | null) => {
  if (!timestamp) return '';
  return new Date(timestamp).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Actions
const refreshData = async () => {
  isRefreshing.value = true;
  try {
    await router.reload({ only: ['dashboardData', 'quickStats'] });
    toast.success('Dashboard refreshed successfully');
  } catch (error) {
    console.error('Refresh error:', error);
    toast.error('Failed to refresh dashboard');
  } finally {
    isRefreshing.value = false;
  }
};

const clearCache = async () => {
  isClearingCache.value = true;
  try {
    await router.post(route('dashboard.clear-cache'));
    toast.success('Cache cleared successfully');
  } catch (error) {
    console.error('Clear cache error:', error);
    toast.error('Failed to clear cache');
  } finally {
    isClearingCache.value = false;
  }
};

// Polling for real-time updates
usePoll(60000, {
  onStart: () => {
    router.reload({
      only: ['quickStats']
    });
  },
  onSuccess: () => {
    console.log('Dashboard polling successful');
  },
  onError: (error) => {
    console.error('Polling error:', error);
  }
});

// Layout
defineOptions({
  layout: AppSidebarLayout
});

// Lifecycle
onMounted(() => {
  console.log('Dashboard mounted with data:', props.dashboardData);
});
</script>

<template>
  <div class="p-6 space-y-8 max-w-5xl">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
          Good {{ new Date().getHours() < 12 ? 'morning' : new Date().getHours() < 18 ? 'afternoon' : 'evening' }},
          {{ user?.first_name }}
        </h1>
        <p class="text-muted-foreground mt-1">
          Here's what's happening with your projects today
        </p>
      </div>

      <div class="flex items-center gap-3">
        <Button
          variant="outline"
          size="sm"
          @click="refreshData"
          :disabled="isRefreshing"
          class="gap-2">
          <IconRefresh :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4" />
          Refresh
        </Button>

        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="outline" size="sm" class="gap-2">
              <MoreHorizontal class="h-4 w-4" />
              More
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem @click="clearCache" :disabled="isClearingCache">
              <IconTrash class="h-4 w-4 mr-2" />
              Clear Cache
            </DropdownMenuItem>
            <DropdownMenuItem>
              <Filter class="h-4 w-4 mr-2" />
              Customize View
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem>
              <Eye class="h-4 w-4 mr-2" />
              View Settings
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>

    <!-- Alert Cards -->
    <div v-if="hasAlerts" class="grid gap-4 md:grid-cols-3">
      <Alert
        v-if="dashboardData.overview?.tasks?.overdue > 0"
        variant="destructive"
        class="border-red-200 dark:border-red-800">
        <IconAlertTriangle class="h-4 w-4" />
        <AlertTitle class="flex items-center justify-between">
          Overdue Tasks
          <Badge variant="destructive" class="ml-2">
            {{ dashboardData.overview.tasks.overdue }}
          </Badge>
        </AlertTitle>
        <AlertDescription class="mt-2">
          You have tasks past their due date that need immediate attention.
          <Link
            :href="route('tasks.index', { filter: 'overdue' })"
            class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300"
          >
            View Tasks
            <ArrowRight class="h-3 w-3" />
          </Link>
        </AlertDescription>
      </Alert>

      <Alert
        v-if="dashboardData.overview?.projects?.overdue > 0"
        variant="destructive"
        class="border-red-200 dark:border-red-800">
        <IconAlertTriangle class="h-4 w-4" />
        <AlertTitle class="flex items-center justify-between">
          Overdue Projects
          <Badge variant="destructive" class="ml-2">
            {{ dashboardData.overview.projects.overdue }}
          </Badge>
        </AlertTitle>
        <AlertDescription class="mt-2">
          Projects need immediate attention to get back on track.
          <Link
            :href="route('projects.index', { filter: 'overdue' })"
            class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300"
          >
            View Projects
            <ArrowRight class="h-3 w-3" />
          </Link>
        </AlertDescription>
      </Alert>

      <Alert
        v-if="dashboardData.overview?.interactions?.follow_ups_overdue > 0" variant="destructive"
        class="border-red-200 dark:border-red-800">
        <IconAlertTriangle class="h-4 w-4" />
        <AlertTitle class="flex items-center justify-between">
          Overdue Follow-ups
          <Badge variant="destructive" class="ml-2">
            {{ dashboardData.overview.interactions.follow_ups_overdue }}
          </Badge>
        </AlertTitle>
        <AlertDescription class="mt-2">
          Follow-ups require your attention to maintain client relationships.
          <Link
            :href="route('interactions.index', { filter: 'overdue' })"
            class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300"
          >
            View Follow-ups
            <ArrowRight class="h-3 w-3" />
          </Link>
        </AlertDescription>
      </Alert>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
      <Card class="bg-blue-400/10 dark:bg-blue-900/20 relative overflow-hidden hover:shadow-lg transition-all duration-200 border-l-4 border-l-blue-500">
        <CardContent class="flex flex-col h-full">
          <QuickStats
            title="Total Tasks"
            :main-icon="ClipboardList"
            :main-count="dashboardData.overview?.tasks?.total_assigned || 0"
            :sub-count-label="`${dashboardData.overview?.tasks?.complete_this_week || 0} completed this week`"
            :sub-count-icon="CheckCircle2"
            :has-warning="dashboardData.overview?.tasks?.overdue > 0"
            :warning-count-label="`${dashboardData.overview.tasks.overdue} overdue`"
            color="blue"
          />
        </CardContent>
      </Card>

      <Card class="bg-green-400/10 dark:bg-green-900/20 relative overflow-hidden hover:shadow-lg transition-all duration-200 border-l-4 border-l-green-500">
        <CardContent class="flex flex-col h-full">
          <QuickStats
            title="Active Projects"
            :main-icon="Briefcase"
            :main-count="dashboardData.overview?.projects?.active || 0"
            :sub-count-label="`${dashboardData.overview?.projects?.completed_this_month || 0} completed this month`"
            :sub-count-icon="Target"
            :has-warning="dashboardData.overview?.projects?.overdue > 0"
            :warning-count-label="`${dashboardData.overview.projects.overdue} overdue`"
            color="green"
          />
        </CardContent>
      </Card>

      <Card class="bg-purple-400/10 dark:bg-purple-900/20 relative overflow-hidden hover:shadow-lg transition-all duration-200 border-l-4 border-l-purple-500">
        <CardContent class="flex flex-col h-full">
          <QuickStats
            title="Recorded Interactions"
            :main-icon="IconUsers"
            :main-count="dashboardData.overview?.interactions?.this_week || 0"
            :sub-count-label="`${dashboardData.overview?.interactions?.follow_ups_today || 0} follow-ups today`"
            :sub-count-icon="Bell"
            :has-warning="dashboardData.overview?.interactions?.follow_ups_overdue > 0"
            :warning-count-label="`${dashboardData.overview.interactions.follow_ups_overdue} overdue follow-ups`"
            color="purple"
          />
        </CardContent>
      </Card>

      <Card class="bg-orange-400/10 dark:bg-orange-900/20 relative overflow-hidden hover:shadow-lg transition-all duration-200 border-l-4 border-l-orange-500">
        <CardContent class="flex flex-col h-full">
          <QuickStats
            title="Processed Reports"
            :main-icon="IconFileText"
            :main-count="dashboardData.overview?.reports?.generated_this_month || 0"
            :sub-count-label="`${dashboardData.overview?.reports?.pending || 0} pending`"
            :sub-count-icon="Clock"
            :has-warning="false"
            color="orange"
          />
        </CardContent>
      </Card>
    </div>

    <!-- Productivity Overview -->
    <div class="grid gap-6 md:grid-cols-3">
      <Card class="md:col-span-2">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Activity class="h-5 w-5" />
            Productivity Overview
          </CardTitle>
          <CardDescription>Your performance metrics this week</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Task Completion Rate</span>
                <span class="text-2xl font-bold text-green-600">{{ taskEfficiency.completion_rate }}%</span>
              </div>
              <Progress :value="taskEfficiency.completion_rate" class="h-2" />

              <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Monthly Goal Progress</span>
                <span class="text-2xl font-bold text-blue-600">{{ monthlyProgressPercentage }}%</span>
              </div>
              <Progress :value="monthlyProgressPercentage" class="h-2" />
            </div>

            <div class="space-y-4">
              <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ dashboardData.productivity?.this_week?.completed_tasks || 0 }}
                </div>
                <div class="text-sm text-muted-foreground">Tasks Completed</div>
              </div>

              <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ dashboardData.productivity?.this_week?.total_hours || 0 }}h
                </div>
                <div class="text-sm text-muted-foreground">Hours Logged</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <BarChart class="h-5 w-5" />
            Task Priorities
          </CardTitle>
          <CardDescription>Distribution by priority level</CardDescription>
        </CardHeader>
        <CardContent>
          <VueApexCharts
            type="donut"
            height="280"
            :options="taskPriorityOptions"
            :series="taskPrioritySeries"
          />
        </CardContent>
      </Card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Recent Activity -->
      <Card class="lg:col-span-2">
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle class="flex items-center gap-2">
              <Zap class="h-5 w-5" />
              Recent Activity
            </CardTitle>
            <CardDescription>Latest updates across your projects</CardDescription>
          </div>
          <Button variant="outline" size="sm">
            View All
          </Button>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-if="dashboardData.recent_activity?.length === 0" class="text-center py-8 text-muted-foreground">
              <Activity class="h-12 w-12 mx-auto mb-4 opacity-50" />
              <p>No recent activity to show</p>
            </div>
            <div v-else v-for="activity in dashboardData.recent_activity?.slice(0, 5)" :key="activity.timestamp"
                 class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/20">
                <CheckCircle2 v-if="activity.action === 'completed'" class="h-4 w-4 text-green-600" />
                <Clock v-else-if="activity.action === 'started'" class="h-4 w-4 text-blue-600" />
                <Plus v-else-if="activity.action === 'created'" class="h-4 w-4 text-purple-600" />
                <Activity v-else class="h-4 w-4 text-gray-600" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">
                  {{ formatTime(activity.timestamp) }}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Today's Focus -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Target class="h-5 w-5" />
            Today's Focus
          </CardTitle>
          <CardDescription>Priority items for today</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Today's Follow-ups -->
            <div v-if="dashboardData.interactions?.today_follow_ups?.length > 0">
              <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Follow-ups Due</h4>
              <div class="space-y-2">
                <div v-for="followUp in dashboardData.interactions.today_follow_ups.slice(0, 3)" :key="followUp.uuid"
                     class="flex items-center gap-2 p-2 rounded-md bg-yellow-50 dark:bg-yellow-900/20">
                  <Bell class="h-4 w-4 text-yellow-600" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ followUp.contact.first_name }}
                      {{ followUp.contact.last_name }}</p>
                    <p class="text-xs text-muted-foreground truncate">{{ followUp.subject || followUp.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Upcoming Tasks -->
            <div v-if="dashboardData.upcoming?.tasks?.length > 0">
              <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Upcoming Tasks</h4>
              <div class="space-y-2">
                <div v-for="task in dashboardData.upcoming.tasks.slice(0, 3)" :key="task.uuid"
                     class="flex items-center gap-2 p-2 rounded-md bg-blue-50 dark:bg-blue-900/20">
                  <ClipboardList class="h-4 w-4 text-blue-600" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ task.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatDate(task.due_date) }}</p>
                  </div>
                  <Badge :class="getPriorityColor(task.priority)" class="text-xs">
                    {{ task.priority }}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="pt-4 border-t">
              <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Quick Actions</h4>
              <div class="grid grid-cols-2 gap-2">
                <Button variant="outline" size="sm" class="gap-2" as="a" :href="route('tasks.create')">
                  <Plus class="h-4 w-4" />
                  New Task
                </Button>
                <Button variant="outline" size="sm" class="gap-2" as="a" :href="route('interactions.create')">
                  <Phone class="h-4 w-4" />
                  Log Call
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Active Projects & Recent Interactions -->
    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Active Projects -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle class="flex items-center gap-2">
              <Briefcase class="h-5 w-5" />
              Active Projects
            </CardTitle>
            <CardDescription>Projects currently in progress</CardDescription>
          </div>
          <Button variant="outline" size="sm" as="a" :href="route('projects.index')">
            View All
          </Button>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-if="dashboardData.projects?.active?.length === 0" class="text-center py-8 text-muted-foreground">
              <Briefcase class="h-12 w-12 mx-auto mb-4 opacity-50" />
              <p>No active projects</p>
              <Button variant="outline" size="sm" class="mt-2" as="a" :href="route('projects.create')">
                Create Project
              </Button>
            </div>
            <div v-else v-for="project in dashboardData.projects.active.slice(0, 4)" :key="project.uuid"
                 class="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ project.name }}</h4>
                  <p class="text-sm text-muted-foreground">
                    {{ project.contact.first_name }} {{ project.contact.last_name }} • {{ project.contact.firm.name }}
                  </p>
                </div>
                <Badge :class="getStatusColor(project.status)" class="text-xs">
                  {{ project.status }}
                </Badge>
              </div>

              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">Progress</span>
                  <span class="font-medium">{{ project.progress?.percentage || 0 }}%</span>
                </div>
                <Progress :value="project.progress?.percentage || 0" class="h-2" />

                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ project.progress?.completed_tasks || 0 }}/{{ project.progress?.total_tasks || 0
                    }} tasks</span>
                  <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
                </div>
              </div>

              <div class="flex items-center justify-between mt-3 pt-3 border-t">
                <Button variant="ghost" size="sm" as="a" :href="route('projects.show', project.uuid)">
                  <Eye class="h-4 w-4 mr-1" />
                  View
                </Button>
                <Button variant="ghost" size="sm" as="a" :href="route('tasks.index', { project: project.uuid })">
                  <ClipboardList class="h-4 w-4 mr-1" />
                  Tasks
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Interactions -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle class="flex items-center gap-2">
              <IconUsers class="h-5 w-5" />
              Recent Interactions
            </CardTitle>
            <CardDescription>Latest client communications</CardDescription>
          </div>
          <Button variant="outline" size="sm" as="a" :href="route('interactions.index')">
            View All
          </Button>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-if="dashboardData.interactions?.recent?.length === 0" class="text-center py-8 text-muted-foreground">
              <IconUsers class="h-12 w-12 mx-auto mb-4 opacity-50" />
              <p>No recent interactions</p>
              <Button variant="outline" size="sm" class="mt-2" as="a" :href="route('interactions.create')">
                Log Interaction
              </Button>
            </div>
            <div v-else v-for="interaction in dashboardData.interactions.recent.slice(0, 4)" :key="interaction.uuid"
                 class="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="flex items-start gap-3">
                <div class="rounded-full bg-purple-100 p-2 dark:bg-purple-900/20">
                  <Phone v-if="interaction.type === 'phone_call'" class="h-4 w-4 text-purple-600" />
                  <Mail v-else-if="interaction.type === 'email'" class="h-4 w-4 text-purple-600" />
                  <Video v-else-if="interaction.type === 'video_call'" class="h-4 w-4 text-purple-600" />
                  <Users2 v-else-if="interaction.type === 'meeting'" class="h-4 w-4 text-purple-600" />
                  <MessageSquare v-else class="h-4 w-4 text-purple-600" />
                </div>

                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">
                      {{ interaction.contact.first_name }} {{ interaction.contact.last_name }}
                    </h4>
                    <span class="text-xs text-muted-foreground">
                        {{ formatTime(interaction.interaction_date) }}
                      </span>
                  </div>

                  <p class="text-sm text-muted-foreground mb-2">
                    {{ interaction.contact.firm.name }}
                  </p>

                  <p class="text-sm text-gray-900 dark:text-gray-100 line-clamp-2">
                    {{ interaction.subject || interaction.description }}
                  </p>

                  <div class="flex items-center justify-between mt-3">
                    <Badge variant="outline" class="text-xs">
                      {{ interaction.type?.replace('_', ' ') }}
                    </Badge>
                    <div v-if="interaction.follow_up_required" class="flex items-center gap-1 text-xs text-orange-600">
                      <Bell class="h-3 w-3" />
                      Follow-up {{ formatDate(interaction.follow_up_date) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Overdue Items Alert -->
    <div v-if="dashboardData.tasks?.overdue?.length > 0 || dashboardData.projects?.overdue?.length > 0"
         class="space-y-4">
      <Card class="border-red-200 dark:border-red-800">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-red-600 dark:text-red-400">
            <IconAlertTriangle class="h-5 w-5" />
            Items Requiring Immediate Attention
          </CardTitle>
          <CardDescription>Overdue tasks and projects that need your focus</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <!-- Overdue Tasks -->
            <div v-if="dashboardData.tasks?.overdue?.length > 0">
              <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Overdue Tasks</h4>
              <div class="space-y-2">
                <div v-for="task in dashboardData.tasks.overdue.slice(0, 3)" :key="task.uuid"
                     class="flex items-center gap-2 p-2 rounded-md bg-red-50 dark:bg-red-900/20">
                  <IconAlertTriangle class="h-4 w-4 text-red-600" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ task.name }}</p>
                    <p class="text-xs text-muted-foreground truncate">{{ task.description }}</p>
                  </div>
                  <Badge :class="getPriorityColor(task.priority)" class="text-xs">
                    {{ task.priority }}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Overdue Projects -->
            <div v-if="dashboardData.projects?.overdue?.length > 0">
              <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Overdue Projects</h4>
              <div class="space-y-2">
                <div v-for="project in dashboardData.projects.overdue.slice(0, 3)" :key="project.uuid"
                     class="flex items-center gap-2 p-2 rounded-md bg-red-50 dark:bg-red-900/20">
                  <IconAlertTriangle class="h-4 w-4 text-red-600" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ project.name }}</p>
                    <p class="text-xs text-muted-foreground truncate">
                      {{ project.contact.first_name }} {{ project.contact.last_name }} • {{ project.contact.firm.name }}
                    </p>
                  </div>
                  <Badge :class="getStatusColor(project.status)" class="text-xs">
                    {{ project.status }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between mt-6 pt-4 border-t">
            <div class="text-sm text-muted-foreground">
              Address these items to stay on track with your goals
            </div>
            <div class="flex gap-2">
              <Button variant="outline" size="sm" as="a" :href="route('tasks.index', { filter: 'overdue' })">
                View All Overdue Tasks
              </Button>
              <Button variant="outline" size="sm" as="a" :href="route('projects.index', { filter: 'overdue' })">
                View All Overdue Projects
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Weekly Summary -->
    <Card v-if="dashboardData.reports?.last_weekly_report"
          class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/50 dark:to-indigo-950/50 border-blue-200 dark:border-blue-800">
      <CardHeader>
        <CardTitle class="flex items-center gap-2 text-blue-700 dark:text-blue-300">
          <Sparkles class="h-5 w-5" />
          Weekly Summary
        </CardTitle>
        <CardDescription>Your performance highlights from last week</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="grid gap-4 md:grid-cols-3">
          <div class="text-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
              {{ dashboardData.reports.last_weekly_report.completed_tasks || 0 }}
            </div>
            <div class="text-sm text-muted-foreground">Tasks Completed</div>
          </div>

          <div class="text-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
              {{ dashboardData.reports.last_weekly_report.total_hours || 0 }}h
            </div>
            <div class="text-sm text-muted-foreground">Hours Logged</div>
          </div>

          <div class="text-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
              {{ dashboardData.reports.last_weekly_report.metadata?.clients_count || 0 }}
            </div>
            <div class="text-sm text-muted-foreground">Clients Served</div>
          </div>
        </div>

        <div class="flex items-center justify-between mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
          <div class="text-sm text-blue-700 dark:text-blue-300">
            Week {{ dashboardData.reports.last_weekly_report.week_number }},
            {{ dashboardData.reports.last_weekly_report.year }}
          </div>
          <Button variant="outline" size="sm" as="a"
                  :href="route('reports.show', dashboardData.reports.last_weekly_report.uuid)">
            <Eye class="h-4 w-4 mr-1" />
            View Full Report
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Quick Actions Footer -->
    <Card class="bg-gray-50 dark:bg-gray-800/50 border-dashed">
      <CardContent class="p-6">
        <div class="text-center space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ready to be productive?</h3>
          <p class="text-muted-foreground">Start your day with these quick actions</p>

          <div class="flex flex-wrap justify-center gap-3">
            <Button as="a" :href="route('tasks.create')" class="gap-2">
              <Plus class="h-4 w-4" />
              Create Task
            </Button>
            <Button variant="outline" as="a" :href="route('projects.create')" class="gap-2">
              <Briefcase class="h-4 w-4" />
              New Project
            </Button>
            <Button variant="outline" as="a" :href="route('interactions.create')" class="gap-2">
              <Phone class="h-4 w-4" />
              Log Interaction
            </Button>
            <Button variant="outline" as="a" :href="route('contacts.create')" class="gap-2">
              <IconUsers class="h-4 w-4" />
              Add Contact
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
