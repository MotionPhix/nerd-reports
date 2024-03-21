import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface MenuState {
  isOpen: boolean
}

export const useMenuStore = defineStore('menu', () => {
  const state: MenuState = reactive({
    isOpen: ref(false),
  })

  const { ...reactiveState } = toRefs(state)

  function toggleOpen() {
    state.isOpen = !state.isOpen
  }

  return { ...reactiveState, toggleOpen }
})
