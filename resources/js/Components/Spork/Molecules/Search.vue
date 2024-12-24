<script setup>
import { MagnifyingGlassIcon } from "@heroicons/vue/20/solid/index.js";
import { ref } from 'vue';

const url = new URL(window.location.href);
const search = url.searchParams.get('query') || ''
const searchQuery = ref(search);
</script>

<template>
    <form class="relative z-10 flex flex-1" :action="route('search')" method="GET">
        <label for="search-field" class="sr-only">Search</label>
        <MagnifyingGlassIcon class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-stone-400" aria-hidden="true" />
        <input
            id="search-field"
            class="block h-full w-full border-0 dark:bg-neutral-900 py-0 pl-8 pr-0 text-stone-900 dark:text-stone-50 placeholder:text-stone-400 focus:ring-0 sm:text-sm"
            placeholder="Search..."
            type="search"
            name="query"
            v-model="searchQuery"
            @keyup.enter="$event.target.form.submit()"
        />
    </form>
</template>
