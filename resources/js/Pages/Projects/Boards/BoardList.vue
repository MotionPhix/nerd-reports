<script setup lang="ts">
import { IconDots } from '@tabler/icons-vue';
import BoardNameForm from '../Boards/BoardNameForm.vue';
import BoardCreateForm from '../Boards/BoardCreateForm.vue';
import BoardItem from "../Boards/BoardItem.vue";

const props = defineProps({
  project: Object
});
</script>

<template>
  <div class="flex-1 overflow-x-auto scrollbar-thin scroll-smooth px-6">

    <div class="inline-flex items-start space-x-4">

      <BoardItem
        v-for="board in project.boards"
        :key="board.id"
        :board="board"
        class="w-80 dark:text-gray-200 max-h-full flex flex-col rounded-md" />

        <div>

          <BoardCreateForm :project="props.project" />

        </div>

    </div>

  </div>

</template>
