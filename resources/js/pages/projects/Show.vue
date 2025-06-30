<script setup lang="ts">
import { ref, computed, onMounted } from "vue"
import { router } from "@inertiajs/vue3"
import { toast } from "vue-sonner"
import { useDark } from "@vueuse/core"
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent
} from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { Progress } from "@/components/ui/progress"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Separator } from "@/components/ui/separator"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel
} from "@/components/ui/dropdown-menu"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger
} from "@/components/ui/alert-dialog"
import {
  ArrowLeft,
  Edit,
  Trash2,
  MoreHorizontal,
  Users,
  Clock,
  Calendar,
  DollarSign,
  CheckCircle,
  AlertTriangle,
  Play,
  Pause,
  Square,
  Plus,
  FileText,
  MessageSquare,
  Settings,
  Share2,
  Download,
  RefreshCw,
  Briefcase,
  Target,
  TrendingUp,
  Activity,
  User,
  Building,
  Mail,
  Phone,
  ExternalLink,
  Archive,
  Star,
  Flag
} from "lucide-vue-next"
import AppSidebarLayout from "@/layouts/AppSidebarLayout.vue"

// Types
interface Contact {
  uuid: string
  first_name: string
  last_name: string
  full_name: string
  primary_email: string | null
  primary_phone_number: string | null
  job_title: string | null
  avatar_url: string | null
  firm?: {
    uuid: string
    name: string
  }
}

interface Task {
  uuid: string
  title: string
  description: string | null
  status: string
  priority: string
  due_date: string | null
  assigned_to: string | null
  estimated_hours: number | null
  actual_hours: number | null
  created_at: string
  updated_at: string
  assignee?: {
    uuid: string
    name: string
    avatar_url: string | null
  }
}

interface TeamMember {
  uuid: string
  name: string
  email: string
  role: string
  avatar_url: string | null
  hours_logged: number
  tasks_assigned: number
  tasks_completed: number
}

interface TimeEntry {
  uuid: string
  description: string
  hours: number
  date: string
  user: {
    uuid: string
    name: string
    avatar_url: string | null
  }
  task?: {
    uuid: string
    title: string
  }
}

interface Project {
  uuid: string
  name: string
  description: string | null
  status: string
  priority: string
  due_date: string | null
  deadline: string | null
  estimated_hours: number | null
  budget: number | null
  hourly_rate: number | null
  is_billable: boolean
  notes: string | null
  created_at: string
  updated_at: string
  contact: Contact
  progress: {
    percentage: number
    completed_tasks: number
    total_tasks: number
    status: string
  }
  stats: {
    total_tasks: number
    completed_tasks: number
    in_progress_tasks: number
    todo_tasks: number
    overdue_tasks: number
    total_hours: number
    estimated_hours: number
    completion_rate: number
    team_members: number
    budget_used: number
    budget_remaining: number
  }
  tags: Array<{
    id: number
    name: string
    type: string | null
  }>
}

interface Props {
  project: Project
  tasks: Task[]
  teamMembers: TeamMember[]
  recentTimeEntries: TimeEntry[]
  recentActivity: Array<{
    id: number
    type: string
    description: string
    user: {
      name: string
      avatar_url: string | null
    }
    created_at: string
  }>
}

// Props
const props = defineProps<Props>()

// Reactive state
const isLoading = ref(false)
const isDeleting = ref(false)
const activeTab = ref("overview")
const isDark = useDark()

// Computed properties
const projectInitials = computed(() => {
  return props.project.name
    .split(" ")
    .map(word => word.charAt(0))
    .join("")
    .toUpperCase()
    .slice(0, 2)
})

const statusColor = computed(() => {
  const colors = {
    "in_progress": "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400",
    "approved": "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
    "completed": "bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400",
    "cancelled": "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400",
    "done": "bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400"
  }
  return colors[props.project.status] || colors["in_progress"]
})

const priorityColor = computed(() => {
  const colors = {
    "low": "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
    "medium": "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400",
    "high": "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
  }
  return colors[props.project.priority] || colors["medium"]
})

const isOverdue = computed(() => {
  if (!props.project.due_date) return false
  return new Date(props.project.due_date) < new Date() &&
    !["completed", "cancelled", "done"].includes(props.project.status)
})

const budgetProgress = computed(() => {
  if (!props.project.budget) return 0
  return Math.min((props.project.stats.budget_used / props.project.budget) * 100, 100)
})

const upcomingTasks = computed(() => {
  return props.tasks
    .filter(task => task.status !== "completed" && task.due_date)
    .sort((a, b) => new Date(a.due_date!).getTime() - new Date(b.due_date!).getTime())
    .slice(0, 5)
})

const overdueTasks = computed(() => {
  return props.tasks.filter(task => {
    if (!task.due_date || task.status === "completed") return false
    return new Date(task.due_date) < new Date()
  })
})

// Methods
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric"
  })
}

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  })
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD"
  }).format(amount)
}

const getTaskStatusColor = (status: string) => {
  const colors = {
    "todo": "bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400",
    "in_progress": "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400",
    "completed": "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
    "cancelled": "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
  }
  return colors[status] || colors["todo"]
}

const getTaskPriorityColor = (priority: string) => {
  const colors = {
    "low": "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
    "medium": "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400",
    "high": "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
  }
  return colors[priority] || colors["medium"]
}

const getActivityIcon = (type: string) => {
  const icons = {
    "task_created": Plus,
    "task_completed": CheckCircle,
    "task_updated": Edit,
    "time_logged": Clock,
    "comment_added": MessageSquare,
    "status_changed": Settings,
    "file_uploaded": FileText
  }
  return icons[type] || Activity
}

// Actions
const deleteProject = async () => {
  isDeleting.value = true
  try {
    await router.delete(route("projects.destroy", props.project.uuid))
    toast.success("Project deleted successfully")
    router.visit(route("projects.index"))
  } catch (error) {
    console.error("Delete error:", error)
    toast.error("Failed to delete project")
  } finally {
    isDeleting.value = false
  }
}

const archiveProject = async () => {
  try {
    await router.post(route("projects.archive", props.project.uuid))
    toast.success("Project archived successfully")
    router.reload()
  } catch (error) {
    console.error("Archive error:", error)
    toast.error("Failed to archive project")
  }
}

const changeProjectStatus = async (status: string) => {
  try {
    await router.patch(route("projects.update-status", props.project.uuid), { status })
    toast.success("Project status updated successfully")
    router.reload()
  } catch (error) {
    console.error("Status update error:", error)
    toast.error("Failed to update project status")
  }
}

const refreshData = async () => {
  isLoading.value = true
  try {
    await router.reload({ only: ["project", "tasks", "teamMembers", "recentTimeEntries", "recentActivity"] })
    toast.success("Data refreshed successfully")
  } catch (error) {
    console.error("Refresh error:", error)
    toast.error("Failed to refresh data")
  } finally {
    isLoading.value = false
  }
}

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  console.log("Project show page mounted for:", props.project.name)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Button variant="ghost" size="sm" as="a" :href="route('projects.index')" class="gap-2">
            <ArrowLeft class="h-4 w-4" />
            Back to Projects
          </Button>
          <Separator orientation="vertical" class="h-6" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              {{ project.name }}
            </h1>
            <p v-if="project.description" class="text-muted-foreground mt-1">
              {{ project.description }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button
            variant="outline"
            size="sm"
            @click="refreshData"
            :disabled="isLoading"
            class="gap-2">
            <RefreshCw :class="{ 'animate-spin': isLoading }" class="h-4 w-4" />
            Refresh
          </Button>

          <Button variant="outline" size="sm" class="gap-2">
            <Share2 class="h-4 w-4" />
            Share
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="sm" class="gap-2">
                <MoreHorizontal class="h-4 w-4" />
                Actions
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuLabel>Project Actions</DropdownMenuLabel>
              <DropdownMenuItem asChild>
                <a :href="route('projects.edit', project.uuid)" class="flex items-center gap-2">
                  <Edit class="h-4 w-4" />
                  Edit Project
                </a>
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="changeProjectStatus('in_progress')" class="flex items-center gap-2">
                <Play class="h-4 w-4" />
                Mark In Progress
              </DropdownMenuItem>
              <DropdownMenuItem @click="changeProjectStatus('completed')" class="flex items-center gap-2">
                <CheckCircle class="h-4 w-4" />
                Mark Completed
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="archiveProject" class="flex items-center gap-2">
                <Archive class="h-4 w-4" />
                Archive Project
              </DropdownMenuItem>
              <AlertDialog>
                <AlertDialogTrigger asChild>
                  <DropdownMenuItem class="text-red-600 dark:text-red-400 focus:text-red-600 dark:focus:text-red-400">
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete Project
                  </DropdownMenuItem>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Delete Project</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to delete "{{ project.name }}"? This action cannot be undone
                      and will also delete all associated tasks, time entries, and files.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="deleteProject" :disabled="isDeleting"
                                       class="bg-red-600 hover:bg-red-700">
                      {{ isDeleting ? "Deleting..." : "Delete Project" }}
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Project Header Card -->
      <Card>
        <CardContent class="p-6">
          <div class="flex flex-col lg:flex-row gap-6">
            <!-- Project Info -->
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div
                  class="h-16 w-16 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                  <span class="text-white font-bold text-lg">{{ projectInitials }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex items-center gap-3">
                  <Badge :class="statusColor">{{ project.status.replace("_", " ") }}</Badge>
                  <Badge :class="priorityColor">{{ project.priority }} priority</Badge>
                  <Badge v-if="isOverdue" variant="destructive" class="gap-1">
                    <AlertTriangle class="h-3 w-3" />
                    Overdue
                  </Badge>
                </div>

                <div class="space-y-1">
                  <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1">
                      <User class="h-4 w-4" />
                      <span>{{ project.contact.full_name }}</span>
                    </div>
                    <div v-if="project.contact.firm" class="flex items-center gap-1">
                      <Building class="h-4 w-4" />
                      <span>{{ project.contact.firm.name }}</span>
                    </div>
                  </div>
                  <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div v-if="project.due_date" class="flex items-center gap-1">
                      <Calendar class="h-4 w-4" />
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
                <Progress :value="project.progress.percentage" class="h-2" />
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
                  <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ project.stats.completed_tasks
                    }}
                  </div>
                  <div class="text-xs text-muted-foreground">Completed</div>
                </div>
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ project.stats.in_progress_tasks
                    }}
                  </div>
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
                <p v-if="project.contact.job_title" class="text-sm text-muted-foreground">{{ project.contact.job_title
                  }}</p>
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
              <Button variant="outline" size="sm" as="a" :href="route('contacts.show', project.contact.uuid)"
                      class="gap-2">
                <ExternalLink class="h-4 w-4" />
                View Contact
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Content Tabs -->
      <Tabs v-model="activeTab" class="space-y-6">
        <TabsList class="grid w-full grid-cols-5">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="tasks">Tasks ({{ project.stats.total_tasks }})</TabsTrigger>
          <TabsTrigger value="team">Team ({{ project.stats.team_members }})</TabsTrigger>
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
                        <span v-if="task.due_date"
                              class="text-xs text-muted-foreground">Due {{ formatDate(task.due_date) }}</span>
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
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(project.budget)
                      }}
                    </div>
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
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ project.stats.total_hours }}
                    </div>
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
                <CardDescription>Manage and track project tasks</CardDescription>
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
              <div v-else class="space-y-4">
                <!-- Overdue Tasks Alert -->
                <div v-if="overdueTasks.length > 0"
                     class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                  <div class="flex items-center gap-2">
                    <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    <h3 class="font-medium text-red-800 dark:text-red-200">Overdue Tasks</h3>
                  </div>
                  <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                    {{ overdueTasks.length }} task{{ overdueTasks.length !== 1 ? "s are" : " is" }} overdue and
                    need{{ overdueTasks.length === 1 ? "s" : "" }} attention.
                  </p>
                </div>

                <!-- Tasks List -->
                <div class="space-y-3">
                  <div v-for="task in tasks" :key="task.uuid"
                       class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                      <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                          <a :href="route('tasks.show', task.uuid)"
                             class="hover:text-blue-600 dark:hover:text-blue-400">
                            {{ task.title }}
                          </a>
                        </h4>
                        <p v-if="task.description" class="text-sm text-muted-foreground mt-1 line-clamp-2">
                          {{ task.description }}
                        </p>
                        <div class="flex items-center gap-2 mt-2">
                          <Badge :class="getTaskStatusColor(task.status)">{{ task.status }}</Badge>
                          <Badge :class="getTaskPriorityColor(task.priority)">{{ task.priority }}</Badge>
                          <span v-if="task.due_date"
                                class="text-xs text-muted-foreground">Due {{ formatDate(task.due_date) }}</span>
                          <span v-if="task.estimated_hours"
                                class="text-xs text-muted-foreground">{{ task.estimated_hours }}h estimated</span>
                        </div>
                      </div>
                      <div class="flex items-center gap-2">
                        <Avatar v-if="task.assignee" class="h-8 w-8">
                          <AvatarImage v-if="task.assignee.avatar_url" :src="task.assignee.avatar_url" />
                          <AvatarFallback class="text-xs">{{ task.assignee.name.charAt(0) }}</AvatarFallback>
                        </Avatar>
                        <Button variant="ghost" size="sm" as="a" :href="route('tasks.show', task.uuid)">
                          <ExternalLink class="h-4 w-4" />
                        </Button>
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
                <CardDescription>People working on this project</CardDescription>
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
                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ member.hours_logged }}</div>
                        <div class="text-xs text-muted-foreground">Hours</div>
                      </div>
                      <div>
                        <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ member.tasks_completed }}
                        </div>
                        <div class="text-xs text-muted-foreground">Completed</div>
                      </div>
                      <div>
                        <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ member.tasks_assigned
                          }}
                        </div>
                        <div class="text-xs text-muted-foreground">Assigned</div>
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
            <CardHeader class="flex flex-row items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <Clock class="h-5 w-5" />
                  Time Tracking
                </CardTitle>
                <CardDescription>Track time spent on project tasks</CardDescription>
              </div>
              <Button variant="outline" size="sm" class="gap-2">
                <Plus class="h-4 w-4" />
                Log Time
              </Button>
            </CardHeader>
            <CardContent>
              <div v-if="recentTimeEntries.length === 0" class="text-center py-12 text-muted-foreground">
                <Clock class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No time entries</h3>
                <p class="mb-4">Start tracking time to monitor project progress.</p>
                <Button variant="outline">
                  <Plus class="h-4 w-4 mr-1" />
                  Log First Entry
                </Button>
              </div>
              <div v-else class="space-y-4">
                <div v-for="entry in recentTimeEntries" :key="entry.uuid"
                     class="flex items-center justify-between p-4 border rounded-lg">
                  <div class="flex items-center gap-4">
                    <Avatar>
                      <AvatarImage v-if="entry.user.avatar_url" :src="entry.user.avatar_url" />
                      <AvatarFallback>{{ entry.user.name.charAt(0) }}</AvatarFallback>
                    </Avatar>
                    <div>
                      <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ entry.user.name }}</h4>
                      <p class="text-sm text-muted-foreground">{{ entry.description }}</p>
                      <p v-if="entry.task" class="text-sm text-blue-600 dark:text-blue-400">{{ entry.task.title }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ entry.hours }}h</div>
                    <div class="text-sm text-muted-foreground">{{ formatDate(entry.date) }}</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Files Tab -->
        <TabsContent value="files">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Project Files
              </CardTitle>
              <CardDescription>Documents and files related to this project</CardDescription>
            </CardHeader>

            <Button variant="outline" size="sm" class="gap-2">
              <Plus class="h-4 w-4" />
              Upload File
            </Button>
            
            <CardContent>
              <div class="text-center py-12 text-muted-foreground">
                <FileText class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No files uploaded</h3>
                <p class="mb-4">Upload documents, images, and other files related to this project.</p>
                <Button variant="outline">
                  <Plus class="h-4 w-4 mr-1" />
                  Upload First File
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>
