<script setup>
const { tag } = defineProps({
  tag: {
    type: String,
    required: true
  }
})
</script>

<template>
  <div class="bg-blue-900 px-1 py-0.5 rounded-lg text-blue-100">{{ tag }}</div>
</template>

<style scoped>

</style>