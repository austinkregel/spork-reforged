<!-- This example requires Tailwind CSS v2.0+ -->
<template>
    <select
        @input="$emit('update:modelValue', $event.target.value)"
        :value="modelValue"
        class="mt-1 block w-full pl-3 pr-10 py-1 text-base border-stone-300 dark:border-stone-700 dark:bg-stone-700 dark:placeholder-stone-300 focus:outline-none focus:ring-slate-500 focus:border-slate-700 sm:text-sm rounded-md"
    >
        <slot name="options"></slot>
    </select>
</template>

<script>
export default {
    props: ['modelValue'],
    emits: ['update:modelValue'],
    methods: {
        focus() {
            this.$refs.input.focus()
        }
    }
}
</script>

