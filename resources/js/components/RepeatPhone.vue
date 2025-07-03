<template>
  <div class="w-full max-w-4xl mx-auto p-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Phone Numbers
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Add and manage multiple phone numbers
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Theme toggle -->
        <button
          @click="toggleTheme"
          class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
          :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
          <SunIcon v-if="isDark" class="w-5 h-5 text-yellow-500" />
          <MoonIcon v-else class="w-5 h-5 text-gray-600" />
        </button>

        <!-- Add button -->
        <button
          @click="addPhoneNumber"
          :disabled="phoneNumbers.length >= maxPhoneNumbers"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg transition-colors duration-200 text-sm font-medium"
        >
          <PlusIcon class="w-4 h-4" />
          <span class="hidden sm:inline">Add Phone</span>
          <span class="sm:hidden">Add</span>
        </button>
      </div>
    </div>

    <!-- Phone numbers list -->
    <div class="space-y-4">
      <TransitionGroup
        name="phone-list"
        tag="div"
        class="space-y-4"
      >
        <div
          v-for="(phone, index) in phoneNumbers"
          :key="phone.id"
          class="relative group"
        >
          <!-- Phone input card -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md">
            <div class="flex flex-col lg:flex-row lg:items-start gap-4">
              <!-- Phone number input -->
              <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Phone Number {{ index + 1 }}
                  <span v-if="phone.required" class="text-red-500">*</span>
                </label>

                <PhoneInput
                  v-model="phone.value"
                  :default-country="phone.country"
                  :placeholder="`Enter phone number ${index + 1}`"
                  @country-change="(country) => updateCountry(phone.id, country)"
                  @validation-change="(validation) => updateValidation(phone.id, validation)"
                />
              </div>

              <!-- Label input -->
              <div class="w-full lg:w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Label
                </label>
                <select
                  v-model="phone.label"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                >
                  <option value="mobile">Mobile</option>
                  <option value="home">Home</option>
                  <option value="work">Work</option>
                  <option value="fax">Fax</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <!-- Actions -->
              <div class="flex items-end gap-2 lg:pt-7">
                <!-- Set as primary -->
                <button
                  @click="setPrimary(phone.id)"
                  :disabled="phone.isPrimary"
                  class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                  :class="{
                    'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600': phone.isPrimary
                  }"
                  :title="phone.isPrimary ? 'Primary phone' : 'Set as primary'"
                >
                  <StarIcon
                    class="w-4 h-4"
                    :class="phone.isPrimary ? 'text-blue-600 fill-current' : 'text-gray-500'"
                  />
                </button>

                <!-- Remove -->
                <button
                  @click="removePhoneNumber(phone.id)"
                  :disabled="phoneNumbers.length <= minPhoneNumbers"
                  class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 text-red-600"
                  title="Remove phone number"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Primary badge -->
            <div
              v-if="phone.isPrimary"
              class="absolute -top-2 left-4"
            >
              <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded-full">
                <StarIcon class="w-3 h-3 fill-current" />
                Primary
              </span>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>

    <!-- Empty state -->
    <div
      v-if="phoneNumbers.length === 0"
      class="text-center py-12 text-gray-500 dark:text-gray-400"
    >
      <PhoneIcon class="w-12 h-12 mx-auto mb-4 opacity-50" />
      <p class="text-lg font-medium mb-2">No phone numbers added</p>
      <p class="text-sm mb-4">Add your first phone number to get started</p>
      <button
        @click="addPhoneNumber"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium"
      >
        <PlusIcon class="w-4 h-4" />
        Add Phone Number
      </button>
    </div>

    <!-- Summary -->
    <div
      v-if="phoneNumbers.length > 0"
      class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800"
    >
      <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
        Summary
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
        <div>
          <span class="text-blue-700 dark:text-blue-300 font-medium">Total:</span>
          <span class="ml-2 text-gray-900 dark:text-gray-100">{{ phoneNumbers.length }}</span>
        </div>
        <div>
          <span class="text-blue-700 dark:text-blue-300 font-medium">Valid:</span>
          <span class="ml-2 text-gray-900 dark:text-gray-100">{{ validPhoneNumbers }}</span>
        </div>
        <div>
          <span class="text-blue-700 dark:text-blue-300 font-medium">Primary:</span>
          <span class="ml-2 text-gray-900 dark:text-gray-100">{{ primaryPhone?.label || 'None' }}</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div
      v-if="phoneNumbers.length > 0"
      class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
    >
      <button
        @click="validateAll"
        class="flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium"
      >
        <CheckCircleIcon class="w-4 h-4" />
        Validate All
      </button>

      <button
        @click="clearAll"
        class="flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium"
      >
        <TrashIcon class="w-4 h-4" />
        Clear All
      </button>

      <button
        @click="exportData"
        class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium"
      >
        <DownloadIcon class="w-4 h-4" />
        Export
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import PhoneInput from './PhoneInput.vue'
import {
  PlusIcon,
  TrashIcon,
  StarIcon,
  PhoneIcon,
  CheckCircleIcon,
  DownloadIcon,
  SunIcon,
  MoonIcon
} from 'lucide-vue-next'

// Props
const props = defineProps({
  maxPhoneNumbers: {
    type: Number,
    default: 5
  },
  minPhoneNumbers: {
    type: Number,
    default: 1
  },
  defaultCountry: {
    type: String,
    default: 'US'
  }
})

// Emits
const emit = defineEmits(['update:phoneNumbers', 'validation-change'])

// Refs
const phoneNumbers = ref([])
const isDark = ref(false)
let nextId = 1

// Computed
const validPhoneNumbers = computed(() => {
  return phoneNumbers.value.filter(phone => phone.isValid).length
})

const primaryPhone = computed(() => {
  return phoneNumbers.value.find(phone => phone.isPrimary)
})

// Methods
const addPhoneNumber = () => {
  if (phoneNumbers.value.length >= props.maxPhoneNumbers) return

  const newPhone = {
    id: nextId++,
    value: '',
    label: 'mobile',
    country: props.defaultCountry,
    isValid: false,
    error: '',
    isPrimary: phoneNumbers.value.length === 0,
    required: phoneNumbers.value.length === 0
  }

  phoneNumbers.value.push(newPhone)
  emitUpdate()
}

const removePhoneNumber = (id) => {
  if (phoneNumbers.value.length <= props.minPhoneNumbers) return

  const index = phoneNumbers.value.findIndex(phone => phone.id === id)
  if (index === -1) return

  const removedPhone = phoneNumbers.value[index]
  phoneNumbers.value.splice(index, 1)

  // If we removed the primary phone, set the first phone as primary
  if (removedPhone.isPrimary && phoneNumbers.value.length > 0) {
    phoneNumbers.value[0].isPrimary = true
  }

  emitUpdate()
}

const setPrimary = (id) => {
  phoneNumbers.value.forEach(phone => {
    phone.isPrimary = phone.id === id
  })
  emitUpdate()
}

const updateCountry = (id, country) => {
  const phone = phoneNumbers.value.find(p => p.id === id)
  if (phone) {
    phone.country = country.code
    emitUpdate()
  }
}

const updateValidation = (id, validation) => {
  const phone = phoneNumbers.value.find(p => p.id === id)
  if (phone) {
    phone.isValid = validation.isValid
    phone.error = validation.error
    emitUpdate()
  }
}

const validateAll = () => {
  // This would trigger validation on all phone inputs
  // The actual validation is handled by individual PhoneInput components
  emit('validation-change', {
    total: phoneNumbers.value.length,
    valid: validPhoneNumbers.value,
    invalid: phoneNumbers.value.length - validPhoneNumbers.value
  })
}

const clearAll = () => {
  if (confirm('Are you sure you want to clear all phone numbers?')) {
    phoneNumbers.value = []
    nextId = 1
    addPhoneNumber() // Add one default phone number
  }
}

const exportData = () => {
  const data = phoneNumbers.value.map(phone => ({
    value: phone.value,
    label: phone.label,
    country: phone.country,
    isValid: phone.isValid,
    isPrimary: phone.isPrimary
  }))

  const blob = new Blob([JSON.stringify(data, null, 2)], {
    type: 'application/json'
  })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'phone-numbers.json'
  a.click()
  URL.revokeObjectURL(url)
}

const toggleTheme = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const emitUpdate = () => {
  emit('update:phoneNumbers', phoneNumbers.value)
}

// Initialize theme
const initializeTheme = () => {
  const savedTheme = localStorage.getItem('theme')
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches

  isDark.value = savedTheme === 'dark' || (!savedTheme && prefersDark)

  if (isDark.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

// Lifecycle
onMounted(() => {
  initializeTheme()
  addPhoneNumber() // Add initial phone number
})
</script>

<style scoped>
/* Transition animations */
.phone-list-enter-active,
.phone-list-leave-active {
  transition: all 0.3s ease;
}

.phone-list-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.phone-list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

.phone-list-move {
  transition: transform 0.3s ease;
}
</style>
