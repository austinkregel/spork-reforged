<script setup>
import Loading from "@/Components/Spork/Atoms/Loading.vue";
const { title, value, loading, subTitle} = defineProps({
    title: String,
    value: Number,
    loading: Boolean,
    subTitle: String,
})
</script>

<template>
    <div class="flex flex-col gap-2 p-4 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-800 rounded-lg shadow">
        <div v-if="!loading" class="font-semibold text-stone-600 dark:text-stone-300 dark:text-stone-300">
            {{ title }}
        </div>
        <div v-if="!loading" class="text-4xl text-stone-800 dark:text-stone-100">
            {{ value }}
        </div>
        <div v-if="!loading && subTitle" class="text-xs">
            {{subTitle}}
        </div>
        <div v-if="loading" class="w-full flex items-center justify-center">
            <Loading />
        </div>
    </div>
</template>
