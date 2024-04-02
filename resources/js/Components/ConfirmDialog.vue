<template>
  <div class="dialog-overlay">
    <div class="dialog">
      <slot />

      <div class="actions">
        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" @click="$emit('cancel')">
          No, cancel
        </button>

        <button type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2" @click="$emit('confirm')">
          Yes, I'm sure
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dialog-overlay {
  @apply fixed inset-0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
}

.dialog {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  @apply dark:bg-gray-600 bg-gray-200 items-center flex flex-col;
  border-radius: 8px;
}

.actions {
  @apply flex items-center gap-4 py-3
}
</style>
