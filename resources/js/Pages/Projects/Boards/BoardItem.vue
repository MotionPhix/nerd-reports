<script setup>
import { IconDots, IconPlus, IconTrash } from '@tabler/icons-vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import TaskCreateForm from "../Tasks/TaskCreateForm.vue";
import TaskItem from "../Tasks/TaskItem.vue";
import { Link, router } from "@inertiajs/vue3"
import { ref, watch } from "vue"
import draggable from "vuedraggable"
import { useFormStore } from '@/Stores/formStore';
import { storeToRefs } from 'pinia';

const props = defineProps({
  board: Object
});

const boardRef = ref();

const tasks = ref(props.board.tasks);

const formStore = useFormStore()

const {
  currentBoardId
} = storeToRefs(formStore);

const {
  setCurrentBoardId,
  unSetCurrentBoardId,
} = formStore

watch(() => props.board.tasks, (newTasks) => tasks.value = newTasks);

function onTaskCreated() {
  boardRef.value.scrollTop = boardRef.value.scrollHeight;
}

function onChange(e) {
  let item = e.added || e.moved;

  if (!item) return;

  let index = item.newIndex;
  let prevTask = tasks.value[index - 1];
  let nextTask = tasks.value[index + 1];
  let task = tasks.value[index];

  let position = task.position;

  if (prevTask && nextTask) {
    position = (prevTask.position + nextTask.position) / 2;
  } else if (prevTask) {
    position = prevTask.position + (prevTask.position / 2);
  } else if (nextTask) {
    position = nextTask.position / 2;
  }

  axios.put(route('tasks.move', {task: task.id}), {
    preserveScroll: true,

    position: position,
    board_id: props.board.id
  });

}

function generateColor() {
  const hue = Math.floor(Math.random() * 360); // Generate a random hue value between 0 and 359
  const saturation = 70; // You can adjust saturation and lightness as needed
  const lightness = 60;
  const color = `hsl(${hue}, ${saturation}%, ${lightness}%)`;

  return color;
}

</script>

<template>
  <div>

    <div class="flex items-center justify-between px-3 py-2">

      <h3 class="font-semibold text-xl">
        {{ props.board.name }}
      </h3>

      <Menu
        as="div"
        class="relative z-10">

        <MenuButton class="hover:bg-gray-300 dark:hover:bg-gray-900 w-8 h-8 rounded-md grid place-content-center">

          <IconDots class="w-5 h-5" />

        </MenuButton>

        <transition
          enter-active-class="transition transform duration-100 ease-out"
          enter-from-class="opacity-0 scale-90"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition transform duration-100 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-90">

          <MenuItems
            class="overflow-hidden absolute right-0 top-5 w-40 dark:text-gray-300 bg-white rounded-md dark:bg-gray-800 dark:border-gray-700 border shadow-lg origin-top-left focus:outline-none">

            <MenuItem v-slot="{ active }">

              <button
                :class="{ 'bg-gray-100': active }"
                class="flex items-center gap-2 px-4 py-2 text-sm dark:hover:bg-gray-600 w-full"
                @click="setCurrentBoardId(props.board.id)">
                <IconPlus stroke="2.5" class="h-4 w-4" />
                <span>Add task</span>
              </button>

            </MenuItem>

            <MenuItem
              v-slot="{ active }">
              <Link
                as="button"
                method="delete"
                class="flex items-center gap-2 px-4 py-2 text-sm dark:hover:bg-gray-600 w-full"
                :href="route('boards.destroy', { project: props.board.project_id, board: props.board })">
                <IconTrash stroke="2.5" class="h-4 w-4" />
                <span>Delete board</span>
              </Link>
            </MenuItem>

          </MenuItems>

        </transition>

      </Menu>

    </div>

    <div class="pb-3 flex flex-col overflow-hidden">

      <div
        ref="boardRef"
        class="px-3 flex-1 overflow-y-auto text-rose-500">

      <div
        ref="boardRef"
        class="flex-1 overflow-y-auto h-[70dvh] scrollbar-none">

        <draggable
          v-model="tasks"
          group="tasks"
          item-key="id"
          class="h-full mt-2 space-y-6 cursor-move"
          ghost-class="ghost"
          drag-class="drag"
          tag="ul"
          @change="onChange"
        >
          <template #item="{ element }">
            <TaskItem
              :task="element" :style="{ 'background-color': generateColor() }" />
          </template>
        </draggable>

      </div>

      </div>

      <div class="px-3 mt-3">
        <TaskCreateForm
          :board="props.board"
          @created="onTaskCreated()" />
      </div>
    </div>
  </div>
</template>
