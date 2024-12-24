<template>
    <AppLayout title="Dashboard">
        <div class="w-full border-b dark:border-slate-700 dark:bg-stone-950">
            <div
                class="max-w-7xl mx-auto px-8 py-4 flex items-center gap-2 font-semibold text-2xl text-stone-800 dark:text-stone-200 leading-tight">
                <Link href="/-/projects" class="underline">
                    Projects
                </Link>
                <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-stone-400" aria-hidden="true"/>
                {{ $page.props.project.name }}
            </div>
        </div>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
                <!--                <div class="uppercase tracking-wider">Todo tasks</div>-->
                <!--                <div class="grid grid-cols-3 w-full gap-4">-->
                <!--                    <div class="bg-stone-700 rounded-lg py-4">-->
                <!--                        <SmallTaskList :tasks="$page.props.daily_tasks" name="Daily Tasks" @open="() => { createTask = true; form.type = 'daily'}"/>-->
                <!--                    </div>-->
                <!--                    <div class="bg-stone-700 rounded-lg py-4">-->
                <!--                        <SmallTaskList :tasks="$page.props.today_tasks" name="Today's Tasks" @open="() => { createTask = true; form.type = 'today'}" />-->
                <!--                    </div>-->
                <!--                    <div class="bg-stone-700 rounded-lg py-4">-->
                <!--                        <SmallTaskList :tasks="$page.props.future_tasks" name="Future tasks" @open="() => { createTask = true; form.type = 'future'}"/>-->
                <!--                    </div>-->
                <!--                </div>-->

                <div class="grid grid-cols-1 gap-6 w-full">

                    <div v-for="deployment in project.deployments" :key="deployment" class="flex flex-col">
                        <div class="text-xl">
                            {{ deployment.name}}
                        </div>
                        <div v-if="deployment.domains.length > 0" class="flex flex-wrap gap-2">
                            <div v-for="domain in deployment.domains" class="text-xs border border-stone-400 dark:border-stone-600 px-1 py-0 rounded-lg">{{ domain.name }}</div>
                        </div>
                        <div class="py-2"></div>

                        <CheckboxList
                            md
                            :data="deployment.domains"
                            :data-for-attachment="attach"
                            :key-accessor="item => item.name"
                            header-text="Domains"
                            open-modal-text="Attach a domains"
                            modal-title="Attaching a domains"
                            no-data-text="There are no domains to attach"
                            @detach="detach"
                            @attach="(items) => items.map(item => attachToDeployment('App\\Models\\Domain', item, deployment))"
                            @open="() => fetchDomains({page: 1, limit: 100})"
                            @close="() => attach = []"
                        >
                            <template #preview="{ item }">
                                <div class="px-2 py-0.5 text-sm">
                                    {{ item.name }}
                                </div>
                            </template>

                            <template #buttons>
                                <Link href="/-/manage/domains">
                                    View All Domains
                                </Link>
                            </template>
                        </CheckboxList>
                        <div class="py-2"></div>
                        <CheckboxList
                            :data="deployment.servers"
                            :data-for-attachment="attach"
                            :key-accessor="item => item.name"
                            header-text="Servers"
                            open-modal-text="Attach a servers"
                            modal-title="Attaching a servers"
                            no-data-text="There are no servers to attach"
                            @detach="detach"
                            @attach="(items) => items.map(item => attachToDeployment('App\\Models\\Server', item, deployment))"
                            @open="() => fetchServers({page: 1, limit: 100})"
                            @close="() => attach = []"
                        >
                            <template #preview="{ item }">
                                <Server :server="item" />
                            </template>

                            <template #buttons>
                                <Link href="/-/manage/servers">
                                    View All servers
                                </Link>
                            </template>
                        </CheckboxList>

                        <pre>{{ deployment }}</pre>

                    </div>

<!--                    <CrudView-->
<!--                        :data="$page.props.project.tasks"-->
<!--                        :key-accessor="item => item.id"-->
<!--                        :fields="[-->
<!--                            {label: 'Name', key: 'name'},-->
<!--                            {label: 'Type', key: 'type'},-->
<!--                            {label: 'Status', key: 'status'},-->
<!--                            {label: 'Notes', key: 'notes'},-->
<!--                            {label: 'Start Date', key: 'start_date'},-->
<!--                            {label: 'Checklist', key: 'checklist'},-->
<!--                        ]"-->
<!--                        :actions="[-->
<!--                            {label: 'Edit', icon: CodeBracketIcon, action: () => {}},-->
<!--                            {label: 'Delete', icon: TrashIcon, action: onDelete},-->
<!--                        ]"-->
<!--                    >-->
<!--                        <template #status="{item}">-->
<!--                            <Status :status="item.status"/>-->
<!--                        </template>-->
<!--                    </CrudView>-->
<!--                    <CheckboxList-->
<!--                        :data="$page.props.project.servers"-->
<!--                        :key-accessor="item => item.name"-->
<!--                        header-text="Infrastructure"-->
<!--                        open-modal-text="Attach a server"-->
<!--                        modal-title="Attaching a servers"-->
<!--                        no-data-text="There are no servers to attach"-->
<!--                        @detach="detach"-->
<!--                        @attach="attachToProject"-->
<!--                    >-->
<!--                        <template #preview>-->
<!--                            <div class="text-2xl">-->
<!--                                {{ item.name }}-->
<!--                            </div>-->
<!--                        </template>-->

<!--                        <template #buttons>-->
<!--                            <Link href="/-/manage/servers">-->
<!--                                View All Infrastructure-->
<!--                            </Link>-->
<!--                        </template>-->
<!--                    </CheckboxList>-->

<!--                    <CheckboxList-->
<!--                        :data="$page.props.project.domains"-->
<!--                        :key-accessor="item => item.name"-->
<!--                        header-text="Domains"-->
<!--                        open-modal-text="Attach a domains"-->
<!--                        modal-title="Attaching a domains"-->
<!--                        no-data-text="There are no domains to attach"-->
<!--                        @detach="detach"-->
<!--                        @attach="attachToProject"-->
<!--                    >-->
<!--                        <template #preview>-->
<!--                            <div class="text-2xl">-->
<!--                                {{ item.name }}-->
<!--                            </div>-->
<!--                        </template>-->

<!--                        <template #buttons>-->
<!--                            <Link href="/-/manage/domains">-->
<!--                                View All Domains-->
<!--                            </Link>-->
<!--                        </template>-->
<!--                    </CheckboxList>-->

<!--                    <CheckboxList-->
<!--                        :data="$page.props.project.pages"-->
<!--                        :key-accessor="item => item.name"-->
<!--                        header-text="Pages"-->
<!--                        open-modal-text="Attach a pages"-->
<!--                        modal-title="Attaching a pages"-->
<!--                        no-data-text="There are no pages to attach"-->
<!--                        @detach="detach"-->
<!--                        @attach="attachToProject"-->
<!--                    >-->
<!--                        <template #preview>-->
<!--                            <div class="flex flex-wrap items-center gap-6">-->
<!--                                <div v-if="item.redirect" class="flex flex-wrap items-center gap-2 text-stone-300">-->
<!--                                    {{ item.domain.name }}-->
<!--                                    <ArrowLongRightIcon class="w-4 h-4 text-stone-100"/>-->
<!--                                    &lt;!&ndash; SSL  &ndash;&gt;-->
<!--                                    <LockClosedIcon class="w-4 h-4 text-green-500"/>-->
<!--                                </div>-->
<!--                                <div v-else class="flex flex-wrap items-center gap-2 text-stone-300">-->
<!--                                    <LockClosedIcon class="w-4 h-4 text-green-500"/>-->
<!--                                    {{ item.domain.name }}-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </template>-->

<!--                        <template #buttons>-->
<!--                            <div>-->

<!--                                <Link href="/-/pages/create">-->
<!--                                    Create a Page-->
<!--                                </Link>-->
<!--                                <Link href="">-->
<!--                                    View all pages-->
<!--                                </Link>-->
<!--                            </div>-->
<!--                        </template>-->
<!--                    </CheckboxList>-->


<!--                    <CheckboxList-->
<!--                        :data="$page.props.project.research"-->
<!--                        :key-accessor="item => item.name"-->
<!--                        header-text="Research"-->
<!--                        open-modal-text="Attach a research"-->
<!--                        modal-title="Attaching a research"-->
<!--                        no-data-text="There are no research to attach"-->
<!--                        @detach="detach"-->
<!--                        @attach="attachToProject"-->
<!--                    >-->
<!--                        <template #preview>-->
<!--                            <div class="text-2xl">-->
<!--                                {{ item.name }}-->
<!--                            </div>-->
<!--                        </template>-->

<!--                        <template #buttons>-->
<!--                            <Link href="/-/research">-->
<!--                                Investigate & Research-->
<!--                            </Link>-->
<!--                        </template>-->
<!--                    </CheckboxList>-->
                </div>
            </div>

            <DialogModal :show="createTask" :closeable="true" @close="createTask = false">
                <template #title>
                    <div class="dark:text-stone-200 p-4">
                        Create a task
                    </div>
                </template>
                <template #content>
                    <div class="dark:text-stone-200 p-4 flex flex-col gap-4 border dark:border-stone-600 rounded-lg">
                        <SporkField v-model="form.name" label="Name" placeholder="hello there"/>
                        <SporkField v-model="form.type" label="Type"/>
                        <SporkField v-model="form.status" label="Status"/>
                        <SporkField v-model="form.notes" label="Notes" type="textarea"/>
                        <SporkField v-model="form.start_date" label="Start Date" type="date"/>
                        <SporkChecklist v-model="form.checklist" label="Checklist"/>
                    </div>
                </template>
                <template #footer>
                    <div class="dark:text-stone-200 p-4 flex justify-between gap-4">
                        <spork-button @click="createTask = !createTask" small secondary>
                            Close
                        </spork-button>
                        <spork-button @click="saveTask(form); createTask = !createTask" small primary>
                            Save
                        </spork-button>
                    </div>
                </template>
            </DialogModal>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CrudView from "@/Components/Spork/CrudView.vue";
import SporkInput from "@/Components/Spork/SporkInput.vue";
import SporkField from '@/Components/Spork/SporkField.vue';
import Status from '@/Components/Status.vue'
import { buildUrl } from "@kbco/query-builder";
import {
    ShieldCheckIcon,
    ChevronRightIcon,
    CheckIcon,
    CheckCircleIcon,
    TrashIcon,
    LockClosedIcon,
    CodeBracketIcon,
    ForwardIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/solid";

import {
    ArrowLongRightIcon,
    DocumentIcon,
} from "@heroicons/vue/20/solid";
import Modal from "@/Components/Modal.vue";
import DialogModal from "@/Components/DialogModal.vue";
import SporkButton from "@/Components/Spork/SporkButton.vue";
import DynamicIcon from "@/Components/DynamicIcon.vue";
import SporkChecklist from "@/Components/Spork/SporkChecklist.vue";
import SmallTaskList from "@/Components/Spork/SmallTaskList.vue";
import CheckboxList from "@/Components/Spork/Atoms/CheckboxList.vue";
import Server from "@/Components/Spork/Molecules/Server.vue";

const allSelected = ref(false);
const createOpen = ref(false);
const form = ref(({
    status: 'To Do',
}));
const $page = usePage();
const data = ref([]);
const pagination = ref({});
const attach = ref([]);
const resources = ref([]);
const attachOpen = ref(false);
const createTask = ref(false);

const project = computed(() => $page.props.project);


const hasErrors = (error) => {
    if (!form.errors) {
        return '';
    }

    return form.errors[error] ?? null;
}
const onDelete = async (data) => {
    await axios.delete('/api/crud/projects/' + form.id);
}
const fetchServers = async ({page, limit}) => {
    const {data: {data, ...pagination}} = await axios.get(buildUrl(
        '/api/crud/servers', {
            page, limit,
            include: ['tags']
        }
    ));
    attach.value = data;
    // Let's set a default server selection to be any server with the default tag.
    resources.value = data.filter(server => server?.tags?.map(tag => tag.name.en).includes('default'))?.map(server => server.id) ?? [];
}
const fetchDomains = async ({page, limit}) => {
    const {data: {data, ...pagination}} = await axios.get(buildUrl(
        '/api/crud/domains', {
            page, limit,
            action: 'pagination:100',
            sort: 'name',
            include: ['projects']
        }
    ));

    attach.value = data.filter(domain => domain.projects.length === 0);
}
const fetchCredentials = async ({page, limit}) => {
    const {data: {data, ...pagination}} = await axios.get(buildUrl(
        '/api/crud/credentials', {
            page, limit,
            action: 'pagination:100',
            sort: 'name',
            include: []
        }
    ));

    attach.value = data;
}
const fetchResearch = async ({page, limit}) => {
    const {data: {data, ...pagination}} = await axios.get(buildUrl(
        '/api/crud/research', {
            page, limit,
            action: 'pagination:100',
            include: []
        }
    ));

    attach.value = data;
}
const fetchPages = async ({page, limit}) => {
    const {data: {data, ...pagination}} = await axios.get(buildUrl(
        '/api/crud/pages', {
            page, limit,
            action: 'pagination:100',
            include: []
        }
    ));

    attach.value = data;
}
const attachToDeployment = async (type, model, deployment) => {
    await axios.post(
        route('deployment.attach', [deployment.id]), {
            resource_type: type,
            resource_id: model,
        }
    )
    router.reload({only: [
            'project',
        'deployments.domains',
        'deployments.servers',
        'deployments.domain',
        'deployments.server',
    ]})
};

const detach = async (item) => {
    await axios.post('/project/' + $page.props.project.id + '/detach', item.pivot);
    router.reload({only: ['project']})
}
const saveTask = async (form) => {
    await axios.post('/api/projects/' + $page.props.project.id + '/tasks', {
        ...form,
    });
    createTask.value = false;

    router.reload({})
}

</script>

<style scoped>

</style>
