<script setup>
import { ref, computed } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import {buildUrl} from "@kbco/query-builder";
import SporkTable from "@/Components/Spork/Atoms/SporkTable.vue";
import SporkButton from "@/Components/Spork/SporkButton.vue";
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import Modal from "@/Components/Modal.vue";
import {router} from "@inertiajs/vue3";

const { description, assets } = defineProps({
    description: Object,
    assets: Object,
});
const DynamicFormToFillableArray = function (model) {
    return Object.keys(model).map(key => ({
        value: typeof (model[key] ?? '') === 'object' ? JSON.stringify(model[key]?? '') : (model[key] ?? null),
        name: key,
    }));
}
const FillableArrayToDynamicForm = function (fillable) {
    return fillable.map(value => ({
        value: null,
        name: value,
    }));
}

const form = ref(null);
const scannedInput = ref(null);

const data = ref(null);
const errors = ref(null);
const isNumber = computed(() => {
    return isNaN(Number(scannedInput.value));
});
const decodedValue = computed(() => {
    return !isNaN(Number(scannedInput.value)) ? Number(scannedInput.value) : atob(scannedInput.value);
});

const search = () => {
    axios.get(buildUrl('/api/crud/assets', {
        filter: {
            id: decodedValue.value
        },
        include: ['owner']
    })).then(response => {
        data.value = (response.data);
        if (data.value.data.length === 1) {
            form.value = DynamicFormToFillableArray(data.value.data[0])
        }
    });
}
const onSave = async (fo, toggle) => {
    const data = fo.reduce((all, { name, value }) => ({ ...all, [name]: value }), {});
    let url = '/api/crud/'+description.name;

    if (data?.id) {
        url += '/'+data.id;
    }

    axios[data?.id ? 'put' : 'post'](url, data).then(() => {
        router.reload({
            only: ['data', 'paginator'],
        });
        toggle();
    }).catch((e) => {
        toggle();
        errors.value = e?.response?.data?.errors;
    });
}

</script>

<template>
    <AppLayout title="Assets">
        <div id="printable" class="text-black px-8 py-2 rounded-lg ">
            <input
                v-model="scannedInput"
                @keyup.enter="search"
                class="dark:bg-stone-800 dark:text-white dark:border-stone-600"
                type="text"
                placeholder="Search by ID or Asset ID"
            />
        </div>

        <SporkTable
            header="Asset Search"
            description="Search for assets by ID or Asset ID"
            :headers="[{
                name: 'ID',
                accessor: 'id'
            }, {
                name: 'Name',
                accessor: 'name'
            },
            {
                name: 'Owner',
                accessor: item => item?.owner?.name
            }
            ]"
            v-if="!form && data && data?.data"
            :items="data.data ?? []"
        />
        <SporkTable
            header="Asset Details"
            description="Asset details"
            :headers="[{
                name: 'ID',
                accessor: 'id'
            }, {
                name: 'Name',
                accessor: 'name'
            },
            {
                name: 'Location',
                accessor: 'location'
            },
            {
                name: 'type',
                accessor: 'type'
            },
            {
                name: 'description',
                accessor: d => d.description ?? ''
            },
            {
                name: 'Owner',
                accessor: item => item?.owner?.name
            }
            ]"
            :items="assets.data"
            />

        <Modal :show="form">
            <div class="text-xl flex flex-col justify-between p-4">
                <div v-for="(field, i) in form">
                    <SporkDynamicInput
                        :key="i+'.form-value'"
                        :autofocus="i === 1"
                        v-model="form[i]"
                        v-if="description.types[field.name]"
                        :type="description.types[field.name].type ?? 'text'"
                        :disabled-input="!description.fillable.includes(field.name)"
                        :editable-label="false"
                        :errors="errors?.[field.name]"
                        class="mt-4"
                    />
                </div>

                <div class="mt-4 gap-2 flex flex-wrap">
                    <SporkButton xsmall primary @click="onSave(form, () => form = null)">
                        Apply
                    </SporkButton>
                    <SporkButton xsmall secondary @click="() => form = null">
                        Cancel
                    </SporkButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
</style>
