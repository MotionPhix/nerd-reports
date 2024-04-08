<script setup lang="ts">
import { IconDots } from '@tabler/icons-vue';
import BoardNameForm from '@/Pages/Projects/Boards/BoardNameForm.vue';
import BoardCreateForm from '@/Pages/Projects/Boards/BoardCreateForm.vue';
import BoardItem from "@/Pages/Projects/Boards/BoardItem.vue";

const props = defineProps<{
  project: App.Data.ProjectFullData;
}>();
</script>

<template>
  <div class="flex-1 px-6 overflow-x-auto scrollbar-thin scroll-smooth">

    <div class="inline-flex items-start space-x-4">

      <BoardItem
        v-for="(board, idx) in props.project.boards"
        :key="board.id"
        :board="board"
        :boardIndex="idx"
        class="flex flex-col max-h-full rounded-md w-80 dark:text-gray-200" />

        <div>

          <BoardCreateForm :project="props.project" />

        </div>

    </div>

  </div>

</template>
