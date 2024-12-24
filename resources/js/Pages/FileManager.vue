<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FileOrFolder from "@/old-spork/Development/FileOrFolder.vue";
import DynamicIcon from "@/Components/DynamicIcon.vue";
import ContextMenu from "@/Components/ContextMenus/ContextMenu.vue";

defineProps({
    directories: {
        type: Array,
        default: () => [],
    },
    files: {
        type: Array,
        default: () => [],
    },
});
const openFile = (file) => console.log('opening file', file)

const url = new URL(window.location.href);
const path = url.searchParams.get('path');

const breadcrumbs = path ? atob(path).split('/').filter(Boolean) : [];

const toPathLink = (path) => btoa(breadcrumbs.slice(0, breadcrumbs.indexOf(path) + 1).join('/'));
</script>

<template>

    <AppLayout title="Dashboard">
        <div class="max-w-7xl mx-auto flex flex-col gap-8">
            <div class="flex gap-2 mt-8">
                <div class="flex items-center">
                    <Link :href="route('file-manager.index')" class="text-stone-400">File Manager</Link>
                    <DynamicIcon v-if="breadcrumbs.length > 0" icon-name="ChevronRightIcon" class="ml-2 w-4 h-4 text-stone-400 stroke-current" />
                </div>
                <div v-for="(path, i) in breadcrumbs" class="flex items-center">
                    <Link :href="'/-/file-manager?path='+toPathLink(path)" class="text-stone-400">
                        {{ path }}
                    </Link>

                    <DynamicIcon v-if="(breadcrumbs.length - 1) !== i" icon-name="ChevronRightIcon" class="w-4 h-4 text-stone-400 stroke-current" />
                </div>
            </div>
            <div class="flex flex-wrap lg:grid-cols-4 gap-2">


                <div v-for="(topic, i) in directories">
                    <Link :href="'/-/file-manager?path='+topic.file_path" class="flex flex-col items-center w-20 h-20 border border-stone-600 truncate">
                        <div class="pt-2">
                            <DynamicIcon icon-name="FolderIcon" class="w-12 h-12 text-stone-400 stroke-current" />
                        </div>
                        <div class="text-xs overflow-ellipsis max-w-full">{{ topic.name }}</div>
                    </Link>
                </div>
            </div>
            <div class="">
                <ContextMenu>
                    <div v-for="(topic, i) in files">
                        <div class="flex items-center border border-stone-600 truncate gap-2 px-2">
                            <DynamicIcon icon-name="DocumentIcon" class="w-4 h-4 text-stone-400 stroke-current" />

                            <div class=" overflow-ellipsis max-w-full">{{ topic.name }}</div>
                        </div>
                    </div>
                    <template #items>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <DynamicIcon icon-name="DownloadIcon" class="w-4 h-4 text-stone-400 stroke-current" />
                                <span>Download</span>
                            </div>
                        </div>
                    </template>
                </ContextMenu>
            </div>
        </div>
    </AppLayout>
</template>
