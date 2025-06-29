<script setup>
import { IconCamera } from '@tabler/icons-vue';
import { ref } from 'vue';

const props = defineProps({
  modelValue: File,
  defaultSrc: String
})

const file = ref()

const src = ref(props.defaultSrc)

const emit = defineEmits(['update:modelValue']);

const browse = () => {
  file.value.click()
}

const onChange = (e) => {
  file.value = e.target.files[0]

  emit('update:modelValue', file.value);

  let reader = new FileReader()
  reader.readAsDataURL(file.value)
  reader.onload = (e) => {
    src.value = e.target.result
  }
}
</script>

<template>
  <div class="relative inline-block">
    <input type="file" accept="image/*" class="hidden" ref="file" @change="onChange" />

    <img :src="src" alt="profile picture" class="h-full w-full rounded-3xl object-cover">

    <section class="absolute top-0 h-full w-full bg-black rounded-3xl bg-opacity-25 flex items-center justify-center">

      <button @click="browse" type="button" class="rounded-full hover:bg-white hover:bg-opacity-35 p-2 focus:outline-none text-white transition duration-300">
        <IconCamera />
      </button>

    </section>
  </div>
</template>
