import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useFieldStore = defineStore('field', () => {

  const hasMiddleName = ref<boolean>(false)
  const hasTitle = ref<boolean>(false)
  const hasNickname = ref<boolean>(false)
  const hasJobTitle = ref<boolean>(false)
  const hasFirm = ref<boolean>(false)
  const hasAddress = ref<boolean>(false)
  const hasSlogan = ref<boolean>(false)
  const hasUrl = ref<boolean>(false)

  const showTag = computed(() => !hasMiddleName.value || !hasTitle.value || !hasNickname.value)

  function toggleField(fieldKey: string) {

    const field = {
      'hasMiddleName': hasMiddleName,
      'hasAddress': hasAddress,
      'hasFirm': hasFirm,
      'hasJobTitle': hasJobTitle,
      'hasNickname': hasNickname,
      'hasTitle': hasTitle,
      'hasUrl': hasUrl,
      'hasSlogan': hasSlogan,
    }[fieldKey]

    console.log(fieldKey);

    if (field !== undefined && field !== null)
      field.value = !field.value

  }

  const unSet = () => {
    [
      hasMiddleName,
      hasTitle,
      hasNickname,
      hasJobTitle,
      hasFirm,
      hasAddress,
      hasSlogan,
      hasUrl
    ].forEach(field => {
      field.value = false
    })
  }

  return {
    hasMiddleName,
    hasTitle,
    hasNickname,
    hasJobTitle,
    hasFirm,
    hasAddress,
    hasSlogan,
    hasUrl,
    showTag,
    toggleField,
    unSet
  }
})
