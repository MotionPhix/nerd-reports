import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface BoardState {
  board: App.Data.BoardData
}
export const useBoardStore = defineStore('board', () => {
  const state: BoardState = reactive({
    board: ref<App.Data.BoardData>(),
  })

  const { ...reactiveState } = toRefs(state)

  function setBoard(board: App.Data.BoardData) {
    state.board = board
  }

  const unSetBoard = () => {
    state.board = null
  }

  return { ...reactiveState, setBoard, unSetBoard }
})
