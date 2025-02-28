<script setup>
const { title } = defineProps({
    title: String,
})
</script>

<template>
    <h2 class="text-base/7 font-semibold text-stone-900 dark:text-stone-50">{{ title }}</h2>
</template>

