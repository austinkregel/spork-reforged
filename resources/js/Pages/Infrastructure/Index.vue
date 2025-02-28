<template>
    <AppLayout title="Dashboard">
        <div class="flex flex-wrap gap-4 m-4">
            <div class="w-full font-medium text-stone-600 dark:text-stone-300 uppercase ml-4">Servers</div>

            <div class="grid grid-cols-1 max-w-5xl mx-auto gap-4 w-full">
                <div
                    v-for="(server, i) in servers ?? []"
                    class="max-h-64 overflow-hidden p-3 border border-stone-200 dark:border-stone-800 rounded-lg bg-white dark:bg-stone-800"
                    :key="'servers-'+i"
                >
                    <ContextMenu>
                        <div class="flex flex-wrap gap-4 w-full">
                            <div class="w-12">
                                <DynamicIcon icon-name="ServerIcon" class="w-12 h-12" />
                            </div>
                            <div class="flex flex-col">
                                <div class="font-medium text-stone-600 dark:text-stone-300">
                                    <Link :href="'/-/servers/'+server.id">{{ server.name }}</Link>
                                </div>
                                <div class="text-stone-500 dark:text-stone-400">{{ server.ip_address }}</div>
                            </div>
                            {{server.services.map(s => s.service).join(', ')}}
                            <div class="flex-grow flex justify-between">
                                <div></div>
                                <div class="flex flex-col gap-2">
                                    <Status :status="server.status" />

                                    <div class="flex flex-wrap gap-2">
                                        <DynamicIcon icon-name="ArrowPathIcon" class="text-white w-5 h-5" data-note="SSH connection test" />
                                        <DynamicIcon icon-name="SignalSlashIcon" class="text-white w-5 h-5" data-note="No active connection to the server"/>
                                        <DynamicIcon icon-name="SignalIcon" class="text-white w-5 h-5" data-note="Has active connection to the server"/>
                                        <DynamicIcon icon-name="PuzzlePieceIcon" class="text-white w-5 h-5" data-note="This can be a button that takes you to install new software"/>
                                        <DynamicIcon icon-name="CalendarDaysIcon" class="text-white w-5 h-5" data-note="The server crontab under root." />
                                        <DynamicIcon icon-name="DocumentTextIcon" class="text-white w-5 h-5" data-note="Logs" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <template #items>
                            {{server}}
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
import DynamicIcon from "@/Components/DynamicIcon.vue";
import Status from "@/Components/Spork/Atoms/Status.vue";

const { servers, pagination } = defineProps({
    servers: Array,
    pagination: Object,
})
const date = () => {}

</script>
