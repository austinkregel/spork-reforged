<script setup>
import {ref} from "vue";

const openContext = ref(false);
const contextX = ref(0);
const contextY = ref(0);

const openContextMenu = (e,) => {
    if (openContext.value){
        return;
    }

    e.preventDefault();
    openContext.value = true;
    contextX.value = e.clientX;
    // Gotta add the scroll amount in the Y direction so we can ensure the menu stays where the mouse is.
    contextY.value = e.clientY + (window.scrollY ?? 0);
};

const { as } = defineProps({
    as: {
        default: () => 'div',
        type: String
    }
})
</script>

<template>
    <Component :is="as" @contextmenu="(e) => openContextMenu(e)">
        <slot :open="openContext"></slot>

        <div v-if="openContext">
            <div @click="openContext = false" class="fixed inset-0 z-0 bg-stone-800/60 cusor-pointer"></div>

            <div class="absolute z-10 mt-2 w-64 rounded-md shadow-lg bg-white dark:bg-stone-700 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1"
                 :style="'top: '+contextY+'px; left: '+contextX+'px;'"
            >
                <div class="py-1 text-sm flex flex-col" role="none">
                    <slot name="items" :close="() => openContext = false"></slot>
                </div>
            </div>
        </div>
    </Component>
</template>
