<template>
  <AppLayout title="Dashboard">
    <div class="flex flex-wrap gap-4 m-4">
        <div class="w-full font-medium text-stone-600 dark:text-stone-300 uppercase ml-4">Recent</div>
        <div
            v-for="(topic, i) in research ?? []"
            class="w-64 p-3 border border-stone-200 dark:border-stone-800 rounded-lg bg-white dark:bg-stone-800"
            :key="'research-'+i"
        >
            <ContextMenu>
                <div>
                    <Link :href="'/-/research/'+ topic.id">
                            <div  class="font-medium truncate">{{ topic.name }}</div>
                            <pre class="h-48 shadow-inset overflow-hidden text-xs border-t py-2 my-2 dark:text-slate-300">{{ topic.notes }}</pre>
                    </Link>
                    <div class="text-stone-500 dark:text-stone-200 border-t mt-4 pt-2 flex items-center justify-between">
                        <span>{{ date(topic.updated_at) }}</span>

                        <button @click="() => deleteFeature(topic)">
                            <TrashIcon class="w-4 h-4 text-red-500" />
                        </button>
                    </div>
                </div>

                <template #items>
                    <!-- Active: "bg-stone-100 text-stone-900", Not Active: "text-stone-700" -->
                    <Link :href="'/-/research/'+topic.id" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" role="menuitem" tabindex="-1">
                        <ArrowTopRightOnSquareIcon  class="w-4 h-4" />
                        Open
                    </Link>

                    <button @click="duplicateFeature" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" role="menuitem" tabindex="-1">
                        <DocumentDuplicateIcon class="w-4 h-4" />
                        Duplicate
                    </button>

                    <button @click="renameFeature" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" role="menuitem" tabindex="-1">
                        <PencilIcon class="w-4 h-4" />
                        Rename
                    </button>

                    <button @click="shareFeature" class="flex items-center gap-2 text-stone-700 dark:text-stone-200 px-4 py-2" role="menuitem" tabindex="-1">
                        <UserPlusIcon class="w-4 h-4" />
                        Share
                    </button>

                    <hr class="border-t border-stone-200 dark:border-stone-500" />

                    <button @click="deleteFeature" class="flex items-center gap-2 px-4 py-2 ">
                        <TrashIcon class="w-4 h-4 text-red-500" />
                        Delete
                    </button>
                </template>
            </ContextMenu>
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

const { research } = defineProps({
  research: Array,
})

const shareFeature = () => {
    console.log('shareFeature')
};
const deleteFeature = () => {
    console.log('deleteFeature')
};
const renameFeature = () => {
    console.log('renameFeature')
};
const duplicateFeature = () => {
    console.log('duplicateFeature')
};
const date = () => {}

</script>
