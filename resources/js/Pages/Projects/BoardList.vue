<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'

import CreateTaskForm from '../Tasks/CreateTaskForm.vue'

import Task from '../Tasks/Task.vue'

import RenameBoard from './RenameBoard.vue'

import type { Board } from '@/types'

import ConfirmDialog from '@/Shared/ConfirmDialog.vue'
import { IconDotsVertical } from '@tabler/icons-vue'

const props = defineProps<Props>()

const tasks = ref(props.board.tasks)

watch(() => props.board.tasks, newTasks => tasks.value = newTasks)

interface Props {
  board: Board
}

const boardRef = ref()

function onTaskCreated() {
  boardRef.value.scrollTop = boardRef.value.scrollHeight
}

function onChange(e) {
  const task = e.added || e.moved

  if (!task)
    return

  const idx = task.newIndex
  const prevTask = tasks.value[idx - 1]
  const nextTask = tasks.value[idx + 1]
  let placement = task.element.postion

  if (prevTask && nextTask)
    position = (prevTask?.position + nextTask?.position) / 2

  else if (prevTask)
    placement = prevTask.position + (prevTask.position / 2)

  else if (nextTask)
    placement = nextTask.position / 2

  router.put(route('tasks.move', task.element.id), {
    position: placement,
    board_id: props.board.id,
  })
}

const showDialog = ref(false)

function deleteBoard() {
  router.delete(route('boards.destroy', props.board.id), {

    onError: error => console.log(error),

    onSuccess: () => {
      showDialog.value = false
    },

  })

  showDialog.value = false
}
</script>

<template>
  <article class="text-gray-700 border-2 border-gray-200 rounded-xl dark:bg-transparent dark:text-gray-100 dark:border-gray-700">
    <div class="flex items-center justify-between px-3 py-2">
      <!-- <h3 class="text-xs font-semibold uppercase truncate">
        {{ board.name }}
      </h3> -->

      <RenameBoard :board="board" />

      <Menu as="div" class="relative z-10">
        <MenuButton class="grid w-8 h-8 transition duration-300 rounded-md hover:bg-gray-300 hover:text-gray-800 place-content-center">
          <IconDotsVertical class="w-5 h-5" />
        </MenuButton>

        <transition
          enter-active-class="transition duration-100 ease-out transform"
          enter-from-class="scale-90 opacity-0"
          enter-to-class="scale-100 opacity-100"
          leave-active-class="transition duration-100 ease-in transform"
          leave-from-class="scale-100 opacity-100"
          leave-to-class="scale-90 opacity-0"
        >
          <MenuItems class="absolute mt-2 overflow-hidden font-semibold text-gray-900 origin-top-right bg-white border border-gray-200 rounded-md shadow-md w-36 right-2 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white">

            <MenuItem v-slot="{ active }">
              <button as="button" :class="{ 'bg-gray-100': active }" class="block w-full px-4 py-2 text-sm text-left transition duration-300 text-rose-700" @click="showDialog = true">
                Delete board
              </button>
            </MenuItem>

          </MenuItems>

        </transition>
      </Menu>
    </div>

    <div class="flex flex-col max-w-sm pb-3 overflow-hidden bg-white border border-gray-200 rounded-b-lg dark:bg-gray-800 dark:border-gray-700">
      <section ref="boardRef" class="relative flex-1 px-3 scrollbar-none overflow-y-auto">
        <draggable
          v-model="tasks"
          group="tasks"
          item-key="id"
          class="mt-2 space-y-3"
          ghost-class="ghost"
          drag-class="drag"
          tag="ul"
          @change="onChange"
        >
          <template #item="{ element }">
            <Task :task="element" />
          </template>
        </draggable>
      </section>

      <div class="px-3 mt-4">
        <CreateTaskForm :board="board" class="z-20 shadow-lg" @created="onTaskCreated" />

        <ConfirmDialog v-if="showDialog" @cancel="showDialog = false" @confirm="deleteBoard()">
          <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
              <div class="p-6">
                <svg aria-hidden="true" class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                  Are you sure you want to delete this board? All tasks on this board will be deleted, too
                </h3>
              </div>
            </div>
          </div>
        </ConfirmDialog>
      </div>
    </div>
  </article>
</template>
