<script setup>
import { IconPaperclip } from '@tabler/icons-vue'
import { ref } from "vue"

const emit = defineEmits(['input'])
const picker = ref();

const browse = () => {
  picker.value.click()
}

const change = (e) => {
  emit('input', e.target.files)
}
</script>

<template>

  <div>

    <input
      ref="picker"
      type="file"
      accept="application/pdf, image/jpg, image/png, image/jpeg"
      class="hidden"
      multiple
      @change="change">

    <button
      type="button"
      @click="browse"
      class="inline-flex justify-center p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">

      <IconPaperclip
        class="w-6 h-6"
        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" />

      <span class="sr-only">Upload image</span>

    </button>

  </div>

</template>

<style scoped>

</style>
