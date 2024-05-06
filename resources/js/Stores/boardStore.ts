import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useBoardStore = defineStore('board', () => {

  const board = ref<App.Data.BoardData | null>()

  function setBoard(newBoard: App.Data.BoardData) {

    board.value = newBoard

  }

  const unSetBoard = () => {

    board.value = null

  }

  return { board, setBoard, unSetBoard }
})
