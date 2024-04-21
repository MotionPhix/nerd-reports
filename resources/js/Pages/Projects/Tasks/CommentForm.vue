<script setup lang="ts">
import InputError from '@/Components/InputError.vue'

import TipTap from "@/Components/TipTap.vue"

import { IconX } from '@tabler/icons-vue'

import { ref } from "vue"

import { useForm } from "@inertiajs/vue3"

import { useNotificationStore } from "@/Stores/notificationStore"

import FileInput from "@/Pages/Projects/Tasks/FileInput.vue"
import axios from "axios"

const props = defineProps<{
  task: App.Data.TaskData,
  cancel: Function
}>()

const toastStore = useNotificationStore();

const { notify } = toastStore

const media = ref([])

const commentPlaceholder = ref('Enter your comment')

const form = useForm({
  body: null,
  task_id: props.task.id
})

function onSubmit() {

  form.post(
    route('comments.store', { task: props.task }),
    {
      preserveScroll: true,

      onError: (errors) => {

        form.reset()

        for (const prop in errors) {

          notify({
            title:  'Resolve errors',
            type: 'warning',
            message: errors[prop]
          })

        }

      },

      onSuccess: (resp) => {

        props.cancel()

        notify({
          title:  true,
          message: 'Comment was added successfully!'
        })

      },

    }

  );

}

const close = () => {

  console.log('We are here')

  form.reset()

  props.cancel()

}

const uploadFiles = (files) => {

  Array.from(files).forEach((file) => {

    let reader = new FileReader()

    reader.readAsDataURL(file)

    reader.onload = (e) => {

      let item = {

        url: e.target.result,

        id: undefined,

        loading: true

      }

      let formData = new FormData()

      formData.append('file', file)

      axios
        .post(
          `/api/comments/uploads/${props.task.id}`,
          formData
        )
        .then(({ data }) => {

          item.id = data.id

        })
        .finally(() => item.loading = false)

      media.value.push(item)

    }

  })

}

const removeMedia = (idx) => {
  media.value.splice(idx, 1)
}
</script>

<template>

  <form class="pb-6 space-y-6" @submit.prevent="onSubmit">

    <div>
      <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Comment
      </label>

      <TipTap v-model="form.body" height="h-54" v-model:placeholder="commentPlaceholder" />

      <InputError :message="form.errors.body" />
    </div>

    <article
      v-if="media.length"
      class="grid gap-2"
      :class="{ 'grid-cols-2': media.length > 1 }">

      <div v-for="(file, idx) in media" class="relative">

        <button
          type="button"
          @click="removeMedia(idx)"
          class="m-1 absolute top-0 bg-black left-0 text-white bg-opacity-75 rounded-full cursor-pointer hover:bg-opacity-100">

          <IconX class="h-5 w-5" />

        </button>

        <img :src="file.url" alt="" class="rounded-xl object-cover h-48 w-full">

      </div>

    </article>

    <div class="flex items-center space-x-4">

      <button
        type="submit"
        :disabled="form.prosessing"
        class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
        Add comment
      </button>

      <button
        @click="close"
        type="button" class="text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
        Cancel
      </button>

      <span class="flex-1" />

      <FileInput @input="uploadFiles" />

    </div>

  </form>

</template>
