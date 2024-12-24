<script setup>
import SporkSelect from "@/Components/Spork/SporkSelect.vue";
import SporkInput from "@/Components/Spork/SporkInput.vue";
import { onMounted, ref, computed } from "vue";

const { conditions, id, type } = defineProps({
    conditions: Object,
    id: Number,
    type: String,
});

const editableConditions = ref(conditions);
const createCondition = (index, condition) => {
    axios.post('/api/crud/conditions', {
        ...condition,
        conditionable_id: id,
        conditionable_type: type,
    }).then((response) => {
        editableConditions.value[index] = response.data;
    });
};
const updateCondition = (index, condition) => {
    axios.put(`/api/crud/conditions/${condition.id}`, condition).then((response) => {
        editableConditions.value[index] = response.data;
    });
};
const deleteCondition = (index, condition) => {
    // editableConditions.value = editableConditions.value.filter((c, i) => i !== index);
    axios.delete(`/api/crud/conditions/${condition.id}`).then(() => {
        editableConditions.value = editableConditions.value.filter((c, i) => i !== index);
    });
};
const parameters = [
    // New Email
    {
        value: 'email.from',
        name: "Email Sender"
    },
    {
        value: 'email.subject',
        name: "Email Subject"
    },
    // New Transaction
    {
        "value": "transaction.name",
        "name": "Transaction Name"
    },
    {
        "value": "transaction.amount",
        "name": "Transaction Amount"
    },
    {
        "value": "transaction.category.name",
        "name": "Category Name"
    },
    // Recent article
    {
        "value": "article.title",
        "name": "Article Title"
    },
];
const comparators = [
    {
        "value": "EQUALS",
        "name": "equal to"
    },
    {
        "value": "NOT_EQUAL",
        "name": "not equal to"
    },
    {
        "value": "LIKE",
        "name": "like"
    },
    {
        "value": "NOTLIKE",
        "name": "not like"
    },
    {
        "value": "IN",
        "name": "in"
    },
    {
        "value": "NOTIN",
        "name": "not in"
    },
    {
        "value": "STARTS_WITH",
        "name": "starts with"
    },
    {
        "value": "ENDS_WITH",
        "name": "ends with"
    },
    {
        "value": "LESS_THAN",
        "name": "less than"
    },
    {
        "value": "LESS_THAN_EQUAL",
        "name": "less than equal"
    },
    {
        "value": "GREATER_THAN",
        "name": "greater than"
    },
    {
        "value": "GREATER_THAN_EQUAL",
        "name": "greater than equal"
    }
];
const addCondition = () => {
    editableConditions.value.push({
        value: '',
        comparator: 'LIKE',
        parameter: 'name',
    });
};
</script>

<template>
<div class="m-4 flex gap-2 flex-col">
    <!-- We want an interface that consists of 2 selects and 1 text input. The Parameter, Comparator, and Value. -->
    <div class="flex gap-2" v-for="(condition, index) in editableConditions">
        <SporkSelect v-model="condition.parameter">
            <template #options>
                <option></option>
                <option v-for="parameter in parameters" :key="parameter.value+ index+'key'" :value="parameter.value">{{ parameter.name }}</option>
            </template>
        </SporkSelect>
        <SporkSelect v-model="condition.comparator">
            <template #options>
                <option></option>
                <option v-for="comparator in comparators" :key="comparator.comparator+ index+'comparatorkey'" :value="comparator.value">{{ comparator.name }}</option>
            </template>
        </SporkSelect>
        <SporkInput v-model="condition.value" />
        <button v-if="condition?.id" @click="() => deleteCondition(index, condition)" class="bg-red-500 text-white rounded-lg px-2">Delete</button>
        <button v-else @click="() => createCondition(index, condition)" class="bg-blue-500 text-white rounded-lg px-2">Create</button>
    </div>
    <div>
        <button @click="addCondition" class="bg-green-500 text-white rounded-lg px-2">Add Condition</button>
    </div>
</div>
</template>

