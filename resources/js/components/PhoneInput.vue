<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { parsePhoneNumberWithError, isValidPhoneNumber, getCountryCallingCode } from 'libphonenumber-js'
import {
  ChevronDownIcon,
  SearchIcon,
  CheckCircleIcon,
  InfoIcon
} from 'lucide-vue-next'

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  defaultCountry: {
    type: String,
    default: 'US'
  },
  placeholder: {
    type: String,
    default: 'Enter phone number'
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'country-change', 'validation-change'])

// Refs
const phoneInput = ref(null)
const searchInput = ref(null)
const showDropdown = ref(false)
const phoneNumber = ref('')
const searchQuery = ref('')
const error = ref('')
const isValid = ref(false)
const selectedCountry = ref(null)

// Countries data
const countries = ref([
  { code: 'US', name: 'United States', dialCode: '1', flag: '🇺🇸' },
  { code: 'CA', name: 'Canada', dialCode: '1', flag: '🇨🇦' },
  { code: 'GB', name: 'United Kingdom', dialCode: '44', flag: '🇬🇧' },
  { code: 'AU', name: 'Australia', dialCode: '61', flag: '🇦🇺' },
  { code: 'DE', name: 'Germany', dialCode: '49', flag: '🇩🇪' },
  { code: 'FR', name: 'France', dialCode: '33', flag: '🇫🇷' },
  { code: 'IT', name: 'Italy', dialCode: '39', flag: '🇮🇹' },
  { code: 'ES', name: 'Spain', dialCode: '34', flag: '🇪🇸' },
  { code: 'NL', name: 'Netherlands', dialCode: '31', flag: '🇳🇱' },
  { code: 'BE', name: 'Belgium', dialCode: '32', flag: '🇧🇪' },
  { code: 'CH', name: 'Switzerland', dialCode: '41', flag: '🇨🇭' },
  { code: 'AT', name: 'Austria', dialCode: '43', flag: '🇦🇹' },
  { code: 'SE', name: 'Sweden', dialCode: '46', flag: '🇸🇪' },
  { code: 'NO', name: 'Norway', dialCode: '47', flag: '🇳🇴' },
  { code: 'DK', name: 'Denmark', dialCode: '45', flag: '🇩🇰' },
  { code: 'FI', name: 'Finland', dialCode: '358', flag: '🇫🇮' },
  { code: 'JP', name: 'Japan', dialCode: '81', flag: '🇯🇵' },
  { code: 'KR', name: 'South Korea', dialCode: '82', flag: '🇰🇷' },
  { code: 'CN', name: 'China', dialCode: '86', flag: '🇨🇳' },
  { code: 'IN', name: 'India', dialCode: '91', flag: '🇮🇳' },
  { code: 'BR', name: 'Brazil', dialCode: '55', flag: '🇧🇷' },
  { code: 'MX', name: 'Mexico', dialCode: '52', flag: '🇲🇽' },
  { code: 'AR', name: 'Argentina', dialCode: '54', flag: '🇦🇷' },
  { code: 'CL', name: 'Chile', dialCode: '56', flag: '🇨🇱' },
  { code: 'CO', name: 'Colombia', dialCode: '57', flag: '🇨🇴' },
  { code: 'PE', name: 'Peru', dialCode: '51', flag: '🇵🇪' },
  { code: 'ZA', name: 'South Africa', dialCode: '27', flag: '🇿🇦' },
  { code: 'EG', name: 'Egypt', dialCode: '20', flag: '🇪🇬' },
  { code: 'NG', name: 'Nigeria', dialCode: '234', flag: '🇳🇬' },
  { code: 'KE', name: 'Kenya', dialCode: '254', flag: '🇰🇪' },
  { code: 'RU', name: 'Russia', dialCode: '7', flag: '🇷🇺' },
  { code: 'TR', name: 'Turkey', dialCode: '90', flag: '🇹🇷' },
  { code: 'AE', name: 'United Arab Emirates', dialCode: '971', flag: '🇦🇪' },
  { code: 'SA', name: 'Saudi Arabia', dialCode: '966', flag: '🇸🇦' },
  { code: 'IL', name: 'Israel', dialCode: '972', flag: '🇮🇱' },
  { code: 'SG', name: 'Singapore', dialCode: '65', flag: '🇸🇬' },
  { code: 'MY', name: 'Malaysia', dialCode: '60', flag: '🇲🇾' },
  { code: 'TH', name: 'Thailand', dialCode: '66', flag: '🇹🇭' },
  { code: 'VN', name: 'Vietnam', dialCode: '84', flag: '🇻🇳' },
  { code: 'PH', name: 'Philippines', dialCode: '63', flag: '🇵🇭' },
  { code: 'ID', name: 'Indonesia', dialCode: '62', flag: '🇮🇩' },
  { code: 'NZ', name: 'New Zealand', dialCode: '64', flag: '🇳🇿' }
])

// Computed
const filteredCountries = computed(() => {
  if (!searchQuery.value) return countries.value

  const query = searchQuery.value.toLowerCase()
  return countries.value.filter(country =>
    country.name.toLowerCase().includes(query) ||
    country.dialCode.includes(query) ||
    country.code.toLowerCase().includes(query)
  )
})

// Methods
const toggleDropdown = () => {
  if (props.disabled) return
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
}

const closeDropdown = () => {
  showDropdown.value = false
  searchQuery.value = ''
}

const selectCountry = (country) => {
  selectedCountry.value = country
  closeDropdown()
  phoneInput.value?.focus()
  emit('country-change', country)
  validatePhone()
}

const handleInput = (event) => {
  phoneNumber.value = event.target.value
  validatePhone()
}

const handleBlur = () => {
  validatePhone()
}

const handleFocus = () => {
  error.value = ''
}

const validatePhone = () => {
  if (!phoneNumber.value) {
    error.value = ''
    isValid.value = false
    emit('update:modelValue', '')
    emit('validation-change', { isValid: false, error: '' })
    return
  }

  try {
    const fullNumber = `+${selectedCountry.value?.dialCode}${phoneNumber.value}`
    const parsed = parsePhoneNumberWithError(fullNumber)

    if (parsed && isValidPhoneNumber(fullNumber)) {
      error.value = ''
      isValid.value = true
      emit('update:modelValue', parsed.format('E.164'))
      emit('validation-change', { isValid: true, error: '' })
    } else {
      error.value = 'Invalid phone number'
      isValid.value = false
      emit('update:modelValue', '')
      emit('validation-change', { isValid: false, error: 'Invalid phone number' })
    }
  } catch (err) {
    error.value = 'Invalid phone number format'
    isValid.value = false
    emit('update:modelValue', '')
    emit('validation-change', { isValid: false, error: 'Invalid phone number format' })
  }
}

const initializeCountry = () => {
  const defaultCountry = countries.value.find(c => c.code === props.defaultCountry)
  selectedCountry.value = defaultCountry || countries.value[0]
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    closeDropdown()
  }
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue && newValue !== phoneNumber.value) {
    try {
      const parsed = parsePhoneNumberWithError(newValue)
      if (parsed) {
        phoneNumber.value = parsed.nationalNumber
        const country = countries.value.find(c => c.dialCode === parsed.countryCallingCode)
        if (country) {
          selectedCountry.value = country
        }
      }
    } catch (err) {
      console.warn('Invalid phone number provided:', newValue)
    }
  }
}, { immediate: true })

// Lifecycle
onMounted(() => {
  initializeCountry()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="relative">
    <!-- Main input container -->
    <div class="relative">
      <div
        class="flex items-center border rounded-md focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-colors duration-200"
        :class="[
          error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
          'bg-white dark:bg-gray-800'
        ]"
      >
        <!-- Country selector button -->
        <button
          type="button"
          @click="toggleDropdown"
          class="flex items-center px-3 py-2 border-r border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
          :class="{ 'rounded-l-md': true }"
        >
          <span class="text-lg mr-2">{{ selectedCountry?.flag || '🌐' }}</span>
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
            +{{ selectedCountry?.dialCode || '' }}
          </span>
          <ChevronDownIcon class="w-4 h-4 ml-1 text-gray-500" />
        </button>

        <!-- Phone number input -->
        <input
          ref="phoneInput"
          v-model="phoneNumber"
          type="tel"
          :placeholder="placeholder"
          class="flex-1 px-3 py-2 bg-transparent border-none outline-none text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
          @input="handleInput"
          @blur="handleBlur"
          @focus="handleFocus"
        />

        <!-- Validation icon -->
        <div class="px-3 py-2">
          <CheckCircleIcon
            v-if="isValid && phoneNumber"
            class="w-5 h-5 text-green-500"
          />
          <InfoIcon
            v-else-if="error && phoneNumber"
            class="w-5 h-5 text-red-500"
          />
        </div>
      </div>

      <!-- Error message -->
      <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </p>
    </div>

    <!-- Country dropdown -->
    <div
      v-if="showDropdown"
      class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-hidden"
    >
      <!-- Search input -->
      <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
          <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            placeholder="Search countries..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>

      <!-- Countries list -->
      <div class="max-h-48 overflow-y-auto">
        <button
          v-for="country in filteredCountries"
          :key="country.code"
          type="button"
          @click="selectCountry(country)"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 text-left"
          :class="{
            'bg-blue-50 dark:bg-blue-900/20': country.code === selectedCountry?.code
          }"
        >
          <span class="text-lg mr-3">{{ country.flag }}</span>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
              {{ country.name }}
            </div>
          </div>
          <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
            +{{ country.dialCode }}
          </span>
        </button>
      </div>
    </div>

    <!-- Backdrop -->
    <div
      v-if="showDropdown"
      class="fixed inset-0 z-40"
      @click="closeDropdown"
    ></div>
  </div>
</template>
