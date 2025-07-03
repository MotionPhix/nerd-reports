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
  Loader2
} from 'lucide-vue-next'
import AppSidebarLayout from '@/layouts/AppLayout.vue'

// Types
interface Props {
  industries: string[]
  sizes: string[]
}

// Props
const props = defineProps<Props>()

// Reactive state
const isDark = useDark()
const isSubmitting = ref(false)
const logoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)

// Form data
const form = useForm({
  name: '',
  slogan: '',
  url: '',
  description: '',
  industry: '',
  size: '',
  status: 'prospect',
  priority: 'medium',
  source: '',

  // Address
  address: {
    street: '',
    city: '',
    state: '',
    country: '',
    postal_code: ''
  },

  // Contact information
  email: '',
  phone: '',

  // Social media
  linkedin_url: '',
  twitter_url: '',
  facebook_url: '',

  // Additional
  notes: '',
  tags: [] as string[],

  // Revenue (optional)
  total_revenue: null as number | null,
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

    await form.post(route('firms.store'), {
      onSuccess: (page) => {
        toast.success('Firm created successfully!')
        // The controller will redirect to the firm show page
      },
      onError: (errors) => {
        console.error('Form errors:', errors)
        toast.error('Please check the form for errors')
      }
    })
  } catch (error) {
    console.error('Submit error:', error)
    toast.error('Failed to create firm. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

const saveDraft = () => {
  // Save to localStorage as draft
  localStorage.setItem('firm_draft', JSON.stringify(form.data()))
  toast.success('Draft saved locally')
}

const loadDraft = () => {
  const draft = localStorage.getItem('firm_draft')
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
  localStorage.removeItem('firm_draft')
  toast.success('Draft cleared')
}

// Layout
defineOptions({
  layout: AppSidebarLayout
})

// Lifecycle
onMounted(() => {
  // Check for existing draft
  const draft = localStorage.getItem('firm_draft')
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
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Button variant="ghost" size="sm" as="a" :href="route('firms.index')" class="gap-2">
            <ArrowLeft class="h-4 w-4" />
            Back to Firms
          </Button>
          <Separator orientation="vertical" class="h-6" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              Add New Firm
            </h1>
            <p class="text-muted-foreground mt-1">
              Create a new firm profile to start managing relationships
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button variant="outline" size="sm" @click="saveDraft" class="gap-2">
            <Save class="h-4 w-4" />
            Save Draft
          </Button>

          <Button variant="outline" size="sm" @click="clearDraft" class="gap-2">
            <X class="h-4 w-4" />
            Clear
          </Button>
        </div>
      </div>

      <!-- Progress Indicator -->
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Progress</span>
            <span class="text-sm text-muted-foreground">{{ formProgress }}% complete</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: `${formProgress}%` }"
            ></div>
          </div>
        </CardContent>
      </Card>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Main Information -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Building2 class="h-5 w-5" />
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
                    <p v-if="form.errors.name" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
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
                    <p v-if="form.errors.slogan" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.slogan }}
                    </p>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="description">Description</Label>
                  <Textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Brief description of the firm"
                    rows="3"
                    :class="{ 'border-red-500': form.errors.description }"
                  />
                  <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.description }}
                  </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                  <div class="space-y-2">
                    <Label for="industry">Industry</Label>
                    <Select v-model="form.industry">
                      <SelectTrigger :class="{ 'border-red-500': form.errors.industry }">
                        <SelectValue placeholder="Select industry" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="">Select industry</SelectItem>
                        <SelectItem v-for="industry in industries" :key="industry" :value="industry">
                          {{ industry }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.industry" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
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
                        <SelectItem value="">Select size</SelectItem>
                        <SelectItem v-for="size in sizes" :key="size" :value="size">
                          {{ size.charAt(0).toUpperCase() + size.slice(1) }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.size" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.size }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="url">Website</Label>
                    <div class="relative">
                      <Globe class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="url"
                        v-model="form.url"
                        placeholder="www.example.com"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.url }"
                      />
                    </div>
                    <p v-if="form.errors.url" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.url }}
                    </p>
                  </div>
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
                  Primary contact details for the firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="email">Email Address</Label>
                    <div class="relative">
                      <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="contact@example.com"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.email }"
                      />
                    </div>
                    <p v-if="form.errors.email" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.email }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="phone">Phone Number</Label>
                    <div class="relative">
                      <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="+1 (555) 123-4567"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.phone }"
                      />
                    </div>
                    <p v-if="form.errors.phone" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.phone }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Address Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <MapPin class="h-5 w-5" />
                  Address Information
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
                    placeholder="123 Main Street"
                    :class="{ 'border-red-500': form.errors['address.street'] }"
                  />
                  <p v-if="form.errors['address.street']" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
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
                    <p v-if="form.errors['address.city']" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
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
                    <p v-if="form.errors['address.state']" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
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
                    <p v-if="form.errors['address.country']" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
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
                    <p v-if="form.errors['address.postal_code']" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
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
                  <Linkedin class="h-5 w-5" />
                  Social Media
                </CardTitle>
                <CardDescription>
                  Social media profiles and online presence
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-4">
                  <div class="space-y-2">
                    <Label for="linkedin_url">LinkedIn Profile</Label>
                    <div class="relative">
                      <Linkedin class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="linkedin_url"
                        v-model="form.linkedin_url"
                        placeholder="linkedin.com/company/example"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.linkedin_url }"
                      />
                    </div>
                    <p v-if="form.errors.linkedin_url" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.linkedin_url }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="twitter_url">Twitter Profile</Label>
                    <div class="relative">
                      <Twitter class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="twitter_url"
                        v-model="form.twitter_url"
                        placeholder="twitter.com/example"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.twitter_url }"
                      />
                    </div>
                    <p v-if="form.errors.twitter_url" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.twitter_url }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="facebook_url">Facebook Page</Label>
                    <div class="relative">
                      <Facebook class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="facebook_url"
                        v-model="form.facebook_url"
                        placeholder="facebook.com/example"
                        class="pl-10"
                        :class="{ 'border-red-500': form.errors.facebook_url }"
                      />
                    </div>
                    <p v-if="form.errors.facebook_url" class="text-sm text-red-600 flex items-center gap-1">
                      <AlertCircle class="h-3 w-3" />
                      {{ form.errors.facebook_url }}
                    </p>
                  </div>
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
                  <Label for="notes">Notes</Label>
                  <Textarea
                    id="notes"
                    v-model="form.notes"
                    placeholder="Any additional notes about this firm..."
                    rows="4"
                    :class="{ 'border-red-500': form.errors.notes }"
                  />
                  <p v-if="form.errors.notes" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.notes }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="source">Source</Label>
                  <Input
                    id="source"
                    v-model="form.source"
                    placeholder="How did you find this firm? (e.g., referral, website, etc.)"
                    :class="{ 'border-red-500': form.errors.source }"
                  />
                  <p v-if="form.errors.source" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.source }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="total_revenue">Total Revenue (Optional)</Label>
                  <Input
                    id="total_revenue"
                    v-model="form.total_revenue"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    :class="{ 'border-red-500': form.errors.total_revenue }"
                  />
                  <p v-if="form.errors.total_revenue" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.total_revenue }}
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Logo Upload -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <ImageIcon class="h-5 w-5" />
                  Logo
                </CardTitle>
                <CardDescription>
                  Upload a logo for this firm
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div v-if="logoPreview" class="text-center">
                  <div class="inline-block relative">
                    <img :src="logoPreview" alt="Logo preview" class="h-24 w-24 object-cover rounded-lg border" />
                    <Button
                      type="button"
                      variant="destructive"
                      size="sm"
                      @click="removeLogo"
                      class="absolute -top-2 -right-2 h-6 w-6 p-0 rounded-full"
                    >
                      <X class="h-3 w-3" />
                    </Button>
                  </div>
                </div>

                <div v-else class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                  <ImageIcon class="h-12 w-12 mx-auto text-gray-400 mb-4" />
                  <p class="text-sm text-muted-foreground mb-2">Upload a logo</p>
                  <p class="text-xs text-muted-foreground">PNG, JPG, GIF up to 5MB</p>
                </div>

                <input
                  id="logo-upload"
                  type="file"
                  accept="image/*"
                  @change="handleLogoUpload"
                  class="hidden"
                />

                <Button
                  type="button"
                  variant="outline"
                  @click="() => document.getElementById('logo-upload')?.click()"
                  class="w-full gap-2"
                >
                  <Upload class="h-4 w-4" />
                  {{ logoPreview ? 'Change Logo' : 'Upload Logo' }}
                </Button>
              </CardContent>
            </Card>

            <!-- Status & Priority -->
            <Card>
              <CardHeader>
                <CardTitle>Status & Priority</CardTitle>
                <CardDescription>
                  Set the current status and priority level
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
                      <SelectItem value="prospect">Prospect</SelectItem>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.status" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
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
                      <SelectItem value="low">Low Priority</SelectItem>
                      <SelectItem value="medium">Medium Priority</SelectItem>
                      <SelectItem value="high">High Priority</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.priority" class="text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.priority }}
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
                      @click="removeTag(index)"
                      class="h-4 w-4 p-0 hover:bg-transparent"
                    >
                      <X class="h-3 w-3" />
                    </Button>
                  </Badge>
                </div>

                <div class="flex gap-2">
                  <Input
                    id="new-tag"
                    placeholder="Add a tag..."
                    @keydown.enter.prevent="(e) => {
                      addTag(e.target.value.trim())
                      e.target.value = ''
                    }"
                    class="flex-1"
                  />
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="() => {
                      const input = document.getElementById('new-tag')
                      addTag(input.value.trim())
                      input.value = ''
                    }"
                  >
                    <Plus class="h-4 w-4" />
                  </Button>
                </div>

                <p class="text-xs text-muted-foreground">
                  Press Enter or click + to add tags
                </p>
              </CardContent>
            </Card>

            <!-- Form Actions -->
            <Card>
              <CardContent class="p-4">
                <div class="space-y-3">
                  <Button
                    type="submit"
                    :disabled="isSubmitting || !form.name"
                    class="w-full gap-2"
                  >
                    <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                    <CheckCircle2 v-else class="h-4 w-4" />
                    {{ isSubmitting ? 'Creating Firm...' : 'Create Firm' }}
                  </Button>

                  <Button
                    type="button"
                    variant="outline"
                    @click="saveDraft"
                    class="w-full gap-2"
                  >
                    <Save class="h-4 w-4" />
                    Save as Draft
                  </Button>

                  <Button
                    type="button"
                    variant="ghost"
                    as="a"
                    :href="route('firms.index')"
                    class="w-full gap-2"
                  >
                    <X class="h-4 w-4" />
                    Cancel
                  </Button>
                </div>

                <Separator class="my-4" />

                <div class="text-center">
                  <p class="text-xs text-muted-foreground">
                    Fields marked with * are required
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
