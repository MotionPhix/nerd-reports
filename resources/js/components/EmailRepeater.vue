<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { usePage } from '@inertiajs/vue3';
import { IconMailHeart, IconPlus, IconTrash } from '@tabler/icons-vue';
import { onMounted } from 'vue';

const emails = defineModel({ default: [] })

const page = usePage()

function onAddEmail() {
  emails.value = [...emails.value, { email: '', is_primary_email: false }]
}

function onRemoveEmail(index) {
  emails.value = emails.value.filter((_, i) => i !== index)
}

function togglePrimaryEmail(index: number) {
    for (let i = 0; i < emails.value.length; i++) {
        if (i === index) {
            emails.value[i].is_primary_email = true;
        } else {
            emails.value[i].is_primary_email = false;
        }
    }
}

onMounted(() => {
  if (emails.value.length <= 0) {
    emails.value = [{ email: '', is_primary_email: true }]
  }
})
</script>

<template>
    <div
        v-for="(mail, idx) in emails"
        :key="idx"
        class="relative mb-8 space-y-2 group first-letter:uppercase"
        >
        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        Email address
        </label>

        <TextInput
        v-model="mail.email"
        placeholder="Enter email address"
        :class="{ 'border-r-4 rounded-xl border-r-indigo-600 dark:border-r-yellow-500': mail.is_primary_email  }"
        type="email"
        />

        <InputError :message="page.props.errors[`emails.${idx}.email`]" />

        <section class="absolute z-10 items-center hidden h-8 gap-2 px-3 py-1 rounded-lg top-9 dark:bg-gray-800 group-hover:inline-flex right-3">
            <button
                type="button"
                @click="togglePrimaryEmail(idx)"
                class="text-gray-500 dark:text-gray-300 hover:text-gray-900 hover:bg-opacity-10">
                <IconMailHeart class="w-5 h-5 transition duration-300 hover:text-lime-500" />
            </button>

            <button
                v-if="emails.length > 1" type="button"
                class="text-gray-500 dark:text-gray-300 group-hover:inline-flex"
                @click="onRemoveEmail(idx)">
                <IconTrash class="w-5 h-5 transition duration-300 stroke-current hover:text-rose-500" />
            </button>
        </section>

    </div>

    <div>
        <button
            v-if="emails.length < 3"
            type="button"
            class="flex items-center gap-2 font-bold text-blue-300 transition duration-300 dark:text-lime-300 hover:text-blue-500"
            @click="onAddEmail"
            >

            <IconPlus class="w-6 h-6" />

            <span>Add email</span>

        </button>
    </div>
</template>
