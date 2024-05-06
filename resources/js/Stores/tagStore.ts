import { defineStore } from 'pinia'
import { ref } from 'vue'

interface SlickTag {
  label: string
  value: number
}

interface Tag {
  id?: number;
  name?: string;
  slug?: string;
}

export const useTagStore = defineStore('tag', () => {
  const tags = ref<Tag[]>([])
  const tag = ref<Tag | null>()

  function setTags(newTags: SlickTag[]) {

    tags.value = newTags.map((tag) => ({

      name: tag.label,
      id: tag.value

    }));

  }

  return { tag, tags, setTags }
})
