<template>
    <AppLayout title="Dashboard">
        <div class="px-4 gap-4 flex flex-col">
            <div class="mt-4 px-4 font-medium text-stone-600 dark:text-stone-300 uppercase">
                <Link :href="route('search')+queryString" class="uppercase">
                    Search
                </Link>
            </div>
            <hr class="border-stone-300 dark:border-stone-700 -mx-4 -mb-2" />

            <div v-for="result in results" class="flex flex-col gap-2">
                <pre class="px-4 mt-4">{{ result.indexUid }} -- found {{ result.estimatedTotalHits }}</pre>
                <div class="grid grid-cols-4 gap-4 mt-2">
                    <div v-for="hit in result.hits" class="bg-white dark:bg-stone-800 text-sm">
                        <!-- Need a polymorphic object that can preview our various types. -->
                        <!-- Also how do we want to sort things?. -->
                        <SearchResultPreview :hit="hit" />
                    </div>
                </div>
                <div>
                    <Link :href="route('search.show', [result.indexUid])+queryString" class="underline text-sm text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 dark:focus:ring-offset-stone-800 px-4">
                        Dig deeper into {{result.indexUid}}
                    </Link>
                </div>
                <hr class="border-stone-300 dark:border-stone-700 mt-4 -mx-4 -mb-2" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchResultPreview from "@/Pages/Search/SearchResultPreview.vue";
import {Link} from "@inertiajs/vue3";
import {computed} from "vue";
const { results } = defineProps({
    results: {
        type: Array,
        required: true,
    },
})

const queryString = computed(() => {
    const url = new URL(window.location.href);
    return url?.search
});
</script>

<style>
img {
    max-height: 150px;
    margin: 0 auto;
}
</style>
