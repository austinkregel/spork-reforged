<script setup>
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
} from '@headlessui/vue'
import dayjs from "dayjs";
</script>

<template>

<div>
    <Disclosure>
        <DisclosureButton>
            <div class="px-4 py-2 text-left flex gap-4 items-center">
                <slot name="header" />
            </div>
        </DisclosureButton>
        <DisclosurePanel>
            <slot></slot>
        </DisclosurePanel>
    </Disclosure>

</div>
</template>

<style scoped>

</style>
