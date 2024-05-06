import { useLocalStorage } from "@/Composables/useLocalStorage"
import axios from "axios"
import { defineStore } from "pinia"
import { computed, ref } from "vue"

export const useNotificationStore = defineStore("notifications", () => {

  const notifications = ref<App.Data.NotificationData[]>([])
  const notification = useLocalStorage<App.Data.NotificationData | null>("active_notification")

  async function fetchNotifications(): Promise<void> {

    try {

      const response = await axios.get(route("notifications.index"));
      notifications.value = response.data;

    } catch (error) {

      console.error("Error fetching notifications:", error);

    }

  }

  async function markNotificationAsRead(notification: App.Data.NotificationData): Promise<void> {

    try {

      await axios.patch(route("notifications.read", { id: notification.id }));
      await fetchNotifications();

    } catch (error) {

      console.error("Error marking notification as read:", error);

    }

  }

  async function deleteNotification(notification: App.Data.NotificationData) {

    try {

      await axios.delete(route("notifications.destroy", { id: notification.id }));
      await fetchNotifications();

    } catch (error) {

      console.error("Error deleting notification:", error);

    }

  }

  const readNotifications = computed(() => notifications.value.filter(notification => notification.read_at !== null));
  const unreadNotifications = computed(() => notifications.value.filter(notification => notification.read_at === null));

  function setActiveNotification(activeNotification: App.Data.NotificationData) {

    notification.value = activeNotification

  }

  function setNotifications(newNotifications: App.Data.NotificationData[]) {

    notifications.value = newNotifications

  }

  function resetActiveNotification() {

    notification.value = null

  }

  return {
    notification,
    notifications,
    fetchNotifications,
    readNotifications,
    unreadNotifications,
    markNotificationAsRead,
    setActiveNotification,
    deleteNotification,
    resetActiveNotification,
    setNotifications
  }
})
