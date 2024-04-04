<script setup>
import { IconDots, IconPlus, IconTrash } from '@tabler/icons-vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import TaskCreateForm from "../Tasks/TaskCreateForm.vue";
import TaskItem from "../Tasks/TaskItem.vue";
import { Link } from "@inertiajs/vue3"
import { ref, watch } from "vue"
import { useFormStore } from '@/Stores/formStore';
import { storeToRefs } from 'pinia';
import { VueDraggableNext } from "vue-draggable-next"
import VueDraggable from "vue3-draggable-next"
import { Container, Draggable } from "vue-dndrop";
import { applyDrag } from "../../Utils";

const props = defineProps({
  board: Object
});

const boardRef = ref();

const drag = ref(false)

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

    position: position,
    board_id: props.board.id

  }).then((resp) => {
    // console.log(resp.data);
  });

}

function generateColor() {
  const hue = Math.floor(Math.random() * 360), saturation = 70, lightness = 40
  return `hsl(${hue}, ${saturation}%, ${lightness}%`
}

const onCheckMove = function(e) {

  console.log(e);
}

const onDrop = (dropResult) => {
  tasks.value = applyDrag(tasks.value, dropResult);
};

const dragOptions = {
  animation: 0,
  group: "description",
  disabled: false,
  ghostClass: "ghost"
}
</script>

<template>
  <div>

    <div class="flex items-center justify-between px-3 py-2">

      <h3 class="text-xl font-semibold">
        {{ props.board.name }}
      </h3>

      <Menu
        as="div"
        class="relative z-10">

        <MenuButton class="grid w-8 h-8 rounded-md hover:bg-gray-300 dark:hover:bg-gray-900 place-content-center">

          <IconDots class="w-5 h-5" />

        </MenuButton>

        <transition
          enter-active-class="transition duration-100 ease-out transform"
          enter-from-class="scale-90 opacity-0"
          enter-to-class="scale-100 opacity-100"
          leave-active-class="transition duration-100 ease-in transform"
          leave-from-class="scale-100 opacity-100"
          leave-to-class="scale-90 opacity-0">

          <MenuItems
            class="absolute right-0 w-40 overflow-hidden origin-top-left bg-white border rounded-md shadow-lg top-5 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:outline-none">

            <MenuItem v-slot="{ active }">

              <button
                :class="{ 'bg-gray-100': active }"
                class="flex items-center w-full gap-2 px-4 py-2 text-sm dark:hover:bg-gray-600"
                @click="setCurrentBoardId(props.board.id)">
                <IconPlus stroke="2.5" class="w-4 h-4" />
                <span>Add task</span>
              </button>

            </MenuItem>

            <MenuItem
              v-slot="{ active }">
              <Link
                as="button"
                method="delete"
                class="flex items-center w-full gap-2 px-4 py-2 text-sm dark:hover:bg-gray-600"
                :href="route('boards.destroy', { project: props.board.project_id, board: props.board })">
                <IconTrash stroke="2.5" class="w-4 h-4" />
                <span>Delete board</span>
              </Link>
            </MenuItem>

          </MenuItems>

        </transition>

      </Menu>

    </div>

    <div class="flex flex-col pb-3 overflow-hidden">

      <div
        ref="boardRef"
        class="flex-1 px-3 overflow-y-auto text-rose-500">

      <div
        ref="boardRef"
        class="flex-1 overflow-y-auto h-[70dvh] scrollbar-none">

        <Container @drop="onDrop" tag="ul" group-name="tasks">

          <Draggable v-for="task in props.board.tasks" :key="task.id" tag="li">

            <TaskItem
              :task="task"
              :style="{ 'background-color': generateColor() }"
              class="list-group-item" />

          </Draggable>

        </Container>

        <!-- <VueDraggableNext
          v-model="tasks"
          group="tasks"
          item-key="id"
          class="h-full mt-2 space-y-6 cursor-move"
          ghost-class="ghost"
          drag-class="drag"
          :move="onCheckMove"
          @change="onChange"
          @start="drag = true"
          @end="drag = false"
          tag="ul">

          <transition-group name="flip-list">

            <TaskItem
              :task="task"
              :key="task.id"
              :style="{ 'background-color': generateColor() }"
              v-for="task in tasks"
              class="list-group-item" />

          </transition-group>

        </VueDraggableNext>  -->

         <!-- <vue-draggable
          item-key="id"
          drag-class="drag"
          ghost-class="ghost"
          tag="transition-group"
          class="mt-2 space-y-6 cursor-move list-group"
          :component-data="{ tag: 'ul', name: 'flip-list', type: 'transition' }"
          v-model="tasks"
          v-bind="dragOptions"
          group="tasks"
          :move="onCheckMove"
          @start="drag = true"
          @end="drag = false"
          @change="onChange">

          <template #item="{ element }">

            <TaskItem
              :task="element"
              :key="element.id"
              :style="{ 'background-color': generateColor() }"
              class="list-group-item" />

          </template>

        </vue-draggable> -->

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
