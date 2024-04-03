import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface FormState {
  currentTaskId: number
  currentBoardId: number
}

export const useFormStore = defineStore('forms', () => {
  const state: FormState = reactive({
    currentTaskId: ref(null),
    currentBoardId: ref(null),
  })

  const { ...reactiveState } = toRefs(state)

  function setCurrentTaskId(taskId: number) {
    state.currentTaskId = taskId
  }

  function setCurrentBoardId(boardId: number) {
    state.currentBoardId = boardId
  }

  function unSetCurrentTaskId() {
    state.currentTaskId = null
  }

  function unSetCurrentBoardId() {
    state.currentBoardId = null
  }

  return { ...reactiveState, setCurrentTaskId, setCurrentBoardId, unSetCurrentTaskId, unSetCurrentBoardId }
})
