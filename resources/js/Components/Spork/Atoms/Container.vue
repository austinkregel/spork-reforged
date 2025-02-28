<script setup>
import { computed } from 'vue';

const { cols } = defineProps({
    cols: {
        type: String,
    }
});

const gridWidth = computed(() => {
    switch (cols) {
        case '1':
            return 'grid-cols-1';
        case '2':
            return 'grid-cols-1 md:grid-cols-2 ';
        case '3':
            return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
        case '4':
            return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4';
        case '5':
            return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5';

    }

    return `grid-cols-${cols}`;
});
</script>

<template>
    <div class="grid gap-4 w-full" :class="gridWidth">
        <slot />
    </div>
</template>

<style scoped>

</style>
