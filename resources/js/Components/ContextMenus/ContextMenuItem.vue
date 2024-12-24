<script setup>
const $emit = defineEmits(['click']);

</script>

<template>
    <button @click="$emit('click')" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" role="menuitem" tabindex="-1">
        <slot></slot>
    </button>
</template>

<style scoped>

</style>
