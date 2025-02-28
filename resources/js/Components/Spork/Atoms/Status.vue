<template>
    <div :class="statusColor" class="max-w-max rounded-md border px-2 py-1 text-xs uppercase">
        {{ status }}
    </div>
</template>
<script setup>
import {computed} from "vue";

const { status } = defineProps({
    status: String,
})

const statusColor = computed(() => {
    switch (status) {
        case 'active':
        case 'ready':
            return 'border-green-300 bg-green-50 text-green-500 dark:border-green-600 dark:bg-green-500 dark:bg-opacity-10';
        case 'suspended':
            return 'border-yellow-300 bg-yellow-50 text-yellow-500 dark:border-yellow-600 dark:bg-yellow-500 dark:bg-opacity-10';
        case 'inactive':
            return 'border-red-300 bg-red-50 text-red-500 dark:border-red-600 dark:bg-red-500 dark:bg-opacity-10';
        default:
            return 'border-slate-300 bg-slate-50 text-slate-500 dark:text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:bg-opacity-40';
    }
})

</script>
