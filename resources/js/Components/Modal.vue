<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

watch(() => props.show, () => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = null;
    }
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" scroll-region>
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="show" class="fixed z-0 inset-0 transform transition-all">
                        <button class="absolute inset-0 bg-stone-500 dark:bg-stone-900 opacity-75" @click="close"/>
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 transition-y-4 sm:transition-y-0 sm:scale-95"
                    enter-to-class="opacity-100 transition-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 transition-y-0 sm:scale-100"
                    leave-to-class="opacity-0 transition-y-4 sm:transition-y-0 sm:scale-95"
                >
                    <div
                        v-show="show"
                        class="mb-6 bg-white dark:bg-stone-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
                        :class="maxWidthClass"
                    >
                        <slot v-if="show" />
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>
