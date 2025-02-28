<script setup>
import { computed } from 'vue'
import { NewspaperIcon } from "@heroicons/vue/20/solid";
import moment from "moment-timezone";
import {router} from "@inertiajs/vue3";
const { notification } = defineProps({
  notification: Object
})

const notificationText = computed(() => {
  return notification.text
})

const date = computed(() => {
  return moment(notification.created_at).format("YYYY-MM-DD")
})

const markAsRead = () => {
  axios.put(`/-/notifications/${notification.id}/mark-as-read`, {})
      .then(res => {
        router.reload({ only: ['notifications', 'notification_count'] })
      })
}
</script>

<template>
  <div class="flex flex-col">
    <div v-if="notification.type === 'App\\Notifications\\Daily\\SummaryNotification'">
      <div class="p-4">
        <NewspaperIcon class="h-6 w-6 text-gray-400" />
      </div>
      <div class="py-2">
        Your summary notification for {{ date }} is ready!
      </div>
    </div>
    <pre v-else>{{ notification }}</pre>

    <button @click="markAsRead" role="button">
      Mark as read
    </button>
  </div>
</template>

<style scoped>

</style>