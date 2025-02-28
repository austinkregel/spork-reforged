<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-stone-900 dark:text-stone-50">{{ header }}</h1>
        <p class="mt-2 text-sm text-stone-700 dark:text-stone-200">{{ description }}</p>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="w-full rounded overflow-x-hidden divide-y divide-stone-300 dark:divide-stone-950 bg-white dark:bg-stone-950 rounded">
            <thead>
            <tr>
              <th v-for="header in headers" scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-stone-900 dark:text-stone-50 sm:pl-3">
                {{  header.name }}
              </th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-stone-900">
            <ContextMenu as="tr" v-for="item in items" :key="item" class="even:bg-stone-50 dark:even:bg-stone-800">
              <td v-for="header in headers" class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-stone-900 dark:text-stone-50 sm:pl-3">
                {{ parseTheAccessor(header, item) }}
              </td>

              <template #items>
                <slot name="context-items" :item="item"></slot>
              </template>
            </ContextMenu>

            <tr v-if="items?.length === 0">
              <td :colspan="headers.length + 1" class="text-center p-4 dark:text-stone-50">
                No data available
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import dayjs from 'dayjs';
import ContextMenu from '@/Components/ContextMenus/ContextMenu.vue'
defineProps({
  headers: Array,
  items: Array,
  header: String,
  description: String,
})

const parseTheAccessor = (header, value) => {
  if (typeof header.accessor === 'function') {
      try {
          return header.accessor(value)
      } catch (e) {
          console.error('Unable to execute the header accessor from header:', header, e);
          return 'an error occurred, check console for logs';
      }
  }

  if (!value) {
      return header.name
  }
  return value[header?.accessor] ?? header.name;
}
</script>
