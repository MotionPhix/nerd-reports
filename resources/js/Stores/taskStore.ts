import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from "axios"
import { useLocalStorage } from "@/Composables/useLocalStorage"

export const useTaskStore = defineStore('task', () => {
  const task = useLocalStorage<App.Data.TaskData>('active_task')
  const isEditing = ref<boolean>(false)

  function setTask(newTask: App.Data.TaskData) {

    task.value = newTask

  }

  function setIsEditing() {

    isEditing.value = true

  }

  const unSet = () => {

    task.value = null

    isEditing.value = false

  }

  async function reFetchTask() {

    await axios.get(route('tasks.show', { task: task.value.tid }))
      .catch((err) => console.log(err))
      .then(({ data }) => {

        task.value = data

      })

  }

  return {
    task,
    isEditing,
    setTask,
    setIsEditing,
    unSet,
    reFetchTask
  }
})
