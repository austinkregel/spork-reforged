<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CollapsibleArticle from "@/Components/Spork/CollapsibleArticle.vue";
import Collapsible from "@/Components/Spork/Atoms/Collapsible.vue";
import {ref} from "vue";

const { paginator } = defineProps({
    paginator: Object
});

const showVendor = ref(false);

const filterOutVendor = (items) => {
    return items.filter(({ file }) => showVendor.value ? !file.includes('vendor') : true);
}

const firstException = (job) => {
    return job.exception.split("\n")[0];
}
</script>

<template>
    <AppLayout title="Profile">
        <div>
            <div class=" py-10 sm:px-6 lg:px-8  gap-2">
                <div v-for="item in paginator.data" class="bg-white dark:bg-stone-800">
                    <div class="text-xl p-4">
                        {{ item.name }} ({{item.failed_jobs}} failed)
                    </div>
                    <div class="bg-stone-200 dark:bg-stone-700 divide-y divide-stone-400 dark:divide-stone-600">
                        <div v-for="job in item.jobs">
                            <Collapsible>
                                <template #header>
                                    <div v-if="job" class="flex flex-col">
                                        <span class="text-stone-700 dark:text-stone-300 text-sm">{{ job?.payload?.displayName }}</span>
                                        <div>
                                            {{ firstException(job)?.split(' in ')[0] }}
                                        </div>
                                        <div class="text-stone-700 dark:text-stone-300 text-sm">
                                            {{ firstException(job)?.split(' in ')[1] }}
                                        </div>
                                    </div>

                                </template>
                                <template #default>
                                    <div class="divide-y divide-stone-400 dark:divide-stone-600">
                                        <div v-for="exception in filterOutVendor(job.parsed_exception)">
                                            <Collapsible>
                                                <template #header>
                                                    <div class="text-sm text-monospace px-4">
                                                        {{ exception.frame }}
                                                    </div>
                                                </template>

                                                <template #default>
                                                    <pre class="text-xs px-8">{{ exception.code }}</pre>
                                                </template>
                                            </Collapsible>
                                        </div>
                                    </div>
                                </template>
                            </Collapsible>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
