<script setup>
import {
Combobox,
ComboboxButton,
ComboboxInput,
ComboboxOption,
ComboboxOptions,
TransitionRoot,
} from "@headlessui/vue";
import { computed, ref, watch } from "vue";

import { IconCheck, IconSelector } from "@tabler/icons-vue";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
    modelValue: Object,
    loadOptions: Function,
    createOption: Function,
    placeholder: {
        type: String,
        default: 'Pick a company'
    },
});

const options = ref();
const isLoading = ref(false);

const queryOption = computed(() => {
    return query.value === ""
        ? null
        : {
            missing: true,
            label: query.value,
        };
});

let query = ref("");
watch(
    query,
    q => {
        if (props.loadOptions) {
            isLoading.value = true;
            props.loadOptions(q, results => {
                options.value = results;

                if (
                    props.modelValue &&
                    !options.value.some(o => {
                        return o.value === props.modelValue?.value;
                    })
                ) {
                    options.value.unshift(props.modelValue);
                }
                isLoading.value = false;
            });
        }
    },
    { immediate: true }
);

let filteredOptions = computed(() =>
    query.value === ""
        ? options.value
        : options.value.filter(option =>
            option?.label
                .toLowerCase()
                .replace(/\s+/g, "")
                .includes(query.value.toLowerCase().replace(/\s+/g, ""))
        )
);

function handleUpdateModelValue(selected) {

      emit("update:modelValue", selected);

      if (props.createOption && selected?.missing) {
        isLoading.value = true;
        props.createOption(selected, option => {
          emit("update:modelValue", option);
          isLoading.value = false;
        });
      }

}
</script>

<template>
    <Combobox by="value" :model-value="props.modelValue" @update:model-value="handleUpdateModelValue">
        <div class="relative mt-1">
            <div class="relative w-full text-left rounded-lg shadow-md cursor-default sm:text-sm">
                <ComboboxInput
                    class="w-full py-3 border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-lime-500 dark:focus:border-lime-600 focus:ring-lime-500 dark:focus:ring-lime-600"
                    :placeholder="props.placeholder" :displayValue="option => option.label"
                    @change="query = $event.target.value" />
                <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                    <IconSelector class="w-5 h-5 text-gray-400" aria-hidden="true" />
                </ComboboxButton>
            </div>
            <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0"
                @after-leave="query = ''">
                <ComboboxOptions
                    class="absolute z-50 w-full py-1 mt-1 overflow-auto text-base bg-white border-2 border-gray-300 rounded-md shadow-lg dark:border-gray-700 scrollbar-thin dark:bg-gray-800 max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                    <div v-if="filteredOptions.length === 0 &&
        !isLoading &&
        !queryOption &&
        !props.createOption
        " class="relative px-4 py-2 text-gray-700 cursor-default select-none">
                        Nothing found.
                    </div>

                    <div v-if="isLoading" class="relative px-4 py-2 text-gray-700 cursor-default select-none">
                        Loading...
                    </div>

                    <template v-if="!isLoading">
                        <ComboboxOption v-if="queryOption && !filteredOptions.length && props.createOption
        " as="template" :value="queryOption" v-slot="{ active }">
                            <li class="relative py-2 pl-10 pr-4 cursor-default select-none" :class="{
        'bg-lime-500 text-white': active,
        'text-gray-900': !active,
    }">
                                Create "{{ queryOption.label }}"
                            </li>
                        </ComboboxOption>
                        <ComboboxOption v-for="option in filteredOptions" as="template" :key="option.value"
                            :value="option" v-slot="{ selected, active }">
                            <li class="relative py-2 pl-10 pr-4 cursor-default select-none" :class="{
        'bg-lime-500 text-white': active,
        'text-gray-900': !active,
    }">
                                <span class="block truncate"
                                    :class="{ 'font-medium': selected, 'font-normal': !selected }">
                                    {{ option.label }}
                                </span>
                                <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3"
                                    :class="{ 'text-white': active, 'text-lime-500': !active }">
                                    <IconCheck class="w-5 h-5" aria-hidden="true" />
                                </span>
                            </li>
                        </ComboboxOption>
                    </template>
                </ComboboxOptions>
            </TransitionRoot>
        </div>
    </Combobox>
</template>