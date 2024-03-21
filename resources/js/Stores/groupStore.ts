import { Group } from '@/types'
import axios from 'axios'
import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface GroupState {
  selectedGroups: (string|undefined)[]
  activeGroup: Group
  isEditing: boolean
  isCreating: boolean
  groups: Group[]
}

export const useGroupStore = defineStore('group', () => {
  const state: GroupState = reactive({
    selectedGroups: ref<(string|undefined)[]>([]),
    activeGroup: ref<Group>(),
    isEditing: ref<boolean>(false),
    isCreating: ref<boolean>(false),
    groups: ref([]),
  })

  const { ...reactiveState } = toRefs(state)

  async function fetchGroups(): Promise<void> {
    const response = await axios.get('/g/i');
    state.groups = response.data;
  }

  async function createOrUpdateGroup(group: Group): Promise<void> {

    const existingGroupIndex = state.groups.findIndex((c) => c.name.toLowerCase() === group.name.toLowerCase());

    if (existingGroupIndex !== -1) {
      // Save to the database
      await axios.patch(`/g/update/${group.gid}`, group);
    } else {
      // Save to the database
      await axios.post('/g/store', group);
    }

    // Fetch the latest data from the database and update the store
    await fetchGroups();

  }

  function setActiveGroup(group: Group | undefined) {
    state.activeGroup = group
  }

  function resetActiveGroup() {
    state.activeGroup = {
      name: '',
      gid: '',
      id: null,
      contacts: []
    }
  }

  function setGroups(groups: Group[]) {
    state.groups = groups
  }

  function resetGroups() {
    state.groups = []
  }

  function addToSelectedGroups(groupId: string | undefined) {
    state.selectedGroups.push(groupId)
  }

  function removeFromSelectedGroups(groupId: string | undefined) {
    state.selectedGroups = (state.selectedGroups.filter(gid => gid !== groupId))
  }

  function resetSelectedGroups() {
    state.selectedGroups = []
  }

  function toggleIsEditing() {
    state.isEditing = !state.isEditing
  }

  function toggleIsCreating() {
    state.isCreating = !state.isCreating
  }

  return {
    ...reactiveState,
    fetchGroups,
    setActiveGroup,
    resetActiveGroup,
    setGroups,
    resetGroups,
    toggleIsEditing,
    toggleIsCreating,
    createOrUpdateGroup,
    resetSelectedGroups,
    addToSelectedGroups,
    removeFromSelectedGroups,
  }
})
