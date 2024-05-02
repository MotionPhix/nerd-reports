<script setup lang="ts">
import BoardCreateForm from '@/Pages/Projects/Boards/BoardCreateForm.vue';
import BoardItem from "@/Pages/Projects/Boards/BoardItem.vue";
import { useProjectStore } from '@/Stores/projectStore';
import { storeToRefs } from 'pinia';

const projectStore = useProjectStore()
const { project } = storeToRefs(projectStore)

</script>

<template>
  <div class="flex-1 px-6 overflow-x-auto scrollbar-thin scroll-smooth">

    <div class="inline-flex items-start space-x-4">

      <BoardItem
        v-for="(board, idx) in project.boards"
        :key="board.id"
        :board="board"
        :boardIndex="idx"
        class="flex flex-col max-h-full rounded-md w-80 dark:text-gray-200" />

        <div>

          <BoardCreateForm />

        </div>

    </div>

  </div>

</template>
