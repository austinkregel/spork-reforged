<script setup>
import { computed } from 'vue';

const { cols } = defineProps({
    cols: {
        type: String,
    }
});
</script>

<template>
    <div class="flex flex-col gap-4 w-full">
        <slot />
    </div>
</template>

<style scoped>

</style>
