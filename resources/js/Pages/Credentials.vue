<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-stone-800 dark:text-stone-200 leading-tight">
                Credentials
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-stone-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- We need to figure out a better way to get the crud actions. -->
                    <crud-view
                        :form="form"
                        singular="Credential"
                        @destroy="onDelete"
                        @index="({ page, limit, ...args}) => fetch({ page, limit, ...args })"
                        @execute="onExecute"
                        @save="save"
                        :save="save"
                        :data="data"
                        :paginator="pagination"
                    >
                        <template #modal-title>
                            <div>
                                Create a credential
                            </div>
                        </template>
                        <template v-slot:data="{ data }">
                            <div class="flex flex-col">
                                <div class="text-lg text-left">
                                    {{ data.name }}
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <div class="text-xs dark:text-stone-300">
                                        {{ data.type }}
                                        / {{ data.service }}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template #no-data>No credentials</template>

                        <template #form>
                            <div>
                                <div class="grid grid-cols-6 gap-4 mt-2">
                                    <div class="col-span-6">
                                        <label for="name" class="block text-sm font-medium">Name</label>
                                        <spork-input v-model="form.name" type="text" name="name" id="name" />
                                    </div>

                                    <div class="col-span-6">
                                        <label for="name" class="block text-sm font-medium">Type</label>
                                        <spork-select v-model="form.type" type="text" name="refresh_token" id="refresh_token">
                                            <option value="">Select one</option>
                                            <option v-for="type in ['domain','registrar', 'ssh','development']" :value="type">{{type}}</option>
                                        </spork-select>
                                    </div>
                                    <div class="col-span-6">
                                        <label for="name" class="block text-sm font-medium">Service</label>
                                        <spork-select v-model="form.service" type="text" name="refresh_token" id="refresh_token">
                                            <option value="">Select one</option>
                                            <option v-for="type in ['namecheap', 'forge', 'cloudflare', 'digitalocean']" :value="type">{{type}}</option>
                                        </spork-select>
                                    </div>

                                    <div class="col-span-6">
                                        <label for="access_token" class="block text-sm font-medium">Api key/Access Token</label>
                                        <spork-input v-model="form.access_token" type="text" name="access_token" id="access_token" />
                                    </div>

                                    <div class="col-span-6" v-if="form.service === 'namecheap'">
                                        <label for="api_user" class="block text-sm font-medium">API User</label>
                                        <spork-input v-model="form.settings.api_user" type="text" name="api_user" id="api_user" />
                                    </div>
                                    <div class="col-span-6" v-if="form.service === 'namecheap'">
                                        <label for="username" class="block text-sm font-medium">Username</label>
                                        <spork-input v-model="form.settings.username" type="text" name="username" id="username" />
                                    </div>
                                    <div class="col-span-6" v-if="form.service === 'namecheap'">
                                        <label for="client_ip" class="block text-sm font-medium">Client IP</label>
                                        <spork-input v-model="form.settings.client_ip" type="text" name="client_ip" id="client_ip" />
                                    </div>

                                    <div class="col-span-6" v-if="form.service === 'cloudflare'">
                                        <label for="account_email" class="block text-sm font-medium">Account Email</label>
                                        <spork-input v-model="form.settings.email" type="text" name="account_email" id="account_email" />
                                    </div>
                                    <div class="col-span-6" v-if="form.service === 'cloudflare'">
                                        <label for="account_id" class="block text-sm font-medium">Account ID</label>
                                        <spork-input v-model="form.settings.account_id" type="text" name="account_id" id="account_id" />
                                    </div>

                                    <div class="col-span-6" v-if="form.type === 'ssh'">
                                        <label for="account_id" class="block text-sm font-medium">SSH Private Key</label>
                                        <spork-input ref="private_key" @change="onFileUploadForPrivateKey" type="file" name="account_id" id="account_id" />
                                    </div>
                                    <div class="col-span-6" v-if="form.type === 'ssh'">
                                        <label for="account_id" class="block text-sm font-medium">SSH Public Key</label>
                                        <spork-input ref="public_key" @change="onFileUploadForPublicKey" type="file" name="account_id" id="account_id" />
                                    </div>
                                </div>
                            </div>
                        </template>

                    </crud-view>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CrudView from "@/Components/Spork/CrudView.vue";
import SporkInput from "@/Components/Spork/SporkInput.vue";
import {buildUrl} from "@kbco/query-builder";
import SporkSelect from "@/Components/Spork/SporkSelect.vue";
export default {
    components: {
        SporkSelect,
        CrudView,
        AppLayout,
        SporkInput
    },
    setup() {
        return {
            createOpen: ref(false),
            form: ref(({
                name: '',
                type: '',
                service: '',
                api_key: '',
                secret_key: '',
                access_token: '',
                refresh_token: '',
                settings: {

                },
            })),
            private_key: ref(null),
            public_key: ref(null),
            data: ref([]),
            pagination: ref({}),
        }
    },
    watch: {
        date(to, from) {
            this.form.remind_at = dayjs(to).startOf('day').utc().format("YYYY-MM-DD HH:mm:ss")
        }
    },
    methods: {
        hasErrors(error) {
            if (!this.form.errors) {
                return '';
            }

            return this.form.errors[error] ?? null;
        },
        dateFormat(contact) {
            return '<span class="text-stone-900">' + contact.starts_at  + '  at </span>' +
                '<span class="text-stone-800">' + dayjs(contact.last_occurrence || contact.remind_at).format('h:mma') + '</span>'
        },
        async save(form) {
            if (!form.id) {
                await axios.post('/api/crud/credentials', form);
            } else {
                console.log('No edit method defined')
            }
        },
        async onDelete(data) {
            await axios.delete('/api/crud/credentials/' + form.id);
        },
        async onExecute({ actionToRun, selectedItems}) {
            try {
                await this.$store.dispatch('executeAction', {
                    url: actionToRun.url,
                    data: {
                        selectedItems
                    },
                });

            } catch (e) {
                console.log(e.message, 'error');
            }
        },
        async fetch({ page, limit, ...args }) {
            try {
                const {data: {data, ...pagination}} = await axios.get(buildUrl(
                    '/api/crud/credentials', {
                        page, limit,
                        ...args,
                        include: []
                    }
                ));

                this.data = data;
                this.pagination = pagination;
            } catch (e) {
                toaster.error(e.message)
            }
        },
        onFileUploadForPrivateKey(event) {
            var fr=new FileReader();
            fr.onload=function(){
                this.form.settings.private_key = fr.result;
            }.bind(this);
            fr.readAsText(event.target.files[0]);
        },
        onFileUploadForPublicKey(event) {
            var fr=new FileReader();
            fr.onload=function(){
                this.form.settings.pub_key = fr.result;
            }.bind(this);
            fr.readAsText(event.target.files[0]);
        },
    },
}
</script>
