<script setup>
const { server } = defineProps({
    server: Object,
})
</script>

<template>
    <div>
        <pre>{{ server }}</pre>
    </div>
</template>
