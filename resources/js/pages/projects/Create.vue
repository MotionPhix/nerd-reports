<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
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
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import {
  ArrowLeft,
  Briefcase,
  User,
  Building,
  Calendar as CalendarIcon,
  Clock,
  FileText,
  Settings,
  Plus,
  Save,
  X,
  AlertCircle,
  CheckCircle,
  Eye
} from 'lucide-vue-next'
import AppSidebarLayout from '@/layouts/AppLayout.vue'

// Types
interface Contact {
  uuid: string
  first_name: string
  last_name: string
  full_name: string
  primary_email: string | null
  job_title: string | null
  firm?: {
    uuid: string
    name: string
  }
}

interface Firm {
  uuid: string
  name: string
}

interface Props {
  contacts: Contact[]
  preselectedContact?: string
  preselectedFirm?: string
}

// Props
const props = defineProps<Props>()

// Form state
const form = useForm({
  name: '',
  description: '',
  contact_id: props.preselectedContact || '',
  status: 'in_progress',
  due_date: '',
  deadline: '',
  estimated_hours: '',
  priority: 'medium',
  is_billable: true,
  hourly_rate: '',
  budget: '',
  notes: '',
  tags: [] as string[],
  send_notification: true
})

// Reactive state
const isSubmitting = ref(false)
const newTag = ref('')
const dueDateOpen = ref(false)
const deadlineOpen = ref(false)

// Computed properties
const selectedContact = computed(() => {
  return props.contacts.find(contact => contact.uuid === form.contact_id)
})

const selectedFirm = computed(() => {
  if (selectedContact.value?.firm) {
    return selectedContact.value.firm
  }
  return null
})

const projectInitials = computed(() => {
  if (!form.name) return 'NP'
  return form.name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const statusOptions = [
  { value: 'in_progress', label: 'In Progress', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' },
  { value: 'approved', label: 'Approved', color: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
  { value: 'completed', label: 'Completed', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400' },
  { value: 'cancelled', label: 'Cancelled', color: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }
]

const priorityOptions = [
  { value: 'low', label: 'Low', color: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
  { value: 'medium', label: 'Medium', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' },
  { value: 'high', label: 'High', color: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }
]

const selectedStatus = computed(() => {
  return statusOptions.find(option => option.value === form.status)
})

const selectedPriority = computed(() => {
  return priorityOptions.find(option => option.value === form.priority)
})

// Methods
const formatDate = (date: Date) => {
  return date.toISOString().split('T')[0]
}

const formatDisplayDate = (dateString: string) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const addTag = () => {
  const tag = newTag.value.trim()
  if (tag && !form.tags.includes(tag)) {
    form.tags.push(tag)
    newTag.value = ''
  }
}

const removeTag = (index: number) => {
  form.tags.splice(index, 1)
}

const handleTagKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    addTag()
  }
}

const submitForm = () => {
  isSubmitting.value = true

  form.post(route('projects.store'), {
    onSuccess: () => {
      toast.success('Project created successfully!')
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
      toast.error('Please check the form for errors')
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  console.log('Project create page mounted')
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
              <Button variant="link" size="sm" as="a" :href="route('projects.index')" class="gap-2">
                <ArrowLeft class="h-4 w-4" />
                Back to Projects
              </Button>
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              Create New Project
            </h1>

            <p class="text-muted-foreground mt-1">
              Set up a new project with all the necessary details
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button
            @click="submitForm"
            :disabled="isSubmitting || !form.name || !form.contact_id"
            class="gap-2">
            <Save class="h-4 w-4" />
            {{ isSubmitting ? 'Creating...' : 'Create Project' }}
          </Button>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Main Information -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Briefcase class="h-5 w-5" />
                  Project Details
                </CardTitle>
                <CardDescription>
                  Enter the basic information about your project
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="name">
                    Project Name <span class="text-red-500">*</span>
                  </Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    placeholder="Enter project name"
                    :class="{ 'border-red-500': form.errors.name }"
                    required
                  />
                  <p v-if="form.errors.name" class="text-sm text-red-600">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="description">Project Description</Label>
                  <Textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Describe the project goals, scope, and requirements"
                    rows="4"
                    :class="{ 'border-red-500': form.errors.description }"
                  />
                  <p v-if="form.errors.description" class="text-sm text-red-600">
                    {{ form.errors.description }}
                  </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="status">Status</Label>
                    <Select v-model="form.status">
                      <SelectTrigger class="w-full" :class="{ 'border-red-500': form.errors.status }">
                        <SelectValue placeholder="Select project status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="status in statusOptions" :key="status.value" :value="status.value">
                          <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" :class="status.color.split(' ')[0]"></div>
                            {{ status.label }}
                          </div>
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.status" class="text-sm text-red-600">
                      {{ form.errors.status }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="priority">Priority</Label>
                    <Select v-model="form.priority">
                      <SelectTrigger class="w-full" :class="{ 'border-red-500': form.errors.priority }">
                        <SelectValue placeholder="Select priority" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
                          <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" :class="priority.color.split(' ')[0]"></div>
                            {{ priority.label }}
                          </div>
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.priority" class="text-sm text-red-600">
                      {{ form.errors.priority }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Client Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="h-5 w-5" />
                  Client Information
                </CardTitle>
                <CardDescription>
                  Select the contact and firm for this project
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="contact_id">
                    Contact <span class="text-red-500">*</span>
                  </Label>
                  <Select v-model="form.contact_id">
                    <SelectTrigger class="w-full" :class="{ 'border-red-500': form.errors.contact_id }">
                      <SelectValue placeholder="Select a contact" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="contact in contacts" :key="contact.uuid" :value="contact.uuid">
                        <div class="flex items-center gap-3">
                          <Avatar class="h-6 w-6">
                            <AvatarFallback class="text-xs">
                              {{ contact.first_name.charAt(0) }}{{ contact.last_name.charAt(0) }}
                            </AvatarFallback>
                          </Avatar>
                          <div>
                            <div class="font-medium">{{ contact.full_name }}</div>
                            <div class="text-xs text-muted-foreground">
                              {{ contact.job_title }}
                              <span v-if="contact.firm"> • {{ contact.firm.name }}</span>
                            </div>
                          </div>
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.contact_id" class="text-sm text-red-600">
                    {{ form.errors.contact_id }}
                  </p>
                </div>

                <div v-if="selectedContact" class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="flex items-center gap-3">
                    <Avatar>
                      <AvatarFallback>
                        {{ selectedContact.first_name.charAt(0) }}{{ selectedContact.last_name.charAt(0) }}
                      </AvatarFallback>
                    </Avatar>
                    <div>
                      <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ selectedContact.full_name }}</h4>
                      <p v-if="selectedContact.job_title" class="text-sm text-muted-foreground">{{ selectedContact.job_title }}</p>
                      <p v-if="selectedContact.primary_email" class="text-sm text-blue-600 dark:text-blue-400">{{ selectedContact.primary_email }}</p>
                    </div>
                  </div>
                  <div v-if="selectedFirm" class="mt-3 pt-3 border-t">
                    <div class="flex items-center gap-2">
                      <Building class="h-4 w-4 text-muted-foreground" />
                      <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ selectedFirm.name }}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Timeline & Budget -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Clock class="h-5 w-5" />
                  Timeline & Budget
                </CardTitle>
                <CardDescription>
                  Set project dates, budget, and billing information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <!-- Due Date -->
                  <div class="space-y-2">
                    <Label for="due_date">Due Date</Label>
                    <Popover v-model:open="dueDateOpen">
                      <PopoverTrigger asChild>
                        <Button
                          variant="outline"
                          class="w-full justify-start text-left font-normal"
                          :class="{ 'border-red-500': form.errors.due_date }"
                        >
                          <CalendarIcon class="mr-2 h-4 w-4" />
                          {{ form.due_date ? formatDisplayDate(form.due_date) : 'Select due date' }}
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent class="w-auto p-0" align="start">
                        <Calendar
                          v-model="form.due_date"
                          @update:model-value="dueDateOpen = false"
                          :formatter="formatDate"
                        />
                      </PopoverContent>
                    </Popover>
                    <p v-if="form.errors.due_date" class="text-sm text-red-600">
                      {{ form.errors.due_date }}
                    </p>
                  </div>

                  <!-- Deadline -->
                  <div class="space-y-2">
                    <Label for="deadline">Hard Deadline</Label>
                    <Popover v-model:open="deadlineOpen">
                      <PopoverTrigger asChild>
                        <Button
                          variant="outline"
                          class="w-full justify-start text-left font-normal"
                          :class="{ 'border-red-500': form.errors.deadline }"
                        >
                          <CalendarIcon class="mr-2 h-4 w-4" />
                          {{ form.deadline ? formatDisplayDate(form.deadline) : 'Select deadline' }}
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent class="w-auto p-0" align="start">
                        <Calendar
                          v-model="form.deadline"
                          @update:model-value="deadlineOpen = false"
                          :formatter="formatDate"
                        />
                      </PopoverContent>
                    </Popover>
                    <p v-if="form.errors.deadline" class="text-sm text-red-600">
                      {{ form.errors.deadline }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="estimated_hours">Estimated Hours</Label>
                    <Input
                      id="estimated_hours"
                      type="number"
                      v-model="form.estimated_hours"
                      placeholder="0"
                      min="0"
                      step="0.5"
                      :class="{ 'border-red-500': form.errors.estimated_hours }"
                    />
                    <p v-if="form.errors.estimated_hours" class="text-sm text-red-600">
                      {{ form.errors.estimated_hours }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="budget">Project Budget ($)</Label>
                    <Input
                      id="budget"
                      type="number"
                      v-model="form.budget"
                      placeholder="0.00"
                      min="0"
                      step="0.01"
                      :class="{ 'border-red-500': form.errors.budget }"
                    />
                    <p v-if="form.errors.budget" class="text-sm text-red-600">
                      {{ form.errors.budget }}
                    </p>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="flex items-center space-x-2">
                    <Checkbox id="is_billable" v-model:checked="form.is_billable" />
                    <Label for="is_billable" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                      This is a billable project
                    </Label>
                  </div>

                  <div v-if="form.is_billable" class="space-y-2">
                    <Label for="hourly_rate">Hourly Rate ($)</Label>
                    <Input
                      id="hourly_rate"
                      type="number"
                      v-model="form.hourly_rate"
                      placeholder="0.00"
                      min="0"
                      step="0.01"
                      :class="{ 'border-red-500': form.errors.hourly_rate }"
                    />
                    <p v-if="form.errors.hourly_rate" class="text-sm text-red-600">
                      {{ form.errors.hourly_rate }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Additional Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="h-5 w-5" />
                  Additional Information
                </CardTitle>
                <CardDescription>
                  Add notes and tags for better organization
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="notes">Project Notes</Label>
                  <Textarea
                    id="notes"
                    v-model="form.notes"
                    placeholder="Add any additional notes, requirements, or special instructions"
                    rows="4"
                    :class="{ 'border-red-500': form.errors.notes }"
                  />
                  <p v-if="form.errors.notes" class="text-sm text-red-600">
                    {{ form.errors.notes }}
                  </p>
                </div>

                <!-- Tags -->
                <div class="space-y-2">
                  <Label>Tags</Label>
                  <div class="flex gap-2">
                    <Input
                      v-model="newTag"
                      placeholder="Enter a tag"
                      @keydown="handleTagKeydown"
                      class="flex-1"
                    />
                    <Button type="button" variant="outline" @click="addTag" :disabled="!newTag.trim()">
                      <Plus class="h-4 w-4" />
                    </Button>
                  </div>

                  <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
                    <Badge
                      v-for="(tag, index) in form.tags"
                      :key="index"
                      variant="secondary"
                      class="gap-1"
                    >
                      {{ tag }}
                      <button
                        type="button"
                        @click="removeTag(index)"
                        class="ml-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full p-0.5"
                      >
                        <X class="h-3 w-3" />
                      </button>
                    </Badge>
                  </div>

                  <p v-if="form.errors.tags" class="text-sm text-red-600">
                    {{ form.errors.tags }}
                  </p>
                </div>

                <!-- Notification Settings -->
                <div class="space-y-4 pt-4 border-t">
                  <div class="flex items-center space-x-2">
                    <Checkbox id="send_notification" v-model:checked="form.send_notification" />
                    <Label for="send_notification" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                      Send notification to client about project creation
                    </Label>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Project Preview -->
            <Card>
              <CardHeader>
                <CardTitle>Project Preview</CardTitle>
                <CardDescription>
                  How this project will appear
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div class="flex items-center gap-3 p-3 border rounded-lg">
                    <div class="rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 p-3 text-white">
                      <span class="font-bold text-sm">{{ projectInitials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">
                        {{ form.name || 'Project Name' }}
                      </h4>
                      <p v-if="selectedContact" class="text-sm text-muted-foreground truncate">
                        {{ selectedContact.full_name }}
                      </p>
                      <p v-if="selectedFirm" class="text-xs text-muted-foreground truncate">
                        {{ selectedFirm.name }}
                      </p>
                    </div>
                  </div>

                  <div class="space-y-2">
                    <div v-if="selectedStatus" class="flex items-center gap-2">
                      <span class="text-sm text-muted-foreground">Status:</span>
                      <Badge :class="selectedStatus.color">{{ selectedStatus.label }}</Badge>
                    </div>

                    <div v-if="selectedPriority" class="flex items-center gap-2">
                      <span class="text-sm text-muted-foreground">Priority:</span>
                      <Badge :class="selectedPriority.color">{{ selectedPriority.label }}</Badge>
                    </div>

                    <div v-if="form.due_date" class="flex items-center gap-2">
                      <span class="text-sm text-muted-foreground">Due:</span>
                      <span class="text-sm text-gray-900 dark:text-gray-100">{{ formatDisplayDate(form.due_date) }}</span>
                    </div>

                    <div v-if="form.budget" class="flex items-center gap-2">
                      <span class="text-sm text-muted-foreground">Budget:</span>
                      <span class="text-sm text-gray-900 dark:text-gray-100">${{ form.budget }}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent class="space-y-2">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  class="w-full justify-start gap-2"
                  as="a"
                  :href="route('contacts.create')"
                >
                  <User class="h-4 w-4" />
                  Create New Contact
                </Button>

                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  class="w-full justify-start gap-2"
                  as="a"
                  :href="route('firms.create')"
                >
                  <Building class="h-4 w-4" />
                  Create New Firm
                </Button>

                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  class="w-full justify-start gap-2"
                  as="a"
                  :href="route('projects.index')"
                >
                  <Eye class="h-4 w-4" />
                  View All Projects
                </Button>
              </CardContent>
            </Card>

            <!-- Tips -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <AlertCircle class="h-5 w-5" />
                  Tips
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-3 text-sm text-muted-foreground">
                <div class="flex items-start gap-2">
                  <CheckCircle class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <p>Choose a clear, descriptive project name that reflects the work scope.</p>
                </div>
                <div class="flex items-start gap-2">
                  <CheckCircle class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <p>Set realistic due dates and deadlines to manage expectations.</p>
                </div>
                <div class="flex items-start gap-2">
                  <CheckCircle class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <p>Use tags to categorize projects for better organization and filtering.</p>
                </div>
                <div class="flex items-start gap-2">
                  <CheckCircle class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <p>Enable notifications to keep clients informed about project progress.</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
