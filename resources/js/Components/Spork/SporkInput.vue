<template>
    <input
        v-if="type !== 'textarea'"
        class="py-1 px-2 border focus:ring-slate-500 focus:border-slate-500 block w-full shadow-sm sm:text-sm border-stone-300 dark:border-stone-700 dark:bg-stone-700 dark:placeholder-stone-500 rounded-md"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
        :type="type"
    />

    <textarea
        v-else
        class="py-1 px-2 border focus:ring-slate-500 focus:border-slate-500 block w-full shadow-sm text-xs border-stone-300 dark:border-stone-700 dark:bg-stone-700 dark:placeholder-stone-500 rounded-md"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />

</template>

<script>
export default {
    props: [
        'modelValue',
        'type'
    ],
    emits: ['update:modelValue'],
    methods: {
        focus() {
            this.$refs.input.focus()
        },

    },

}
</script>

