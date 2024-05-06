import { useLocalStorage } from '@/Composables/useLocalStorage'
import axios, { AxiosResponse } from "axios"
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useProjectStore = defineStore('projects', () => {

  const projects = ref<(App.Data.ProjectData)[]>([])
  const project = useLocalStorage<App.Data.ProjectFullData | 'null'>('active_project')

  async function fetchProjects(): Promise<void> {

    const response = await axios.get('/projects');
    projects.value = response.data;

  }

  async function reFetchProject(): Promise<void> {

    const resp = await axios.get(`/projects/s/${project.value.pid}`)
    project.value = resp.data

  }

  async function getProject(projectPid: string): Promise<void> {

    const resp: AxiosResponse = await axios.get(`/projects/s/${projectPid}`)
    project.value = resp.data

  }

  async function setProject(updatedProject: App.Data.ProjectFullData) {

    project.value = updatedProject

  }

  async function setProjects(projects: App.Data.ProjectData[]) {

    projects = projects

  }

  return { project, projects, fetchProjects, reFetchProject, setProject, setProjects, getProject }

})
