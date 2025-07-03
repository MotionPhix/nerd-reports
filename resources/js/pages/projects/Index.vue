<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { useDark, useStorage } from '@vueuse/core';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from '@/components/ui/dropdown-menu'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Separator } from '@/components/ui/separator'
import {
  Plus,
  Search,
  Filter,
  MoreHorizontal,
  Eye,
  Edit,
  Archive,
  Trash2,
  Calendar,
  User,
  Building,
  Clock,
  AlertTriangle,
  TrendingUp,
  Briefcase,
  CheckCircle,
  XCircle,
  RefreshCw,
  Download,
  Settings,
  BarChart3
} from 'lucide-vue-next'
import AppSidebarLayout from '@/layouts/AppLayout.vue'
import { debounce } from 'lodash-es'

// Types
interface Contact {
  uuid: string
  first_name: string
  last_name: string
  full_name: string
  firm?: {
    uuid: string
    name: string
  }
}

interface Firm {
  uuid: string
  name: string
}

interface Project {
  uuid: string
  name: string
  description: string | null
  status: string
  due_date: string | null
  deadline: string | null
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
  }
}

interface Props {
  projects: Project[]
  overdueProjects: Project[]
  recentlyActive: Project[]
  filters: {
    status?: string
    contact_id?: string
    firm_id?: string
    due_date_from?: string
    due_date_to?: string
    overdue?: boolean
    search?: string
    order_by?: string
    order_direction?: string
  }
  contacts: Contact[]
  firms: Firm[]
}

// Props
const props = defineProps<Props>()

// Reactive state
const isLoading = ref(false)
const activeTab = useStorage('project_index_tabs', 'all')
const searchQuery = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')
const selectedContact = ref(props.filters.contact_id || '')
const selectedFirm = ref(props.filters.firm_id || '')
const dueDateFrom = ref(props.filters.due_date_from || '')
const dueDateTo = ref(props.filters.due_date_to || '')
const showOverdueOnly = ref(props.filters.overdue || false)
const orderBy = ref(props.filters.order_by || 'created_at')
const orderDirection = ref(props.filters.order_direction || 'desc')

// Computed properties
const filteredProjects = computed(() => {
  let filtered = [...props.projects]

  if (activeTab.value === 'overdue') {
    return props.overdueProjects
  }

  if (activeTab.value === 'recent') {
    return props.recentlyActive
  }

  if (activeTab.value === 'completed') {
    filtered = filtered.filter(project => project.status === 'completed')
  }

  if (activeTab.value === 'active') {
    filtered = filtered.filter(project =>
      ['in_progress', 'approved'].includes(project.status)
    )
  }

  return filtered
})

const projectStats = computed(() => {
  const total = props.projects.length
  const active = props.projects.filter(p => ['in_progress', 'approved'].includes(p.status)).length
  const completed = props.projects.filter(p => p.status === 'completed').length
  const overdue = props.overdueProjects.length

  return { total, active, completed, overdue }
})

const hasActiveFilters = computed(() => {
  return !!(
    searchQuery.value ||
    selectedStatus.value ||
    selectedContact.value ||
    selectedFirm.value ||
    dueDateFrom.value ||
    dueDateTo.value ||
    showOverdueOnly.value
  )
})

// Methods
const getStatusColor = (status: string) => {
  const colors = {
    'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    'approved': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'completed': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    'done': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
  }
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getStatusIcon = (status: string) => {
  const icons = {
    'in_progress': Clock,
    'approved': CheckCircle,
    'completed': CheckCircle,
    'cancelled': XCircle,
    'done': CheckCircle
  }
  return icons[status] || Clock
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const isOverdue = (project: Project) => {
  if (!project.due_date) return false
  return new Date(project.due_date) < new Date() &&
    !['completed', 'cancelled', 'done'].includes(project.status)
}

const applyFilters = () => {
  isLoading.value = true

  const filters = {
    search: searchQuery.value || undefined,
    status: selectedStatus.value || undefined,
    contact_id: selectedContact.value || undefined,
    firm_id: selectedFirm.value || undefined,
    due_date_from: dueDateFrom.value || undefined,
    due_date_to: dueDateTo.value || undefined,
    overdue: showOverdueOnly.value || undefined,
    order_by: orderBy.value,
    order_direction: orderDirection.value,
  }

  // Remove undefined values
  Object.keys(filters).forEach(key => {
    if (filters[key] === undefined) {
      delete filters[key]
    }
  })

  router.get(route('projects.index'), filters, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false
    }
  })
}

// Debounced version for search input
const debouncedApplyFilters = debounce(applyFilters, 500)

const clearFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedContact.value = ''
  selectedFirm.value = ''
  dueDateFrom.value = ''
  dueDateTo.value = ''
  showOverdueOnly.value = false
  orderBy.value = 'created_at'
  orderDirection.value = 'desc'

  applyFilters()
}

const archiveProject = async (project: Project) => {
  try {
    await router.post(route('projects.archive', project.uuid))
    toast.success('Project archived successfully!')
  } catch (error) {
    console.error('Archive error:', error)
    toast.error('Failed to archive project')
  }
}

const deleteProject = async (project: Project) => {
  try {
    await router.delete(route('projects.destroy', project.uuid))
    toast.success('Project deleted successfully!')
  } catch (error) {
    console.error('Delete error:', error)
    toast.error('Failed to delete project')
  }
}

const refreshData = () => {
  isLoading.value = true
  router.reload({
    onFinish: () => {
      isLoading.value = false
      toast.success('Data refreshed successfully!')
    }
  })
}

// Watchers for automatic filter application
watch(searchQuery, () => {
  debouncedApplyFilters()
})

watch([selectedStatus, selectedContact, selectedFirm, dueDateFrom, dueDateTo, showOverdueOnly, orderBy, orderDirection], () => {
  applyFilters()
})

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  console.log('Projects index loaded with', props.projects.length, 'projects')
})
</script>

<template>
  <div class="space-y-6 p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">
          Projects
        </h1>
        <p class="text-muted-foreground mt-1">
          Manage and track your projects
        </p>
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
          <Download class="h-4 w-4" />
          Export
        </Button>

        <Button as="a" :href="route('projects.create')" class="gap-2">
          <Plus class="h-4 w-4" />
          New Project
        </Button>
      </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
      <Card class="relative overflow-hidden border-0 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/50 dark:to-blue-900/50">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Projects</p>
              <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ projectStats.total }}</p>
              <p class="text-xs text-blue-600 dark:text-blue-400">All time</p>
            </div>
            <div class="rounded-full bg-blue-500/20 p-3">
              <Briefcase class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
          <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-blue-500/10"></div>
        </CardContent>
      </Card>

      <Card class="relative overflow-hidden border-0 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950/50 dark:to-green-900/50">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-green-700 dark:text-green-300">Active Projects</p>
              <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ projectStats.active }}</p>
              <p class="text-xs text-green-600 dark:text-green-400">In progress</p>
            </div>
            <div class="rounded-full bg-green-500/20 p-3">
              <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
          <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-green-500/10"></div>
        </CardContent>
      </Card>

      <Card class="relative overflow-hidden border-0 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-950/50 dark:to-purple-900/50">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Completed</p>
              <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ projectStats.completed }}</p>
              <p class="text-xs text-purple-600 dark:text-purple-400">Finished</p>
            </div>
            <div class="rounded-full bg-purple-500/20 p-3">
              <CheckCircle class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
          <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-purple-500/10"></div>
        </CardContent>
      </Card>

      <Card class="relative overflow-hidden border-0 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-950/50 dark:to-red-900/50">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-red-700 dark:text-red-300">Overdue</p>
              <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ projectStats.overdue }}</p>
              <p class="text-xs text-red-600 dark:text-red-400">Need attention</p>
            </div>
            <div class="rounded-full bg-red-500/20 p-3">
              <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
            </div>
          </div>
          <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-red-500/10"></div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Filter class="h-5 w-5" />
          Filters & Search
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <!-- Search -->
          <div class="space-y-2">
            <Label for="search">Search</Label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                id="search"
                v-model="searchQuery"
                placeholder="Search projects..."
                class="pl-10"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger>
                <SelectValue placeholder="All statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null">All statuses</SelectItem>
                <SelectItem value="in_progress">In Progress</SelectItem>
                <SelectItem value="approved">Approved</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
                <SelectItem value="done">Done</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Contact Filter -->
          <div class="space-y-2">
            <Label for="contact">Contact</Label>
            <Select v-model="selectedContact">
              <SelectTrigger>
                <SelectValue placeholder="All contacts" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null">All contacts</SelectItem>
                <SelectItem v-for="contact in contacts" :key="contact.uuid" :value="contact.uuid">
                  {{ contact.full_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Firm Filter -->
          <div class="space-y-2">
            <Label for="firm">Firm</Label>
            <Select v-model="selectedFirm">
              <SelectTrigger>
                <SelectValue placeholder="All firms" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null">All firms</SelectItem>
                <SelectItem v-for="firm in firms" :key="firm.uuid" :value="firm.uuid">
                  {{ firm.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-4">
            <Button
              v-if="hasActiveFilters"
              variant="outline"
              @click="clearFilters"
              class="gap-2">
              <XCircle class="h-4 w-4" />
              Clear Filters
            </Button>

            <div v-if="isLoading" class="flex items-center gap-2 text-sm text-muted-foreground">
              <RefreshCw class="h-4 w-4 animate-spin" />
              Applying filters...
            </div>
          </div>

          <div class="flex items-center gap-2">
            <Label for="sort" class="text-sm">Sort by:</Label>
            <Select v-model="orderBy">
              <SelectTrigger class="w-40">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="created_at">Created Date</SelectItem>
                <SelectItem value="updated_at">Updated Date</SelectItem>
                <SelectItem value="due_date">Due Date</SelectItem>
                <SelectItem value="name">Name</SelectItem>
                <SelectItem value="status">Status</SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="orderDirection">
              <SelectTrigger class="w-32">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="desc">Descending</SelectItem>
                <SelectItem value="asc">Ascending</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Projects Tabs -->
    <Tabs v-model="activeTab" class="space-y-6">
      <TabsList class="grid w-full grid-cols-5">
        <TabsTrigger value="all">All Projects ({{ projectStats.total }})</TabsTrigger>
        <TabsTrigger value="active">Active ({{ projectStats.active }})</TabsTrigger>
        <TabsTrigger value="overdue">Overdue ({{ projectStats.overdue }})</TabsTrigger>
        <TabsTrigger value="completed">Completed ({{ projectStats.completed }})</TabsTrigger>
        <TabsTrigger value="recent">Recent Activity</TabsTrigger>
      </TabsList>

      <!-- All Projects -->
      <TabsContent value="all" class="space-y-4">
        <div v-if="filteredProjects.length === 0" class="text-center py-12">
          <Briefcase class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
          <h3 class="text-lg font-semibold mb-2">No projects found</h3>
          <p class="text-muted-foreground mb-4">
            {{ hasActiveFilters ? 'Try adjusting your filters or search terms.' : 'Get started by creating your first project.' }}
          </p>
          <Button as="a" :href="route('projects.create')" class="gap-2">
            <Plus class="h-4 w-4" />
            Create Project
          </Button>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <Card v-for="project in filteredProjects" :key="project.uuid" class="group hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
            <CardHeader class="pb-3">
              <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0 space-y-1">
                  <CardTitle class="text-lg leading-tight">
                    <a
                      :href="route('projects.show', project.uuid)"
                      class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2 block"
                      :title="project.name">
                      {{ project.name }}
                    </a>
                  </CardTitle>

                  <CardDescription class="text-sm truncate">
                    {{ project.contact.full_name }}
                    <span v-if="project.contact.firm" class="text-xs opacity-75">
                      • {{ project.contact.firm.name }}
                    </span>
                  </CardDescription>
                </div>

                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuItem as="a" :href="route('projects.show', project.uuid)" class="gap-2">
                      <Eye class="h-4 w-4" />
                      View Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.edit', project.uuid)" class="gap-2">
                      <Edit class="h-4 w-4" />
                      Edit Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.stats', project.uuid)" class="gap-2">
                      <BarChart3 class="h-4 w-4" />
                      View Stats
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="archiveProject(project)" class="gap-2">
                      <Archive class="h-4 w-4" />
                      Archive
                    </DropdownMenuItem>
                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <DropdownMenuItem class="text-red-600 dark:text-red-400 gap-2">
                          <Trash2 class="h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Delete Project</AlertDialogTitle>
                          <AlertDialogDescription>
                            Are you sure you want to delete "{{ project.name }}"? This action cannot be undone
                            and will remove all associated tasks and data.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction @click="deleteProject(project)" class="bg-red-600 hover:bg-red-700">
                            Delete Project
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>

              <div class="flex items-center gap-2 mt-3">
                <Badge :class="getStatusColor(project.status)" class="gap-1 text-xs">
                  <component :is="getStatusIcon(project.status)" class="h-3 w-3" />
                  {{ project.status.replace('_', ' ') }}
                </Badge>

                <Badge v-if="isOverdue(project)" variant="destructive" class="gap-1 text-xs">
                  <AlertTriangle class="h-3 w-3" />
                  Overdue
                </Badge>
              </div>
            </CardHeader>

            <CardContent class="space-y-4">
              <!-- Progress -->
              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">Progress</span>
                  <span class="font-medium">{{ project.progress.percentage }}%</span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${project.progress.percentage}%` }">
                  </div>
                </div>

                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ project.progress.completed_tasks }}/{{ project.progress.total_tasks }} tasks</span>
                  <span>{{ project.stats.team_members }} members</span>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.total_hours }}</div>
                  <div class="text-xs text-muted-foreground">Hours</div>
                </div>

                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.overdue_tasks }}</div>
                  <div class="text-xs text-muted-foreground">Overdue</div>
                </div>

                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.completion_rate }}%</div>
                  <div class="text-xs text-muted-foreground">Complete</div>
                </div>
              </div>

              <!-- Dates -->
              <div class="flex items-center justify-between text-xs text-muted-foreground pt-2 border-t">
                <span>Created {{ formatDate(project.created_at) }}</span>
                <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </TabsContent>

      <!-- Active Projects -->
      <TabsContent value="active">
        <div v-if="filteredProjects.length === 0" class="text-center py-12">
          <TrendingUp class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
          <h3 class="text-lg font-semibold mb-2">No active projects</h3>
          <p class="text-muted-foreground mb-4">All your projects are either completed or not yet started.</p>
        </div>
        <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <Card v-for="project in filteredProjects" :key="project.uuid" class="group hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
            <CardHeader class="pb-3">
              <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0 space-y-1">
                  <CardTitle class="text-lg leading-tight">
                    <a
                      :href="route('projects.show', project.uuid)"
                      class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2 block"
                      :title="project.name"
                    >
                      {{ project.name }}
                    </a>
                  </CardTitle>
                  <CardDescription class="text-sm truncate">
                    {{ project.contact.full_name }}
                    <span v-if="project.contact.firm" class="text-xs opacity-75">
                      • {{ project.contact.firm.name }}
                    </span>
                  </CardDescription>
                </div>

                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuItem as="a" :href="route('projects.show', project.uuid)" class="gap-2">
                      <Eye class="h-4 w-4" />
                      View Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.edit', project.uuid)" class="gap-2">
                      <Edit class="h-4 w-4" />
                      Edit Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.stats', project.uuid)" class="gap-2">
                      <BarChart3 class="h-4 w-4" />
                      View Stats
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="archiveProject(project)" class="gap-2">
                      <Archive class="h-4 w-4" />
                      Archive
                    </DropdownMenuItem>
                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <DropdownMenuItem class="text-red-600 dark:text-red-400 gap-2">
                          <Trash2 class="h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Delete Project</AlertDialogTitle>
                          <AlertDialogDescription>
                            Are you sure you want to delete "{{ project.name }}"? This action cannot be undone
                            and will remove all associated tasks and data.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction @click="deleteProject(project)" class="bg-red-600 hover:bg-red-700">
                            Delete Project
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>

              <div class="flex items-center gap-2 mt-3">
                <Badge :class="getStatusColor(project.status)" class="gap-1 text-xs">
                  <component :is="getStatusIcon(project.status)" class="h-3 w-3" />
                  {{ project.status.replace('_', ' ') }}
                </Badge>

                <Badge v-if="isOverdue(project)" variant="destructive" class="gap-1 text-xs">
                  <AlertTriangle class="h-3 w-3" />
                  Overdue
                </Badge>
              </div>
            </CardHeader>

            <CardContent class="space-y-4">
              <!-- Progress -->
              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">Progress</span>
                  <span class="font-medium">{{ project.progress.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${project.progress.percentage}%` }"
                  ></div>
                </div>
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ project.progress.completed_tasks }}/{{ project.progress.total_tasks }} tasks</span>
                  <span>{{ project.stats.team_members }} members</span>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.total_hours }}</div>
                  <div class="text-xs text-muted-foreground">Hours</div>
                </div>
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.overdue_tasks }}</div>
                  <div class="text-xs text-muted-foreground">Overdue</div>
                </div>
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.completion_rate }}%</div>
                  <div class="text-xs text-muted-foreground">Complete</div>
                </div>
              </div>

              <!-- Dates -->
              <div class="flex items-center justify-between text-xs text-muted-foreground pt-2 border-t">
                <span>Created {{ formatDate(project.created_at) }}</span>
                <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </TabsContent>

      <!-- Overdue Projects -->
      <TabsContent value="overdue">
        <div v-if="filteredProjects.length === 0" class="text-center py-12">
          <CheckCircle class="h-16 w-16 mx-auto mb-4 text-green-500 opacity-50" />
          <h3 class="text-lg font-semibold mb-2">No overdue projects</h3>
          <p class="text-muted-foreground mb-4">Great job! All your projects are on track.</p>
        </div>
        <div v-else class="space-y-4">
          <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center gap-2">
              <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
              <h3 class="font-medium text-red-800 dark:text-red-200">Attention Required</h3>
            </div>
            <p class="text-sm text-red-700 dark:text-red-300 mt-1">
              You have {{ filteredProjects.length }} overdue project{{ filteredProjects.length !== 1 ? 's' : '' }} that need immediate attention.
            </p>
          </div>

          <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="project in filteredProjects" :key="project.uuid" class="group hover:shadow-lg transition-all duration-200 hover:-translate-y-1 border-red-200 dark:border-red-800">
              <!-- Same card content structure as above -->
              <CardHeader class="pb-3">
                <div class="flex items-start gap-3">
                  <div class="flex-1 min-w-0 space-y-1">
                    <CardTitle class="text-lg leading-tight">
                      <a
                        :href="route('projects.show', project.uuid)"
                        class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2 block"
                        :title="project.name"
                      >
                        {{ project.name }}
                      </a>
                    </CardTitle>
                    <CardDescription class="text-sm truncate">
                      {{ project.contact.full_name }}
                      <span v-if="project.contact.firm" class="text-xs opacity-75">
                        • {{ project.contact.firm.name }}
                      </span>
                    </CardDescription>
                  </div>

                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="ghost" size="sm" class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuLabel>Actions</DropdownMenuLabel>
                      <DropdownMenuItem as="a" :href="route('projects.show', project.uuid)" class="gap-2">
                        <Eye class="h-4 w-4" />
                        View Project
                      </DropdownMenuItem>
                      <DropdownMenuItem as="a" :href="route('projects.edit', project.uuid)" class="gap-2">
                        <Edit class="h-4 w-4" />
                        Edit Project
                      </DropdownMenuItem>
                      <DropdownMenuItem as="a" :href="route('projects.stats', project.uuid)" class="gap-2">
                        <BarChart3 class="h-4 w-4" />
                        View Stats
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem @click="archiveProject(project)" class="gap-2">
                        <Archive class="h-4 w-4" />
                        Archive
                      </DropdownMenuItem>
                      <AlertDialog>
                        <AlertDialogTrigger asChild>
                          <DropdownMenuItem class="text-red-600 dark:text-red-400 gap-2">
                            <Trash2 class="h-4 w-4" />
                            Delete
                          </DropdownMenuItem>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Delete Project</AlertDialogTitle>
                            <AlertDialogDescription>
                              Are you sure you want to delete "{{ project.name }}"? This action cannot be undone
                              and will remove all associated tasks and data.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click="deleteProject(project)" class="bg-red-600 hover:bg-red-700">
                              Delete Project
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>

                <div class="flex items-center gap-2 mt-3">
                  <Badge :class="getStatusColor(project.status)" class="gap-1 text-xs">
                    <component :is="getStatusIcon(project.status)" class="h-3 w-3" />
                    {{ project.status.replace('_', ' ') }}
                  </Badge>

                  <Badge v-if="isOverdue(project)" variant="destructive" class="gap-1 text-xs">
                    <AlertTriangle class="h-3 w-3" />
                    Overdue
                  </Badge>
                </div>
              </CardHeader>

              <CardContent class="space-y-4">
                <!-- Progress -->
                <div class="space-y-2">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="font-medium">{{ project.progress.percentage }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div
                      class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${project.progress.percentage}%` }"
                    ></div>
                  </div>
                  <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <span>{{ project.progress.completed_tasks }}/{{ project.progress.total_tasks }} tasks</span>
                    <span>{{ project.stats.team_members }} members</span>
                  </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-3 text-center">
                  <div class="p-2 bg-muted/50 rounded-lg">
                    <div class="text-sm font-medium">{{ project.stats.total_hours }}</div>
                    <div class="text-xs text-muted-foreground">Hours</div>
                  </div>
                  <div class="p-2 bg-muted/50 rounded-lg">
                    <div class="text-sm font-medium">{{ project.stats.overdue_tasks }}</div>
                    <div class="text-xs text-muted-foreground">Overdue</div>
                  </div>
                  <div class="p-2 bg-muted/50 rounded-lg">
                    <div class="text-sm font-medium">{{ project.stats.completion_rate }}%</div>
                    <div class="text-xs text-muted-foreground">Complete</div>
                  </div>
                </div>

                <!-- Dates -->
                <div class="flex items-center justify-between text-xs text-muted-foreground pt-2 border-t">
                  <span>Created {{ formatDate(project.created_at) }}</span>
                  <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </TabsContent>

      <!-- Completed Projects -->
      <TabsContent value="completed">
        <div v-if="filteredProjects.length === 0" class="text-center py-12">
          <CheckCircle class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
          <h3 class="text-lg font-semibold mb-2">No completed projects</h3>
          <p class="text-muted-foreground mb-4">Completed projects will appear here.</p>
        </div>
        <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <Card v-for="project in filteredProjects" :key="project.uuid" class="group hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
            <!-- Same card content structure as above -->
            <CardHeader class="pb-3">
              <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0 space-y-1">
                  <CardTitle class="text-lg leading-tight">
                    <a
                      :href="route('projects.show', project.uuid)"
                      class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2 block"
                      :title="project.name"
                    >
                      {{ project.name }}
                    </a>
                  </CardTitle>
                  <CardDescription class="text-sm truncate">
                    {{ project.contact.full_name }}
                    <span v-if="project.contact.firm" class="text-xs opacity-75">
                      • {{ project.contact.firm.name }}
                    </span>
                  </CardDescription>
                </div>

                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuItem as="a" :href="route('projects.show', project.uuid)" class="gap-2">
                      <Eye class="h-4 w-4" />
                      View Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.edit', project.uuid)" class="gap-2">
                      <Edit class="h-4 w-4" />
                      Edit Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.stats', project.uuid)" class="gap-2">
                      <BarChart3 class="h-4 w-4" />
                      View Stats
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="archiveProject(project)" class="gap-2">
                      <Archive class="h-4 w-4" />
                      Archive
                    </DropdownMenuItem>
                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <DropdownMenuItem class="text-red-600 dark:text-red-400 gap-2">
                          <Trash2 class="h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Delete Project</AlertDialogTitle>
                          <AlertDialogDescription>
                            Are you sure you want to delete "{{ project.name }}"? This action cannot be undone
                            and will remove all associated tasks and data.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction @click="deleteProject(project)" class="bg-red-600 hover:bg-red-700">
                            Delete Project
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>

              <div class="flex items-center gap-2 mt-3">
                <Badge :class="getStatusColor(project.status)" class="gap-1 text-xs">
                  <component :is="getStatusIcon(project.status)" class="h-3 w-3" />
                  {{ project.status.replace('_', ' ') }}
                </Badge>

                <Badge v-if="isOverdue(project)" variant="destructive" class="gap-1 text-xs">
                  <AlertTriangle class="h-3 w-3" />
                  Overdue
                </Badge>
              </div>
            </CardHeader>

            <CardContent class="space-y-4">
              <!-- Progress -->
              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">Progress</span>
                  <span class="font-medium">{{ project.progress.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${project.progress.percentage}%` }"
                  ></div>
                </div>
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ project.progress.completed_tasks }}/{{ project.progress.total_tasks }} tasks</span>
                  <span>{{ project.stats.team_members }} members</span>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.total_hours }}</div>
                  <div class="text-xs text-muted-foreground">Hours</div>
                </div>
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.overdue_tasks }}</div>
                  <div class="text-xs text-muted-foreground">Overdue</div>
                </div>
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.completion_rate }}%</div>
                  <div class="text-xs text-muted-foreground">Complete</div>
                </div>
              </div>

              <!-- Dates -->
              <div class="flex items-center justify-between text-xs text-muted-foreground pt-2 border-t">
                <span>Created {{ formatDate(project.created_at) }}</span>
                <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </TabsContent>

      <!-- Recent Activity -->
      <TabsContent value="recent">
        <div v-if="filteredProjects.length === 0" class="text-center py-12">
          <Clock class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
          <h3 class="text-lg font-semibold mb-2">No recent activity</h3>
          <p class="text-muted-foreground mb-4">Projects with recent activity will appear here.</p>
        </div>
        <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <Card v-for="project in filteredProjects" :key="project.uuid" class="group hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
            <!-- Same card content structure as above -->
            <CardHeader class="pb-3">
              <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0 space-y-1">
                  <CardTitle class="text-lg leading-tight">
                    <a
                      :href="route('projects.show', project.uuid)"
                      class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2 block"
                      :title="project.name"
                    >
                      {{ project.name }}
                    </a>
                  </CardTitle>
                  <CardDescription class="text-sm truncate">
                    {{ project.contact.full_name }}
                    <span v-if="project.contact.firm" class="text-xs opacity-75">
                      • {{ project.contact.firm.name }}
                    </span>
                  </CardDescription>
                </div>

                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuItem as="a" :href="route('projects.show', project.uuid)" class="gap-2">
                      <Eye class="h-4 w-4" />
                      View Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.edit', project.uuid)" class="gap-2">
                      <Edit class="h-4 w-4" />
                      Edit Project
                    </DropdownMenuItem>
                    <DropdownMenuItem as="a" :href="route('projects.stats', project.uuid)" class="gap-2">
                      <BarChart3 class="h-4 w-4" />
                      View Stats
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="archiveProject(project)" class="gap-2">
                      <Archive class="h-4 w-4" />
                      Archive
                    </DropdownMenuItem>
                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <DropdownMenuItem class="text-red-600 dark:text-red-400 gap-2">
                          <Trash2 class="h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Delete Project</AlertDialogTitle>
                          <AlertDialogDescription>
                            Are you sure you want to delete "{{ project.name }}"? This action cannot be undone
                            and will remove all associated tasks and data.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction @click="deleteProject(project)" class="bg-red-600 hover:bg-red-700">
                            Delete Project
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>

              <div class="flex items-center gap-2 mt-3">
                <Badge :class="getStatusColor(project.status)" class="gap-1 text-xs">
                  <component :is="getStatusIcon(project.status)" class="h-3 w-3" />
                  {{ project.status.replace('_', ' ') }}
                </Badge>

                <Badge v-if="isOverdue(project)" variant="destructive" class="gap-1 text-xs">
                  <AlertTriangle class="h-3 w-3" />
                  Overdue
                </Badge>
              </div>
            </CardHeader>

            <CardContent class="space-y-4">
              <!-- Progress -->
              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">Progress</span>
                  <span class="font-medium">{{ project.progress.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${project.progress.percentage}%` }"
                  ></div>
                </div>
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ project.progress.completed_tasks }}/{{ project.progress.total_tasks }} tasks</span>
                  <span>{{ project.stats.team_members }} members</span>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.total_hours }}</div>
                  <div class="text-xs text-muted-foreground">Hours</div>
                </div>
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.overdue_tasks }}</div>
                  <div class="text-xs text-muted-foreground">Overdue</div>
                </div>
                <div class="p-2 bg-muted/50 rounded-lg">
                  <div class="text-sm font-medium">{{ project.stats.completion_rate }}%</div>
                  <div class="text-xs text-muted-foreground">Complete</div>
                </div>
              </div>

              <!-- Dates -->
              <div class="flex items-center justify-between text-xs text-muted-foreground pt-2 border-t">
                <span>Created {{ formatDate(project.created_at) }}</span>
                <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </TabsContent>
    </Tabs>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
