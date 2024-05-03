import axios from 'axios'
import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface ProjectState {
  projects: (App.Data.ProjectData|undefined)[]
  project: App.Data.ProjectFullData|null
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

  async function reFetchProject() {

    const resp = await axios.get(`/projects/s/${state.project.pid}`)
    state.project = resp.data

  }

  async function getProject(projectPid: string) {

    const resp = await axios.get(`/projects/s/${projectPid}`)
    state.project = resp.data

  }

  async function setProject(project: App.Data.ProjectFullData) {

    state.project = project

  }

  async function setProjects(projects: App.Data.ProjectData[]) {

    state.projects = projects

  }

  return { ...reactiveState, fetchProjects, reFetchProject, setProject, setProjects, getProject }

})
