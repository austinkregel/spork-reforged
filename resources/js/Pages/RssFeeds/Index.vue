<template>
    <AppLayout title="Dashboard">
        <div class="flex flex-wrap gap-4 m-4">
            <div class="w-full font-medium text-stone-600 dark:text-stone-300 uppercase ml-4">Rss Feeds</div>

            <div class="grid grid-cols-1 max-w-5xl mx-auto gap-4 ">
                <div
                    v-for="(topic, i) in feeds ?? []"
                    class="max-h-64 overflow-hidden p-3 border border-stone-200 dark:border-stone-600 rounded-lg bg-white dark:bg-stone-600"
                    :key="'research-'+i"
                >
                    <ContextMenu>
                        <div>
                            <a target="_blank" :href="topic.url" class="text-xl font-bold underline">{{ topic.headline }}</a>
                            <div class="overflow-hidden max-h-32">
                                <div v-html="topic.content"></div>
                            </div>
                            <div class="flex flex-wrap mt-2 gap-2">
                                <div v-for="tag in topic.author?.tags" :key="tag.name"
                                     class="py-1 px-2 rounded-full bg-blue-300 dark:bg-blue-600 text-xs">
                                    {{ tag.name.en }}
                                </div>

                                <div class="py-1 px-2 rounded-full bg-slate-700 text-xs">{{ date(topic.last_modified)}}</div>
                            </div>
                        </div>

                        <template #items>
                            <Link :href="'/-/research/'+topic.id" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" role="menuitem" tabindex="-1">
                                <ArrowTopRightOnSquareIcon  class="w-4 h-4" />
                                Open
                            </Link>
                        </template>
                    </ContextMenu>
                </div>
            </div>
            <div class="w-full dark:text-white flex justify-between flex-wrap px-4 py-2 mb-20">
                <Link
                    :href="pagination.prev_page_url"
                    :disabled="!pagination.prev_page_url"
                    :plain="true"
                    :xlarge="true"
                    :class="[!pagination.prev_page_url ? 'opacity-50 cursor-not-allowed': '']"
                >
                    Previous
                </Link>

                <div class="py-2">
                    {{ (pagination.current_page * pagination.per_page) - pagination.per_page }} total items, {{ pagination.current_page  }} of {{ pagination?.total}}
                </div>
                <Link
                    :href="pagination.next_page_url"
                    :disabled="!pagination.next_page_url"
                    :class="[!pagination.next_page_url ? 'opacity-50 cursor-not-allowed': 'cursor-pointer']"
                >Next</Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import {
    TrashIcon,
    ArrowTopRightOnSquareIcon ,
    DocumentDuplicateIcon,
    PencilIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline';
import AppLayout from "@/Layouts/AppLayout.vue";
import ContextMenu from "@/Components/ContextMenus/ContextMenu.vue";
import { Link } from '@inertiajs/vue3'
import LaravelVuePagination from "@/Components/Spork/LaravelVuePagination.vue";

const { feeds, pagination } = defineProps({
    feeds: Array,
    pagination: Object,
})
const date = (d) => dayjs(d).format('YYYY-MM-DD HH:mm:ss')

</script>
