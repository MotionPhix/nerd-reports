<script setup lang="ts">
import type { Board } from '@/types'
import { useForm, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import { nextTick, ref, watch } from 'vue'

const props = defineProps<{
  board: Board
}>()

const page = usePage()

const form = useForm({
  name: props.board.name,
})

const isEditing = ref(false)
const input = ref()

const lag = debounce(function() {

  form.patch(route('boards.update', props.board.id))

}, 1500)

watch(() => form.name, async (newValue, oldValue) => {

  if (!form.name) return form.reset()

  await nextTick()

  lag()

})

async function cancel() {

  isEditing.value = false

  await nextTick()

}

async function edit() {

  isEditing.value = true

  await nextTick()

  input.value.select()

}
</script>

<template>
  <div class="relative flex flex-col items-start">
    <h3
      class="text-xs border font-semibold uppercase truncate whitespace-pre w-full overflow-hidden text-ellipsis border-transparent leading-none tracking-tight text-gray-900 dark:text-white px-3 py-1.5 hover:bg-gray-400 hover:text-gray-900 cursor-pointer rounded-md transition duration-300"
      :class="[isEditing ? 'invisible' : '']"
      @click="edit()"
    >
      {{ form.name ?? ' ' }}
    </h3>

    <div v-if="isEditing">
      <input
        ref="input"
        v-model="form.name"
        type="text"
        @focusout="cancel()"
        placeholder="Board name"
        class="absolute inset-0 px-3 text-xs uppercase font-semibold placeholder-gray-500 py-1.5 rounded-md focus:ring-2 dark:text-gray-800 focus:ring-lime-600">
    </div>
  </div>
</template>
