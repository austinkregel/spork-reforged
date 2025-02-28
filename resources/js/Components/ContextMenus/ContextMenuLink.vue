<script setup>
import { Link } from '@inertiajs/vue3';
const $emit = defineEmits(['click']);

const { href } = defineProps({
  href: {
    type: String,
    required: true
  }
})
</script>

<template>
    <Link :href="href" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" tabindex="-1">
        <slot></slot>
    </Link>
</template>

<style scoped>

</style>
