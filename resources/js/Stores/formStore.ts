import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useFormStore = defineStore('forms', () => {

  const currentTaskId = ref<number | null>(null)
  const currentBoardId = ref<number | null>(null)

  function setCurrentTaskId(taskId: number) {

    currentTaskId.value = taskId

  }

  function setCurrentBoardId(boardId: number) {

    currentBoardId.value = boardId

  }

  function unSetCurrentTaskId() {

    currentTaskId.value = null

  }

  function unSetCurrentBoardId() {

    currentBoardId.value = null

  }

  return {
    currentTaskId,
    currentBoardId,
    setCurrentTaskId,
    setCurrentBoardId,
    unSetCurrentTaskId,
    unSetCurrentBoardId
  }
})
