<template>
    <div class="overflow-hidden p-4">
    <!-- Type: App\Models\Article -->
        <div v-if="hit?.author_type">
            <div>{{ hit.headline }}</div>
        </div>
        <!-- Type: App\Models\Message, but not emails -->
        <div v-else-if="hit?.event_id && hit?.html_message !== 'render-rich-inbox'">
            <div>{{ hit.message }}</div>
        </div>
        <!-- Type: App\Models\Message, but explicitly emails -->
        <div v-else-if="hit?.event_id && hit?.html_message === 'render-rich-inbox'">
            <div>{{ hit?.subject }}</div>
            <div>TO: {{ hit?.to_email }}</div>
            <div>FROM: {{ hit?.from_email }}</div>
        </div>
        <!-- Type: App\Models\Domain -->
        <div v-else-if="hit?.verification_key">
            <div>{{ hit }}</div>
        </div>
        <!-- Type: App\Models\Finance\Account -->
        <div v-else-if="hit?.account_id && hit?.type">
            <div>{{ hit?.name }}</div>
            <div>{{ hit?.balance }}</div>/
            <div>{{ hit?.available }}</div>
        </div>
        <!-- Type: App\Models\Finance\Transaction -->
        <div v-else-if="hit?.account_id && hit?.transaction_id">
            <div>{{ hit?.name }}</div>
            <div>${{ hit?.amount }}</div>
        </div>
        <!-- Type: App\Models\Person -->
        <div v-else-if="hit?.emails || hit?.identifiers">
            <div>{{ hit?.name }}</div>
            <div>{{ hit?.primary_address }}</div>
        </div>

        <!-- Type: App\Models\Research -->
        <div v-else-if="hit?.topic || hit?.notes">
            <div>{{ hit?.topic }}</div>
        </div>

        <!-- Type: App\Models\Domain -->
        <div v-else-if="hit?.verification_key">
            <div>{{ hit.name }}</div>
            <div>{{ hit.verification_key }}</div>
        </div>
        <!-- Type: App\Models\DomainRecord -->
        <div v-else-if="hit?.domain_id && hit?.record_id">
            <div>{{ hit.name }}</div>
            <div>{{ hit?.type }}</div>
        </div>

        <!-- Type: App\Models\Research -->
        <div v-else-if="hit">
            <div>{{ hit }}</div>
        </div>

    </div>
</template>
<script setup>
const { hit } = defineProps({
    hit: {
        type: Object,
        required: true,
    },
})
</script>
