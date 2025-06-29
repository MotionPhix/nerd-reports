<script setup>
import {
IconCircleCheck as CheckCircleIcon,
IconExclamationCircle as ExclamationTriangleIcon,
IconInfoSquareRounded as InformationCircleIcon,
IconSquareRoundedX as XCircleIcon
} from '@tabler/icons-vue';
import { cva } from 'class-variance-authority';
import { computed, onMounted } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'success',
  },
  title: {
    type: String,
    default: null,
  },
  duration: {
    type: Number,
    default: 7000,
  },
  message: String,
})

const emit = defineEmits(['remove'])
const reason = props.type

onMounted(() => {
  setTimeout(() => emit('remove'), props.duration)
})

const containerClass = computed(() => {
  return cva('w-80 p-4 rounded-lg shadow flex items-center gap-4', {
    variants: {
      intent: {
        info: 'bg-blue-100',
        success: 'bg-green-100',
        warning: 'bg-yellow-100',
        danger: 'bg-red-100',
      },
    },
  })({
    intent: reason,
  })
})

const iconClass = computed(() => {
  return cva('w-12 h-12 font-semibold', {
    variants: {
      intent: {
        info: 'text-blue-700',
        success: 'text-green-700',
        warning: 'text-yellow-700',
        danger: 'text-red-700',
      },
    },
  })({
    intent: reason,
  })
})

const titleClass = computed(() => {
  return cva('mb-1 font-semibold text-xl', {
    variants: {
      intent: {
        info: 'text-blue-900',
        success: 'text-green-900',
        warning: 'text-yellow-900',
        danger: 'text-red-900',
      },
    },
  })({
    intent: reason,
  })
})

const contentClass = computed(() => {
  return cva(`leading-none ${props.title ? 'mb-1' : ''}`, {
    variants: {
      intent: {
        info: 'text-blue-700',
        success: 'text-green-700',
        warning: 'text-yellow-700',
        danger: 'text-red-700',
      },
    },
  })({
    intent: reason,
  })
})

const btnClass = computed(() => {
  return cva('ml-auto -mx-1.5 transition duration-300 -my-1.5 p-0.5 hover:bg-gray-100 inline-flex h-6 w-6 rounded-lg', {
    variants: {
      intent: {
        info: 'text-blue-900/70 hover:text-blue-900 hover:bg-blue-200',
        success: 'text-green-900/70 hover:text-green-900 hover:bg-green-200',
        warning: 'text-yellow-900/70 hover:text-yellow-900 hover:bg-yellow-200',
        danger: 'text-red-900/70 hover:text-red-900 hover:bg-red-200',
      },
    },
  })({
    intent: reason,
  })
})

const iconComponent = computed(() => {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationTriangleIcon,
    danger: XCircleIcon,
    info: InformationCircleIcon,
  }

  return icons[reason]
})
</script>

<template>
  <div :class="containerClass" role="alert">
    <div class="flex" :class="title ? 'items-start' : 'items-center'">
      <component :is="iconComponent" :class="iconClass" />

      <div class="ml-3 text-sm font-normal">
        <span v-if="title" :class="titleClass">
          {{ title }}
        </span>

        <div :class="contentClass">
          {{ message }}
        </div>
      </div>
    </div>

    <button
      type="button"
      :class="btnClass"
      data-dismiss-target="#toast-default" aria-label="Close" @click="emit('remove')"
    >
      <span class="sr-only">Close</span>
      <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path
          fill-rule="evenodd"
          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>
  </div>
</template>
