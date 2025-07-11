<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Progress } from '@/components/ui/progress'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import {
  Calendar,
  Clock,
  DollarSign,
  Edit,
  ExternalLink,
  Mail,
  Phone,
  Plus,
  Target,
  Activity,
  FileText,
  CheckCircle,
  Users,
  ChevronRight,
  MoreHorizontal,
  Archive,
  Trash,
  AlertTriangle
} from 'lucide-vue-next'

const props = defineProps({
  project: Object,
  tasks: Array,
  teamMembers: Array,
  recentTimeEntries: Array,
  recentActivity: Array,
})

const activeTab = ref('overview')

// Computed properties for task filtering
const completedTasks = computed(() => props.tasks.filter(task => task.status === 'completed'))
const inProgressTasks = computed(() => props.tasks.filter(task => task.status === 'in_progress'))
const overdueTasks = computed(() => props.tasks.filter(task => {
  if (!task.due_date) return false
  return new Date(task.due_date) < new Date() && task.status !== 'completed'
}))

const upcomingTasks = computed(() => {
  return props.tasks
    .filter(task => task.due_date && task.status !== 'completed')
    .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
    .slice(0, 5)
})

const budgetProgress = computed(() => {
  if (!props.project.budget) return 0
  return (props.project.stats.budget_used / props.project.budget) * 100
})

// Helper functions
const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const getProjectStatusColor = (status) => {
  const colors = {
    'active': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'on_hold': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    'completed': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getProjectPriorityColor = (priority) => {
  const colors = {
    'low': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    'medium': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    'high': 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
    'urgent': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[priority] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getTaskStatusColor = (status) => {
  const colors = {
    'pending': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    'completed': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getTaskPriorityColor = (priority) => {
  const colors = {
    'low': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    'medium': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    'high': 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
    'urgent': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[priority] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getActivityIcon = (type) => {
  const icons = {
    'task_created': CheckCircle,
    'task_completed': CheckCircle,
    'time_logged': Clock,
    'comment_added': FileText,
    'file_uploaded': FileText
  }
  return icons[type] || Activity
}
</script>

<template>
  <Head title="Project Details" />

  <AppLayout>
    <div class="max-w-5xl space-y-6 p-6">
      <!-- Project Header -->
      <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-muted-foreground mb-2">
          <a :href="route('projects.index')" class="hover:text-gray-900 dark:hover:text-gray-100">Projects</a>
          <ChevronRight class="h-4 w-4" />
          <span>{{ project.name }}</span>
        </div>

        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ project.name }}</h1>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" as="a" :href="route('projects.edit', project.uuid)">
              <Edit class="h-4 w-4 mr-1" />
              Edit
            </Button>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm">
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem as="a" :href="route('projects.edit', project.uuid)">
                  <Edit class="h-4 w-4 mr-2" />
                  Edit Project
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <Archive class="h-4 w-4 mr-2" />
                  Archive Project
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem class="text-red-600">
                  <Trash class="h-4 w-4 mr-2" />
                  Delete Project
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>

      <!-- Project Overview Card -->
      <Card class="mb-8">
        <CardContent class="p-6">
          <div class="flex flex-col lg:flex-row gap-6">
            <!-- Project Details -->
            <div class="flex-1 space-y-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Project Details</h3>
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <Badge :class="getProjectStatusColor(project.status)">{{ project.status }}</Badge>
                    <Badge :class="getProjectPriorityColor(project.priority)">{{ project.priority }}</Badge>
                  </div>
                  <p v-if="project.description" class="text-gray-600 dark:text-gray-400">{{ project.description }}</p>
                  <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div v-if="project.start_date" class="flex items-center gap-1">
                      <Calendar class="h-4 w-4" />
                      <span>Started {{ formatDate(project.start_date) }}</span>
                    </div>
                    <div v-if="project.due_date" class="flex items-center gap-1">
                      <Clock class="h-4 w-4" />
                      <span>Due {{ formatDate(project.due_date) }}</span>
                    </div>
                    <div v-if="project.budget" class="flex items-center gap-1">
                      <DollarSign class="h-4 w-4" />
                      <span>{{ formatCurrency(project.budget) }} budget</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Progress & Stats -->
            <div class="flex-1 space-y-4">
              <!-- Progress -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Project Progress</span>
                  <span class="text-sm font-medium">{{ project.progress.percentage }}%</span>
                </div>
                <Progress v-model="project.progress.percentage" class="h-1 rounded-none" />
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ project.progress.completed_tasks }}/{{ project.progress.total_tasks }} tasks completed</span>
                  <span>{{ project.stats.team_members }} team members</span>
                </div>
              </div>

              <!-- Quick Stats -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ project.stats.total_hours }}</div>
                  <div class="text-xs text-muted-foreground">Hours Logged</div>
                </div>
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ project.stats.completed_tasks }}</div>
                  <div class="text-xs text-muted-foreground">Completed</div>
                </div>
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ project.stats.in_progress_tasks }}</div>
                  <div class="text-xs text-muted-foreground">In Progress</div>
                </div>
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ project.stats.overdue_tasks }}</div>
                  <div class="text-xs text-muted-foreground">Overdue</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="mt-6 pt-6 border-t">
            <div class="flex items-center gap-4">
              <Avatar class="h-12 w-12">
                <AvatarImage v-if="project.contact.avatar_url" :src="project.contact.avatar_url" />
                <AvatarFallback>
                  {{ project.contact.first_name.charAt(0) }}{{ project.contact.last_name.charAt(0) }}
                </AvatarFallback>
              </Avatar>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ project.contact.full_name }}</h4>
                <p v-if="project.contact.job_title" class="text-sm text-muted-foreground">{{ project.contact.job_title }}</p>
                <div class="flex items-center gap-4 mt-1">
                  <a v-if="project.contact.primary_email" :href="`mailto:${project.contact.primary_email}`"
                     class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 flex items-center gap-1">
                    <Mail class="h-3 w-3" />
                    {{ project.contact.primary_email }}
                  </a>
                  <a v-if="project.contact.primary_phone_number" :href="`tel:${project.contact.primary_phone_number}`"
                     class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 flex items-center gap-1">
                    <Phone class="h-3 w-3" />
                    {{ project.contact.primary_phone_number }}
                  </a>
                </div>
              </div>
              <Button variant="outline" size="sm" as="a" :href="route('contacts.show', project.contact.uuid)" class="gap-2">
                <ExternalLink class="h-4 w-4" />
                View Contact
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Content Tabs -->
      <Tabs v-model="activeTab" class="space-y-6 mt-12">
        <TabsList class="grid w-full grid-cols-5 max-w-2xl mx-auto">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="tasks">Tasks ({{ tasks.length }})</TabsTrigger>
          <TabsTrigger value="team">Team ({{ teamMembers.length }})</TabsTrigger>
          <TabsTrigger value="time">Time Tracking</TabsTrigger>
          <TabsTrigger value="files">Files</TabsTrigger>
        </TabsList>

        <!-- Overview Tab -->
        <TabsContent value="overview" class="space-y-6">
          <div class="grid gap-6 lg:grid-cols-2">
            <!-- Upcoming Tasks -->
            <Card>
              <CardHeader class="flex flex-row items-center justify-between">
                <div>
                  <CardTitle class="flex items-center gap-2">
                    <Target class="h-5 w-5" />
                    Upcoming Tasks
                  </CardTitle>
                  <CardDescription>Tasks due soon</CardDescription>
                </div>
                <Button variant="outline" size="sm" as="a" :href="route('tasks.create', { project: project.uuid })">
                  <Plus class="h-4 w-4 mr-1" />
                  Add Task
                </Button>
              </CardHeader>
              <CardContent>
                <div v-if="upcomingTasks.length === 0" class="text-center py-8 text-muted-foreground">
                  <Target class="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p>No upcoming tasks</p>
                </div>
                <div v-else class="space-y-3">
                  <div v-for="task in upcomingTasks" :key="task.uuid"
                       class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex-1 min-w-0">
                      <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ task.title }}</h4>
                      <div class="flex items-center gap-2 mt-1">
                        <Badge :class="getTaskStatusColor(task.status)" class="text-xs">{{ task.status }}</Badge>
                        <Badge :class="getTaskPriorityColor(task.priority)" class="text-xs">{{ task.priority }}</Badge>
                        <span v-if="task.due_date" class="text-xs text-muted-foreground">Due {{ formatDate(task.due_date) }}</span>
                      </div>
                    </div>
                    <Button variant="ghost" size="sm" as="a" :href="route('tasks.show', task.uuid)">
                      <ExternalLink class="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Recent Activity -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Activity class="h-5 w-5" />
                  Recent Activity
                </CardTitle>
                <CardDescription>Latest project updates</CardDescription>
              </CardHeader>
              <CardContent>
                <div v-if="recentActivity.length === 0" class="text-center py-8 text-muted-foreground">
                  <Activity class="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p>No recent activity</p>
                </div>
                <div v-else class="space-y-4">
                  <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start gap-3">
                    <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/20">
                      <component :is="getActivityIcon(activity.type)" class="h-4 w-4 text-blue-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-900 dark:text-gray-100">{{ activity.description }}</p>
                      <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-muted-foreground">{{ activity.user.name }}</span>
                        <span class="text-xs text-muted-foreground">{{ formatDateTime(activity.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Budget & Time Overview -->
          <div class="grid gap-6 lg:grid-cols-2">
            <!-- Budget Tracking -->
            <Card v-if="project.budget">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <DollarSign class="h-5 w-5" />
                  Budget Tracking
                </CardTitle>
                <CardDescription>Project budget utilization</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Budget Used</span>
                    <span class="text-sm font-medium">{{ Math.round(budgetProgress) }}%</span>
                  </div>
                  <Progress :value="budgetProgress" class="h-2" />
                  <div class="flex items-center justify-between text-sm text-muted-foreground">
                    <span>{{ formatCurrency(project.stats.budget_used) }} used</span>
                    <span>{{ formatCurrency(project.stats.budget_remaining) }} remaining</span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(project.budget) }}</div>
                    <div class="text-xs text-muted-foreground">Total Budget</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                      {{ formatCurrency(project.hourly_rate || 0) }}
                    </div>
                    <div class="text-xs text-muted-foreground">Hourly Rate</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Time Overview -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Clock class="h-5 w-5" />
                  Time Overview
                </CardTitle>
                <CardDescription>Time tracking summary</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ project.stats.total_hours }}</div>
                    <div class="text-xs text-blue-700 dark:text-blue-300">Hours Logged</div>
                  </div>
                  <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                      {{ project.stats.estimated_hours || 0 }}
                    </div>
                    <div class="text-xs text-green-700 dark:text-green-300">Estimated</div>
                  </div>
                </div>

                <div v-if="recentTimeEntries.length > 0" class="space-y-2 pt-4 border-t">
                  <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Recent Time Entries</h4>
                  <div class="space-y-2">
                    <div v-for="entry in recentTimeEntries.slice(0, 3)" :key="entry.uuid"
                         class="flex items-center justify-between text-sm">
                      <div class="flex items-center gap-2">
                        <Avatar class="h-6 w-6">
                          <AvatarImage v-if="entry.user.avatar_url" :src="entry.user.avatar_url" />
                          <AvatarFallback class="text-xs">{{ entry.user.name.charAt(0) }}</AvatarFallback>
                        </Avatar>
                        <span class="text-gray-900 dark:text-gray-100">{{ entry.user.name }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-muted-foreground">{{ entry.hours }}h</span>
                        <span class="text-muted-foreground">{{ formatDate(entry.date) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Project Notes -->
          <Card v-if="project.notes">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Project Notes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="prose prose-sm max-w-none dark:prose-invert">
                <pre class="whitespace-pre-wrap text-sm">{{ project.notes }}</pre>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Tasks Tab -->
        <TabsContent value="tasks">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <CheckCircle class="h-5 w-5" />
                  Project Tasks
                </CardTitle>
                <CardDescription>Manage and track project tasks ({{ tasks.length }} total)</CardDescription>
              </div>
              <Button as="a" :href="route('tasks.create', { project: project.uuid })" class="gap-2">
                <Plus class="h-4 w-4" />
                Add Task
              </Button>
            </CardHeader>
            <CardContent>
              <div v-if="tasks.length === 0" class="text-center py-12 text-muted-foreground">
                <CheckCircle class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No tasks yet</h3>
                <p class="mb-4">Create your first task to start organizing project work.</p>
                <Button as="a" :href="route('tasks.create', { project: project.uuid })">
                  <Plus class="h-4 w-4 mr-1" />
                  Create First Task
                </Button>
              </div>
              <div v-else class="space-y-6">
                <!-- Overdue Tasks Alert -->
                <div v-if="overdueTasks.length > 0"
                     class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                  <div class="flex items-center gap-2">
                    <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    <h3 class="font-medium text-red-800 dark:text-red-200">Overdue Tasks</h3>
                  </div>
                  <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                    {{ overdueTasks.length }} task{{ overdueTasks.length !== 1 ? 's are' : ' is' }} overdue and
                    need{{ overdueTasks.length === 1 ? 's' : '' }} attention.
                  </p>
                </div>

                <!-- Task Status Summary -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ tasks.length }}</div>
                    <div class="text-sm text-muted-foreground">Total Tasks</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ completedTasks.length }}</div>
                    <div class="text-sm text-muted-foreground">Completed</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ inProgressTasks.length }}</div>
                    <div class="text-sm text-muted-foreground">In Progress</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ overdueTasks.length }}</div>
                    <div class="text-sm text-muted-foreground">Overdue</div>
                  </div>
                </div>

                <!-- Tasks Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <div v-for="task in tasks" :key="task.uuid"
                       class="group border rounded-lg p-4 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-600">
                    <div class="flex items-start justify-between mb-3">
                      <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                          <a :href="route('tasks.show', task.uuid)" class="block truncate">
                            {{ task.title }}
                          </a>
                        </h4>
                        <p v-if="task.description" class="text-sm text-muted-foreground mt-1 line-clamp-2">
                          {{ task.description }}
                        </p>
                      </div>
                      <Button variant="ghost" size="sm" as="a" :href="route('tasks.show', task.uuid)"
                              class="opacity-0 group-hover:opacity-100 transition-opacity">
                        <ExternalLink class="h-4 w-4" />
                      </Button>
                    </div>

                    <div class="space-y-3">
                      <!-- Status and Priority Badges -->
                      <div class="flex items-center gap-2 flex-wrap">
                        <Badge :class="getTaskStatusColor(task.status)" class="text-xs">{{ task.status }}</Badge>
                        <Badge :class="getTaskPriorityColor(task.priority)" class="text-xs">{{ task.priority }}</Badge>
                      </div>

                      <!-- Task Meta Information -->
                      <div class="space-y-2 text-xs text-muted-foreground">
                        <div v-if="task.due_date" class="flex items-center gap-1">
                          <Calendar class="h-3 w-3" />
                          <span>Due {{ formatDate(task.due_date) }}</span>
                        </div>
                        <div v-if="task.estimated_hours" class="flex items-center gap-1">
                          <Clock class="h-3 w-3" />
                          <span>{{ task.estimated_hours }}h estimated</span>
                        </div>
                      </div>

                      <!-- Assignee -->
                      <div v-if="task.assignee" class="flex items-center gap-2 pt-2 border-t">
                        <Avatar class="h-6 w-6">
                          <AvatarImage v-if="task.assignee.avatar_url" :src="task.assignee.avatar_url" />
                          <AvatarFallback class="text-xs">{{ task.assignee.name.charAt(0) }}</AvatarFallback>
                        </Avatar>
                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ task.assignee.name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Team Tab -->
        <TabsContent value="team">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <Users class="h-5 w-5" />
                  Team Members
                </CardTitle>
                <CardDescription>People working on this project ({{ teamMembers.length }} members)</CardDescription>
              </div>
              <Button variant="outline" size="sm" class="gap-2">
                <Plus class="h-4 w-4" />
                Add Member
              </Button>
            </CardHeader>
            <CardContent>
              <div v-if="teamMembers.length === 0" class="text-center py-12 text-muted-foreground">
                <Users class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No team members</h3>
                <p class="mb-4">Add team members to collaborate on this project.</p>
                <Button variant="outline">
                  <Plus class="h-4 w-4 mr-1" />
                  Add Team Member
                </Button>
              </div>
              <div v-else class="space-y-4">
                <div v-for="member in teamMembers" :key="member.uuid"
                     class="flex items-center justify-between p-4 border rounded-lg">
                  <div class="flex items-center gap-4">
                    <Avatar class="h-12 w-12">
                      <AvatarImage v-if="member.avatar_url" :src="member.avatar_url" />
                      <AvatarFallback>{{ member.name.charAt(0) }}</AvatarFallback>
                    </Avatar>
                    <div>
                      <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ member.name }}</h4>
                      <p class="text-sm text-muted-foreground">{{ member.role }}</p>
                      <p class="text-sm text-blue-600 dark:text-blue-400">{{ member.email }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="grid grid-cols-3 gap-4 text-center">
                      <div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ member.tasks_count || 0 }}</div>
                        <div class="text-xs text-muted-foreground">Tasks</div>
                      </div>
                      <div>
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ member.hours_logged || 0 }}</div>
                        <div class="text-xs text-muted-foreground">Hours</div>
                      </div>
                      <div>
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ member.completion_rate || 0 }}%</div>
                        <div class="text-xs text-muted-foreground">Complete</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Time Tracking Tab -->
        <TabsContent value="time">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Clock class="h-5 w-5" />
                Time Tracking
              </CardTitle>
              <CardDescription>Track time spent on this project</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="text-center py-12 text-muted-foreground">
                <Clock class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Time Tracking</h3>
                <p>Time tracking functionality coming soon.</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Files Tab -->
        <TabsContent value="files">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Project Files
              </CardTitle>
              <CardDescription>Manage project documents and files</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="text-center py-12 text-muted-foreground">
                <FileText class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No files yet</h3>
                <p>Upload files to share with your team.</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
