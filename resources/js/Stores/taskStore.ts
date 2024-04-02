import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'
import axios from "axios"

interface TaskState {
  task: App.Data.TaskData
  isEditing: boolean
}
export const useTaskStore = defineStore('task', () => {
  const state: TaskState = reactive({
    task: ref<App.Data.TaskData>(),
    isEditing: ref<boolean>(false),
  })

  const { ...reactiveState } = toRefs(state)

  function setTask(task: App.Data.TaskData) {
    state.task = task
  }

  function setIsEditing() {
    state.isEditing = true
  }
  const unSet = () => {
    state.task = null
    state.isEditing = false
  }

  return { ...reactiveState, setTask, setIsEditing, unSet }
})
