<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import {
  ArrowLeft,
  Save,
  X,
  Calendar,
  DollarSign,
  Clock,
  User,
  Building,
  FileText,
  CheckCircle,
  Settings
} from 'lucide-vue-next';
import AppSidebarLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import DatePicker from '@/components/DatePicker.vue';
import InputError from '@/components/InputError.vue';

// Types
interface Contact {
  uuid: string;
  first_name: string;
  last_name: string;
  full_name: string;
  primary_email: string | null;
  job_title: string | null;
  firm?: {
    uuid: string;
    name: string;
  };
}

interface Project {
  uuid: string;
  name: string;
  description: string | null;
  contact_id: string;
  due_date: string | null;
  status: string;
  priority: string;
  estimated_hours: number | null;
  budget: number | null;
  hourly_rate: number | null;
  is_billable: boolean;
  notes: string | null;
  contact: Contact;
}

interface Props {
  project: Project;
  contacts: Contact[];
}

// Props
const props = defineProps<Props>();

// Form data
const form = useForm({
  name: props.project.name,
  description: props.project.description || '',
  contact_id: props.project.contact_id,
  due_date: props.project.due_date || '',
  status: props.project.status,
  priority: props.project.priority,
  estimated_hours: props.project.estimated_hours || '',
  budget: props.project.budget || '',
  hourly_rate: props.project.hourly_rate || '',
  is_billable: props.project.is_billable,
  notes: props.project.notes || ''
});

// Reactive state
const isSubmitting = ref(false);

// Computed properties
const selectedContact = computed(() => {
  return props.contacts.find(contact => contact.uuid === form.contact_id);
});

const statusOptions = [
  { value: 'planning', label: 'Planning', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400' },
  { value: 'in_progress', label: 'In Progress', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' },
  { value: 'on_hold', label: 'On Hold', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' },
  { value: 'completed', label: 'Completed', color: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
  { value: 'cancelled', label: 'Cancelled', color: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }
];

const priorityOptions = [
  { value: 'low', label: 'Low', color: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
  { value: 'medium', label: 'Medium', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' },
  { value: 'high', label: 'High', color: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }
];

const currentStatusOption = computed(() => {
  return statusOptions.find(option => option.value === form.status);
});

const currentPriorityOption = computed(() => {
  return priorityOptions.find(option => option.value === form.priority);
});

// Methods
const formatContactDisplay = (contact: Contact) => {
  if (contact.firm) {
    return `${contact.full_name} (${contact.firm.name})`;
  }
  return contact.full_name;
};

const handleSubmit = async () => {
  if (isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    await form.patch(route('projects.update', props.project.uuid), {
      onSuccess: () => {
        toast.success('Project updated successfully!');
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
        toast.error('Please check the form for errors');
      }
    });
  } catch (error) {
    console.error('Update error:', error);
    toast.error('Failed to update project');
  } finally {
    isSubmitting.value = false;
  }
};

const handleCancel = () => {
  router.visit(route('projects.show', props.project.uuid));
};

// Layout
defineOptions({
  layout: AppSidebarLayout
});

// Lifecycle
onMounted(() => {
  console.log('Project edit page mounted for:', props.project.name);
});
</script>

<template>
  <div class="p-6 space-y-6 max-w-4xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <Button variant="ghost" size="sm" as="a" :href="route('projects.show', project.uuid)" class="gap-2 mb-4">
          <ArrowLeft class="h-4 w-4" />
          Back to Project
        </Button>

        <Heading
          title="Edit Project"
          :description="`Update details for ${project.name}`"
        />
      </div>

      <div class="flex items-center gap-3">
        <Button
          variant="outline"
          @click="handleCancel"
          :disabled="form.processing"
          class="gap-2">
          <X class="h-4 w-4" />
          Cancel
        </Button>

        <Button
          @click="handleSubmit"
          :disabled="form.processing || isSubmitting"
          class="gap-2">
          <Save class="h-4 w-4" />
          {{ form.processing ? 'Saving...' : 'Save Changes' }}
        </Button>
      </div>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Basic Information -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <CheckCircle class="h-5 w-5" />
            Basic Information
          </CardTitle>
          <CardDescription>
            Update the core project details
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Project Name -->
          <div class="space-y-2">
            <Label for="name">Project Name *</Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="Enter project name"
              :class="{ 'border-red-500': form.errors.name }"
              required
            />
            <InputError :message="form.errors.name" class="mt-2" />
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <Label for="description">Description</Label>
            <Textarea
              id="description"
              v-model="form.description"
              placeholder="Describe the project goals and requirements"
              rows="3"
              :class="{ 'border-red-500': form.errors.description }"
            />

            <InputError :message="form.errors.description" class="mt-2" />
          </div>

          <!-- Contact Selection -->
          <div class="space-y-2">
            <Label for="contact_id">Client Contact *</Label>
            <Select v-model="form.contact_id" required>
              <SelectTrigger class="w-full" :class="{ 'border-red-500': form.errors.contact_id }">
                <SelectValue placeholder="Select a contact" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="contact in contacts"
                  :key="contact.uuid"
                  :value="contact.uuid"
                >
                  <div class="flex items-center gap-2">
                    <User class="h-4 w-4" />
                    <span>{{ formatContactDisplay(contact) }}</span>
                  </div>
                </SelectItem>
              </SelectContent>
            </Select>

            <InputError :message="form.errors.contact_id" class="mt-2" />

            <!-- Selected Contact Info -->
            <div v-if="selectedContact" class="mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="flex-1">
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ selectedContact.full_name }}</p>
                  <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <span v-if="selectedContact.job_title">{{ selectedContact.job_title }}</span>
                    <span v-if="selectedContact.firm" class="flex items-center gap-1">
                      <Building class="h-3 w-3" />
                      {{ selectedContact.firm.name }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Project Settings -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Settings class="h-5 w-5" />
            Project Settings
          </CardTitle>

          <CardDescription>
            Configure project status, priority, and timeline
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            <!-- Status -->
            <div class="space-y-2">
              <Label for="status">Status</Label>
              <Select v-model="form.status">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="option in statusOptions"
                    :key="option.value"
                    :value="option.value"
                  >
                    <div class="flex items-center gap-2">
                      <Badge :class="option.color" class="text-xs">{{ option.label }}</Badge>
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>

              <InputError :message="form.errors.status" class="mt-2" />
            </div>

            <!-- Priority -->
            <div class="space-y-2">
              <Label for="priority">Priority</Label>
              <Select v-model="form.priority">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select priority" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="option in priorityOptions"
                    :key="option.value"
                    :value="option.value"
                  >
                    <div class="flex items-center gap-2">
                      <Badge :class="option.color" class="text-xs">{{ option.label }}</Badge>
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>

              <InputError :message="form.errors.priority" class="mt-2" />
            </div>
          </div>

          <!-- Due Date -->
          <div class="space-y-2">
            <Label for="due_date" class="flex items-center gap-2">
              <Calendar class="h-4 w-4" />
              Due Date
            </Label>
            <DatePicker
              id="due_date"
              v-model="form.due_date"
              :class="{ 'border-red-500': form.errors.due_date }"
            />

            <InputError :message="form.errors.due_date" class="mt-2" />
          </div>
        </CardContent>
      </Card>

      <!-- Budget & Time -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <DollarSign class="h-5 w-5" />
            Budget & Time Tracking
          </CardTitle>
          <CardDescription>
            Set budget limits and time estimates
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            <!-- Estimated Hours -->
            <div class="space-y-2">
              <Label for="estimated_hours" class="flex items-center gap-2">
                <Clock class="h-4 w-4" />
                Estimated Hours
              </Label>
              <Input
                id="estimated_hours"
                v-model="form.estimated_hours"
                type="number"
                step="0.5"
                min="0"
                placeholder="0"
                :class="{ 'border-red-500': form.errors.estimated_hours }"
              />

              <InputError :message="form.errors.estimated_hours" class="mt-2" />
            </div>

            <!-- Budget -->
            <div class="space-y-2">
              <Label for="budget">Budget ($)</Label>
              <Input
                id="budget"
                v-model="form.budget"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                :class="{ 'border-red-500': form.errors.budget }"
              />
              <InputError :message="form.errors.budget" class="mt-2" />
            </div>
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <!-- Hourly Rate -->
            <div class="space-y-2">
              <Label for="hourly_rate">Hourly Rate ($)</Label>
              <Input
                id="hourly_rate"
                v-model="form.hourly_rate"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                :class="{ 'border-red-500': form.errors.hourly_rate }"
              />
              <InputError :message="form.errors.hourly_rate" class="mt-2" />
            </div>

            <!-- Billable Toggle -->
            <div class="space-y-2">
              <Label for="is_billable">Billing</Label>
              <div class="flex items-center space-x-2">
                <Switch
                  id="is_billable"
                  v-model:checked="form.is_billable"
                />
                <Label for="is_billable" class="text-sm font-normal">
                  This is a billable project
                </Label>
              </div>

              <InputError :message="form.errors.is_billable" class="mt-2" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Notes -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <FileText class="h-5 w-5" />
            Project Notes
          </CardTitle>
          <CardDescription>
            Additional information and requirements
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-2">
            <Label for="notes">Notes</Label>
            <Textarea
              id="notes"
              v-model="form.notes"
              placeholder="Add any additional notes, requirements, or important information about this project"
              rows="4"
              :class="{ 'border-red-500': form.errors.notes }"
            />
            <InputError :message="form.errors.notes" class="mt-2" />
          </div>
        </CardContent>
      </Card>

      <!-- Form Actions -->
      <div class="flex items-center justify-end gap-3 pt-6 border-t">
        <Button
          type="button"
          variant="outline"
          @click="handleCancel"
          :disabled="form.processing"
        >
          Cancel
        </Button>

        <Button
          type="submit"
          :disabled="form.processing || isSubmitting"
          class="gap-2"
        >
          <Save class="h-4 w-4" />
          {{ form.processing ? 'Saving...' : 'Save Changes' }}
        </Button>
      </div>
    </form>
  </div>
</template>
