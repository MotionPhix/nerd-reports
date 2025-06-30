<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'

// UI Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'

// Icons
import {
  Users, Building2, Mail, Calendar, Search, Plus, Download, Upload,
  Eye, Edit, Copy, Trash2, MoreHorizontal, X, ChevronLeft, ChevronRight
} from 'lucide-vue-next'

// Props
const props = defineProps({
  contacts: Object,
  stats: Object,
  firms: Array,
  availableTags: Array,
  filters: Object,
})

// Page data
const page = usePage()

// Reactive data
const selectedContacts = ref([])
const showDeleteDialog = ref(false)
const showBulkDeleteDialog = ref(false)
const showImportModal = ref(false)
const contactToDelete = ref(null)
const selectedFile = ref(null)
const importing = ref(false)

// Filters
const filters = ref({
  search: props.filters.search || '',
  firm_id: props.filters.firm_id || '',
  tags: props.filters.tags || '',
  sort_by: props.filters.sort_by || 'created_at',
  sort_order: props.filters.sort_order || 'desc',
})

// Computed properties
const isAllSelected = computed(() => {
  return props.contacts.data.length > 0 &&
    selectedContacts.value.length === props.contacts.data.length
})

const hasActiveFilters = computed(() => {
  return filters.value.search ||
    filters.value.firm_id ||
    filters.value.tags
})

const paginationPages = computed(() => {
  const current = props.contacts.current_page
  const last = props.contacts.last_page
  const pages = []

  // Always show first page
  if (current > 3) pages.push(1)
  if (current > 4) pages.push('...')

  // Show pages around current
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }

  // Always show last page
  if (current < last - 3) pages.push('...')
  if (current < last - 2) pages.push(last)

  return pages.filter((page, index, arr) => arr.indexOf(page) === index)
})

// Methods
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedContacts.value = []
  } else {
    selectedContacts.value = props.contacts.data.map(contact => contact.uuid)
  }
}

const toggleContactSelection = (contactId) => {
  const index = selectedContacts.value.indexOf(contactId)
  if (index > -1) {
    selectedContacts.value.splice(index, 1)
  } else {
    selectedContacts.value.push(contactId)
  }
}

const applyFilters = () => {
  router.get(route('contacts.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const clearFilter = (filterName) => {
  filters.value[filterName] = ''
  applyFilters()
}

const clearAllFilters = () => {
  filters.value = {
    search: '',
    firm_id: '',
    tags: '',
    sort_by: 'created_at',
    sort_order: 'desc',
  }
  applyFilters()
}

const getFirmName = (firmId) => {
  const firm = props.firms.find(f => f.uuid === firmId)
  return firm ? firm.name : ''
}

const goToPage = (page) => {
  router.get(route('contacts.index'), {
    ...filters.value,
    page: page
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const confirmDelete = (contact) => {
  contactToDelete.value = contact
  showDeleteDialog.value = true
}

const deleteContact = () => {
  if (contactToDelete.value) {
    router.delete(route('contacts.destroy', contactToDelete.value.uuid), {
      onSuccess: () => {
        showDeleteDialog.value = false
        contactToDelete.value = null
      }
    })
  }
}

const confirmBulkDelete = () => {
  showBulkDeleteDialog.value = true
}

const bulkDeleteContacts = () => {
  router.post(route('contacts.bulk-delete'), {
    contact_ids: selectedContacts.value
  }, {
    onSuccess: () => {
      selectedContacts.value = []
      showBulkDeleteDialog.value = false
    }
  })
}

const duplicateContact = (contactId) => {
  router.post(route('contacts.duplicate', contactId))
}

const exportContacts = () => {
  const params = new URLSearchParams({
    ...filters.value,
    format: 'csv'
  })
  window.open(route('contacts.export') + '?' + params.toString())
}

const bulkExport = () => {
  const params = new URLSearchParams({
    contact_ids: selectedContacts.value,
    format: 'csv'
  })
  window.open(route('contacts.export') + '?' + params.toString())
}

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0]
}

const importContacts = () => {
  if (!selectedFile.value) return

  importing.value = true
  const formData = new FormData()
  formData.append('file', selectedFile.value)

  router.post(route('contacts.import'), formData, {
    onSuccess: () => {
      showImportModal.value = false
      selectedFile.value = null
      importing.value = false
    },
    onError: () => {
      importing.value = false
    }
  })
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Watch for filter changes
watch(() => props.filters, (newFilters) => {
  filters.value = { ...filters.value, ...newFilters }
}, { deep: true })

// Clear selections when data changes
watch(() => props.contacts.data, () => {
  selectedContacts.value = []
})
</script>

<template>
  <AppLayout title="Contacts">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Contacts
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage your business contacts and relationships
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Button
            variant="outline"
            @click="showImportModal = true"
            class="flex items-center gap-2"
          >
            <Upload class="h-4 w-4" />
            Import
          </Button>
          <Button
            variant="outline"
            @click="exportContacts"
            class="flex items-center gap-2"
          >
            <Download class="h-4 w-4" />
            Export
          </Button>
          <Button
            @click="router.visit(route('contacts.create'))"
            class="flex items-center gap-2"
          >
            <Plus class="h-4 w-4" />
            Add Contact
          </Button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardContent class="p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Users class="h-8 w-8 text-blue-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Total Contacts
                  </p>
                  <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ stats.total.toLocaleString() }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Building2 class="h-8 w-8 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    With Firms
                  </p>
                  <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ stats.with_firms.toLocaleString() }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Mail class="h-8 w-8 text-purple-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    With Emails
                  </p>
                  <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ stats.with_emails.toLocaleString() }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Calendar class="h-8 w-8 text-orange-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Recent (30 days)
                  </p>
                  <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ stats.recent.toLocaleString() }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Filters and Search -->
        <Card class="mb-6">
          <CardContent class="p-6">
            <div class="grid gap-4 md:grid-cols-4">
              <!-- Search -->
              <div class="relative">
                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="filters.search"
                  placeholder="Search contacts..."
                  class="pl-10"
                  @input="debouncedSearch"
                />
              </div>

              <!-- Firm Filter -->
              <Select v-model="filters.firm_id" @update:modelValue="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="All Firms" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All Firms</SelectItem>
                  <SelectItem
                    v-for="firm in firms"
                    :key="firm.uuid"
                    :value="firm.uuid"
                  >
                    {{ firm.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <!-- Tags Filter -->
              <Select v-model="filters.tags" @update:modelValue="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="All Tags" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All Tags</SelectItem>
                  <SelectItem
                    v-for="tag in availableTags"
                    :key="tag"
                    :value="tag"
                  >
                    {{ tag }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <!-- Sort Options -->
              <Select v-model="filters.sort_by" @update:modelValue="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="created_at">Recently Added</SelectItem>
                  <SelectItem value="updated_at">Recently Updated</SelectItem>
                  <SelectItem value="first_name">First Name</SelectItem>
                  <SelectItem value="last_name">Last Name</SelectItem>
                  <SelectItem value="job_title">Job Title</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Active Filters -->
            <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
              <Badge
                v-if="filters.search"
                variant="secondary"
                class="flex items-center gap-1"
              >
                Search: {{ filters.search }}
                <X
                  class="h-3 w-3 cursor-pointer"
                  @click="clearFilter('search')"
                />
              </Badge>
              <Badge
                v-if="filters.firm_id"
                variant="secondary"
                class="flex items-center gap-1"
              >
                Firm: {{ getFirmName(filters.firm_id) }}
                <X
                  class="h-3 w-3 cursor-pointer"
                  @click="clearFilter('firm_id')"
                />
              </Badge>
              <Badge
                v-if="filters.tags"
                variant="secondary"
                class="flex items-center gap-1"
              >
                Tag: {{ filters.tags }}
                <X
                  class="h-3 w-3 cursor-pointer"
                  @click="clearFilter('tags')"
                />
              </Badge>
              <Button
                variant="ghost"
                size="sm"
                @click="clearAllFilters"
                class="text-xs"
              >
                Clear All
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Bulk Actions -->
        <div
          v-if="selectedContacts.length > 0"
          class="mb-4 flex items-center justify-between rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20"
        >
          <div class="flex items-center gap-2">
            <Checkbox
              :checked="isAllSelected"
              @update:checked="toggleSelectAll"
            />
            <span class="text-sm font-medium">
              {{ selectedContacts.length }} contact(s) selected
            </span>
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              @click="bulkExport"
              class="flex items-center gap-1"
            >
              <Download class="h-3 w-3" />
              Export Selected
            </Button>
            <Button
              variant="destructive"
              size="sm"
              @click="confirmBulkDelete"
              class="flex items-center gap-1"
            >
              <Trash2 class="h-3 w-3" />
              Delete Selected
            </Button>
          </div>
        </div>

        <!-- Contacts Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-12">
                      <Checkbox
                        :checked="isAllSelected"
                        @update:checked="toggleSelectAll"
                      />
                    </TableHead>
                    <TableHead>Contact</TableHead>
                    <TableHead>Job Title</TableHead>
                    <TableHead>Firm</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Phone</TableHead>
                    <TableHead>Tags</TableHead>
                    <TableHead>Added</TableHead>
                    <TableHead class="w-20">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="contact in contacts.data"
                    :key="contact.uuid"
                    class="hover:bg-muted/50"
                  >
                    <TableCell>
                      <Checkbox
                        :checked="selectedContacts.includes(contact.uuid)"
                        @update:checked="toggleContactSelection(contact.uuid)"
                      />
                    </TableCell>
                    <TableCell>
                      <div class="flex items-center gap-3">
                        <Avatar class="h-10 w-10">
                          <AvatarImage
                            :src="contact.avatar_url"
                            :alt="contact.full_name"
                          />
                          <AvatarFallback>
                            {{ contact.initials }}
                          </AvatarFallback>
                        </Avatar>
                        <div>
                          <div class="font-medium">{{ contact.full_name }}</div>
                          <div
                            v-if="contact.nickname"
                            class="text-sm text-muted-foreground"
                          >
                            "{{ contact.nickname }}"
                          </div>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <span v-if="contact.job_title" class="text-sm">
                        {{ contact.job_title }}
                      </span>
                      <span v-else class="text-sm text-muted-foreground">
                        No job title
                      </span>
                    </TableCell>
                    <TableCell>
                      <Link
                        v-if="contact.firm"
                        :href="route('firms.show', contact.firm.uuid)"
                        class="text-blue-600 hover:text-blue-800 hover:underline"
                      >
                        {{ contact.firm.name }}
                      </Link>
                      <span v-else class="text-sm text-muted-foreground">
                        No firm
                      </span>
                    </TableCell>
                    <TableCell>
                      <a
                        v-if="contact.primary_email"
                        :href="`mailto:${contact.primary_email}`"
                        class="text-blue-600 hover:text-blue-800 hover:underline"
                      >
                        {{ contact.primary_email }}
                      </a>
                      <span v-else class="text-sm text-muted-foreground">
                        No email
                      </span>
                    </TableCell>
                    <TableCell>
                      <a
                        v-if="contact.primary_phone"
                        :href="`tel:${contact.primary_phone}`"
                        class="text-blue-600 hover:text-blue-800 hover:underline"
                      >
                        {{ contact.primary_phone }}
                      </a>
                      <span v-else class="text-sm text-muted-foreground">
                        No phone
                      </span>
                    </TableCell>
                    <TableCell>
                      <div class="flex flex-wrap gap-1">
                        <Badge
                          v-for="tag in contact.tags.slice(0, 2)"
                          :key="tag.name"
                          variant="outline"
                          class="text-xs"
                        >
                          {{ tag.name }}
                        </Badge>
                        <Badge
                          v-if="contact.tags.length > 2"
                          variant="outline"
                          class="text-xs"
                        >
                          +{{ contact.tags.length - 2 }}
                        </Badge>
                      </div>
                    </TableCell>
                    <TableCell>
                      <time
                        :datetime="contact.created_at"
                        class="text-sm text-muted-foreground"
                      >
                        {{ formatDate(contact.created_at) }}
                      </time>
                    </TableCell>
                    <TableCell>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="sm">
                            <MoreHorizontal class="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem
                            @click="router.visit(route('contacts.show', contact.uuid))"
                          >
                            <Eye class="mr-2 h-4 w-4" />
                            View
                          </DropdownMenuItem>
                          <DropdownMenuItem
                            @click="router.visit(route('contacts.edit', contact.uuid))"
                          >
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                          </DropdownMenuItem>
                          <DropdownMenuItem
                            @click="duplicateContact(contact.uuid)"
                          >
                            <Copy class="mr-2 h-4 w-4" />
                            Duplicate
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem
                            @click="confirmDelete(contact)"
                            class="text-red-600 focus:text-red-600"
                          >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>

              <!-- Empty State -->
              <div
                v-if="contacts.data.length === 0"
                class="flex flex-col items-center justify-center py-12"
              >
                <Users class="h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                  No contacts found
                </h3>
                <p class="text-sm text-muted-foreground mb-4 text-center max-w-sm">
                  {{ hasActiveFilters
                  ? 'Try adjusting your search criteria or filters.'
                  : 'Get started by adding your first contact.'
                  }}
                </p>
                <Button
                  v-if="!hasActiveFilters"
                  @click="router.visit(route('contacts.create'))"
                  class="flex items-center gap-2"
                >
                  <Plus class="h-4 w-4" />
                  Add Your First Contact
                </Button>
                <Button
                  v-else
                  variant="outline"
                  @click="clearAllFilters"
                >
                  Clear Filters
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Pagination -->
        <div
          v-if="contacts.data.length > 0"
          class="mt-6 flex items-center justify-between"
        >
          <div class="text-sm text-muted-foreground">
            Showing {{ contacts.from }} to {{ contacts.to }} of {{ contacts.total }} contacts
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              :disabled="!contacts.prev_page_url"
              @click="goToPage(contacts.current_page - 1)"
            >
              <ChevronLeft class="h-4 w-4" />
              Previous
            </Button>

            <div class="flex items-center gap-1">
              <Button
                v-for="page in paginationPages"
                :key="page"
                :variant="page === contacts.current_page ? 'default' : 'outline'"
                size="sm"
                @click="goToPage(page)"
                class="w-10"
              >
                {{ page }}
              </Button>
            </div>

            <Button
              variant="outline"
              size="sm"
              :disabled="!contacts.next_page_url"
              @click="goToPage(contacts.current_page + 1)"
            >
              Next
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="showDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Contact</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete {{ contactToDelete?.full_name }}?
            This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            @click="deleteContact"
            class="bg-red-600 hover:bg-red-700"
          >
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Bulk Delete Confirmation Dialog -->
    <AlertDialog v-model:open="showBulkDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Multiple Contacts</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete {{ selectedContacts.length }} contact(s)?
            This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            @click="bulkDeleteContacts"
            class="bg-red-600 hover:bg-red-700"
          >
            Delete All
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Import Modal -->
    <Dialog v-model:open="showImportModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Import Contacts</DialogTitle>
          <DialogDescription>
            Upload a CSV file to import multiple contacts at once.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <div class="grid w-full max-w-sm items-center gap-1.5">
            <Label for="import-file">CSV File</Label>
            <Input
              id="import-file"
              type="file"
              accept=".csv"
              @change="handleFileSelect"
            />
          </div>
          <div class="text-sm text-muted-foreground">
            <p>CSV should include columns: first_name, last_name, email, phone, job_title, firm_name</p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showImportModal = false">
            Cancel
          </Button>
          <Button
            @click="importContacts"
            :disabled="!selectedFile || importing"
          >
            <Upload class="mr-2 h-4 w-4" />
            {{ importing ? 'Importing...' : 'Import' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
