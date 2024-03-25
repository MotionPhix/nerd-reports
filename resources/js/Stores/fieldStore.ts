import { defineStore } from 'pinia'
import { computed, reactive, ref, toRefs } from 'vue'

interface FiedState {
  hasMiddleName: boolean
  hasTitle: boolean
  hasNickname: boolean
  hasJobTitle: boolean
  hasFirm: Boolean
  hasAddress: Boolean
  hasSlogan: Boolean
  hasUrl: boolean
}

export const useFieldStore = defineStore('field', () => {
  const state: FiedState = reactive({
    hasMiddleName: ref(false),
    hasTitle: ref(false),
    hasNickname: ref(false),
    hasJobTitle: ref(false),
    hasFirm: ref(false),
    hasAddress: ref(false),
    hasSlogan: ref(false),
    hasUrl: ref(false),
  })

  const { ...reactiveState } = toRefs(state)

  const showTag = computed(() => !state.hasMiddleName || !state.hasTitle || !state.hasNickname)

  function toggleField(fieldKey: keyof FiedState) {
    const field = reactiveState[fieldKey]

    if (field !== undefined && field !== null)
      field.value = !field.value
  }

  const unSet = () => {
    state.hasMiddleName = false
    state.hasTitle = false
    state.hasNickname = false
    state.hasJobTitle = false
    state.hasFirm = false
    state.hasAddress = false
    state.hasSlogan = false
    state.hasUrl = false
  }

  return { showTag, ...reactiveState, toggleField, unSet }
})
