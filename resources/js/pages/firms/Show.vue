<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import { router, Link } from '@inertiajs/vue3'
import { useDark, useStorage } from "@vueuse/core"
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Separator } from '@/components/ui/separator'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
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
import {
  Edit,
  Trash2,
  MoreHorizontal,
  Users,
  Briefcase,
  Phone,
  Mail,
  MapPin,
  Globe,
  Star,
  Plus,
  ArrowLeft,
  ExternalLink,
  FileText,
  Image as ImageIcon,
  Upload,
  MessageSquare,
  Settings,
  Eye,
  Linkedin,
  Twitter,
  Facebook,
  Copy,
  Forward,
  ListRestart
} from 'lucide-vue-next'
import AppSidebarLayout from '@/layouts/AppSidebarLayout.vue'

// Types
interface Firm {
  id: number
  uuid: string
  name: string
  slogan: string | null
  url: string | null
  description: string | null
  industry: string | null
  size: string | null
  status: 'active' | 'inactive' | 'prospect'
  priority: 'low' | 'medium' | 'high'
  source: string | null
  assigned_to: number | null
  linkedin_url: string | null
  twitter_url: string | null
  facebook_url: string | null
  total_revenue: number | null
  metadata: any
  notes: string | null
  created_at: string
  updated_at: string
  logo_url: string | null
  logo_thumbnail_url: string | null
  primary_email: string | null
  primary_phone: string | null
  full_address: string | null
  status_label: string
  priority_label: string
  formatted_revenue: string
  contacts_count: number
  projects_count: number
  active_projects_count: number
  completion_rate: number
  activity_score: number
  is_high_value: boolean
  address: {
    id: number
    street: string | null
    city: string | null
    state: string | null
    country: string | null
    type: string
  } | null
  contacts: Array<{
    id: number
    uuid: string
    first_name: string
    last_name: string
    title: string | null
    primary_email: string | null
    primary_phone: string | null
    created_at: string
  }>
  projects: Array<{
    id: number
    uuid: string
    name: string
    status: string
    due_date: string | null
    deadline: string | null
    created_at: string
    contact: {
      id: number
      uuid: string
      first_name: string
      last_name: string
    }
  }>
  tags: Array<{
    id: number
    name: string
    type: string | null
  }>
}

interface Props {
  firm: Firm
  recentInteractions: Record<string, {
    id: number
    uuid: string
    contact_id: string
    project_id: string | null
    user_id: number
    type: string
    subject: string | null
    description: string
    notes: string | null
    duration_minutes: number | null
    interaction_date: string
    follow_up_required: boolean
    follow_up_date: string | null
    outcome: string | null
    location: string | null
    participants: string[] | null
    metadata: any
    created_at: string
    updated_at: string
    contact?: {
      id: number
      uuid: string
      first_name: string
      last_name: string
    }
  }>
  activeProjects: Array<{
    id: number
    uuid: string
    name: string
    status: string
    due_date: string | null
    progress: {
      percentage: number
      completed_tasks: number
      total_tasks: number
    }
    contact: {
      id: number
      uuid: string
      first_name: string
      last_name: string
    }
  }>
}

// Props
const props = defineProps<Props>()

// Reactive state
const isLoading = ref(false)
const isDeleting = ref(false)
const activeTab = useStorage('tabs_on_firm', 'overview')
const isDark = useDark()

// Computed properties
const firmInitials = computed(() => {
  return props.firm.name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const statusColor = computed(() => {
  const colors = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    prospect: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
  }
  return colors[props.firm.status] || colors.prospect
})

const priorityColor = computed(() => {
  const colors = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    high: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[props.firm.priority] || colors.medium
})

const socialMediaLinks = computed(() => {
  return [
    { name: 'LinkedIn', url: props.firm.linkedin_url, icon: Linkedin },
    { name: 'Twitter', url: props.firm.twitter_url, icon: Twitter },
    { name: 'Facebook', url: props.firm.facebook_url, icon: Facebook },
  ].filter(link => link.url)
})

// Utility functions
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
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

const getInteractionIcon = (type: string) => {
  const icons = {
    phone_call: Phone,
    email: Mail,
    meeting: Users,
    video_call: Users,
    whatsapp: MessageSquare,
    sms: MessageSquare,
    in_person: Users,
    teams: Users,
    zoom: Users,
    other: MessageSquare
  }
  return icons[type] || MessageSquare
}

const getStatusColor = (status: string) => {
  const colors = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[status.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const copyToClipboard = async (text: string, label: string) => {
  try {
    await navigator.clipboard.writeText(text)
    toast.success(`${label} copied to clipboard`)
  } catch (error) {
    toast.error('Failed to copy to clipboard')
  }
}

// Helper function to get contact name from interaction
const getContactName = (interaction: any) => {
  if (interaction.contact) {
    return `${interaction.contact.first_name} ${interaction.contact.last_name}`
  }

  // Fallback: try to find contact in firm.contacts by contact_id
  const contact = props.firm.contacts.find(c => c.uuid === interaction.contact_id)
  if (contact) {
    return `${contact.first_name} ${contact.last_name}`
  }

  // Final fallback
  return 'Unknown Contact'
}

// Actions
const deleteFirm = async () => {
  isDeleting.value = true
  try {
    await router.delete(route('firms.destroy', props.firm.uuid))
    toast.success('Firm deleted successfully')
    router.visit(route('firms.index'))
  } catch (error) {
    console.error('Delete error:', error)
    toast.error('Failed to delete firm')
  } finally {
    isDeleting.value = false
  }
}

const refreshData = async () => {
  isLoading.value = true
  try {
    await router.reload({ only: ['firm', 'recentInteractions', 'activeProjects'] })
    toast.success('Data refreshed successfully')
  } catch (error) {
    console.error('Refresh error:', error)
    toast.error('Failed to refresh data')
  } finally {
    isLoading.value = false
  }
}

// Fixed interactions computed properties
const interactionsArray = computed(() => {
  return Object.values(props.recentInteractions)
})

const partialInteractions = computed(() => interactionsArray.value.slice(0, 5))

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  console.log('Firm show page mounted for:', props.firm.name)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div>
            <div>
              <Button variant="link" size="sm" as="a" :href="route('firms.index')" class="gap-2">
                <ArrowLeft class="h-4 w-4" />
                Back to Firms
              </Button>
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              {{ firm.name }}
            </h1>

            <p v-if="firm.slogan" class="text-muted-foreground mt-1">
              {{ firm.slogan }}
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
            <ListRestart :class="{ 'animate-spin': isLoading }" class="h-4 w-4" />
            Refresh
          </Button>

          <Button variant="outline" size="sm" class="gap-2">
            <Forward class="h-4 w-4" />
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
              <DropdownMenuLabel>Actions</DropdownMenuLabel>
              <DropdownMenuItem asChild>
                <Link :href="route('firms.edit', firm.uuid)" class="flex items-center gap-2">
                  <Edit class="h-4 w-4" />
                  Edit Firm
                </Link>
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem asChild>
                <Link :href="route('contacts.create', { firm: firm.uuid })" class="flex items-center gap-2">
                  <Plus class="h-4 w-4" />
                  Add Contact
                </Link>
              </DropdownMenuItem>
              <DropdownMenuItem asChild>
                <Link :href="route('projects.create', { firm: firm.uuid })" class="flex items-center gap-2">
                  <Plus class="h-4 w-4" />
                  Add Project
                </Link>
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <AlertDialog>
                <AlertDialogTrigger asChild>
                  <DropdownMenuItem class="text-red-600 dark:text-red-400 focus:text-red-600 dark:focus:text-red-400">
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete Firm
                  </DropdownMenuItem>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Delete Firm</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to delete "{{ firm.name }}"? This action cannot be undone
                      and will also delete all associated contacts ({{ firm.contacts_count }}) and projects ({{ firm.projects_count }}).
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="deleteFirm" :disabled="isDeleting" class="bg-red-600 hover:bg-red-700">
                      {{ isDeleting ? 'Deleting...' : 'Delete Firm' }}
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Firm Header Card -->
      <Card>
        <CardContent class="p-6">
          <div class="flex flex-col lg:flex-row gap-6">
            <!-- Logo and Basic Info -->
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div v-if="firm.logo_url" class="h-20 w-20 rounded-xl overflow-hidden">
                  <img :src="firm.logo_url" :alt="firm.name" class="h-full w-full object-cover" />
                </div>
                <div v-else class="h-20 w-20 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                  <span class="text-white font-bold text-xl">{{ firmInitials }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex items-center gap-3">
                  <Badge :class="statusColor">{{ firm.status_label }}</Badge>
                  <Badge :class="priorityColor">{{ firm.priority_label }}</Badge>
                  <Badge v-if="firm.is_high_value" variant="secondary" class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                    <Star class="h-3 w-3 mr-1" />
                    High Value
                  </Badge>
                </div>

                <div class="space-y-1">
                  <div v-if="firm.industry" class="text-sm text-muted-foreground">
                    {{ firm.industry }} • {{ firm.size }}
                  </div>
                  <div v-if="firm.description" class="text-sm text-gray-900 dark:text-gray-100">
                    {{ firm.description }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="flex-1 space-y-4">
              <div class="grid gap-4 md:grid-cols-2">
                <!-- Contact Details -->
                <div class="space-y-3">
                  <h3 class="font-medium text-gray-900 dark:text-gray-100">Contact Information</h3>

                  <div v-if="firm.primary_email" class="flex items-center gap-2 text-sm">
                    <Mail class="h-4 w-4 text-muted-foreground" />
                    <a :href="`mailto:${firm.primary_email}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                      {{ firm.primary_email }}
                    </a>
                    <Button variant="ghost" size="sm" @click="copyToClipboard(firm.primary_email, 'Email')" class="h-6 w-6 p-0">
                      <Copy class="h-3 w-3" />
                    </Button>
                  </div>

                  <div v-if="firm.primary_phone" class="flex items-center gap-2 text-sm">
                    <Phone class="h-4 w-4 text-muted-foreground" />
                    <a :href="`tel:${firm.primary_phone}`" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                      {{ firm.primary_phone }}
                    </a>
                    <Button variant="ghost" size="sm" @click="copyToClipboard(firm.primary_phone, 'Phone')" class="h-6 w-6 p-0">
                      <Copy class="h-3 w-3" />
                    </Button>
                  </div>

                  <div v-if="firm.url" class="flex items-center gap-2 text-sm">
                    <Globe class="h-4 w-4 text-muted-foreground" />
                    <a :href="firm.url" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
                      Visit Website
                      <ExternalLink class="h-3 w-3" />
                    </a>
                  </div>

                  <div v-if="firm.full_address" class="flex items-start gap-2 text-sm">
                    <MapPin class="h-4 w-4 text-muted-foreground mt-0.5" />
                    <span class="text-gray-600 dark:text-gray-400">{{ firm.full_address }}</span>
                  </div>
                </div>

                <!-- Statistics -->
                <div class="space-y-3">
                  <h3 class="font-medium text-gray-900 dark:text-gray-100">Statistics</h3>

                  <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ firm.contacts_count }}</div>
                      <div class="text-xs text-muted-foreground">Contacts</div>
                    </div>

                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ firm.projects_count }}</div>
                      <div class="text-xs text-muted-foreground">Projects</div>
                    </div>

                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ firm.completion_rate }}%</div>
                      <div class="text-xs text-muted-foreground">Completion</div>
                    </div>

                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ firm.activity_score }}</div>
                      <div class="text-xs text-muted-foreground">Activity</div>
                    </div>
                  </div>

                  <div v-if="firm.total_revenue" class="text-center p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/50 dark:to-emerald-950/50 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ firm.formatted_revenue }}</div>
                    <div class="text-xs text-green-700 dark:text-green-300">Total Revenue</div>
                  </div>
                </div>
              </div>

              <!-- Social Media Links -->
              <div v-if="socialMediaLinks.length > 0" class="flex items-center gap-3 pt-4 border-t">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Social Media:</span>
                <div class="flex items-center gap-2">
                  <Button
                    v-for="social in socialMediaLinks"
                    :key="social.name"
                    variant="outline"
                    size="sm"
                    as="a"
                    :href="social.url"
                    target="_blank"
                    class="gap-2"
                  >
                    <component :is="social.icon" class="h-4 w-4" />
                    {{ social.name }}
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Content Tabs -->
      <Tabs v-model="activeTab" class="space-y-6">
        <TabsList class="grid w-full grid-cols-5">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="contacts">Contacts ({{ firm.contacts_count }})</TabsTrigger>
          <TabsTrigger value="projects">Projects ({{ firm.projects_count }})</TabsTrigger>
          <TabsTrigger value="interactions">Interactions</TabsTrigger>
          <TabsTrigger value="files">Files</TabsTrigger>
        </TabsList>

        <!-- Overview Tab -->
        <TabsContent value="overview" class="space-y-6">
          <div class="grid gap-6 lg:grid-cols-2">
            <!-- Active Projects -->
            <Card>
              <CardHeader class="flex flex-row items-center justify-between">
                <div>
                  <CardTitle class="flex items-center gap-2">
                    <Briefcase class="h-5 w-5" />
                    Active Projects
                  </CardTitle>
                  <CardDescription>Currently ongoing projects</CardDescription>
                </div>
                <Button variant="outline" size="sm" as="a" :href="route('projects.index', { firm: firm.uuid })">
                  View All
                </Button>
              </CardHeader>
              <CardContent>
                <div v-if="activeProjects.length === 0" class="text-center py-8 text-muted-foreground">
                  <Briefcase class="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p>No active projects</p>
                  <Button variant="outline" size="sm" class="mt-2" as="a" :href="route('projects.create', { firm: firm.uuid })">
                    Create Project
                  </Button>
                </div>
                <div v-else class="space-y-4">
                  <div v-for="project in activeProjects.slice(0, 3)" :key="project.uuid" class="border rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                      <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ project.name }}</h4>
                        <p class="text-sm text-muted-foreground">
                          {{ project.contact.first_name }} {{ project.contact.last_name }}
                        </p>
                      </div>
                      <Badge variant="outline">{{ project.status }}</Badge>
                    </div>

                    <div class="space-y-2">
                      <div class="flex items-center justify-between text-sm">
                        <span class="text-muted-foreground">Progress</span>
                        <span class="font-medium">{{ project.progress?.percentage || 0 }}%</span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: `${project.progress?.percentage || 0}%` }"></div>
                      </div>
                      <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <span>{{ project.progress?.completed_tasks || 0 }}/{{ project.progress?.total_tasks || 0 }} tasks</span>
                        <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
                      </div>
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
                    <MessageSquare class="h-5 w-5" />
                    Recent Interactions
                  </CardTitle>
                  <CardDescription>Latest communications</CardDescription>
                </div>
                <Button variant="outline" size="sm" as="a" :href="route('interactions.index', { firm: firm.uuid })">
                  View All
                </Button>
              </CardHeader>
              <CardContent>
                <div v-if="interactionsArray.length === 0" class="text-center py-8 text-muted-foreground">
                  <MessageSquare class="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p>No recent interactions</p>
                  <Button variant="outline" size="sm" class="mt-2" as="a" :href="route('interactions.create', { firm: firm.uuid })">
                    Log Interaction
                  </Button>
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="interaction in partialInteractions"
                    :key="interaction.uuid"
                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="rounded-full bg-purple-100 p-2 dark:bg-purple-900/20">
                      <component :is="getInteractionIcon(interaction.type)" class="h-4 w-4 text-purple-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between mb-1">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">
                          {{ getContactName(interaction) }}
                        </h4>
                        <span class="text-xs text-muted-foreground">
                          {{ formatDateTime(interaction.interaction_date) }}
                        </span>
                      </div>
                      <p class="text-sm text-gray-900 dark:text-gray-100 line-clamp-2">
                        {{ interaction.subject || interaction.description }}
                      </p>
                      <Badge variant="outline" class="text-xs mt-2">
                        {{ interaction.type?.replace('_', ' ') }}
                      </Badge>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Additional Information -->
          <div class="grid gap-6 lg:grid-cols-2">
            <!-- Notes -->
            <Card v-if="firm.notes">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="h-5 w-5" />
                  Notes
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="prose prose-sm max-w-none dark:prose-invert">
                  <pre class="whitespace-pre-wrap text-sm">{{ firm.notes }}</pre>
                </div>
              </CardContent>
            </Card>

            <!-- Metadata -->
            <Card v-if="firm.metadata && Object.keys(firm.metadata).length > 0">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Settings class="h-5 w-5" />
                  Additional Information
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-2">
                  <div v-for="(value, key) in firm.metadata" :key="key" class="flex justify-between text-sm">
                    <span class="font-medium capitalize">{{ key.replace('_', ' ') }}:</span>
                    <span class="text-muted-foreground">{{ value }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Contacts Tab -->
        <TabsContent value="contacts">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <Users class="h-5 w-5" />
                  Contacts
                </CardTitle>
                <CardDescription>People associated with this firm</CardDescription>
              </div>
              <Button variant="outline" size="sm" as="a" :href="route('contacts.create', { firm: firm.uuid })">
                <Plus class="h-4 w-4 mr-1" />
                Add Contact
              </Button>
            </CardHeader>
            <CardContent>
              <div v-if="firm.contacts.length === 0" class="text-center py-12 text-muted-foreground">
                <Users class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No contacts yet</h3>
                <p class="mb-4">Start building relationships by adding contacts for this firm.</p>
                <Button as="a" :href="route('contacts.create', { firm: firm.uuid })">
                  <Plus class="h-4 w-4 mr-1" />
                  Add First Contact
                </Button>
              </div>
              <div v-else class="space-y-4">
                <div v-for="contact in firm.contacts" :key="contact.uuid" class="flex items-center justify-between p-4 border rounded-lg hover:shadow-md transition-shadow">
                  <div class="flex items-center gap-4">
                    <Avatar>
                      <AvatarFallback>
                        {{ contact.first_name.charAt(0) }}{{ contact.last_name.charAt(0) }}
                      </AvatarFallback>
                    </Avatar>
                    <div>
                      <h4 class="font-medium text-gray-900 dark:text-gray-100">
                        {{ contact.first_name }} {{ contact.last_name }}
                      </h4>
                      <p v-if="contact.title" class="text-sm text-muted-foreground">{{ contact.title }}</p>
                      <div class="flex items-center gap-4 mt-1">
                        <a v-if="contact.primary_email" :href="`mailto:${contact.primary_email}`" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                          {{ contact.primary_email }}
                        </a>
                        <a v-if="contact.primary_phone" :href="`tel:${contact.primary_phone}`" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400">
                          {{ contact.primary_phone }}
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <Button variant="ghost" size="sm" as="a" :href="route('contacts.show', contact.uuid)">
                      <Eye class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="sm" as="a" :href="route('contacts.edit', contact.uuid)">
                      <Edit class="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Projects Tab -->
        <TabsContent value="projects">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <Briefcase class="h-5 w-5" />
                  Projects
                </CardTitle>
                <CardDescription>All projects for this firm</CardDescription>
              </div>
              <Button variant="outline" size="sm" as="a" :href="route('projects.create', { firm: firm.uuid })">
                <Plus class="h-4 w-4 mr-1" />
                Add Project
              </Button>
            </CardHeader>
            <CardContent>
              <div v-if="firm.projects.length === 0" class="text-center py-12 text-muted-foreground">
                <Briefcase class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No projects yet</h3>
                <p class="mb-4">Create your first project to start tracking work for this firm.</p>
                <Button as="a" :href="route('projects.create', { firm: firm.uuid })">
                  <Plus class="h-4 w-4 mr-1" />
                  Create First Project
                </Button>
              </div>
              <div v-else class="space-y-4">
                <div v-for="project in firm.projects" :key="project.uuid" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                      <Link :href="route('projects.show', project.uuid)" class="font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
                        {{ project.name }}
                      </Link>
                      <p class="text-sm text-muted-foreground mt-1">
                        Contact: {{ project.contact.first_name }} {{ project.contact.last_name }}
                      </p>
                    </div>
                    <div class="flex items-center gap-2">
                      <Badge :class="getStatusColor(project.status)">{{ project.status }}</Badge>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                            <MoreHorizontal class="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem asChild>
                            <Link :href="route('projects.show', project.uuid)" class="flex items-center gap-2">
                              <Eye class="h-4 w-4" />
                              View Project
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem asChild>
                            <Link :href="route('projects.edit', project.uuid)" class="flex items-center gap-2">
                              <Edit class="h-4 w-4" />
                              Edit Project
                            </Link>
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>
                  </div>

                  <div class="flex items-center justify-between text-sm text-muted-foreground">
                    <span>Created {{ formatDate(project.created_at) }}</span>
                    <span v-if="project.due_date">Due {{ formatDate(project.due_date) }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Interactions Tab -->
        <TabsContent value="interactions">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <MessageSquare class="h-5 w-5" />
                  Interactions
                </CardTitle>
                <CardDescription>Communication history with this firm</CardDescription>
              </div>
              <Button variant="outline" size="sm" as="a" :href="route('interactions.create', { firm: firm.uuid })">
                <Plus class="h-4 w-4 mr-1" />
                Log Interaction
              </Button>
            </CardHeader>
            <CardContent>
              <div v-if="interactionsArray.length === 0" class="text-center py-12 text-muted-foreground">
                <MessageSquare class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No interactions logged</h3>
                <p class="mb-4">Start tracking your communications with this firm.</p>
                <Button as="a" :href="route('interactions.create', { firm: firm.uuid })">
                  <Plus class="h-4 w-4 mr-1" />
                  Log First Interaction
                </Button>
              </div>
              <div v-else class="space-y-4">
                <div v-for="interaction in interactionsArray" :key="interaction.uuid" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div class="flex items-start gap-4">
                    <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                      <component :is="getInteractionIcon(interaction.type)" class="h-5 w-5 text-purple-600" />
                    </div>

                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                          {{ getContactName(interaction) }}
                        </h4>
                        <div class="flex items-center gap-2">
                          <Badge variant="outline" class="text-xs">
                            {{ interaction.type?.replace('_', ' ') }}
                          </Badge>
                          <span class="text-sm text-muted-foreground">
                            {{ formatDateTime(interaction.interaction_date) }}
                          </span>
                        </div>
                      </div>

                      <h5 v-if="interaction.subject" class="font-medium text-gray-900 dark:text-gray-100 mb-1">
                        {{ interaction.subject }}
                      </h5>

                      <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                        {{ interaction.description }}
                      </p>

                      <div v-if="interaction.outcome" class="mt-2">
                        <Badge variant="secondary" class="text-xs">
                          Outcome: {{ interaction.outcome }}
                        </Badge>
                      </div>

                      <div class="flex items-center justify-between mt-3">
                        <div class="flex items-center gap-2">
                          <Button variant="ghost" size="sm" as="a" :href="route('interactions.show', interaction.uuid)">
                            <Eye class="h-4 w-4 mr-1" />
                            View Details
                          </Button>
                        </div>
                        <div v-if="interaction.duration_minutes" class="text-xs text-muted-foreground">
                          Duration: {{ interaction.duration_minutes }} minutes
                        </div>
                      </div>
                    </div>
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
              <div>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="h-5 w-5" />
                  Files & Documents
                </CardTitle>
                <CardDescription>Documents and media files for this firm</CardDescription>
              </div>
              <Button variant="outline" size="sm">
                <Upload class="h-4 w-4 mr-1" />
                Upload File
              </Button>
            </CardHeader>
            <CardContent>
              <div class="text-center py-12 text-muted-foreground">
                <FileText class="h-16 w-16 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No files uploaded</h3>
                <p class="mb-4">Upload documents, images, and other files related to this firm.</p>
                <div class="flex items-center justify-center gap-2">
                  <Button variant="outline">
                    <Upload class="h-4 w-4 mr-1" />
                    Upload Document
                  </Button>
                  <Button variant="outline">
                    <ImageIcon class="h-4 w-4 mr-1" />
                    Upload Image
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>
