<script setup>
import { Link } from "@inertiajs/vue3";
import SporkButton from "@/Components/Spork/SporkButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { computed, ref, watch } from "vue";

const {
    data,
    headerText,
    keyAccessor,
    openModalText,
    modalTitle,
    noDataText,
    dataForAttachment,
    xl,
    lg,
    md,
    sm,
    xs,
} = defineProps({
    data: Array,
    dataForAttachment: Array,
    keyAccessor: {
        type: Function,
        default: (item) => item.name
    },
    headerText: String,
    openModalText: String,
    modalTitle: String,
    noDataText: String,
    xl: Boolean,
    lg: Boolean,
    md: Boolean,
    sm: Boolean,
    xs: Boolean,
});

const attachOpen = ref(false);
const allSelected = ref(false);
const resources = ref([]);

const emits = defineEmits([
    'detach',
    'attach',
    'open',
    'close',
]);

watch(() => attachOpen.value, (newData) => {
    if (newData) {
        emits('open');
    } else {
        emits('close');
    }
});

const width = computed(() => {
    switch (true) {
        case xl:
            return 'grid-cols-1 md:grid-cols-3 xl:grid-cols-5';
        case lg:
            return 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4';
        case md:
            return 'grid-cols-1 md:grid-cols-3';
        case sm:
            return 'grid-cols-1 md:grid-cols-2';
        case xs:
        default:
            return 'grid-cols-1';
    }
})

</script>

<template>
    <div>
        <h3 class="text-base font-semibold leading-6 text-stone-900 dark:text-stone-50">{{ headerText }}</h3>
        <dl class="mt-5 grid gap-5 dark:text-stone-50 text-stone-900" :class="width">
            <div v-for="item in data" :key="keyAccessor(item)" class="overflow-hidden rounded-lg bg-white dark:bg-stone-700 shadow">
                <slot name="preview" :item="item" />
            </div>
            <div v-if="data?.length === 0" class="p-3 rounded bg-stone-700 italic col-span-2 text-sm">
                {{ noDataText }}
            </div>
        </dl>

        <div class="text-stone-300 text-sm font-semibold mt-2 flex justify-between">
            <button @click="() => attachOpen = !attachOpen">
                {{ openModalText }}
<!--                Attach a credential-->
            </button>
            <slot name="buttons" />
        </div>
    </div>


    <DialogModal :show="attachOpen" :closeable="true" @close="attachOpen = false" >
        <template #title>
            <div class="dark:text-stone-200 p-4">
                {{ modalTitle }}
            </div>
        </template>
        <template #content>
            <div class="max-h-72 overflow-y-scroll dark:text-stone-200 p-4 flex flex-col gap-2 border dark:border-stone-600 rounded-lg">
                <button @click="allSelected = !allSelected" class="cursor-pointer flex flex-wrap items-center gap-2">
                    <input
                        class="dark:bg-stone-700"
                        type="checkbox"
                        v-model="allSelected"
                    />
                    <span>
                        Select All
                    </span>
                </button>

                <div v-for="item in dataForAttachment">
                    <label class="cursor-pointer flex flex-wrap items-center gap-2">
                        <input
                            class="dark:bg-stone-700"
                            type="checkbox"
                            v-model="resources"
                            :value="item.id"
                        />
                        <span>
                            {{ item.name ?? item.title ?? item.topic}}
                            <!-- If we have tags, let's try to map them out for visibility -->
                            <span v-if="item?.tags?.map(i => i.name?.en)?.join(', ') ?? item?.credential?.name ?? item?.credential_id ?? item.slug ?? item?.type">
                                ({{item?.tags?.map(i => i.name?.en)?.join(', ') ?? item?.credential?.name ?? item?.credential_id ?? item.slug ?? item?.type }})
                            </span>
                        </span>
                    </label>
                </div>

                <div v-if="dataForAttachment.length === 0">
                    <div class="italic col-span-2 text-sm pt-1">
                        {{ noDataText }}
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="dark:text-stone-200 p-4 flex justify-between">
                <spork-button @click="attachOpen = !attachOpen" small secondary>
                    Close
                </spork-button>
                <spork-button @click="emits('attach', resources); attachOpen = !attachOpen" small primary>
                    Attach
                </spork-button>
            </div>
        </template>
    </DialogModal>

</template>
