<script setup>
import { computed } from "vue"
import {
  Listbox,
  ListboxButton,
  ListboxOption,
  ListboxOptions
} from "@headlessui/vue"
import { IconCheck, IconSelector } from "@tabler/icons-vue"

const props = defineProps({
  options: Array,
  modelValue: [String, Number, Array],
  placeholder: {
    type: String,
    default: "Select option"
  },
  multiple: Boolean,
  error: String
})

const emit = defineEmits(["update:modelValue"])

const label = computed(() => {
  return props.options
    .filter(option => {
      if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(option.value)
      }

      return props.modelValue === option.value
    })
    .map(option => option.label)
    .join(", ")
})
</script>

<template>
  <Listbox
    :model-value="props.modelValue"
    :multiple="props.multiple"
    @update:modelValue="value => emit('update:modelValue', value)">
    <div class="relative mt-1">
      <ListboxButton
        class="relative py-3 pr-10 pl-3 w-full text-left border-gray-700 bg-white rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
      >
        <span
          v-if="label"
          class="block truncate">
          {{ label }}
        </span>

        <span
          v-else
          class="text-gray-500">
          {{ props.placeholder }}
        </span>

        <span
          class="flex absolute inset-y-0 right-0 items-center pr-2 pointer-events-none">
          <IconSelector
            aria-hidden="true"
            class="w-5 h-5 text-gray-400"
          />
        </span>
      </ListboxButton>

      <transition
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0" >
        <ListboxOptions
          class="overflow-auto absolute z-10 py-1 mt-1 w-full max-h-60 text-base bg-white dark:bg-gray-900 rounded-md ring-1 ring-black ring-opacity-5 shadow-lg focus:outline-none sm:text-sm"
        >
          <ListboxOption
            v-for="option in props.options"
            :key="option.label"
            v-slot="{active, selected}"
            :value="option.value"
            as="template">
            <li
              :class="[
                active ? 'dark:bg-gray-800 dark:text-gray-400 bg-gray-200' : 'text-gray-900 dark:text-gray-300',
                'relative cursor-default select-none py-2 pl-10 pr-4',
              ]">
              <span
                :class="[
                  selected ? 'font-medium' : 'font-normal',
                  'block truncate',
                ]">
                {{ option.label }}
              </span>

              <span
                v-if="selected"
                class="flex absolute inset-y-0 left-0 items-center pl-3 text-lime-600">
                <IconCheck
                  aria-hidden="true"
                  class="w-5 h-5"
                  stroke="2.5"
                />
              </span>
            </li>
          </ListboxOption>

        </ListboxOptions>

      </transition>

      <div class="text-xs text-red-400 mt-1" v-if="props.error">
        {{ props.error }}
      </div>

    </div>

  </Listbox>

</template>
