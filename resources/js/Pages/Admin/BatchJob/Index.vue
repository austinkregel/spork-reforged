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
    return items.filter(({ file }) => !file.includes('vendor'));
}
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-stone-800 dark:text-stone-200 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8  gap-2">
                <div v-for="item in paginator.data" class="bg-white dark:bg-stone-800">
                    <Collapsible>
                        <template #header>
                            <div>
                                {{ item.name }} ({{item.failed_jobs}} failed)
                            </div>
                        </template>

                        <template #default>
                            <div class="bg-stone-200 dark:bg-stone-700 divide-y divide-stone-400 dark:divide-stone-600">
                                <div v-for="job in item.jobs">
                                    <Collapsible>
                                        <template #header>
                                            <div v-if="job">
                                                {{ job?.payload?.displayName }}
                                                {{ job.exception.split("\n")[0]}}

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
                        </template>
                    </Collapsible>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
