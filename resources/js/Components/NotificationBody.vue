<script setup>
import { computed } from 'vue'
import { NewspaperIcon } from "@heroicons/vue/20/solid";
import moment from "moment-timezone";
const { notification } = defineProps({
  notification: Object
})

const notificationText = computed(() => {


  return notification.text
})

const date = computed(() => {
  return moment(notification.created_at).format("YYYY-MM-DD")
})
</script>

<template>
  <div v-if="notification.type === 'App\\Notifications\\Daily\\SummaryNotification'">
    <div class="p-4">
      <NewspaperIcon class="h-6 w-6 text-gray-400" />
    </div>
    <div class="py-2">
      Your summary notification for {{ date }} is ready!
    </div>
  </div>
  <pre v-else>{{ notification }}</pre>
</template>

<style scoped>

</style>