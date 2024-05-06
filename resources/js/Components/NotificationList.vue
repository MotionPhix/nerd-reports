<script setup lang="ts">
import NotificationItem from "@/Components/NotificationItem.vue"
import { useNotificationStore } from "@/Stores/notificationStore"
import { storeToRefs } from "pinia"
import { onMounted, ref } from "vue"
import { IconBellRinging } from "@tabler/icons-vue"
import axios from "axios"
import MazTabs from 'maz-ui/components/MazTabs'
import MazTabsBar, { MazTabsBarItem } from 'maz-ui/components/MazTabsBar'
import MazTabsContent from 'maz-ui/components/MazTabsContent'
import MazTabsContentItem from 'maz-ui/components/MazTabsContentItem'

const notificationStore = useNotificationStore()
const { fetchNotifications, setNotifications } = notificationStore
const { readNotifications, unreadNotifications } = storeToRefs(notificationStore)
const tab = ref(1)

const fetchRead = () => {

  tab.value = 1

  /*axios
    .get(route('notifications.index', { seen: true }))
    .catch((err) => console.log(err))
    .then((resp) => {

      setNotifications(resp.data)

    })*/

}

const fetchUnread = () => {

  tab.value = 2

  /*axios
    .get(route('notifications.index'))
    .catch((err) => console.log(err))
    .then((resp) => {

      setNotifications(resp.data)

    })*/

}

onMounted(() => {
  fetchNotifications()
})

const tabs: MazTabsBarItem[] = [
  { label: 'New', disabled: false },
  { label: 'Read', disabled: false },
]
</script>

<template>
  <div>
<!--    <div-->
<!--      class="flex items-center gap-4 px-4 py-3 border-b border-neutral-300 dark:border-neutral-700">-->
<!--      <button-->
<!--        class="font-bold text-gray-800 dark:text-white"-->
<!--        @click="fetchUnread">-->
<!--        Notifications-->
<!--      </button>-->

<!--      <button-->
<!--        class="dark:text-gray-400"-->
<!--        @click="fetchRead">-->
<!--        Read-->
<!--      </button>-->
<!--    </div>-->

    <MazTabs v-model="tab">

      <MazTabsBar :items="tabs" persistent block>

        <template #item="{ item, index, active }">

          <button
            v-if="item.label === 'New'"
            @click="fetchUnread"
            class="font-bold text-gray-800 dark:text-white">
            {{ item.label }}
          </button>

          <button
            v-if="item.label === 'Read'"
            @click="fetchRead"
            class="font-bold text-gray-800 dark:text-white">
            {{ item.label }}
          </button>

        </template>

      </MazTabsBar>

      <MazTabsContent>

        <MazTabsContentItem :tab="1" class="maz-py-4">

          <ul
            class="overflow-y-auto divide-y divide-gray-200 scrollbar-none dark:divide-gray-600 max-h-96">

            <template v-if="unreadNotifications.length">
              <NotificationItem
                :notification="notification"
                v-for="notification in unreadNotifications"
                :key="notification.id"
              />
            </template>

            <template v-else>
              <div class="flex flex-col gap-4 items-center justify-center text-gray-400 p-5 dark:text-blue-300">

                <IconBellRinging class="w-24 h-24" />

                <div class="flex flex-col gap-4 items-center text-gray-800">
                  <p class="dark:text-neutral-200 text-lg">
                    No unread notifications
                  </p>

                  <p class="dark:text-neutral-400 text-center text-sm">
                    We'll notify you about updates and any time you're mentioned
                    on <strong>Nerd Reports</strong>.
                  </p>
                </div>

              </div>
            </template>
          </ul>

        </MazTabsContentItem>

        <MazTabsContentItem :tab="2" class="maz-py-4">
          <ul
            class="overflow-y-auto divide-y divide-gray-200 scrollbar-none dark:divide-gray-600 max-h-96">

            <template v-if="readNotifications.length">
              <NotificationItem
                :notification="notification"
                v-for="notification in readNotifications"
                :key="notification.id"
              />
            </template>

            <template v-else>
              <div class="flex flex-col gap-4 items-center justify-center text-gray-400 p-5 dark:text-blue-300">

                <IconBellRinging class="w-24 h-24" />

                <div class="flex flex-col gap-4 items-center text-gray-800">
                  <p class="dark:text-neutral-200 text-lg">
                    No notifications
                  </p>

                  <p class="dark:text-neutral-400 text-center text-sm">
                    You don't have any notifications. Your <strong>read</strong> notifications will apear here.
                  </p>
                </div>

              </div>
            </template>
          </ul>
        </MazTabsContentItem>
      </MazTabsContent>
    </MazTabs>

  </div>
</template>
