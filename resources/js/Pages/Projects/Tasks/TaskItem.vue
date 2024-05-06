<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'

import {
  IconMenu,
  IconUser,
  IconClock,
  IconCalendar,
  IconFile,
  IconMessage,
  IconMessageX,
  IconX,
  IconFileDescription,
  IconPencil,
  IconTrash
} from '@tabler/icons-vue'

import InputError from '@/Components/InputError.vue'

import TextInput from '@/Components/TextInput.vue'

import TipTap from '@/Components/TipTap.vue'

import SelectInput from '@/Components/SelectInput.vue'

import axios from "axios"

import { computed, nextTick, onMounted, ref } from 'vue'

import { useFormStore } from '@/Stores/formStore'

import { storeToRefs } from 'pinia'

import Priority from '@/Components/Priority.vue'

import { cva } from "class-variance-authority";

import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'

import { useToastStore } from '@/Stores/toastStore'

import Modal from "@/Components/Modal.vue"

import CommentShell from '@/Pages/Projects/Comments/CommentShell.vue'

import CommentItem from '@/Pages/Projects/Comments/CommentItem.vue'

import CommentForm from '@/Pages/Projects/Comments/CommentForm.vue'

import { useProjectStore } from '@/Stores/projectStore'

const props = defineProps<{
  task: App.Data.TaskData,
  boardId: Number
}>();

const projectStore = useProjectStore()

const { setProject, reFetchProject } = projectStore

const formStore = useFormStore()

const toastStore = useToastStore();

const { notify } = toastStore

const showTaskDetail = ref(false);

const inputTitleRef = ref();

const isShowingForm = computed(() => props.task.id === currentTaskId.value);

const boardId = computed(() => props.boardId)

const isAddingComment = ref(false)

const users = ref<App.Data.UserData[]>([])

const placeholder = ref('Task description')

const {
  currentTaskId
} = storeToRefs(formStore);

const {
  setCurrentTaskId,
  unSetCurrentTaskId,
} = formStore

const form = useForm({

  name: props.task.name,

  description: props.task.description,

  assigned_to: props.task.assigned_to,

  priority: props.task.priority,

  status: props.task.status,

  board_id: props.task.board_id,

});

async function showForm() {
  setCurrentTaskId(props.task.id)

  await nextTick();

  inputTitleRef.value.focus();
}

const taskBgClasses = computed(() => {

  return cva('border border-rose-100 dark:border-gray-600', {

    variants: {

      intent: {

        1: 'bg-blue-100 dark:bg-blue-600',
        2: 'bg-green-100 dark:bg-green-600',
        3: 'bg-yellow-100 dark:bg-yellow-600',
        4: 'bg-red-100 dark:bg-red-600',
        5: 'bg-orange-100',
        6: 'bg-pink-100',
        7: 'bg-indigo-100',
        8: 'bg-purple-100',
        9: 'bg-purple-100',
        0: 'bg-pink-100 dark:bg-pink-400',

      },

    },

  })({ intent: boardId.value });
});

const priorities = [
  {
    value: 'normal',
    label: 'Normal'
  },

  {
    value: 'medium',
    label: 'Medium'
  },

  {
    value: 'high',
    label: 'High'
  }
]

const statuses = [
  {
    value: 'in_progress',
    label: 'Being worked on'
  },

  {
    value: 'done',
    label: 'Completed'
  },

  {
    value: 'cancelled',
    label: 'Cancelled'
  }
]

onMounted(() => {

  axios
    .get('/api/users')
    .then((response) => {
      users.value = response.data.users;
    })

})

function onSubmit() {

  form.transform((data) => {

    let formData: Partial<App.Data.TaskData> = {
      name: data.name,
      status: data.status,
      priority: data.priority,
      assigned_to: data.assigned_to,
      board_id: data.board_id,
    };

    if (!! form.description) {
      formData.description = form.description
    }

    return formData

  })

  form.patch(route('tasks.update', { task: props.task }), {
    preserveScroll: true,

    onError: (errors) => {

      for (const prop in errors) {

        notify({
          title:  'Some error occurred!',
          type: 'warning',
          message: errors[prop]
        })

      }

    },

    onSuccess: (data: any) => {

      setProject(data.props.project)

      form.reset()

      unSetCurrentTaskId()

    },
  })

}

const closeModal = () => {

  showTaskDetail.value = false;

};

const cancelComment = () => {
  isAddingComment.value = false
}

const deleteTask = (task: App.Data.TaskData) => {

  router
    .delete(route('tasks.destroy', { task: task }), {

      preserveScroll: true,

      onError: (error) => console.log(error),

      onSuccess: () => reFetchProject()

    })
}
</script>

<template>
  <li
    class="relative group">

    <div
      :class="taskBgClasses"
      class="p-2 rounded-xl">

      <form
        v-if="isShowingForm"
        @keydown.esc="unSetCurrentTaskId()"
        class="grid grid-cols-2 gap-6"
        @submit.prevent="onSubmit()">

        <div class="col-span-2">
          <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Task
          </label>

          <TextInput
            id="title"
            ref="inputTitleRef"
            v-model="form.name"
            type="text"
            placeholder="Enter a task" />

          <InputError :message="form.errors.name" />
        </div>

        <div class="col-span-2">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign task</label>

          <SelectInput
            placeholder="Select a person"
            v-model="form.assigned_to"
            :options="users"
          />

          <InputError :message="form.errors.assigned_to" />
        </div>

        <div class="col-span-2">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Priority
          </label>

          <SelectInput
            placeholder="Set task priority"
            v-model="form.priority"
            :options="priorities"
          />

          <InputError :message="form.errors.priority" />
        </div>

        <div class="col-span-2">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Status
          </label>

          <SelectInput
            placeholder="Current state of the task"
            v-model="form.status"
            :options="statuses"
          />

          <InputError :message="form.errors.status" />
        </div>

        <div class="col-span-2">
          <label
            for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Description
          </label>

          <TipTap
            v-model="form.description" height="h-54"
            v-model:placeholder="placeholder" />

          <InputError :message="form.errors.description" />
        </div>

        <div class="flex items-center justify-between col-span-2">
          <button
            class="px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm bg-rose-600 hover:bg-rose-500 focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
            type="submit">
            Save task
          </button>

          <button
            class="px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-black focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
            type="button"
            @click="form.reset(); unSetCurrentTaskId()">
            Cancel
          </button>
        </div>

      </form>

      <template v-if="!isShowingForm">

        <div class="flex flex-col p-2">

          <div class="relative flex flex-col items-start">

            <Menu
              as="div"
              class="absolute top-0 right-0 z-10 hidden group-hover:flex">

              <MenuButton
                class="flex items-center justify-center w-5 h-5 text-gray-500 dark:text-gray-100">

                <IconMenu stroke="3" class="w-5 h-5" />

              </MenuButton>

              <transition
                enter-active-class="transition duration-100 ease-out transform"
                enter-from-class="scale-90 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transition duration-100 ease-in transform"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-90 opacity-0">

                <MenuItems
                  class="absolute right-0 w-40 overflow-hidden origin-top-left bg-white border border-gray-300 rounded-md shadow-lg top-5 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:outline-none">

                  <MenuItem>

                    <button
                      class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600"
                      @click="showTaskDetail = true">
                      <IconFileDescription stroke="2.5" class="w-4 h-4" />
                      <span>Task detail</span>
                    </button>

                  </MenuItem>

                  <MenuItem>

                    <button
                      class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600"
                      @click="setCurrentTaskId(props.task.id)">
                      <IconPencil stroke="2.5" class="w-4 h-4" />
                      <span>Edit task</span>
                    </button>

                  </MenuItem>

                  <MenuItem>

                    <button
                      type="button"
                      @click="deleteTask(props.task)"
                      class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">

                      <IconTrash stroke="2.5" class="w-4 h-4" />

                      <span>Delete task</span>

                    </button>

                  </MenuItem>

                </MenuItems>

              </transition>

            </Menu>

            <Priority :priority="props.task.priority" font-size="text-sm font-semibold">
              {{ props.task.priority }}
            </Priority>

            <h4 class="mt-3 text-2xl font-medium text-gray-800 font-display">
              {{ props.task.name }}
            </h4>

            <section v-html="props.task.description" class="mt-6 text-xs prose dark:prose-invert line-clamp-2" />

            <div
              class="flex items-center w-full mt-3 text-xs font-medium text-gray-700 dark:text-gray-200"
              v-tooltip="{
                global: true,
                theme: {
                  placement: 'left',
                  width: 'max-content',
                  padding: '0.5rem',
                },
              }">

              <div class="flex items-center">
                <IconCalendar stroke="1.5" class="w-4 h-4" />

                <span class="ml-1 leading-none">
                  {{ props.task.created_at }}
                </span>
              </div>

              <div class="relative flex items-center ml-4">
                <IconMessage stroke="1.5" class="w-4 h-4" />

                <span class="ml-1 leading-none">
                  {{ props.task.comments_count }}
                </span>
              </div>

              <div class="relative flex items-center ml-3">
                <IconFile stroke="1.5" class="w-4 h-4" />

                <span class="ml-1 leading-none">
                  {{ props.task.files_count }}
                </span>
              </div>

              <div
                v-tooltip="props.task.user.name"
                class="ml-auto">

                <img
                  class="w-6 h-6 rounded-full"
                  :src='props.task.user.avatar_url'/>

              </div>
            </div>
          </div>
        </div>
      </template>

    </div>

    <Modal :show="showTaskDetail" @close="closeModal" max-width="lg">

      <div class="relative bg-white dark:bg-gray-800">

        <div class="sticky top-0 z-10 flex items-center gap-4 px-5 pt-3 pb-4 mb-4 bg-white border-b border-gray-300 rounded-t dark:bg-gray-800 sm:mb-5 dark:border-gray-600">

          <h2 class="text-xl font-semibold prose text-gray-900 dark:text-white">
            Task detail
          </h2>

          <span class="flex-1"></span>

          <button
            type="submit"
            @click="isAddingComment = !isAddingComment"
            class="text-white flex items-center gap-2 bg-primary-700 hover:bg-primary-800 font-medium rounded text-sm px-2 py-1.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700">
            <IconMessageX class="w-5" v-if="isAddingComment" />
            <IconMessage class="w-5" v-else />
          </button>

          <button
            type="button"
            @click="showTaskDetail = false"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <IconX class="w-5 h-5" />
            <span class="sr-only">Close modal</span>
          </button>

        </div>

        <section class="px-5">

          <article
            v-if="!isAddingComment"
            class="grid gap-6 mb-4 sm:grid-cols-2">

            <div class="sm:col-span-2">

              <p
                class="text-gray-900 text-3xl font-thin block w-full py-2.5 dark:text-white">
                {{ props.task.name }}
              </p>

            </div>

            <section class="grid gap-6 sm:grid-cols-2 sm:col-span-2">

              <div class="relative p-2 overflow-hidden border border-gray-300 rounded-lg dark:border-gray-700">

                <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">

                  <span>Assigned to</span>

                  <IconUser stroke="2" class="absolute h-36 w-36 -top-8 -right-10 rotate-12 -z-0" />

                </label>

                <p
                  class="relative text-gray-900 font-thin block w-full py-2.5 dark:text-white">
                  {{ props.task?.user?.name }}
                </p>
              </div>

              <div class="relative p-2 overflow-hidden border border-gray-300 rounded-lg dark:border-gray-700">
                <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">

                  <span>Priority</span>

                  <IconClock stroke="2" class="absolute h-36 w-36 -top-8 -right-10 rotate-12 -z-0" />

                </label>

                <p
                  class="relative capitalize py-2.5 max-w-[20%]">

                  <Priority :priority="props.task.priority" font-size="text-md font-thin">
                    {{ props.task.priority }}
                  </Priority>

                </p>

              </div>

            </section>

            <div class="sm:col-span-2" v-if="!! props.task.description">

              <label class="block mb-2 text-sm font-medium text-gray-400 dark:text-gray-500">
                Description
              </label>

              <article
                v-html="props.task.description"
                class="block py-2.5 w-full prose dark:prose-invert rounded-lg">
              </article>

            </div>

            <div class="sm:col-span-2">

              <label class="block mb-2 text-lg font-medium text-gray-400 dark:text-gray-500">
                Discussions
              </label>

              <CommentShell>

                <CommentItem v-for="comment in props.task.comments" :comment="comment" :key="comment.id" />

              </CommentShell>

            </div>

          </article>

          <article v-else>

            <CommentForm :task="props.task" :cancel="cancelComment" />

          </article>

        </section>

      </div>

    </Modal>

  </li>
</template>

<style scoped>
.drag {
  cursor: move;
}

.drag > div {
  transform: rotate(5deg);
}

.list-group-item {
  cursor: move;
}

.ghost {
  /* @apply bg-rose-600/40 rounded-lg opacity-30; */
  background: lightgray;
  border-radius: 6px;
}

.ghost > div {
  /* @apply opacity-25 */
  visibility: hidden;
}

.flip-list-move {
  transition: transform 0.5s;
}

.no-move {
  transition: transform 0s;
}

.list-group {
  min-height: 20px;
}

.list-group-item {
  cursor: move;
}

.list-group-item i {
  cursor: pointer;
}


/* <!--      <a-->
<!--        class="text-sm"-->
<!--        href="#">-->
<!--        {{ task.name }}-->
<!--      </a>-->

<!--      <button-->
<!--        class="absolute hidden w-8 h-8 text-gray-600 rounded-md top-1 right-1 bg-gray-50 group-hover:grid place-content-center hover:text-black hover:bg-gray-200"-->
<!--        @click="showForm()">-->
<!--        <IconPencil class="w-5 h-5"/>-->
<!--      </button>--> */
</style>
