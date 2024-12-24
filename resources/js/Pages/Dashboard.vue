<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import MetricCard from '@/Components/Spork/Atoms/MetricCard.vue';
import MetricApiCard from '@/Components/Spork/Molecules/MetricApiCard.vue';
import WeatherHeader from "@/Pages/Petoskey/WeatherHeader.vue";
import { ref, onMounted } from 'vue';
import dayjs from 'dayjs';
import { Link } from '@inertiajs/vue3';
import CollapsibleArticle from "@/Components/Spork/CollapsibleArticle.vue";
import duration from 'dayjs/plugin/duration';
import relativeTime from 'dayjs/plugin/relativeTime';

const { weather, news, expiring_domains, job_batches, accounts } = defineProps({
    project_count: Number,
    server_count: Number,
    domain_count: Number,
    page_count: Number,
    credential_count: Number,
    user_count: Number,
    weather: Object,
    tasks_today: Number,
    news: Object,
    expiring_domains: Object,
    job_batches: Object,
    accounts: Object,
})

const now = ref(dayjs());
const timeInterval = ref(null);

const domainColors = (domain) => {
    if (dayjs(domain.expires_at).diff(now.value, 'days') < 7) {
        return 'text-orange-500 dark:text-orange-400';
    }
    if (dayjs(domain.expires_at).diff(now.value, 'days') < 0) {
        return 'text-red-500 dark:text-red-300';
    }
    return 'text-stone-400 dark:text-yellow-400';
}
const dateFormat = (date) => {
    return dayjs(date * 1000).format('MMMM D HH:mm');
}
dayjs.extend(duration);
dayjs.extend(relativeTime);

const round = (value)=> Math.round(value * 10) / 10

const relativeDateFormat = (date) => dayjs(date).from(dayjs())

onMounted(() => {
  timeInterval.value = setInterval(() => {
    now.value = dayjs();
  }, 1000)

    console.log(MetricApiCard)

    return () => {
        clearInterval(timeInterval.value)
    }
})
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-stone-800 dark:text-stone-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="mx-auto max-w-7xl px-4 ">
          <WeatherHeader
              :weather="weather"
              :now="now"
          />
        </div>
        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-5 gap-4">
                <MetricApiCard url="/api/crud/projects?action=count&filter[relative]=user" title="Projects" />
                <MetricApiCard url="/api/crud/people?action=count&filter[relative]=user" title="people" />
                <MetricApiCard url="/api/crud/servers?action=count&filter[relative]=user" title="Servers" />
                <MetricApiCard url="/api/crud/domains?action=count&filter[relative]=user" title="Domains" />
                <MetricApiCard url="/api/crud/credentials?action=count&filter[relative]=user" title="Credentials" />
            </div>
        </div>
        <div class="border-t border-stone-700 my-4"></div>
        <div class="max-w-7xl mx-auto px-2 flex flex-col">
            <div class="text-xl px-6 p-4">
                State of Money
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mx-6">
                <div v-for="account in accounts" :key="account.account_id" class="bg-stone-200 dark:bg-stone-800 p-2 md:p-4 rounded">
                    <div class="text-xs tracking-wider">{{ account.name }}</div>
                    <div class="text-2xl font-bold tracking-wide">${{ account.balance }}</div>
                    <div class="text-xs">{{ account.available }} available</div>
                </div>
            </div>

            <Link href="/-/banking" class="text-white text-sm px-6 underline pt-2">
                More Banking Details...
            </Link>

        </div>
        <div class="border-t border-stone-700 my-4"></div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-4 xl:px-8 px-4">
            <div class="">
                <div class="text-xl tracking-wider leading-tight underline pb-4 pt-2">News</div>
                <div class="flex-col flex max-h-[50vh] overflow-auto dark:bg-stone-800 rounded-lg divide-y dark:divide-stone-600">
                    <CollapsibleArticle v-for="article in news.data" :article="article" />
                </div>
            </div>

            <div>
                <div class="text-xl tracking-wider leading-tight underline pb-4 pt-2">Domains Expiring Soon</div>
                <div class="flex-col flex gap-4 max-h-[50vh] overflow-auto dark:bg-stone-800 p-4 rounded-lg">
                    <div v-for="domain in expiring_domains.data" class="flex justify-between">
                        <div class="flex-grow text-indigo-200">{{domain.name}}</div>
                        <div :class="domainColors(domain)">{{domain.expires_at}}</div>
                    </div>
                    <div v-if="expiring_domains.total === 0" class="italic text-stone-300">
                        No domains are expiring soon.
                    </div>
                </div>
                <div class="text-xl tracking-wider leading-tight underline pb-4 pt-4">Batch Jobs</div>
                <div class="flex-col flex gap-4 max-h-[50vh] overflow-auto dark:bg-stone-800 p-4 rounded-lg">
                    <div v-for="batch in job_batches.data" class="flex justify-between">
                        <div class="flex-grow text-indigo-200">
                            <Link class="underline" :href="route('batch-jobs.show', [batch.id])">{{batch.name}}</Link>
                            <div class="text-xs">created {{relativeDateFormat(batch.created_at)}}</div>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <span v-if="batch.total_jobs === batch.failed_jobs" class="text-red-300">Failed</span>
                            <span v-else-if="batch.pending_jobs > 0" class="text-yellow-300">Processing {{ round(((batch.total_jobs - batch.pending_jobs) / batch.total_jobs) * 100)}}</span>
                            <span v-else-if="batch.failed_at !== null" class="text-red-600">Failed at {{ dateFormat(batch.failed_at)}}</span>
                            <span v-else-if="batch.finished_at !== null" class="text-green-400">{{ dateFormat(batch.finished_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style type="sass">
.prose > img, .prose > figure > img {
    max-height: 150px !important;
    border-radius: 1rem;
    margin: 0 auto;
}

</style>
