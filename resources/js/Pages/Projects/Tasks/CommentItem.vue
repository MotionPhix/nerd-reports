<script setup lang="ts">

const props = defineProps<{
  comment: App.Data.CommentData
}>()

</script>

<template>
  <li class="flex items-start gap-2.5">

    <img
      class="w-8 h-8 rounded-full"
      :src="props.comment.user.profile_picture"
      :alt="props.comment.user.name">

    <div class="flex flex-col gap-1 w-full max-w-[320px]">

      <div class="flex items-center space-x-2 rtl:space-x-reverse">

        <span class="text-sm font-semibold text-gray-900 dark:text-white">
          {{ props.comment.user.name }}
        </span>

        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
          {{ props.comment.created_at }}
        </span>

      </div>

      <div class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 rounded-e-xl rounded-es-xl dark:bg-gray-700">

        <article class="text-sm font-normal text-gray-900 dark:text-white" v-html="props.comment.body" />

      </div>

    </div>
  </li>
</template>

<style scoped>

</style>
