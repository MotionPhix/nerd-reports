<script setup lang="ts">
import InputError from '@/Components/InputError.vue'

import TipTap from "@/Components/TipTap.vue"

import { IconSend, IconX } from '@tabler/icons-vue'

import { ref } from "vue"

import { useForm, usePage } from "@inertiajs/vue3"

import { useToastStore } from "@/Stores/toastStore"

import FileInput from '@/Components/FileInput.vue'

import axios from "axios"

import { useProjectStore } from '@/Stores/projectStore'

import { useTaskStore } from "@/Stores/taskStore"

const props = defineProps<{
  task: App.Data.TaskData,
  cancel: Function
}>()

const toastStore = useToastStore();

const projectStore = useProjectStore();

const { notify } = toastStore

const { setProject } = projectStore

const media = ref([])
const hidePreview = ref(false)

const commentPlaceholder = ref('Enter your comment')

const taskStore = useTaskStore()

const { reFetchTask } = taskStore

const form = useForm({
  body: null,
  task_id: props.task.id,
  files: [],
})

function onSubmit() {

  form.transform((data) => {
    let modifiedForm: Partial<App.Data.CommentData> = {}

    modifiedForm.body = data.body

    modifiedForm.task_id = data.task_id

    if (media.value.length) {
      modifiedForm.files = media.value.map(file => ({id: file.id }))
    }

    return modifiedForm
  })

  form.post(
    route('comments.store', { task: props.task }),
    {
      preserveScroll: true,

      onError: (errors) => {

        for (const prop in errors) {

          notify({
            title:  'Resolve errors',
            type: 'warning',
            message: errors[prop]
          })

        }

      },

      onSuccess: (data: any) => {

        setProject(data.props.project)

        if (usePage().url.startsWith('/tasks/s')) {

          reFetchTask()

        }

        form.reset()

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

  form.reset()

  props.cancel()

}

const uploadFiles = (files: File[]) => {

  hidePreview.value = false

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
          `/api/files/u/${props.task.id}`,
          formData
        )
        .then(({ data }) => {

          item.id = data.id
          item.loading = false

          form.reset()
          form.body = null
          hidePreview.value = true

        })

      media.value.push(item)

    }

  })

}

const removeMedia = (idx, item) => {
  media.value.splice(idx, 1)

  if (item.id) {

    axios
      .delete(`/api/files/d/${item.id}`)
      .catch((e) => {
        media.value.splice(idx, 0, item)
      })

  }

}
</script>

<template>

  <form
    class="pb-6 space-y-6 relative"
    @submit.prevent="onSubmit">

    <div>
      <label
        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Comment
      </label>

      <TipTap
        height="h-54"
        v-model="form.body"
        v-model:placeholder="commentPlaceholder"
        :has-controls="false" />

      <InputError :message="form.errors.body" />
    </div>

    <article
      v-if="media.length && ! hidePreview"
      class="grid gap-2"
      :class="{ 'grid-cols-2': media.length > 1 }">

      <div
        v-for="(item, idx) in media"
        class="relative flex flex-col items-center justify-center">

        <button
          type="button"
          @click="removeMedia(idx, item)"
          class="absolute top-0 left-0 m-1 text-white bg-black bg-opacity-75 rounded-full cursor-pointer hover:bg-opacity-100">

          <IconX stroke="3" class="h-5 w-5 p-0.5" />

        </button>

        <img :src="item.url" alt="" class="object-cover w-full h-48 rounded-xl">

        <div
          v-if="item.loading"
          class="absolute px-2 text-sm text-white bg-black bg-opacity-75 rounded">
          Uploading ...
        </div>

      </div>

    </article>

    <div class="flex items-center space-x-2 absolute top-3 right-3">

      <!-- <button
        @click="close"
        type="button"
        class="text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
        Cancel
      </button> -->

      <FileInput @input="uploadFiles" />

      <button
        type="submit"
        :disabled="form.processing"
        class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-2.5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
        <IconSend class="w-5 h-5" />
      </button>

    </div>

  </form>

</template>
