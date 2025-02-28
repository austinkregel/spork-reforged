<script setup>
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc.js';
import {computed, ref} from "vue";
import { usePage, Link, router } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import LinkAccount from "@/Components/Spork/Finance/LinkAccount.vue";
import SporkTable from "@/Components/Spork/Atoms/SporkTable.vue";
import {buildUrl} from "@kbco/query-builder";
import SporkSelect from "@/Components/Spork/SporkSelect.vue";
import Graph from "@/Components/Graph.vue";
import TransactionList from "@/Components/Spork/Molecules/TransactionList.vue";
const page = usePage();
dayjs.extend(utc);
const accounts = computed(() => page.props.accounts)

const { stats, selected_range } = defineProps([
    'stats',
    'selected_range'
])

const graphs = page.props.graphs

const transactionHeaders = [
    {
        name: 'Name',
        accessor:'name'
    },
    {
        name: 'Amount',
        accessor: value => value?.amount ? value.amount.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) : null
    },
    {
        name: 'Date',
        accessor: (value) => value?.date ? dayjs.utc(value.date).format("MMM DD, YYYY") : null
    },
    {
        name : 'Tags',
        accessor: value => value?.tags?.map(tag => tag.name.en)?.join(', ')
    }
]
const dateFormat = (value) => dayjs(value).format('YYYY-MM-DD')
const filterUrl = (field, value) => {
    return buildUrl('/-/banking', {
        filter: {
            [field]: value
        }
    })
}

const rangeUrl = (field, value) => {
  return buildUrl('/-/banking', {
    [field]: value
  })
}

const labels = computed(() => {
  return graphs.labels
});

const pinnedTags = ref([]);
const addTag = (tag) => {
    pinnedTags.value.push(tag);
}
const removeTag = (tag) => {
    pinnedTags.value = pinnedTags.value.filter(t => t !== tag);
}

</script>

<template>
  <AppLayout title="Profile">
    <div class="grid grid-cols-1 lg:grid-cols-3 px-4 gap-4">
        <div class="text-2xl my-4 flex flex-col col-span-full">
            Banking <span class="text-xs">Link your account, and tag your transactions</span>
        </div>


      <div class="col-span-2 gap-4">
        <div class="flex flex-wrap gap-4 col-span-1 lg:col-span-2">
            <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="col-span-1 md:col-span-3">
                <div v-if="graphs" class="bg-stone-100 dark:bg-stone-800 p-4 rounded-lg shadow">
                  <Graph :labels="labels" :datasets="graphs.datasets" />
                </div>
              </div>

              <div class="flex flex-col gap-2 p-4 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-800 rounded-lg shadow">
                <div class="font-semibold text-stone-600 dark:text-stone-300 dark:text-stone-300">
                  Total Income
                </div>
                <div class="text-4xl text-stone-800 dark:text-stone-100">
                  ${{stats.total_income.current.toLocaleString() }}
                </div>

                <div>
                  ${{ stats.total_income.previous.toLocaleString() }} last month
                </div>
              </div>
              <div class="flex flex-col gap-2 p-4 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-800 rounded-lg shadow">
                <div class="font-semibold text-stone-600 dark:text-stone-300 dark:text-stone-300">
                  Total Expenses
                </div>
                <div class="text-4xl text-stone-800 dark:text-stone-100">
                  ${{stats.total_expenses.current.toLocaleString() }}
                </div>

                <div>
                  ${{stats.total_expenses.previous.toLocaleString() }} last month
                </div>
              </div>
              <div class="flex flex-col gap-2 p-4 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-800 rounded-lg shadow">
                <div class="font-semibold text-stone-600 dark:text-stone-300 dark:text-stone-300">
                  Food Expenses
                </div>
                <div class="text-4xl text-stone-800 dark:text-stone-100">
                  ${{stats.other.current.toLocaleString() }}
                </div>

                <div>
                  ${{stats.other.previous.toLocaleString() }} last month
                </div>
              </div>
            </div>
            <div class="w-1/4">
            <SporkSelect :model-value="selected_range" @update:model-value="router.get(rangeUrl('range', $event))">
              <template #options>
                <option value="YTD">YTD</option>
                <option value="MTD">MTD</option>
                <option value="WTD">WTD</option>
                <option value="1d">1 day</option>
                <option value="7d">7 days</option>
                <option value="30d">30 days</option>
              </template>
            </SporkSelect>
          </div>
        </div>
        <div class="col-span-2">
          <LinkAccount :accounts="accounts" />
        </div>
      </div>
      <div class="col-span-1 flex flex-col gap-2 bg-stone-800">
        <TransactionList :transactions="page.props.transactions.data" />

        <div class="flex justify-between px-2 py-2">
          <Link class="text-white border px-4 py-2 rounded"
                :class="[page.props?.transactions?.prev_page_url ? 'border-stone-300 dark:border-stone-600' : 'border-stone-300 dark:border-stone-700 bg-stone-200 dark:bg-stone-800/70 text-stone-100/50']"
                :disabled="!page.props?.transactions?.prev_page_url"
                :href="page.props?.transactions?.prev_page_url"
          >Previous</Link>

          <Link class="text-white border px-4 py-2 rounded"
                :class="[page.props?.transactions?.next_page_url ? 'border-stone-300 dark:border-stone-600' : 'border-stone-300 dark:border-stone-700 bg-stone-200 dark:bg-stone-800/70 text-stone-100/50']"
                :disabled="!page.props?.transactions?.next_page_url"
                :href="page.props?.transactions?.next_page_url"
          >Next</Link>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
