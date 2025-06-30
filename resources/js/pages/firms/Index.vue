<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { toast } from 'vue-sonner'
import { router, Link, usePage } from '@inertiajs/vue3'
import { useDark, useDebounce } from '@vueuse/core'
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
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
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Building2,
  Plus,
  Search,
  Filter,
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  Users,
  Phone,
  Mail,
  MapPin,
  Globe,
  Calendar,
  ArrowUpDown,
  ChevronLeft,
  ChevronRight,
  RefreshCw,
  Download,
  Upload,
  Settings,
  SortAsc,
  SortDesc,
  X,
  Building,
  Briefcase,
  Star,
  Clock,
  CheckCircle2,
  AlertTriangle
} from 'lucide-vue-next'
import AppSidebarLayout from '@/layouts/AppSidebarLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue';

// Types
interface Firm {
  id: number
  uuid: string
  name: string
  email: string | null
  phone: string | null
  website: string | null
  address: string | null
  city: string | null
  state: string | null
  postal_code: string | null
  country: string | null
  industry: string | null
  size: string | null
  description: string | null
  logo_url: string | null
  status: 'active' | 'inactive' | 'prospect'
  created_at: string
  updated_at: string
  contacts_count: number
  projects_count: number
  active_projects_count: number
  total_revenue: number | null
  last_interaction_date: string | null
  tags: string[]
  metadata: {
    linkedin_url?: string
    twitter_url?: string
    facebook_url?: string
    notes?: string
    priority?: 'low' | 'medium' | 'high'
    source?: string
    assigned_to?: string
  }
}

interface PaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

interface Props {
  firms: {
    data: Firm[]
    current_page: number
    from: number | null
    last_page: number
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
    path: string
    per_page: number
    to: number | null
    total: number
  }
  filters: {
    search?: string
    status?: string
    industry?: string
    size?: string
    sort?: string
    direction?: 'asc' | 'desc'
    per_page?: number
  }
  industries: string[]
  sizes: string[]
  stats: {
    total: number
    active: number
    inactive: number
    prospects: number
    total_contacts: number
    total_projects: number
  }
}

// Props
const props = defineProps<Props>()

// Reactive state
const isLoading = ref(false)
const isRefreshing = ref(false)
const searchQuery = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || 'all')
const selectedIndustry = ref(props.filters.industry || 'all')
const selectedSize = ref(props.filters.size || 'all')
const sortField = ref(props.filters.sort || 'name')
const sortDirection = ref(props.filters.direction || 'asc')
const perPage = ref(props.filters.per_page || 15)
const selectedFirms = ref<string[]>([])
const showFilters = ref(false)
const viewMode = ref<'table' | 'grid'>('table')
const isDark = useDark()

// Debounced search
const debouncedSearch = useDebounce(searchQuery, 300)

// Computed properties
const hasFilters = computed(() => {
  return selectedStatus.value !== 'all' ||
    selectedIndustry.value !== 'all' ||
    selectedSize.value !== 'all' ||
    searchQuery.value !== ''
})

const filteredFirmsCount = computed(() => props.firms.total)

const isAllSelected = computed(() => {
  return props.firms.data.length > 0 &&
    selectedFirms.value.length === props.firms.data.length
})

const isIndeterminate = computed(() => {
  return selectedFirms.value.length > 0 &&
    selectedFirms.value.length < props.firms.data.length
})

const selectedFirmsCount = computed(() => selectedFirms.value.length)

// Status color mapping
const getStatusColor = (status: string) => {
  const colors = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    prospect: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
  }
  return colors[status] || colors.active
}

const getPriorityColor = (priority: string) => {
  const colors = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    high: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[priority] || colors.medium
}

// Utility functions
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number | null) => {
  if (!amount) return '$0'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Actions
const applyFilters = () => {
  const params = {
    search: searchQuery.value || undefined,
    status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    industry: selectedIndustry.value !== 'all' ? selectedIndustry.value : undefined,
    size: selectedSize.value !== 'all' ? selectedSize.value : undefined,
    sort: sortField.value,
    direction: sortDirection.value,
    per_page: perPage.value,
    page: 1 // Reset to first page when filtering
  }

  router.get(route('firms.index'), params, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = 'all'
  selectedIndustry.value = 'all'
  selectedSize.value = 'all'
  sortField.value = 'name'
  sortDirection.value = 'asc'
  perPage.value = 15

  router.get(route('firms.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const sortBy = (field: string) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  applyFilters()
}

const toggleFirmSelection = (firmUuid: string) => {
  const index = selectedFirms.value.indexOf(firmUuid)
  if (index > -1) {
    selectedFirms.value.splice(index, 1)
  } else {
    selectedFirms.value.push(firmUuid)
  }
}

const toggleAllFirms = () => {
  if (isAllSelected.value) {
    selectedFirms.value = []
  } else {
    selectedFirms.value = props.firms.data.map(firm => firm.uuid)
  }
}

const deleteFirm = async (firmUuid: string) => {
  try {
    await router.delete(route('firms.destroy', firmUuid))
    toast.success('Firm deleted successfully')
    selectedFirms.value = selectedFirms.value.filter(uuid => uuid !== firmUuid)
  } catch (error) {
    console.error('Delete error:', error)
    toast.error('Failed to delete firm')
  }
}

const bulkDelete = async () => {
  if (selectedFirms.value.length === 0) return

  try {
    await router.post(route('firms.bulk-delete'), {
      firm_uuids: selectedFirms.value
    })
    toast.success(`${selectedFirms.value.length} firms deleted successfully`)
    selectedFirms.value = []
  } catch (error) {
    console.error('Bulk delete error:', error)
    toast.error('Failed to delete selected firms')
  }
}

const exportFirms = async () => {
  try {
    const params = {
      search: searchQuery.value || undefined,
      status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
      industry: selectedIndustry.value !== 'all' ? selectedIndustry.value : undefined,
      size: selectedSize.value !== 'all' ? selectedSize.value : undefined,
      selected: selectedFirms.value.length > 0 ? selectedFirms.value : undefined
    }

    window.open(route('firms.export', params))
    toast.success('Export started successfully')
  } catch (error) {
    console.error('Export error:', error)
    toast.error('Failed to export firms')
  }
}

const refreshData = async () => {
  isRefreshing.value = true
  try {
    await router.reload({ only: ['firms', 'stats'] })
    toast.success('Data refreshed successfully')
  } catch (error) {
    console.error('Refresh error:', error)
    toast.error('Failed to refresh data')
  } finally {
    isRefreshing.value = false
  }
}

// Watchers
watch(debouncedSearch, () => {
  applyFilters()
})

watch([selectedStatus, selectedIndustry, selectedSize, perPage], () => {
  applyFilters()
})

// Layout
defineOptions({
  layout: AppLayout
})

// Lifecycle
onMounted(() => {
  console.log('Firms index mounted with:', props.firms.data.length, 'firms')
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
            Firms
          </h1>
          <p class="text-muted-foreground mt-1">
            Manage your client firms and organizations
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Button
            variant="outline"
            size="sm"
            @click="refreshData"
            :disabled="isRefreshing"
            class="gap-2">
            <RefreshCw :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4" />
            Refresh
          </Button>

          <Button
            variant="outline"
            size="sm"
            @click="exportFirms"
            class="gap-2">
            <Download class="h-4 w-4" />
            Export
          </Button>

          <Button class="gap-2">
            <Plus class="h-4 w-4" />
            Add Firm
          </Button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <Card class="border-l-4 border-l-blue-500">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Firms</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                  {{ stats.total }}
                </p>
              </div>
              <Building2 class="h-8 w-8 text-blue-600 dark:text-blue-400" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-green-500">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Active</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                  {{ stats.active }}
                </p>
              </div>
              <CheckCircle2 class="h-8 w-8 text-green-600 dark:text-green-400" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-yellow-500">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Prospects</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                  {{ stats.prospects }}
                </p>
              </div>
              <Star class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-gray-500">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Inactive</p>
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                  {{ stats.inactive }}
                </p>
              </div>
              <Clock class="h-8 w-8 text-gray-600 dark:text-gray-400" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-purple-500">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Contacts</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                  {{ stats.total_contacts }}
                </p>
              </div>
              <Users class="h-8 w-8 text-purple-600 dark:text-purple-400" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-orange-500">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Projects</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                  {{ stats.total_projects }}
                </p>
              </div>
              <Briefcase class="h-8 w-8 text-orange-600 dark:text-orange-400" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Search and Filters -->
      <Card>
        <CardContent class="p-6">
          <div class="space-y-4">
            <!-- Search Bar -->
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="searchQuery"
                  placeholder="Search firms by name, email, or industry..."
                  class="pl-10"
                />
              </div>
              <div class="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  @click="showFilters = !showFilters"
                  class="gap-2">
                  <Filter class="h-4 w-4" />
                  Filters
                  <Badge v-if="hasFilters" variant="secondary" class="ml-1">
                    Active
                  </Badge>
                </Button>
                <Button
                  v-if="hasFilters"
                  variant="ghost"
                  size="sm"
                  @click="clearFilters"
                  class="gap-2">
                  <X class="h-4 w-4" />
                  Clear
                </Button>
              </div>
            </div>

            <!-- Filters Panel -->
            <div v-if="showFilters" class="grid gap-4 md:grid-cols-4 pt-4 border-t">
              <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                  Status
                </label>
                <Select v-model="selectedStatus">
                  <SelectTrigger>
                    <SelectValue placeholder="All Statuses" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="inactive">Inactive</SelectItem>
                    <SelectItem value="prospect">Prospect</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                  Industry
                </label>
                <Select v-model="selectedIndustry">
                  <SelectTrigger>
                    <SelectValue placeholder="All Industries" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Industries</SelectItem>
                    <SelectItem v-for="industry in industries" :key="industry" :value="industry">
                      {{ industry }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                  Size
                </label>
                <Select v-model="selectedSize">
                  <SelectTrigger>
                    <SelectValue placeholder="All Sizes" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Sizes</SelectItem>
                    <SelectItem v-for="size in sizes" :key="size" :value="size">
                      {{ size }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                  Per Page
                </label>
                <Select v-model="perPage">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="10">10 per page</SelectItem>
                    <SelectItem value="15">15 per page</SelectItem>
                    <SelectItem value="25">25 per page</SelectItem>
                    <SelectItem value="50">50 per page</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Bulk Actions -->
      <div v-if="selectedFirmsCount > 0" class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-950/50 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-center gap-3">
          <Badge variant="secondary">
            {{ selectedFirmsCount }} selected
          </Badge>
          <span class="text-sm text-muted-foreground">
            Bulk actions available
          </span>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="exportFirms">
            <Download class="h-4 w-4 mr-1" />
            Export Selected
          </Button>
          <AlertDialog>
            <AlertDialogTrigger asChild>
              <Button variant="destructive" size="sm">
                <Trash2 class="h-4 w-4 mr-1" />
                Delete Selected
              </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Delete Selected Firms</AlertDialogTitle>
                <AlertDialogDescription>
                  Are you sure you want to delete {{ selectedFirmsCount }} selected firms?
                  This action cannot be undone and will also delete all associated contacts and projects.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="bulkDelete" class="bg-red-600 hover:bg-red-700">
                  Delete Firms
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>
      </div>

      <!-- Firms Table -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle>Firms Directory</CardTitle>
            <CardDescription>
              {{ filteredFirmsCount }} firms found
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-12">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      :indeterminate="isIndeterminate"
                      @change="toggleAllFirms"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </TableHead>
                  <TableHead>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="sortBy('name')"
                      class="gap-1 font-semibold">
                      Firm Name
                      <ArrowUpDown class="h-3 w-3" />
                    </Button>
                  </TableHead>
                  <TableHead>Contact Info</TableHead>
                  <TableHead>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="sortBy('industry')"
                      class="gap-1 font-semibold">
                      Industry
                      <ArrowUpDown class="h-3 w-3" />
                    </Button>
                  </TableHead>
                  <TableHead>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="sortBy('status')"
                      class="gap-1 font-semibold">
                      Status
                      <ArrowUpDown class="h-3 w-3" />
                    </Button>
                  </TableHead>
                  <TableHead>Stats</TableHead>
                  <TableHead>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="sortBy('created_at')"
                      class="gap-1 font-semibold">
                      Created
                      <ArrowUpDown class="h-3 w-3" />
                    </Button>
                  </TableHead>
                  <TableHead class="w-12"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="isLoading" v-for="n in 5" :key="n">
                  <TableCell><Skeleton class="h-4 w-4" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-32" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-24" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-20" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-20" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                  <TableCell><Skeleton class="h-4 w-8" /></TableCell>
                </TableRow>

                <TableRow v-else-if="firms.data.length === 0">
                  <TableCell colspan="8" class="text-center py-12">
                    <div class="flex flex-col items-center gap-4">
                      <Building2 class="h-12 w-12 text-muted-foreground opacity-50" />
                      <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">No firms found</h3>
                        <p class="text-muted-foreground mt-1">
                          {{ hasFilters ? 'Try adjusting your search criteria' : 'Get started by adding your first firm' }}
                        </p>
                      </div>

                      <Button class="gap-2" v-if="!hasFilters">
                        <Plus class="h-4 w-4" />
                        Add Your First Firm
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>

                <TableRow
                  v-else
                  v-for="firm in firms.data"
                  :key="firm.uuid"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                >
                  <TableCell>
                    <input
                      type="checkbox"
                      :checked="selectedFirms.includes(firm.uuid)"
                      @change="toggleFirmSelection(firm.uuid)"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </TableCell>

                  <TableCell>
                    <div class="flex items-center gap-3">
                      <div class="flex-shrink-0">
                        <div v-if="firm.logo_url" class="h-10 w-10 rounded-lg overflow-hidden">
                          <img :src="firm.logo_url" :alt="firm.name" class="h-full w-full object-cover" />
                        </div>
                        <div v-else class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                          <span class="text-white font-semibold text-sm">{{ getInitials(firm.name) }}</span>
                        </div>
                      </div>
                      <div class="min-w-0 flex-1">
                        <Link
                          :href="route('firms.show', firm.uuid)"
                          class="font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                        >
                          {{ firm.name }}
                        </Link>
                        <div v-if="firm.description" class="text-sm text-muted-foreground truncate mt-1">
                          {{ firm.description }}
                        </div>
                        <div v-if="firm.tags && firm.tags.length > 0" class="flex gap-1 mt-2">
                          <Badge v-for="tag in firm.tags.slice(0, 2)" :key="tag" variant="outline" class="text-xs">
                            {{ tag }}
                          </Badge>
                          <Badge v-if="firm.tags.length > 2" variant="outline" class="text-xs">
                            +{{ firm.tags.length - 2 }}
                          </Badge>
                        </div>
                      </div>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="space-y-1">
                      <div v-if="firm.email" class="flex items-center gap-2 text-sm">
                        <Mail class="h-3 w-3 text-muted-foreground" />
                        <a :href="`mailto:${firm.email}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                          {{ firm.email }}
                        </a>
                      </div>
                      <div v-if="firm.phone" class="flex items-center gap-2 text-sm">
                        <Phone class="h-3 w-3 text-muted-foreground" />
                        <a :href="`tel:${firm.phone}`" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                          {{ firm.phone }}
                        </a>
                      </div>
                      <div v-if="firm.website" class="flex items-center gap-2 text-sm">
                        <Globe class="h-3 w-3 text-muted-foreground" />
                        <a :href="firm.website" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                          Visit Website
                        </a>
                      </div>
                      <div v-if="firm.city || firm.state" class="flex items-center gap-2 text-sm text-muted-foreground">
                        <MapPin class="h-3 w-3" />
                        <span>{{ [firm.city, firm.state].filter(Boolean).join(', ') }}</span>
                      </div>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="space-y-1">
                      <div v-if="firm.industry" class="text-sm font-medium">{{ firm.industry }}</div>
                      <div v-if="firm.size" class="text-xs text-muted-foreground">{{ firm.size }}</div>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="space-y-2">
                      <Badge :class="getStatusColor(firm.status)" class="text-xs capitalize">
                        {{ firm.status }}
                      </Badge>
                      <div v-if="firm.metadata?.priority" class="flex items-center gap-1">
                        <Badge :class="getPriorityColor(firm.metadata.priority)" class="text-xs">
                          {{ firm.metadata.priority }}
                        </Badge>
                      </div>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="space-y-1 text-sm">
                      <div class="flex items-center gap-2">
                        <Users class="h-3 w-3 text-muted-foreground" />
                        <span>{{ firm.contacts_count }} contacts</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <Briefcase class="h-3 w-3 text-muted-foreground" />
                        <span>{{ firm.projects_count }} projects</span>
                        <span v-if="firm.active_projects_count > 0" class="text-green-600 dark:text-green-400">
                          ({{ firm.active_projects_count }} active)
                        </span>
                      </div>
                      <div v-if="firm.total_revenue" class="text-xs text-muted-foreground">
                        Revenue: {{ formatCurrency(firm.total_revenue) }}
                      </div>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="space-y-1 text-sm">
                      <div>{{ formatDate(firm.created_at) }}</div>
                      <div v-if="firm.last_interaction_date" class="text-xs text-muted-foreground">
                        Last contact: {{ formatDate(firm.last_interaction_date) }}
                      </div>
                    </div>
                  </TableCell>

                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                        <DropdownMenuItem asChild>
                          <Link :href="route('firms.show', firm.uuid)" class="flex items-center gap-2">
                            <Eye class="h-4 w-4" />
                            View Details
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link :href="route('firms.edit', firm.uuid)" class="flex items-center gap-2">
                            <Edit class="h-4 w-4" />
                            Edit Firm
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                          <Link :href="route('contacts.index', { firm: firm.uuid })" class="flex items-center gap-2">
                            <Users class="h-4 w-4" />
                            View Contacts ({{ firm.contacts_count }})
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link :href="route('projects.index', { firm: firm.uuid })" class="flex items-center gap-2">
                            <Briefcase class="h-4 w-4" />
                            View Projects ({{ firm.projects_count }})
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
                              <AlertDialogAction @click="deleteFirm(firm.uuid)" class="bg-red-600 hover:bg-red-700">
                                Delete Firm
                              </AlertDialogAction>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>

        <!-- Pagination -->
        <CardFooter v-if="firms.last_page > 1" class="flex items-center justify-between">
          <div class="text-sm text-muted-foreground">
            Showing {{ firms.from || 0 }} to {{ firms.to || 0 }} of {{ firms.total }} results
          </div>

          <div class="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              :disabled="!firms.links.prev"
              asChild
            >
              <Link v-if="firms.links.prev" :href="firms.links.prev" class="flex items-center gap-1">
                <ChevronLeft class="h-4 w-4" />
                Previous
              </Link>
              <span v-else class="flex items-center gap-1 opacity-50">
                <ChevronLeft class="h-4 w-4" />
                Previous
              </span>
            </Button>

            <div class="flex items-center gap-1">
              <template v-for="link in firms.links" :key="link.label">
                <Button
                  v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                  :variant="link.active ? 'default' : 'outline'"
                  size="sm"
                  asChild
                >
                  <Link :href="link.url">{{ link.label }}</Link>
                </Button>
                <span v-else-if="!link.url && !link.label.includes('Previous') && !link.label.includes('Next')" class="px-3 py-1 text-sm">
                  {{ link.label }}
                </span>
              </template>
            </div>

            <Button
              variant="outline"
              size="sm"
              :disabled="!firms.links.next"
              asChild
            >
              <Link v-if="firms.links.next" :href="firms.links.next" class="flex items-center gap-1">
                Next
                <ChevronRight class="h-4 w-4" />
              </Link>
              <span v-else class="flex items-center gap-1 opacity-50">
                Next
                <ChevronRight class="h-4 w-4" />
              </span>
            </Button>
          </div>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>
