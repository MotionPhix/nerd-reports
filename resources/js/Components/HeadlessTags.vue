<script setup>
import { useTagStore } from '@/Stores/tagStore';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import { IconPlus } from '@tabler/icons-vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { onMounted, ref } from 'vue';

const tagStore = useTagStore()

const { setTags } = tagStore

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  contact: {
    type: Object,
    default: () => {}
  }
})

const filteredTags = ref([]);
const tags = ref([]);
const searchTerm = ref('');
const inputVisible = ref(false);

const availableTags = ref([]);

const error = ref(null)

const removeTag = (tag) => {
  tags.value = tags.value.filter((t) => t !== tag);

  axios
    .patch(route('tags.detach', props.contact.cid),
    {
      name: tag.label
    },
    {
      onSuccess: () => {
        loadTags()
      }
    })
};

const toggleDropdown = (show) => {
  inputVisible.value = show;
};

const addSelectedTag = (tag) => {
  error.value = null;

  const trimmedLabel = tag.label.trim().toLowerCase();

  if (!tags.value.some((existingTag) => existingTag.label.trim().toLowerCase() === trimmedLabel)) {
    tags.value.push({ label: tag.label });

    axios
      .put(route('tags.update', props.contact.cid), {
        name: tag.label,
      },
      {
        onSuccess: (() => loadTags())
      })
  } else {
    if (!tags.value.some((existingTag) => existingTag.label.trim().toLowerCase() === searchTerm.value.trim().toLowerCase())) {
      error.value = `<strong>${tag.label}</strong> already exists!`;
    }
  }

  toggleDropdown(false);
};


const addNewTag = async () => {
  error.value = null

  if (searchTerm.value.trim() && !tags.value.includes(searchTerm.value.trim())) {
    tags.value.push(searchTerm.value.trim());
    await createTag(searchTerm.value)
  } else {
    if (!tags.value.includes(searchTerm.value.trim())) {
      error.value = 'Tag already exists!'
    }
  }

  toggleDropdown(false);
};

const filterTags = () => {
  error.value = null;

  filteredTags.value = availableTags.value.filter(
    (tag) =>
      tag.label.toLowerCase().includes(searchTerm.value.toLowerCase()) // || tag.toLowerCase().includes(searchTerm.value.toLowerCase())
  );
};

const loadTags = debounce((query = null) => {
  axios
    .get(query ? `/api/tags/${query}` : '/api/tags')
    .then((resp) => {
      availableTags.value = resp.data
    })
    .finally(() => {
      filteredTags.value = availableTags.value;
      setTags(availableTags.value)
    })
}, 500)

async function createTag(tag) {
  axios
    .post(route('tags.store', props.contact.cid), {
      name: tag,
    },
    {
      onSuccess: (() => loadTags())
    })
}

onMounted(() => {
  loadTags()

  props.modelValue.map((tag) => {
    tags.value.push({
      label: tag.name.en
    })
  })
});

</script>

<template>
  <div class="tag-input">
    <div
      class="bg-blue-100 m-0.5 text-blue-800 text-xs font-medium px-2.5 py-2 rounded dark:bg-blue-900 dark:text-blue-300"
      v-for="tag in tags" :key="tag.id || tag">
      {{ tag.label || tag }}

      <span @click="removeTag(tag)" class="px-1 transition duration-200 rounded-full hover:cursor-pointer tag-close hover:bg-rose-500">&times;</span>
    </div>

    <Menu
      as="div"
      class="relative z-10 inline-flex">
      <MenuButton
        class="flex hover:opacity-75 duration-300 transition items-center gap-2 py-1 bg-blue-300 text-gray-800 m-0.5 px-2.5 rounded dark:bg-blue-700 dark:text-gray-300"
      >
        <IconPlus stroke="2.5" class="h-5" />
        <span class="font-bold">New</span>
      </MenuButton>

      <transition
        enter-active-class="transition duration-100 ease-out transform"
        enter-from-class="scale-90 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in transform"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-90 opacity-0">
        <MenuItems
          class="absolute w-40 right-[50%] left-[-50%] overflow-hidden overflow-y-auto origin-center bg-white rounded-lg shadow max-h-40 scrollbar-none dark:border dark:border-gray-500 bottom-full dark:bg-gray-700 dark:divide-gray-600"
        >
          <div class="sticky top-0">
            <input
              v-model="searchTerm"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-t-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
              placeholder="Search tags"
              @input="filterTags" />
          </div>

          <MenuItem
            v-slot="{ active }"
            v-for="tag in filteredTags"
            @click="addSelectedTag(tag)"
            :key="tag.id || tag">
            <span
              :class="{ 'font-semibold bg-blue-500 dark:bg-lime-500': active }"
            class="flex items-center p-2.5 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
            {{ tag.label || tag }}
            </span>
          </MenuItem>

          <MenuItem
            v-if="searchTerm && !filteredTags.length"
            v-slot="{ active }"
            @click="addNewTag">
            <div  class="px-4 py-2 rounded">
            Create <strong>{{ searchTerm }}</strong> tag
            </div>
          </MenuItem>

        </MenuItems>

      </transition>
    </Menu>
  </div>

  <div
    class="px-2 mt-1 text-sm text-rose-500 empty:hidden"
    v-html="error"
    v-if="error" />
</template>

<style scoped>
.tag-input {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}

.tag-item {
  margin: 4px;
  padding: 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
}

.tag-close {
  cursor: pointer;
  margin-left: 8px;
}

.tag-input-field {
  margin: 4px;
  padding: 8px;
}

.tag-input-search {
  padding: 4px;
  margin-bottom: 8px;
}

.tag-dropdown ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.tag-dropdown li {
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  margin-bottom: 4px;
}

.tag-dropdown li:hover {
  background-color: #ddd;
}
</style>
