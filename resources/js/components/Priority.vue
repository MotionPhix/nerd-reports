<script setup>
import { computed } from "vue"
import { cva } from "class-variance-authority"

const props = defineProps({
  priority: {
    type: String,
    default: 'normal'
  },

  fontSize: {
    type: String,
    default: ''
  }
})

const reason = props.priority

const priorityClass = computed(() => {
  return cva('flex items-center h-6 px-3 rounded capitalize', {
    variants: {
      intent: {
        normal: 'text-green-700 bg-green-100',
        medium: 'text-yellow-700 bg-yellow-100',
        high: 'text-red-700 bg-red-100',
      },
    },
  })({
    intent: reason,
  })
})
</script>

<template>
  <span :class="[priorityClass, props.fontSize]">
    <slot />
  </span>
</template>
