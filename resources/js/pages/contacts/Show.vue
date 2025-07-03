<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// UI Components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Separator } from '@/components/ui/separator'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// Icons
import {
  ArrowLeft, Edit, Copy, Trash2, MoreHorizontal, Mail, Phone, Building2,
  MapPin, Calendar, Tag, Star, Plus, X, Check, Upload, Download,
  MessageCircle, Briefcase, Clock, User, Globe
} from 'lucide-vue-next'
import AppSidebarLayout from "@/layouts/AppLayout.vue"

// Props
const props = defineProps({
  contact: Object,
  activity: Object,
})

// Reactive data
const showDeleteDialog = ref(false)
const showAddEmailDialog = ref(false)
const showAddPhoneDialog = ref(false)
const showAvatarUploadDialog = ref(false)
const selectedFile = ref(null)
const uploading = ref(false)

// Forms
const emailForm = useForm({
  email: '',
  is_primary: false,
})

const phoneForm = useForm({
  phone: '',
  country_code: '+1',
  type: 'mobile',
  is_primary: false,
})

// Computed properties
const completionPercentage = computed(() => {
  let completed = 0
  const total = 8

  if (props.contact.first_name) completed++
  if (props.contact.last_name) completed++
  if (props.contact.job_title) completed++
  if (props.contact.bio) completed++
  if (props.contact.firm) completed++
  if (props.contact.emails?.length > 0) completed++
  if (props.contact.phones?.length > 0) completed++
  if (props.activity.has_avatar) completed++

  return Math.round((completed / total) * 100)
})

const completionColor = computed(() => {
  if (completionPercentage.value >= 80) return 'text-green-600'
  if (completionPercentage.value >= 60) return 'text-yellow-600'
  return 'text-red-600'
})

// Methods
const goBack = () => {
  router.visit(route('contacts.index'))
}

const editContact = () => {
  router.visit(route('contacts.edit', props.contact.uuid))
}

const duplicateContact = () => {
  router.post(route('contacts.duplicate', props.contact.uuid))
}

const confirmDelete = () => {
  showDeleteDialog.value = true
}

const deleteContact = () => {
  router.delete(route('contacts.destroy', props.contact.uuid), {
    onSuccess: () => {
      router.visit(route('contacts.index'))
    }
  })
}

const addEmail = () => {
  emailForm.post(route('contacts.emails.add', props.contact.uuid), {
    onSuccess: () => {
      showAddEmailDialog.value = false
      emailForm.reset()
    }
  })
}

const removeEmail = (emailId: string) => {
  router.delete(route('contacts.emails.remove', [props.contact.uuid, emailId]))
}

const setPrimaryEmail = (emailId: string) => {
  router.patch(route('contacts.emails.set-primary', [props.contact.uuid, emailId]))
}

const addPhone = () => {
  phoneForm.post(route('contacts.phones.add', props.contact.uuid), {
    onSuccess: () => {
      showAddPhoneDialog.value = false
      phoneForm.reset()
    }
  })
}

const removePhone = (phoneId: string) => {
  router.delete(route('contacts.phones.remove', [props.contact.uuid, phoneId]))
}

const setPrimaryPhone = (phoneId: string) => {
  router.patch(route('contacts.phones.set-primary', [props.contact.uuid, phoneId]))
}

const handleAvatarUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  selectedFile.value = target.files?.[0] || null
}

const uploadAvatar = () => {
  if (!selectedFile.value) return

  uploading.value = true
  const formData = new FormData()
  formData.append('avatar', selectedFile.value)

  router.post(route('contacts.avatar.upload', props.contact.uuid), formData, {
    onSuccess: () => {
      showAvatarUploadDialog.value = false
      selectedFile.value = null
      uploading.value = false
    },
    onError: () => {
      uploading.value = false
    }
  })
}

const removeAvatar = () => {
  router.delete(route('contacts.avatar.remove', props.contact.uuid))
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

defineOptions({
  layout: AppSidebarLayout
})
</script>

<template>
  <Head :title="contact.full_name" />

  <div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div>
            <div>
              <Button
                variant="link"
                @click="goBack"
                class="flex items-center gap-2">
                <ArrowLeft class="h-4 w-4" />
                All contacts
              </Button>
            </div>

            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
              {{ contact.full_name }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ contact.job_title || 'Contact Details' }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Button
            variant="outline"
            @click="duplicateContact"
            class="flex items-center gap-2"
          >
            <Copy class="h-4 w-4" />
            Duplicate
          </Button>
          <Button
            variant="outline"
            @click="editContact"
            class="flex items-center gap-2"
          >
            <Edit class="h-4 w-4" />
            Edit
          </Button>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="sm">
                <MoreHorizontal class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem @click="showAvatarUploadDialog = true">
                <Upload class="mr-2 h-4 w-4" />
                Upload Avatar
              </DropdownMenuItem>
              <DropdownMenuItem v-if="activity.has_avatar" @click="removeAvatar">
                <X class="mr-2 h-4 w-4" />
                Remove Avatar
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                @click="confirmDelete"
                class="text-red-600 focus:text-red-600"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Delete Contact
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

    <div>

      <div class="grid gap-6 lg:grid-cols-3">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Contact Overview -->
            <Card>
              <CardContent class="p-6">
                <div class="flex items-start gap-6">
                  <Avatar class="h-24 w-24">
                    <AvatarImage
                      :src="contact.avatar_url"
                      :alt="contact.full_name"
                    />

                    <AvatarFallback class="text-2xl">
                      {{ contact.initials }}
                    </AvatarFallback>
                  </Avatar>

                  <div class="flex-1">
                    <div class="flex items-start justify-between">
                      <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                          {{ contact.full_name }}
                        </h1>

                        <p v-if="contact.nickname" class="text-lg text-gray-600 dark:text-gray-400">
                          "{{ contact.nickname }}"
                        </p>

                        <div class="mt-2 space-y-1">
                          <div v-if="contact.job_title" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <Briefcase class="h-4 w-4" />
                            <span>{{ contact.job_title }}</span>
                          </div>

                          <div v-if="contact.firm" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <Building2 class="h-4 w-4" />
                            <Link
                              :href="route('firms.show', contact.firm.uuid)"
                              class="text-blue-600 hover:text-blue-800 hover:underline">
                              {{ contact.firm.name }}
                            </Link>
                          </div>
                        </div>
                      </div>

                      <div class="text-right">
                        <div class="flex items-center gap-2 mb-2">
                          <span class="text-sm text-gray-600 dark:text-gray-400">Profile Completion</span>
                          <span :class="completionColor" class="text-sm font-medium">
                            {{ completionPercentage }}%
                          </span>
                        </div>

                        <div class="w-24 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                          <div
                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${completionPercentage}%` }"></div>
                        </div>
                      </div>
                    </div>

                    <!-- Bio -->
                    <div v-if="contact.bio" class="mt-4">
                      <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ contact.bio }}
                      </p>
                    </div>

                    <!-- Tags -->
                    <div v-if="contact.tags?.length > 0" class="mt-4">
                      <div class="flex flex-wrap gap-2">
                        <Badge
                          v-for="tag in contact.tags"
                          :key="tag.name"
                          variant="secondary"
                          class="flex items-center gap-1">
                          <Tag class="h-3 w-3" />
                          {{ tag.name }}
                        </Badge>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Detailed Information Tabs -->
            <Tabs default-value="contact-info" class="w-full">
              <TabsList class="grid w-full grid-cols-4">
                <TabsTrigger value="contact-info">Contact Info</TabsTrigger>
                <TabsTrigger value="interactions">Interactions</TabsTrigger>
                <TabsTrigger value="projects">Projects</TabsTrigger>
                <TabsTrigger value="documents">Documents</TabsTrigger>
              </TabsList>

              <!-- Contact Information Tab -->
              <TabsContent value="contact-info" class="space-y-6">
                <!-- Email Addresses -->
                <Card>
                  <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                      <CardTitle class="flex items-center gap-2">
                        <Mail class="h-5 w-5" />
                        Email Addresses
                      </CardTitle>

                      <CardDescription>
                        Manage contact email addresses
                      </CardDescription>
                    </div>
                    <Button
                      size="sm"
                      @click="showAddEmailDialog = true"
                      class="flex items-center gap-2">
                      <Plus class="h-4 w-4" />
                      Add Email
                    </Button>
                  </CardHeader>

                  <CardContent>
                    <div v-if="contact.emails?.length > 0" class="space-y-3">
                      <div
                        v-for="email in contact.emails"
                        :key="email.uuid"
                        class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center gap-3">
                          <Mail class="h-4 w-4 text-gray-500" />
                          <div>
                            <div class="flex items-center gap-2">
                              <a
                                :href="`mailto:${email.email}`"
                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ email.email }}
                              </a>

                              <Badge v-if="email.is_primary_email" variant="default" class="text-xs">
                                Primary
                              </Badge>
                            </div>

                            <p v-if="email.verified_at" class="text-xs text-green-600 mt-1">
                              Verified {{ formatDate(email.verified_at) }}
                            </p>
                          </div>
                        </div>

                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal class="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>

                          <DropdownMenuContent align="end">
                            <DropdownMenuItem
                              v-if="!email.is_primary_email"
                              @click="setPrimaryEmail(email.uuid)">
                              <Star class="mr-2 h-4 w-4" />
                              Set as Primary
                            </DropdownMenuItem>

                            <DropdownMenuItem
                              @click="removeEmail(email.uuid)"
                              class="text-red-600 focus:text-red-600">
                              <Trash2 class="mr-2 h-4 w-4" />
                              Remove
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </div>

                    <div v-else class="text-center py-8 text-gray-500">
                      <Mail class="h-12 w-12 mx-auto mb-4 text-gray-300" />
                      <p>No email addresses added yet</p>
                    </div>
                  </CardContent>
                </Card>

                <!-- Phone Numbers -->
                <Card>
                  <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                      <CardTitle class="flex items-center gap-2">
                        <Phone class="h-5 w-5" />
                        Phone Numbers
                      </CardTitle>

                      <CardDescription>
                        Manage contact phone numbers
                      </CardDescription>
                    </div>

                    <Button
                      size="sm"
                      @click="showAddPhoneDialog = true"
                      class="flex items-center gap-2">
                      <Plus class="h-4 w-4" />
                      Add Phone
                    </Button>
                  </CardHeader>
                  <CardContent>
                    <div v-if="contact.phones?.length > 0" class="space-y-3">
                      <div
                        v-for="phone in contact.phones"
                        :key="phone.uuid"
                        class="flex items-center justify-between p-3 border rounded-lg"
                      >
                        <div class="flex items-center gap-3">
                          <Phone class="h-4 w-4 text-gray-500" />
                          <div>
                            <div class="flex items-center gap-2">
                              <a
                                :href="`tel:${phone.number}`"
                                class="text-blue-600 hover:text-blue-800 hover:underline"
                              >
                                {{ phone.formatted }}
                              </a>
                              <Badge v-if="phone.is_primary_phone" variant="default" class="text-xs">
                                Primary
                              </Badge>
                              <Badge variant="outline" class="text-xs capitalize">
                                {{ phone.type }}
                              </Badge>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                              {{ phone.country_code }}
                            </p>
                          </div>
                        </div>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal class="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem
                              v-if="!phone.is_primary_phone"
                              @click="setPrimaryPhone(phone.uuid)"
                            >
                              <Star class="mr-2 h-4 w-4" />
                              Set as Primary
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              @click="removePhone(phone.uuid)"
                              class="text-red-600 focus:text-red-600"
                            >
                              <Trash2 class="mr-2 h-4 w-4" />
                              Remove
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                      <Phone class="h-12 w-12 mx-auto mb-4 text-gray-300" />
                      <p>No phone numbers added yet</p>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <!-- Interactions Tab -->
              <TabsContent value="interactions" class="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                      <MessageCircle class="h-5 w-5" />
                      Recent Interactions
                    </CardTitle>
                    <CardDescription>
                      Communication history and notes
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div v-if="contact.interactions?.length > 0" class="space-y-4">
                      <div
                        v-for="interaction in contact.interactions"
                        :key="interaction.id"
                        class="border-l-4 border-blue-500 pl-4 py-2"
                      >
                        <div class="flex items-center justify-between mb-2">
                          <h4 class="font-medium">{{ interaction.type }}</h4>
                          <time class="text-sm text-gray-500">
                            {{ formatDateTime(interaction.interaction_date) }}
                          </time>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300">
                          {{ interaction.notes }}
                        </p>
                      </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                      <MessageCircle class="h-12 w-12 mx-auto mb-4 text-gray-300" />
                      <p>No interactions recorded yet</p>
                      <Button class="mt-4" variant="outline">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Interaction
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <!-- Projects Tab -->
              <TabsContent value="projects" class="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                      <Briefcase class="h-5 w-5" />
                      Associated Projects
                    </CardTitle>
                    <CardDescription>
                      Projects involving this contact
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div v-if="contact.projects?.length > 0" class="space-y-4">
                      <div
                        v-for="project in contact.projects"
                        :key="project.id"
                        class="p-4 border rounded-lg"
                      >
                        <div class="flex items-center justify-between mb-2">
                          <h4 class="font-medium">{{ project.name }}</h4>
                          <Badge :variant="project.status === 'active' ? 'default' : 'secondary'">
                            {{ project.status }}
                          </Badge>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                          {{ project.description }}
                        </p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                          <span>Started: {{ formatDate(project.start_date) }}</span>
                          <span v-if="project.end_date">
                            Ended: {{ formatDate(project.end_date) }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                      <Briefcase class="h-12 w-12 mx-auto mb-4 text-gray-300" />
                      <p>No projects associated yet</p>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <!-- Documents Tab -->
              <TabsContent value="documents" class="space-y-6">
                <Card>
                  <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                      <CardTitle class="flex items-center gap-2">
                        <Download class="h-5 w-5" />
                        Documents
                      </CardTitle>
                      <CardDescription>
                        Files and documents related to this contact
                      </CardDescription>
                    </div>
                    <Button size="sm" class="flex items-center gap-2">
                      <Upload class="h-4 w-4" />
                      Upload Document
                    </Button>
                  </CardHeader>
                  <CardContent>
                    <div class="text-center py-8 text-gray-500">
                      <Download class="h-12 w-12 mx-auto mb-4 text-gray-300" />
                      <p>No documents uploaded yet</p>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>
            </Tabs>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Quick Actions -->
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <Button
                  v-if="contact.primary_email"
                  variant="outline"
                  class="w-full justify-start"
                  @click="window.open(`mailto:${contact.primary_email}`)"
                >
                  <Mail class="mr-2 h-4 w-4" />
                  Send Email
                </Button>
                <Button
                  v-if="contact.primary_phone"
                  variant="outline"
                  class="w-full justify-start"
                  @click="window.open(`tel:${contact.primary_phone}`)"
                >
                  <Phone class="mr-2 h-4 w-4" />
                  Call Contact
                </Button>
                <Button
                  variant="outline"
                  class="w-full justify-start"
                  @click="editContact"
                >
                  <Edit class="mr-2 h-4 w-4" />
                  Edit Contact
                </Button>
                <Button
                  variant="outline"
                  class="w-full justify-start"
                  @click="duplicateContact"
                >
                  <Copy class="mr-2 h-4 w-4" />
                  Duplicate Contact
                </Button>
              </CardContent>
            </Card>

            <!-- Contact Stats -->
            <Card>
              <CardHeader>
                <CardTitle>Contact Statistics</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Total Emails</span>
                  <span class="font-medium">{{ activity.total_emails }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Total Phones</span>
                  <span class="font-medium">{{ activity.total_phones }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Tags</span>
                  <span class="font-medium">{{ activity.total_tags }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Interactions</span>
                  <span class="font-medium">{{ contact.interactions?.length || 0 }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Created</span>
                  <span class="text-sm">{{ formatDate(contact.created_at) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Last Updated</span>
                  <span class="text-sm">{{ formatDate(contact.updated_at) }}</span>
                </div>
                <div v-if="activity.last_updated_days_ago !== null" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Days Since Update</span>
                  <span class="text-sm">{{ activity.last_updated_days_ago }}</span>
                </div>
              </CardContent>
            </Card>

            <!-- Firm Information -->
            <Card v-if="contact.firm">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Building2 class="h-5 w-5" />
                  Firm Details
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="text-center">
                  <Avatar class="h-16 w-16 mx-auto mb-3">
                    <AvatarImage
                      :src="contact.firm.logo_url"
                      :alt="contact.firm.name"
                    />
                    <AvatarFallback>
                      {{ contact.firm.name.charAt(0) }}
                    </AvatarFallback>
                  </Avatar>
                  <h3 class="font-medium">{{ contact.firm.name }}</h3>
                  <p v-if="contact.firm.slogan" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ contact.firm.slogan }}
                  </p>
                </div>

                <Separator />

                <div class="space-y-3">
                  <div v-if="contact.firm.industry" class="flex items-center gap-2">
                    <Tag class="h-4 w-4 text-gray-500" />
                    <span class="text-sm">{{ contact.firm.industry }}</span>
                  </div>
                  <div v-if="contact.firm.size" class="flex items-center gap-2">
                    <User class="h-4 w-4 text-gray-500" />
                    <span class="text-sm">{{ contact.firm.size_label }}</span>
                  </div>
                  <div v-if="contact.firm.url" class="flex items-center gap-2">
                    <Globe class="h-4 w-4 text-gray-500" />
                    <a
                      :href="contact.firm.url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-sm text-blue-600 hover:text-blue-800 hover:underline"
                    >
                      Visit Website
                    </a>
                  </div>
                  <div v-if="contact.firm.full_address" class="flex items-start gap-2">
                    <MapPin class="h-4 w-4 text-gray-500 mt-0.5" />
                    <span class="text-sm">{{ contact.firm.full_address }}</span>
                  </div>
                </div>

                <Button
                  variant="outline"
                  class="w-full"
                  @click="router.visit(route('firms.show', contact.firm.uuid))"
                >
                  View Firm Details
                </Button>
              </CardContent>
            </Card>

            <!-- Recent Activity -->
            <Card v-if="contact.interactions?.length > 0">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Clock class="h-5 w-5" />
                  Recent Activity
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <div
                    v-for="interaction in contact.interactions.slice(0, 3)"
                    :key="interaction.id"
                    class="flex items-start gap-3"
                  >
                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ interaction.type }}
                      </p>
                      <p class="text-xs text-gray-500 truncate">
                        {{ interaction.notes }}
                      </p>
                      <p class="text-xs text-gray-400 mt-1">
                        {{ formatDateTime(interaction.interaction_date) }}
                      </p>
                    </div>
                  </div>
                </div>
                <Button
                  v-if="contact.interactions.length > 3"
                  variant="ghost"
                  size="sm"
                  class="w-full mt-3"
                >
                  View All Activity
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>

    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="showDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Contact</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete {{ contact.full_name }}?
            This action cannot be undone and will remove all associated data.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            @click="deleteContact"
            class="bg-red-600 hover:bg-red-700"
          >
            Delete Contact
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Add Email Dialog -->
    <Dialog v-model:open="showAddEmailDialog">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Add Email Address</DialogTitle>
          <DialogDescription>
            Add a new email address for this contact.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="addEmail" class="space-y-4">
          <div class="space-y-2">
            <Label for="email">Email Address</Label>
            <Input
              id="email"
              v-model="emailForm.email"
              type="email"
              placeholder="contact@example.com"
              required
              :class="{ 'border-red-500': emailForm.errors.email }"
            />
            <p v-if="emailForm.errors.email" class="text-sm text-red-600">
              {{ emailForm.errors.email }}
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <input
              id="is-primary-email"
              v-model="emailForm.is_primary"
              type="checkbox"
              class="rounded border-gray-300"
            />
            <Label for="is-primary-email" class="text-sm">
              Set as primary email
            </Label>
          </div>
        </form>
        <DialogFooter>
          <Button variant="outline" @click="showAddEmailDialog = false">
            Cancel
          </Button>
          <Button
            @click="addEmail"
            :disabled="emailForm.processing || !emailForm.email"
          >
            {{ emailForm.processing ? 'Adding...' : 'Add Email' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Add Phone Dialog -->
    <Dialog v-model:open="showAddPhoneDialog">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Add Phone Number</DialogTitle>
          <DialogDescription>
            Add a new phone number for this contact.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="addPhone" class="space-y-4">
          <div class="grid grid-cols-3 gap-3">
            <div class="space-y-2">
              <Label for="country-code">Country</Label>
              <Select v-model="phoneForm.country_code">
                <SelectTrigger>
                  <SelectValue placeholder="+1" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="+1">🇺🇸 +1</SelectItem>
                  <SelectItem value="+44">🇬🇧 +44</SelectItem>
                  <SelectItem value="+33">🇫🇷 +33</SelectItem>
                  <SelectItem value="+49">🇩🇪 +49</SelectItem>
                  <SelectItem value="+81">🇯🇵 +81</SelectItem>
                  <SelectItem value="+86">🇨🇳 +86</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="col-span-2 space-y-2">
              <Label for="phone">Phone Number</Label>
              <Input
                id="phone"
                v-model="phoneForm.phone"
                type="tel"
                placeholder="(555) 123-4567"
                required
                :class="{ 'border-red-500': phoneForm.errors.phone }"
              />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="phone-type">Type</Label>
            <Select v-model="phoneForm.type">
              <SelectTrigger>
                <SelectValue placeholder="Select type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="mobile">Mobile</SelectItem>
                <SelectItem value="home">Home</SelectItem>
                <SelectItem value="work">Work</SelectItem>
                <SelectItem value="fax">Fax</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-center space-x-2">
            <input
              id="is-primary-phone"
              v-model="phoneForm.is_primary"
              type="checkbox"
              class="rounded border-gray-300"
            />
            <Label for="is-primary-phone" class="text-sm">
              Set as primary phone
            </Label>
          </div>
          <p v-if="phoneForm.errors.phone" class="text-sm text-red-600">
            {{ phoneForm.errors.phone }}
          </p>
        </form>
        <DialogFooter>
          <Button variant="outline" @click="showAddPhoneDialog = false">
            Cancel
          </Button>
          <Button
            @click="addPhone"
            :disabled="phoneForm.processing || !phoneForm.phone"
          >
            {{ phoneForm.processing ? 'Adding...' : 'Add Phone' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Avatar Upload Dialog -->
    <Dialog v-model:open="showAvatarUploadDialog">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Upload Avatar</DialogTitle>
          <DialogDescription>
            Choose a new profile picture for this contact.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <div class="grid w-full max-w-sm items-center gap-1.5">
            <Label for="avatar-file">Image File</Label>
            <Input
              id="avatar-file"
              type="file"
              accept="image/*"
              @change="handleAvatarUpload"
            />
          </div>
          <div class="text-sm text-muted-foreground">
            <p>Supported formats: JPEG, PNG, GIF, WebP</p>
            <p>Maximum file size: 2MB</p>
          </div>
          <div v-if="selectedFile" class="text-sm text-green-600">
            Selected: {{ selectedFile.name }}
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showAvatarUploadDialog = false">
            Cancel
          </Button>
          <Button
            @click="uploadAvatar"
            :disabled="!selectedFile || uploading"
          >
            <Upload class="mr-2 h-4 w-4" />
            {{ uploading ? 'Uploading...' : 'Upload Avatar' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>
