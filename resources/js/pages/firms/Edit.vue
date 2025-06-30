<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import { router, useForm } from '@inertiajs/vue3'
import { useDark } from '@vueuse/core'
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
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge'
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
  Building2,
  ArrowLeft,
  Save,
  X,
  Plus,
  Globe,
  Mail,
  Phone,
  MapPin,
  Linkedin,
  Twitter,
  Facebook,
  Upload,
  Image as ImageIcon,
  AlertCircle,
  CheckCircle2,
  Loader2,
  Trash2,
  Eye,
  RefreshCw
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
  linkedin_url: string | null
  twitter_url: string | null
  facebook_url: string | null
  total_revenue: number | null
  notes: string | null
  created_at: string
  updated_at: string
  logo_url: string | null
  logo_thumbnail_url: string | null
  primary_email: string | null
  primary_phone: string | null
  address: {
    id: number
    street: string | null
    city: string | null
    state: string | null
    country: string | null
    postal_code: string | null
    type: string
  } | null
  tags: Array<{
    id: number
    name: string
    type: string | null
  }>
}

interface Props {
  firm: Firm
  industries: string[]
  sizes: string[]
}

// Props
const props = defineProps<Props>()

// Reactive state
const isDark = useDark()
const isSubmitting = ref(false)
const isDeleting = ref(false)
const logoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(props.firm.logo_url)

// Form data - initialize with existing firm data
const form = useForm({
  name: props.firm.name || '',
  slogan: props.firm.slogan || '',
  url: props.firm.url || '',
  description: props.firm.description || '',
  industry: props.firm.industry || '',
  size: props.firm.size || '',
  status: props.firm.status || 'prospect',
  priority: props.firm.priority || 'medium',
  source: props.firm.source || '',

  // Address
  address: {
    street: props.firm.address?.street || '',
    city: props.firm.address?.city || '',
    state: props.firm.address?.state || '',
    country: props.firm.address?.country || '',
    postal_code: props.firm.address?.postal_code || ''
  },

  // Contact information
  email: props.firm.primary_email || '',
  phone: props.firm.primary_phone || '',

  // Social media
  linkedin_url: props.firm.linkedin_url || '',
  twitter_url: props.firm.twitter_url || '',
  facebook_url: props.firm.facebook_url || '',

  // Additional
  notes: props.firm.notes || '',
  tags: props.firm.tags?.map(tag => tag.name) || [] as string[],

  // Revenue (optional)
  total_revenue: props.firm.total_revenue || null as number | null,
})

// Computed properties
const hasAddressData = computed(() => {
  return form.address.street || form.address.city || form.address.state || form.address.country
})

const hasContactData = computed(() => {
  return form.email || form.phone
})

const hasSocialMedia = computed(() => {
  return form.linkedin_url || form.twitter_url || form.facebook_url
})

const formProgress = computed(() => {
  const requiredFields = ['name', 'status']
  const optionalSections = [
    form.slogan,
    form.url,
    form.description,
    form.industry,
    hasAddressData.value,
    hasContactData.value,
    hasSocialMedia.value
  ]

  const requiredComplete = requiredFields.every(field => form[field])
  const optionalComplete = optionalSections.filter(Boolean).length

  const progress = requiredComplete ? 30 + (optionalComplete * 10) : (optionalComplete * 4)
  return Math.min(progress, 100)
})

const hasChanges = computed(() => {
  return form.isDirty
})

// Utility functions
const handleLogoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    if (file.size > 5 * 1024 * 1024) { // 5MB limit
      toast.error('Logo file size must be less than 5MB')
      return
    }

    if (!file.type.startsWith('image/')) {
      toast.error('Please select a valid image file')
      return
    }

    logoFile.value = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeLogo = () => {
  logoFile.value = null
  logoPreview.value = null
  const input = document.getElementById('logo-upload') as HTMLInputElement
  if (input) input.value = ''
}

const addTag = (tag: string) => {
  if (tag && !form.tags.includes(tag)) {
    form.tags.push(tag)
  }
}

const removeTag = (index: number) => {
  form.tags.splice(index, 1)
}

const formatUrl = (url: string) => {
  if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
    return 'https://' + url
  }
  return url
}

// Actions
const submitForm = async () => {
  isSubmitting.value = true

  try {
    // Format URLs
    if (form.url) form.url = formatUrl(form.url)
    if (form.linkedin_url) form.linkedin_url = formatUrl(form.linkedin_url)
    if (form.twitter_url) form.twitter_url = formatUrl(form.twitter_url)
    if (form.facebook_url) form.facebook_url = formatUrl(form.facebook_url)

    // Clean up empty address
    if (!hasAddressData.value) {
      form.address = {
        street: '',
        city: '',
        state: '',
        country: '',
        postal_code: ''
      }
    }

    await form.put(route('firms.update', props.firm.uuid), {
      onSuccess: (page) => {
        toast.success('Firm updated successfully!')
        // The controller will redirect to the firm show page
      },
      onError: (errors) => {
        console.error('Form errors:', errors)
        toast.error('Please check the form for errors')
      }
    })
  } catch (error) {
    console.error('Submit error:', error)
    toast.error('Failed to update firm. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

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

const resetForm = () => {
  form.reset()
  logoPreview.value = props.firm.logo_url
  logoFile.value = null
  toast.success('Form reset to original values')
}

const saveDraft = () => {
  // Save to localStorage as draft
  localStorage.setItem(`firm_edit_draft_${props.firm.uuid}`, JSON.stringify(form.data()))
  toast.success('Draft saved locally')
}

const loadDraft = () => {
  const draft = localStorage.getItem(`firm_edit_draft_${props.firm.uuid}`)
  if (draft) {
    try {
      const draftData = JSON.parse(draft)
      Object.assign(form, draftData)
      toast.success('Draft loaded')
    } catch (error) {
      toast.error('Failed to load draft')
    }
  }
}

const clearDraft = () => {
  localStorage.removeItem(`firm_edit_draft_${props.firm.uuid}`)
  toast.success('Draft cleared')
}

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  // Check for existing draft
  const draft = localStorage.getItem(`firm_edit_draft_${props.firm.uuid}`)
  if (draft) {
    toast('Draft found', {
      description: 'Would you like to load your previous draft?',
      action: {
        label: 'Load Draft',
        onClick: loadDraft
      }
    })
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
          <Button variant="ghost" size="sm" as="a" :href="route('firms.show', firm.uuid)" class="gap-2">
            <ArrowLeft class="w-4 h-4" />
            Back to Firm
          </Button>
          <Separator orientation="vertical" class="h-6" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              Edit {{ firm.name }}
            </h1>
            <p class="mt-1 text-muted-foreground">
              Update firm information and settings
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button variant="outline" size="sm" as="a" :href="route('firms.show', firm.uuid)" class="gap-2">
            <Eye class="w-4 h-4" />
            View Firm
          </Button>

          <Button variant="outline" size="sm" @click="saveDraft" class="gap-2">
            <Save class="w-4 h-4" />
            Save Draft
          </Button>

          <Button variant="outline" size="sm" @click="resetForm" :disabled="!hasChanges" class="gap-2">
            <RefreshCw class="w-4 h-4" />
            Reset
          </Button>
        </div>
      </div>

      <!-- Progress Indicator -->
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Completion</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-muted-foreground">{{ formProgress }}% complete</span>
              <Badge v-if="hasChanges" variant="secondary" class="text-xs">
                Unsaved Changes
              </Badge>
            </div>
          </div>
          <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
            <div
              class="h-2 transition-all duration-300 bg-blue-600 rounded-full"
              :style="{ width: `${formProgress}%` }"
            ></div>
          </div>
        </CardContent>
      </Card>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Main Information -->
          <div class="space-y-6 lg:col-span-2">
            <!-- Basic Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Building2 class="w-5 h-5" />
                  Basic Information
                </CardTitle>
                <CardDescription>
                  Essential details about the firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="name">Firm Name *</Label>
                    <Input
                      id="name"
                      v-model="form.name"
                      placeholder="Enter firm name"
                      :class="{ 'border-red-500': form.errors.name }"
                      required
                    />
                    <p v-if="form.errors.name" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors.name }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="slogan">Slogan / Tagline</Label>
                    <Input
                      id="slogan"
                      v-model="form.slogan"
                      placeholder="Enter firm slogan"
                      :class="{ 'border-red-500': form.errors.slogan }"
                    />
                    <p v-if="form.errors.slogan" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors.slogan }}
                    </p>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="url">Website URL</Label>
                  <div class="relative">
                    <Globe class="absolute w-4 h-4 left-3 top-3 text-muted-foreground" />
                    <Input
                      id="url"
                      v-model="form.url"
                      placeholder="https://example.com"
                      class="pl-10"
                      :class="{ 'border-red-500': form.errors.url }"
                    />
                  </div>
                  <p v-if="form.errors.url" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.url }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="description">Description</Label>
                  <Textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Describe the firm's business, services, or focus areas..."
                    rows="4"
                    :class="{ 'border-red-500': form.errors.description }"
                  />
                  <p v-if="form.errors.description" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.description }}
                  </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="industry">Industry</Label>
                    <Select v-model="form.industry">
                      <SelectTrigger :class="{ 'border-red-500': form.errors.industry }">
                        <SelectValue placeholder="Select industry" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="industry in industries" :key="industry" :value="industry">
                          {{ industry }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.industry" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors.industry }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="size">Company Size</Label>
                    <Select v-model="form.size">
                      <SelectTrigger :class="{ 'border-red-500': form.errors.size }">
                        <SelectValue placeholder="Select size" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="size in sizes" :key="size" :value="size">
                          {{ size }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.size" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors.size }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Contact Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Mail class="w-5 h-5" />
                  Contact Information
                </CardTitle>
                <CardDescription>
                  Primary contact details for the firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="email">Primary Email</Label>
                    <div class="relative">
                      <Mail class="absolute w-4 h-4 left-3 top-3 text-muted-foreground" />
                      <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="contact@firm.com"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.email }"
                      />
                    </div>
                    <p v-if="form.errors.email" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors.email }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="phone">Primary Phone</Label>
                    <div class="relative">
                      <Phone class="absolute w-4 h-4 left-3 top-3 text-muted-foreground" />
                      <Input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="+1 (555) 123-4567"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.phone }"
                      />
                    </div>
                    <p v-if="form.errors.phone" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors.phone }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Address -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <MapPin class="w-5 h-5" />
                  Address
                </CardTitle>
                <CardDescription>
                  Physical location of the firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="street">Street Address</Label>
                  <Input
                    id="street"
                    v-model="form.address.street"
                    placeholder="123 Business Ave"
                    :class="{ 'border-red-500': form.errors['address.street'] }"
                  />
                  <p v-if="form.errors['address.street']" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors['address.street'] }}
                  </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="city">City</Label>
                    <Input
                      id="city"
                      v-model="form.address.city"
                      placeholder="New York"
                      :class="{ 'border-red-500': form.errors['address.city'] }"
                    />
                    <p v-if="form.errors['address.city']" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors['address.city'] }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="state">State/Province</Label>
                    <Input
                      id="state"
                      v-model="form.address.state"
                      placeholder="NY"
                      :class="{ 'border-red-500': form.errors['address.state'] }"
                    />
                    <p v-if="form.errors['address.state']" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors['address.state'] }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="country">Country</Label>
                    <Input
                      id="country"
                      v-model="form.address.country"
                      placeholder="United States"
                      :class="{ 'border-red-500': form.errors['address.country'] }"
                    />
                    <p v-if="form.errors['address.country']" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors['address.country'] }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="postal_code">Postal Code</Label>
                    <Input
                      id="postal_code"
                      v-model="form.address.postal_code"
                      placeholder="10001"
                      :class="{ 'border-red-500': form.errors['address.postal_code'] }"
                    />
                    <p v-if="form.errors['address.postal_code']" class="flex items-center gap-1 text-sm text-red-600">
                      <AlertCircle class="w-3 h-3" />
                      {{ form.errors['address.postal_code'] }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Social Media -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Globe class="w-5 h-5" />
                  Social Media
                </CardTitle>
                <CardDescription>
                  Social media profiles and online presence
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="linkedin_url">LinkedIn URL</Label>
                  <div class="relative">
                    <Linkedin class="absolute w-4 h-4 left-3 top-3 text-muted-foreground" />
                    <Input
                      id="linkedin_url"
                      v-model="form.linkedin_url"
                      placeholder="https://linkedin.com/company/firm-name"
                      class="pl-10"
                      :class="{ 'border-red-500': form.errors.linkedin_url }"
                    />
                  </div>
                  <p v-if="form.errors.linkedin_url" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.linkedin_url }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="twitter_url">Twitter URL</Label>
                  <div class="relative">
                    <Twitter class="absolute w-4 h-4 left-3 top-3 text-muted-foreground" />
                    <Input
                      id="twitter_url"
                      v-model="form.twitter_url"
                      placeholder="https://twitter.com/firmname"
                      class="pl-10"
                      :class="{ 'border-red-500': form.errors.twitter_url }"
                    />
                  </div>
                  <p v-if="form.errors.twitter_url" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.twitter_url }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="facebook_url">Facebook URL</Label>
                  <div class="relative">
                    <Facebook class="absolute w-4 h-4 left-3 top-3 text-muted-foreground" />
                    <Input
                      id="facebook_url"
                      v-model="form.facebook_url"
                      placeholder="https://facebook.com/firmname"
                      class="pl-10"
                      :class="{ 'border-red-500': form.errors.facebook_url }"
                    />
                  </div>
                  <p v-if="form.errors.facebook_url" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.facebook_url }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Additional Information -->
            <Card>
              <CardHeader>
                <CardTitle>Additional Information</CardTitle>
                <CardDescription>
                  Extra details and notes about the firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="total_revenue">Total Revenue (Optional)</Label>
                  <Input
                    id="total_revenue"
                    v-model="form.total_revenue"
                    type="number"
                    placeholder="1000000"
                    :class="{ 'border-red-500': form.errors.total_revenue }"
                  />
                  <p v-if="form.errors.total_revenue" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.total_revenue }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="source">Source</Label>
                  <Input
                    id="source"
                    v-model="form.source"
                    placeholder="How did you discover this firm?"
                    :class="{ 'border-red-500': form.errors.source }"
                  />
                  <p v-if="form.errors.source" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.source }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="notes">Notes</Label>
                  <Textarea
                    id="notes"
                    v-model="form.notes"
                    placeholder="Additional notes about the firm..."
                    rows="4"
                    :class="{ 'border-red-500': form.errors.notes }"
                  />
                  <p v-if="form.errors.notes" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.notes }}
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Status & Priority -->
            <Card>
              <CardHeader>
                <CardTitle>Status & Priority</CardTitle>
                <CardDescription>
                  Current status and priority level
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="status">Status *</Label>
                  <Select v-model="form.status">
                    <SelectTrigger :class="{ 'border-red-500': form.errors.status }">
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="prospect">
                        <div class="flex items-center gap-2">
                          <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                          Prospect
                        </div>
                      </SelectItem>
                      <SelectItem value="active">
                        <div class="flex items-center gap-2">
                          <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                          Active
                        </div>
                      </SelectItem>
                      <SelectItem value="inactive">
                        <div class="flex items-center gap-2">
                          <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                          Inactive
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.status" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.status }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="priority">Priority</Label>
                  <Select v-model="form.priority">
                    <SelectTrigger :class="{ 'border-red-500': form.errors.priority }">
                      <SelectValue placeholder="Select priority" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="low">
                        <div class="flex items-center gap-2">
                          <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                          Low
                        </div>
                      </SelectItem>
                      <SelectItem value="medium">
                        <div class="flex items-center gap-2">
                          <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                          Medium
                        </div>
                      </SelectItem>
                      <SelectItem value="high">
                        <div class="flex items-center gap-2">
                          <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                          High
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.priority" class="flex items-center gap-1 text-sm text-red-600">
                    <AlertCircle class="w-3 h-3" />
                    {{ form.errors.priority }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Logo Upload -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <ImageIcon class="w-5 h-5" />
                  Logo
                </CardTitle>
                <CardDescription>
                  Upload or update the firm's logo
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex flex-col items-center space-y-4">
                  <div v-if="logoPreview" class="relative">
                    <img
                      :src="logoPreview"
                      alt="Logo preview"
                      class="object-contain w-24 h-24 border border-gray-200 rounded-lg dark:border-gray-700"
                    />
                    <Button
                      type="button"
                      variant="destructive"
                      size="sm"
                      class="absolute w-6 h-6 p-0 rounded-full -top-2 -right-2"
                      @click="removeLogo"
                    >
                      <X class="w-3 h-3" />
                    </Button>
                  </div>

                  <div class="w-full">
                    <input
                      id="logo-upload"
                      type="file"
                      accept="image/*"
                      class="hidden"
                      @change="handleLogoUpload"
                    />
                    <Button
                      type="button"
                      variant="outline"
                      class="w-full gap-2"
                      @click="() => document.getElementById('logo-upload')?.click()"
                    >
                      <Upload class="w-4 h-4" />
                      {{ logoPreview ? 'Change Logo' : 'Upload Logo' }}
                    </Button>
                  </div>

                  <p class="text-xs text-center text-muted-foreground">
                    Recommended: Square image, max 5MB
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Tags -->
            <Card>
              <CardHeader>
                <CardTitle>Tags</CardTitle>
                <CardDescription>
                  Add tags to categorize this firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="(tag, index) in form.tags"
                    :key="index"
                    variant="secondary"
                    class="flex items-center gap-1"
                  >
                    {{ tag }}
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      class="w-3 h-3 p-0 hover:bg-transparent"
                      @click="removeTag(index)"
                    >
                      <X class="w-2 h-2" />
                    </Button>
                  </Badge>
                </div>

                <div class="flex gap-2">
                  <Input
                    id="new-tag"
                    placeholder="Add a tag..."
                    @keydown.enter.prevent="(e) => {
                      const target = e.target as HTMLInputElement;
                      addTag(target.value);
                      target.value = '';
                    }"
                  />
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="() => {
                      const input = document.getElementById('new-tag') as HTMLInputElement;
                      addTag(input.value);
                      input.value = '';
                    }"
                  >
                    <Plus class="w-4 h-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>

            <!-- Actions -->
            <Card>
              <CardHeader>
                <CardTitle>Actions</CardTitle>
                <CardDescription>
                  Manage this firm record
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-3">
                <Button
                  type="submit"
                  class="w-full gap-2"
                  :disabled="isSubmitting || !hasChanges"
                >
                  <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                  <Save v-else class="w-4 h-4" />
                  {{ isSubmitting ? 'Updating...' : 'Update Firm' }}
                </Button>

                <Separator />

                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button variant="destructive" class="w-full gap-2" :disabled="isDeleting">
                      <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                      <Trash2 v-else class="w-4 h-4" />
                      {{ isDeleting ? 'Deleting...' : 'Delete Firm' }}
                    </Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                      <AlertDialogDescription>
                        This action cannot be undone. This will permanently delete the firm
                        "{{ firm.name }}" and remove all associated data from our servers.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction
                        class="bg-red-600 hover:bg-red-700"
                        @click="deleteFirm"
                      >
                        Delete Firm
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles for better form presentation */
.logo-preview {
  transition: all 0.2s ease-in-out;
}

.logo-preview:hover {
  transform: scale(1.05);
}

.action-button {
  transition: all 0.2s ease-in-out;
}

.action-button:hover {
  transform: scale(1.05);
}

.error-message {
  animation: slideInFromLeft 0.2s ease-in-out;
}

.success-indicator {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes slideInFromLeft {
  from {
    opacity: 0;
    transform: translateX(-8px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
