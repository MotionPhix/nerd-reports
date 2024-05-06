<script setup>
import { IconDots, IconPlus, IconTrash } from '@tabler/icons-vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import TaskCreateForm from "@/Pages/Projects/Tasks/TaskCreateForm.vue";
import TaskItem from "@/Pages/Projects/Tasks/TaskItem.vue";
import { router } from "@inertiajs/vue3"
import { ref, watch } from "vue"
import { useFormStore } from '@/Stores/formStore';
import { VueDraggableNext } from "vue-draggable-next"
import BoardNameForm from '@/Pages/Projects/Boards/BoardNameForm.vue';
import { useProjectStore } from '@/Stores/projectStore';

const props = defineProps({
  board: Object,
  boardIndex: Number
});

const boardRef = ref();

const projectStore = useProjectStore()

const { setProject } = projectStore

const tasks = ref(props.board.tasks);

const formStore = useFormStore()

const {
  setCurrentBoardId,
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

  router.put(
    route('tasks.move', {task: task.id}),

    {

      position: position,
      board_id: props.board.id

    },

    {
      preserveScroll: true,

      onError: (error) => console.log(error),

      onSuccess: (data) => setProject(data.props.project)
    },

  )

}

const dragOptions = {
  animation: 1,
  group: "tasks",
  disabled: false,
  ghostClass: "ghost",
  dragClass: "drag"
}

function deleteBoard () {
  router.
    delete(route('boards.destroy', { project: props.board.project_id, board: props.board.id }), {

      preserveScroll: true,

      onError: (err) => {
        console.log(err);
      },

      onSuccess: () => {
        reFetchProject()
      }

    })
}
</script>

<template>
  <div>

    <div class="flex items-center justify-between px-3 py-2">

      <BoardNameForm :board="props.board" />

      <Menu
        as="div"
        class="relative z-20">

        <MenuButton
          class="grid w-8 h-8 rounded-md hover:bg-gray-300 dark:hover:bg-gray-900 place-content-center">

          <IconDots stroke="3" class="w-5 h-5" />

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
                @click="setCurrentBoardId(props.board.id)">
                <IconPlus stroke="2.5" class="w-4 h-4" />
                <span>Add task</span>
              </button>

            </MenuItem>

            <MenuItem>

              <button
                type="button"
                @click="deleteBoard(props.board)"
                class="flex items-center w-full gap-2 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">

                <IconTrash stroke="2.5" class="w-4 h-4" />

                <span>Delete board</span>

              </button>

            </MenuItem>

          </MenuItems>

        </transition>

      </Menu>

    </div>

    <div class="flex flex-col pb-3 overflow-hidden">

      <div
        ref="boardRef"
        class="flex-1 overflow-y-auto h-[70dvh] scrollbar-none">

        <VueDraggableNext
          v-model="tasks"
          item-key="id"
          class="mt-2 space-y-6 cursor-move"
          v-bind="dragOptions"
          @change="onChange"
          tag="ul">

          <transition-group name="flip-list">

            <TaskItem
              :task="task"
              :key="task.id"
              v-for="task in tasks"
              :board-id="props.boardIndex"
              class="list-group-item" />

          </transition-group>

        </VueDraggableNext>

      </div>

      <div class="px-3 mt-3">
        <TaskCreateForm
          :board="props.board"
          @created="onTaskCreated()" />
      </div>
    </div>
  </div>
</template>
