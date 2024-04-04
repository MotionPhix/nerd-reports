<script setup>
import { useForm } from "@inertiajs/vue3";
import {IconDotsVertical, IconCalendar, IconMessage } from "@tabler/icons-vue";
import { computed, nextTick, ref } from "vue"
import { useFormStore } from "@/Stores/formStore"
import { storeToRefs } from "pinia"
import Priority from "@/Components/Priority.vue"

const props = defineProps({
  task: Object
});

const formStore = useFormStore()

const {
  currentTaskId
} = storeToRefs(formStore);

const {
  setCurrentTaskId,
  unSetCurrentTaskId,
} = formStore

const inputTitleRef = ref();

const isShowingForm = computed(() => props.task.id === currentTaskId.value);

const form = useForm({
  name: props.task.name,
});

async function showForm() {
  setCurrentTaskId(props.task.id)

  await nextTick();

  inputTitleRef.value.focus();
}

// function onSubmit() {
//
//   form.put(route('tasks.update', {task: props.task.id}), {
//
//     onSuccess: () => unSetCurrentTaskId()
//
//   });
//
// }

function generateColor() {
  let color;
  do {
    const hue = Math.floor(Math.random() * 360); // Generate a random hue value between 0 and 359
    const saturation = Math.floor(Math.random() * 70) + 20; // Generate a random saturation value between 20 and 90
    const lightness = Math.floor(Math.random() * 50) + 25; // Generate a random lightness value between 25 and 75
    color = `hsl(${hue}, ${saturation}%, ${lightness}%)`;
  } while (isBlackOrWhite(color)); // Repeat until the color is not black or white
  return color;
}

function isBlackOrWhite(color) {
  // Convert the color to RGB
  const rgb = color
    .replace('hsl(', '')
    .replace(')', '')
    .split(',')
    .map((value) => parseFloat(value));

  // Calculate the luminance (brightness) of the color
  const luminance = (0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2]) / 255;

  // Check if the luminance is close to 0 (black) or 1 (white)
  return luminance < 0.1 || luminance > 0.9;
}

</script>

<template>
  <li
    class="relative p-2 group rounded-xl">

    <form
      v-if="isShowingForm"
      @keydown.esc="unSetCurrentTaskId()"
      @submit.prevent="onSubmit()">

      <textarea
        ref="inputTitleRef"
        v-model="form.name"
        class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-400 focus:ring-blue-400"
        placeholder="Enter task title..."
        rows="3"
        @keydown.enter.prevent="onSubmit()">
      </textarea>

      <div class="mt-2 space-x-2">
        <button
          class="px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm bg-rose-600 hover:bg-rose-500 focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
          type="submit">
          Save task
        </button>

        <button
          class="px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-black focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 focus:outline-none"
          type="button"
          @click="unSetCurrentTaskId()">
          Cancel
        </button>
      </div>

    </form>

    <template v-if="!isShowingForm">
<!--      <a-->
<!--        class="text-sm"-->
<!--        href="#">-->
<!--        {{ task.name }}-->
<!--      </a>-->

<!--      <button-->
<!--        class="absolute hidden w-8 h-8 text-gray-600 rounded-md top-1 right-1 bg-gray-50 group-hover:grid place-content-center hover:text-black hover:bg-gray-200"-->
<!--        @click="showForm()">-->
<!--        <IconPencil class="w-5 h-5"/>-->
<!--      </button>-->

      <div class="flex flex-col p-2">

        <div class="relative flex flex-col items-start">
          <button class="absolute top-0 right-0 items-center justify-center hidden w-5 h-5 text-gray-500 rounded hover:bg-gray-200 hover:text-gray-700 group-hover:flex">
            <IconDotsVertical stroke="2.5" class="w-4 h-4" />
          </button>

          <Priority :priority="props.task.priority">
            {{ props.task.priority }}
          </Priority>

          <h4 class="mt-3 text-2xl font-medium text-gray-800 font-display">
            {{ props.task.name }}
          </h4>

          <section v-html="props.task.description" class="mt-6 text-xs prose dark:prose-invert line-clamp-2" />

          <div class="flex items-center w-full mt-3 text-xs font-medium text-gray-200">

            <div class="flex items-center">
              <IconCalendar class="w-4 h-4" />

              <span class="ml-1 leading-none">
                {{ props.task.created_at }}
              </span>
            </div>

            <div class="relative flex items-center ml-4">
              <IconMessage class="w-4 h-4" />

              <span class="ml-1 leading-none">4</span>
            </div>

            <img
              class="w-6 h-6 ml-auto rounded-full"
              src='https://randomuser.me/api/portraits/women/26.jpg'/>
          </div>
        </div>
      </div>
    </template>

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
  @apply bg-rose-600/40 rounded-lg;
  opacity: 0.5;
  background: #c8ebfb;
}

.ghost > div {
  opacity: 0.75;
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
</style>
