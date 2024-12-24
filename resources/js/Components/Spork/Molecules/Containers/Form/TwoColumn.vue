<script setup>
import Header from "@/Components/Spork/Atoms/Header.vue";
import Description from "@/Components/Spork/Atoms/Description.vue";

const { title, description } = defineProps({
    title: String,
    description: String,
})
</script>

<template>
    <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
        <div>
            <Header :title="title"></Header>
            <Description>{{ description }}</Description>
        </div>

        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 2xl:grid-cols-6 md:col-span-2">
            <slot />
        </div>
    </div>
</template>

<style scoped>

</style>
