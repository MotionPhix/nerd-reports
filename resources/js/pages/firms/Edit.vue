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
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Button variant="ghost" size="sm" as="a" :href="route('firms.show', firm.uuid)" class="gap-2">
            <ArrowLeft class="h-4 w-4" />
            Back to Firm
          </Button>
          <Separator orientation="vertical" class="h-6" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
              Edit {{ firm.name }}
            </h1>
            <p class="text-muted-foreground mt-1">
              Update firm information and settings
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button variant="outline" size="sm" as="a" :href="route('firms.show', firm.uuid)" class="gap-2">
            <Eye class="h-4 w-4" />
            View Firm
          </Button>

          <Button variant="outline" size="sm" @click="saveDraft" class="gap-2">
            <Save class="h-4 w-4" />
            Save Draft
          </Button>

          <Button variant="outline" size="sm" @click="resetForm" :disabled="!hasChanges" class="gap-2">
            <RefreshCw class="h-4 w-4" />
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
                      :class="{ 'border-
