<script setup lang="ts">
import { computed, ref } from 'vue'

const props = withDefaults(defineProps<{
  content: string
  position?: string
}>(), {
  position: 'top',
})

const tooltip = ref(false)

const positionStyle = computed(() => {
  switch (props.position) {
    case 'top':
      return { top: '-35px', left: '50%', transform: 'translateX(-50%)' }
    case 'bottom':
      return { bottom: '-30px', left: '50%', transform: 'translateX(-50%)' }
    case 'left':
      return { top: '50%', left: '-60px', transform: 'translateY(-50%)' }
    case 'right':
      return { top: '50%', right: '-60px', transform: 'translateY(-50%)' }
    default:
      return { display: 'none' } // fallback style when position is not recognized
  }
})
</script>

<template>
  <div class="relative" @mouseenter="tooltip = true" @mouseleave="tooltip = false">
    <slot @mouseenter="tooltip = true" @mouseleave="tooltip = false" />

    <div
      v-if="tooltip"
      class="absolute px-2 py-1 text-xs font-semibold text-gray-700 bg-white border rounded-lg shadow-md pointer-events-none dark:border-gray-500 dark:text-white dark:bg-gray-700"
      :class="{ 'opacity-100': tooltip, 'opacity-0': !tooltip }"
      :style="positionStyle"
      v-text="content"
    />
  </div>
</template>
