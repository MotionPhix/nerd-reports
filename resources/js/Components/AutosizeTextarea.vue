<script setup>
import autosize from 'autosize/dist/autosize';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const textarea = ref()

onMounted(() => autosize(textarea.value))

onBeforeUnmount(() => autosize.destroy(textarea.value))
</script>

<template>
  <textarea
    ref="textarea"
    :value="modelValue"
    class="w-full py-3 border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-lime-500 dark:focus:border-lime-600 focus:ring-lime-500 dark:focus:ring-lime-600"
    @input="emit('update:modelValue', $event.target.value)"
  />
</template>
