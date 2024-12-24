<style scoped>

</style>

<template>
<div class="w-full rounded-lg overflow-hidden shadow">
    <div class="w-full dark:text-white bg-white dark:bg-stone-800 rounded-t-lg flex flex-wrap items-center justify-between">
        <div class="bg-stone-600 dark:bg-stone-700 relative z-0 border-b border-stone-300 dark:border-stone-700/70 w-full py-2 px-4 flex flex-wrap justify-between items-center">
            <slot name="table-top" />
        </div>
        <table class="w-full text-left">
            <thead class="bg-white dark:bg-stone-700 w-full">
                <tr class="w-full">
                    <th v-for="(header, $i) in headers" :key="'crud-header'+$i" scope="col" class="relative z-0 isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900 table-cell">
                       {{header}}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200 dark:divide-stone-600">
            <tr v-for="datum in data" :key="datum" class="w-full ">
                <td class="w-full relative z-0 py-2 pr-3 text-sm font-medium">
                    <slot name="datum" :datum="datum"></slot>
                </td>
            </tr>
            <tr v-if="data.length === 0">
                <td class="relative z-0 py-4 px-4 text-sm font-medium">
                    <slot name="no-data" class="w-full p-4 italic text-center">
                        No data present in table
                    </slot>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="w-full dark:text-white flex justify-between flex-wrap bg-stone-100 dark:bg-stone-800 px-4 py-2">
        <slot name="table-bottom" />
    </div>
</div>
</template>

<script setup>
import {ref} from "vue";

const { headers, data } = defineProps({
      headers: {
        type: Array,
        required: true,
          default: () => []
      },
    data: {
        type: Array,
        required: true,
        default: () => []
    },
})

const currentPage = ref(1);
const filtersOpen = ref(false);
const selectedItems = ref([]);

</script>
