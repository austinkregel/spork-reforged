<template>
    <!-- Notification panel, dynamically insert this into the live region when it needs to be displayed -->
    <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show" class="pointer-events-auto max-w-xl w-full overflow-hidden rounded-lg bg-white dark:bg-stone-950 shadow-lg ring-1 ring-black ring-opacity-5">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <DynamicIcon icon-name="NewspaperIcon" class="w-8 h-8 text-stone-400" />
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-stone-900 dark:text-stone-300"><slot name="headline" /></p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-50"><slot name="body" /></p>
                        <div class="mt-3">
                            <slot name="actions"></slot>
                        </div>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button type="button" @click="show = false" class="inline-flex rounded-md bg-white dark:bg-stone-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref } from 'vue'
import { XMarkIcon } from '@heroicons/vue/20/solid'
import DynamicIcon from "@/Components/DynamicIcon.vue";
const show = ref(true)

</script>
