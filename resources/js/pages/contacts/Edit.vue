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
  ArrowLeft,
  User,
  Mail,
  Phone,
  Briefcase,
  Building,
  Upload,
  X,
  Plus,
  Save,
  Trash2,
  Eye,
  RefreshCw
} from 'lucide-vue-next'
import AppSidebarLayout from '@/layouts/AppLayout.vue'

// Types
interface Firm {
  uuid: string
  name: string
}

interface Contact {
  uuid: string
  first_name: string
  last_name: string
  middle_name: string | null
  nickname: string | null
  job_title: string | null
  title: string | null
  bio: string | null
  firm_id: string | null
  avatar_url: string | null
  avatar_thumbnail_url: string | null
  primary_email: string | null
  primary_phone_number: string | null
  full_name: string
  created_at: string
  updated_at: string
  firm?: {
    uuid: string
    name: string
  }
  tags: Array<{
    id: number
    name: string
    type: string | null
  }>
}

interface Props {
  contact: Contact
  firms: Firm[]
}

// Props
const props = defineProps<Props>()

// Form state
const form = useForm({
  first_name: props.contact.first_name,
  last_name: props.contact.last_name,
  middle_name: props.contact.middle_name || '',
  nickname: props.contact.nickname || '',
  job_title: props.contact.job_title || '',
  title: props.contact.title || '',
  bio: props.contact.bio || '',
  firm_id: props.contact.firm_id || '',
  email: props.contact.primary_email || '',
  phone: props.contact.primary_phone_number || '',
  avatar: null as File | null,
  tags: props.contact.tags.map(tag => tag.name),
  _method: 'PUT'
})

// Reactive state
const isSubmitting = ref(false)
const isDeleting = ref(false)
const avatarPreview = ref<string | null>(null)
const newTag = ref('')
const fileInputRef = ref<HTMLInputElement | null>(null)

// Computed properties
const contactInitials = computed(() => {
  const first = form.first_name.charAt(0).toUpperCase()
  const last = form.last_name.charAt(0).toUpperCase()
  return first + last || 'CN'
})

const selectedFirm = computed(() => {
  return props.firms.find(firm => firm.uuid === form.firm_id)
})

const currentAvatarUrl = computed(() => {
  return avatarPreview.value || props.contact.avatar_url
})

const hasChanges = computed(() => {
  return form.isDirty || avatarPreview.value !== null
})

// Methods
const handleAvatarUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      toast.error('Please select a valid image file')
      return
    }

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
      toast.error('Image size must be less than 2MB')
      return
    }

    form.avatar = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const triggerFileUpload = () => {
  fileInputRef.value?.click()
}

const removeAvatar = () => {
  form.avatar = null
  avatarPreview.value = null
  // Reset file input
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
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

  form.post(route('contacts.update', props.contact.uuid), {
    onSuccess: () => {
      toast.success('Contact updated successfully!')
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

const deleteContact = () => {
  isDeleting.value = true

  form.delete(route('contacts.destroy', props.contact.uuid), {
    onSuccess: () => {
      toast.success('Contact deleted successfully!')
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      toast.error('Failed to delete contact')
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  console.log('Contact edit page mounted for:', props.contact.full_name)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <Button variant="link" size="sm" as="a" :href="route('contacts.show', contact.uuid)" class="gap-2">
                <ArrowLeft class="h-4 w-4" />
                Back to Contact
              </Button>
              <Button variant="link" size="sm" as="a" :href="route('contacts.index')" class="gap-2">
                View All Contacts
              </Button>
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              Edit {{ contact.full_name }}
            </h1>

            <p class="text-muted-foreground mt-1">
              Update contact information and details
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button variant="outline" as="a" :href="route('contacts.show', contact.uuid)">
            <Eye class="h-4 w-4 mr-1" />
            View Contact
          </Button>

          <Button
            @click="submitForm"
            :disabled="isSubmitting || !form.first_name || !form.last_name"
            class="gap-2">
            <Save class="h-4 w-4" />
            {{ isSubmitting ? 'Updating...' : 'Update Contact' }}
          </Button>

          <AlertDialog>
            <AlertDialogTrigger asChild>
              <Button variant="destructive" size="sm" class="gap-2">
                <Trash2 class="h-4 w-4" />
                Delete
              </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Delete Contact</AlertDialogTitle>
                <AlertDialogDescription>
                  Are you sure you want to delete "{{ contact.full_name }}"? This action cannot be undone
                  and will remove all associated data including interactions and project assignments.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="deleteContact" :disabled="isDeleting" class="bg-red-600 hover:bg-red-700">
                  {{ isDeleting ? 'Deleting...' : 'Delete Contact' }}
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>
      </div>

      <!-- Unsaved Changes Warning -->
      <div v-if="hasChanges" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex items-center gap-2">
          <RefreshCw class="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
          <p class="text-sm text-yellow-800 dark:text-yellow-200">
            You have unsaved changes. Don't forget to save your updates.
          </p>
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
                  <User class="h-5 w-5" />
                  Basic Information
                </CardTitle>
                <CardDescription>
                  Update the contact's personal details
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="first_name">
                      First Name <span class="text-red-500">*</span>
                    </Label>
                    <Input
                      id="first_name"
                      v-model="form.first_name"
                      placeholder="Enter first name"
                      :class="{ 'border-red-500': form.errors.first_name }"
                      required
                    />
                    <p v-if="form.errors.first_name" class="text-sm text-red-600">
                      {{ form.errors.first_name }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="last_name">
                      Last Name <span class="text-red-500">*</span>
                    </Label>
                    <Input
                      id="last_name"
                      v-model="form.last_name"
                      placeholder="Enter last name"
                      :class="{ 'border-red-500': form.errors.last_name }"
                      required
                    />
                    <p v-if="form.errors.last_name" class="text-sm text-red-600">
                      {{ form.errors.last_name }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="middle_name">Middle Name</Label>
                    <Input
                      id="middle_name"
                      v-model="form.middle_name"
                      placeholder="Enter middle name"
                      :class="{ 'border-red-500': form.errors.middle_name }"
                    />
                    <p v-if="form.errors.middle_name" class="text-sm text-red-600">
                      {{ form.errors.middle_name }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="nickname">Nickname</Label>
                    <Input
                      id="nickname"
                      v-model="form.nickname"
                      placeholder="Enter nickname"
                      :class="{ 'border-red-500': form.errors.nickname }"
                    />
                    <p v-if="form.errors.nickname" class="text-sm text-red-600">
                      {{ form.errors.nickname }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input
                      id="title"
                      v-model="form.title"
                      placeholder="e.g., Mr., Ms., Dr."
                      :class="{ 'border-red-500': form.errors.title }"
                    />
                    <p v-if="form.errors.title" class="text-sm text-red-600">
                      {{ form.errors.title }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="job_title">Job Title</Label>
                    <Input
                      id="job_title"
                      v-model="form.job_title"
                      placeholder="e.g., CEO, Manager, Developer"
                      :class="{ 'border-red-500': form.errors.job_title }"
                    />
                    <p v-if="form.errors.job_title" class="text-sm text-red-600">
                      {{ form.errors.job_title }}
                    </p>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="bio">Bio</Label>
                  <Textarea
                    id="bio"
                    v-model="form.bio"
                    placeholder="Enter a brief description about this contact"
                    rows="4"
                    :class="{ 'border-red-500': form.errors.bio }"
                  />
                  <p v-if="form.errors.bio" class="text-sm text-red-600">
                    {{ form.errors.bio }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Contact Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Mail class="h-5 w-5" />
                  Contact Information
                </CardTitle>
                <CardDescription>
                  Update how to reach this contact
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="email">Email Address</Label>
                    <Input
                      id="email"
                      type="email"
                      v-model="form.email"
                      placeholder="Enter email address"
                      :class="{ 'border-red-500': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="text-sm text-red-600">
                      {{ form.errors.email }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="phone">Phone Number</Label>
                    <Input
                      id="phone"
                      type="tel"
                      v-model="form.phone"
                      placeholder="Enter phone number"
                      :class="{ 'border-red-500': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="text-sm text-red-600">
                      {{ form.errors.phone }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Organization -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Building class="h-5 w-5" />
                  Organization
                </CardTitle>
                <CardDescription>
                  Update firm association
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="firm_id">Firm</Label>
                  <Select v-model="form.firm_id">
                    <SelectTrigger :class="{ 'border-red-500': form.errors.firm_id }">
                      <SelectValue placeholder="Select a firm (optional)" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem :value="null">No firm selected</SelectItem>
                      <SelectItem v-for="firm in firms" :key="firm.uuid" :value="firm.uuid">
                        {{ firm.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.firm_id" class="text-sm text-red-600">
                    {{ form.errors.firm_id }}
                  </p>
                  <p v-if="selectedFirm" class="text-sm text-muted-foreground">
                    Selected: {{ selectedFirm.name }}
                  </p>
                  <p v-else-if="contact.firm" class="text-sm text-muted-foreground">
                    Previously: {{ contact.firm.name }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Tags -->
            <Card>
              <CardHeader>
                <CardTitle>Tags</CardTitle>
                <CardDescription>
                  Update tags to categorize this contact
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
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
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Avatar Upload -->
            <Card>
              <CardHeader>
                <CardTitle>Profile Picture</CardTitle>
                <CardDescription>
                  Update the profile picture for this contact
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex flex-col items-center space-y-4">
                  <Avatar class="h-24 w-24">
                    <AvatarImage v-if="currentAvatarUrl" :src="currentAvatarUrl" />
                    <AvatarFallback class="text-lg">
                      {{ contactInitials }}
                    </AvatarFallback>
                  </Avatar>

                  <div class="flex flex-col gap-2 w-full">
                    <input
                      ref="fileInputRef"
                      type="file"
                      accept="image/*"
                      @change="handleAvatarUpload"
                      class="hidden"
                    />
                    <Button
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="triggerFileUpload"
                      class="gap-2"
                    >
                      <Upload class="h-4 w-4" />
                      {{ currentAvatarUrl ? 'Change Image' : 'Upload Image' }}
                    </Button>

                    <Button
                      v-if="currentAvatarUrl"
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="removeAvatar"
                      class="gap-2 text-red-600 hover:text-red-700"
                    >
                      <X class="h-4 w-4" />
                      Remove
                    </Button>
                  </div>

                  <p class="text-xs text-muted-foreground text-center">
                    Supported formats: JPEG, PNG, GIF<br>
                    Maximum size: 2MB
                  </p>
                </div>

                <p v-if="form.errors.avatar" class="text-sm text-red-600">
                  {{ form.errors.avatar }}
                </p>
              </CardContent>
            </Card>

            <!-- Contact Preview -->
            <Card>
              <CardHeader>
                <CardTitle>Preview</CardTitle>
                <CardDescription>
                  How this contact will appear
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="flex items-center gap-3 p-3 border rounded-lg">
                  <Avatar>
                    <AvatarImage v-if="currentAvatarUrl" :src="currentAvatarUrl" />
                    <AvatarFallback>
                      {{ contactInitials }}
                    </AvatarFallback>
                  </Avatar>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">
                      {{ form.first_name || 'First' }} {{ form.last_name || 'Last' }}
                    </h4>
                    <p v-if="form.job_title" class="text-sm text-muted-foreground truncate">
                      {{ form.job_title }}
                    </p>
                    <p v-if="selectedFirm" class="text-xs text-muted-foreground truncate">
                      {{ selectedFirm.name }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Contact Info -->
            <Card>
              <CardHeader>
                <CardTitle>Contact Details</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="text-sm">
                  <span class="font-medium text-muted-foreground">Created:</span>
                  <p class="text-gray-900 dark:text-gray-100">{{ formatDate(contact.created_at) }}</p>
                </div>
                <div class="text-sm">
                  <span class="font-medium text-muted-foreground">Last Updated:</span>
                  <p class="text-gray-900 dark:text-gray-100">{{ formatDate(contact.updated_at) }}</p>
                </div>
                <div v-if="contact.firm" class="text-sm">
                  <span class="font-medium text-muted-foreground">Current Firm:</span>
                  <p class="text-gray-900 dark:text-gray-100">{{ contact.firm.name }}</p>
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
                  :href="route('contacts.show', contact.uuid)"
                >
                  <Eye class="h-4 w-4" />
                  View Contact
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
                  :href="route('contacts.index')"
                >
                  <User class="h-4 w-4" />
                  View All Contacts
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
