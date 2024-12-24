<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const { budget } = defineProps(["budget"]);
</script>

<template>
    <AppLayout title="Profile">
        <div>
            <div class="text-2xl my-4 w-full flex flex-col px-4">
                Banking
                <span class="text-xs">Link your account, and tag your transactions</span>
            </div>
            <pre>{{ budget }}</pre>
        </div>
    </AppLayout>
</template>
