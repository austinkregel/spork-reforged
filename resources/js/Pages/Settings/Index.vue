<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {usePage} from "@inertiajs/vue3";
import TwoColumn from "@/Components/Spork/Molecules/Containers/Form/TwoColumn.vue";
import FieldInput from "@/Builder/Components/Form/FieldInput.vue";
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import { ref } from 'vue';
const page = usePage()

const settings = ref([{
    value: "",
    name: "Website",
},{
    value: false,
    name: "Is Active",
}]);
</script>
<template>
    <AppLayout>

        <template #default>
            <div>
                <div class="mx-16 mt-8">
                <TwoColumn
                    title="Activity Log"
                    description="Basic CRUD edit tracking across the application."
                    >
                    <template #default>
                        <div>
                            <div v-for="(setting, i) in settings" class="sm:col-span-4">
                                <SporkDynamicInput
                                    v-model="settings[i]"
                                />

                            </div>

                        </div>
                    </template>
                </TwoColumn>
                </div>
                <div class="py-4" v-for="(data, index) in page.props.settings.configs">
                    <div class="text-2xl py-2">{{index}}</div>

                    <div v-if="typeof data !== 'string'" v-for="(value, key) in data">{{index}}.{{ key }}: {{ value }}</div>

                    <div v-else>{{index}}: {{ data }}</div>
                </div>

                <pre>{{ page.props.settings.configs }}</pre>
            </div>
        </template>
    </AppLayout>
</template>

<style scoped>

</style>
