import { Tag } from '@/types'
import { defineStore } from 'pinia'
import { reactive, ref, toRefs } from 'vue'

interface TagState {
  tags: Tag[]
  tag: Tag
}

interface SlickTag {
  label: string
  value: number
}

export const useTagStore = defineStore('tag', () => {
  const state: TagState = reactive({
    tags: ref<Tag[]>([]),
    tag: ref<Tag>({}),
  })

  const { ...reactiveState } = toRefs(state)

  function setTags(tags: SlickTag[]) {
    state.tags = tags.map((tag) => ({
      name: tag.label,
      id: tag.value
    }));
  }

  return { ...reactiveState, setTags }
})
