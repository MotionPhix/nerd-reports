import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'
import axios from "axios"
import { useToastStore } from "./toastStore"
import { useLocalStorage } from "../Composables/useLocalStorage"

interface TaskState {
  task: App.Data.TaskData
  isEditing: boolean
}
export const useTaskStore = defineStore('task', () => {
  const state: TaskState = reactive({
    task: useLocalStorage<App.Data.TaskData>('active_task'),
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

  async function reFetchTask() {
    await axios.get(route('tasks.show', { task: state.task.tid }))
      .catch((err) => console.log(err))
      .then((resp) => {
        state.task = resp.data
      })
  }

  return { ...reactiveState, setTask, setIsEditing, unSet, reFetchTask }
})
