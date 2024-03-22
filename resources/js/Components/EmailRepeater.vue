<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Array, String],
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const page = usePage()

onMounted(() => {
  if (props.modelValue.length <= 0) {
    emit('update:modelValue', [...props.modelValue, { email: '' }])
  }
})

function addEmail() {
  emit('update:modelValue', [...props.modelValue, { email: '' }])
}

function removeEmail(index) {
  const updatedEmails = props.modelValue.filter((_, i) => i !== index)
  emit('update:modelValue', updatedEmails)
}
</script>

<template>
  <div
    v-for="(mail, idx) in modelValue"
    :key="idx"
    class="relative mb-8 space-y-2 group first-letter:uppercase"
  >
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
      Email address
    </label>

    <TextInput
      v-model="mail.email"
      placeholder="Enter email address"
      type="email"
      @input:model-value="value => $emit('update:modelValue', value)"
    />

    <InputError :message="page.props.errors[`emails.${idx}.email`]" />

    <button
      v-if="modelValue.length > 1" type="button"
      class="absolute hidden w-4 h-4 text-gray-300 group-hover:inline-flex top-9 right-3 hover:text-rose-500"
      @click="removeEmail(idx)"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-current" viewBox="0 0 24 24" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
    </button>
  </div>

  <div>
    <button
      v-if="modelValue.length < 3"
      type="button"
      class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500"
      @click="addEmail"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="w-6 h-6"
        viewBox="0 0 24 24" stroke-width="2"
        stroke="currentColor" fill="none"
        stroke-linecap="round" stroke-linejoin="round"
      >
        <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 5l0 14" />
        <path d="M5 12l14 0" />
      </svg>
      <span>Add email</span>
    </button>
  </div>
</template>
