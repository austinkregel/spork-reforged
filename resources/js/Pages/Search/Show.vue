<template>
    <AppLayout :title="'Searching through ' + table + ' for '">
        <div class="px-4">
            <div class="mt-4 px-4 font-medium text-stone-600 dark:text-stone-300 uppercase">
                <Link :href="route('search')+queryString" class="underline">
                    Search
                </Link>
                <span class="mx-2">&gt;</span>
                <Link :href="route('search.show', [table])" class="">
                    {{table}}
                </Link>
            </div>
            <hr class="border-stone-300 dark:border-stone-700 mt-4 -mx-4 -mb-2" />
            <SporkTable
                :headers="headers"
                :items="data"
                :header="'Results from your query ' +queryString"
                :description="plural"
                class="-mx-4"
            >
                <template #context-items="{ item }">
                    <div class="p-2 flex-col flex gap-1">
                        <pre>{{ item }}</pre>
                    </div>
                </template>
            </SporkTable>

            <div class="flow-root">
                <div class="flex justify-between mx-4 -mt-4">
                    <Link class="border px-4 py-2 rounded"
                          :class="[paginator?.prev_page_url ? 'border-stone-300 dark:border-stone-600 text-white' : 'text-stone-400 border-stone-300 dark:border-stone-700 bg-stone-200 dark:bg-stone-800/70 cursor-not-allowed']"
                          :disabled="!(paginator?.prev_page_url)"
                          :href="paginator?.prev_page_url"

                    >
                        Previous
                    </Link>
                    <Link class="text-white border px-4 py-2 rounded"
                          :class="[paginator?.next_page_url ? 'border-stone-300 dark:border-stone-600' : 'border-stone-300 dark:border-stone-700 bg-stone-200 dark:bg-stone-800/70 text-stone-100/50']"
                          :disabled="!paginator?.next_page_url"
                          :href="paginator?.next_page_url"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchResultPreview from "@/Pages/Search/SearchResultPreview.vue";
import SporkTable from "@/Components/Spork/Atoms/SporkTable.vue";
import {Link} from "@inertiajs/vue3";
import { computed } from 'vue';

const { data, paginator, table, description, model } = defineProps({
    paginator: {
        type: Object,
        required: true,
    },
    data: {
        type: Array
    },
    table: String,
    plural: String,
    model: String,
    description: Object,
});

const date = (d) => dayjs(d).format('YYYY-MM-DD HH:mm:ss');

const headers = computed(() => {
    switch (model) {
        case 'App\\Models\\User':
            return [
                {name: 'Name', accessor: (v) => v.name},
                {name: 'Email', accessor: (v) => v.email},
                {name: 'Created At', accessor: (v) => date(v.created_at)},
            ];
        case 'App\\Models\\Person':
            return [
                {name: 'Name', accessor: (v) => v.name},
                {name: 'Emails', accessor: (v) => v.emails},
                {name: 'Created At', accessor: (v) => date(v.created_at)},
            ];
        case 'App\\Models\\Domain':
            return [
                {name: 'Name', accessor: (v) => v.name},
                {name: 'Registered At', accessor: (v) => date(v.registered_at)},
                {name: 'Expires At', accessor: (v) => date(v.expires_at)},
            ];
        case 'App\\Models\\DomainRecord':
            return [
                {name: 'Name', accessor: (v) => v.name },
                {name: 'Type', accessor: (v) => v.type },
                {name: 'TTL', accessor: (v) => v.ttl },
                {name: 'Value', accessor: (v) => v.value },
                {name: 'Priority', accessor: (v) => v.priority },
            ];
        case 'App\\Models\\Article':
            return [
                { name: 'Headline', accessor: (v) => v.headline },
                { name: 'Last Modified', accessor: (v) => date(v.last_modified) },
            ]
        case 'App\\Models\\Tag':
            return [
                {name: 'Name', accessor: (v) => v.name},
                {name: 'Created At', accessor: (v) => date(v.created_at)},
            ];
        case 'App\\Models\\Finance\\Account':
            return [
                {
                    name: 'Name',
                    accessor: item => item.name
                },
                {
                    name: 'Balance',
                    accessor: item => item.balance
                },
                {
                    name: 'Available',
                    accessor: item => item.available
                },
                {
                    name: 'Date',
                    accessor: item => date(item.date)
                },
                {
                    name: 'Account ID',
                    accessor: item => item.account_id
                },
            ];
        case 'App\\Models\\Finance\\Transaction':
            return [
                {
                    name: 'Name',
                    accessor: item => item.name
                },
                {
                    name: 'Amount',
                    accessor: item => item.amount
                },
                {
                    name: 'Date',
                    accessor: item => date(item.date)
                },
                {
                    name: 'Account ID',
                    accessor: item => item.account_id
                },
            ];
        case 'App\\Models\\Message':
            return [
                {
                    name: 'From',
                    accessor: item => item.from?.name ?? item?.from_email
                },
                {
                    name: 'To',
                    accessor: item => item.to?.name ?? item?.to_email
                },
                {
                    name: 'Message',
                    accessor: item => item.message
                },
                {
                    name: 'Sent At',
                    accessor: item => date(item.sent_at)
                },
                {
                    name: 'Message ID',
                    accessor: item => item.event_id
                },
            ];
    }
})
const queryString = computed(() => {
    const url = new URL(window.location.href);
    return url?.search
});

</script>
