import axios from 'axios'
import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface ProjectState {
  projects: (App.Data.ProjectData|undefined)[]
  project: App.Data.ProjectFullData
}

export const useProjectStore = defineStore('projects', () => {

  const state: ProjectState = reactive({

    projects: ref<(App.Data.ProjectData|undefined)[]>([]),
    project: ref(),

  })

  const { ...reactiveState } = toRefs(state)

  async function fetchProjects(): Promise<void> {

    const response = await axios.get('/projects');
    state.projects = response.data;

  }

  async function getProject(projectId: string) {

    const resp = await axios.get(`/projects/s/${projectId}`)
    state.project = resp.data

    console.log('it happening');


  }

  return { ...reactiveState, fetchProjects, getProject }
})
