<script setup>
import {usePage, Link, router} from '@inertiajs/vue3'
import { ref } from 'vue';
import DynamicIcon from "@/Components/DynamicIcon.vue";
import CrudView from "@/Components/Spork/CrudView.vue";
import { buildUrl } from '@kbco/query-builder';
import Manage from "@/Layouts/Manage.vue";
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import MetricApiCard from "@/Components/Spork/Molecules/MetricApiCard.vue";
const { title, data, description, paginator, link, plural, apiLink, singular, body, metrics } = defineProps({
  data: Array,
  title: String,
  paginator: Object,
  description: Object,
  singular: String,
  plural: String,
  link: String,
  body: String,
  apiLink: String,
    metrics: Array,
})

const FillableArrayToDynamicForm = function (fillable) {
  return fillable.map(value => ({
    value: '',
    name: value,
  }));
}

const DynamicFormToFillableArray = function (model) {
  return Object.keys(model).map(key => ({
    value: typeof (model[key] ?? '') === 'object' ? JSON.stringify(model[key]?? '') : (model[key] ?? ''),
    name: key,
  }));
}

const formatDateIso = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
}
const form = ref(FillableArrayToDynamicForm(description.fillable));
const errors = ref(null);
const fetchData = async (options) => {
  const response = await axios.get(buildUrl(apiLink, {
    page: 1,
    limit: 15,
    ...(options ?? {})
  }))

    const { data: pageOfData, ...pagination_ } = response.data;
    data.value =pageOfData;
    paginator.value = pagination_;
}
const colors = (type) => {
    switch(type) {
        case 'finance':
            return 'bg-blue-300 dark:bg-blue-600';
        case 'server':
            return 'bg-amber-300 dark:bg-amber-600';
        case 'automatic':
        default:
            return 'bg-green-300 dark:bg-green-700';
    }
};
const onDelete = (data) => {
    axios.delete('/api/crud/'+plural+'/'+data.id).finally(() => {
    router.reload({
      only: ['data', 'paginator'],
    });
  });
}

const onDeleteMany = (manyData) => {
    axios.post('/api/crud/'+plural+'/delete-many', {
        items: manyData.map(item => item.id)
    }).finally(() => {
        router.reload({
        only: ['data', 'paginator'],
        });
    });
}
const onExecute = async ({ selectedItems, actionToRun, next }) => {
  await axios.post('/api/actions/'+actionToRun.slug, {
    items: selectedItems.map(item => item.id)
  });

  next()
}
const onSave = async (form, toggle) => {
    const data = form.reduce((all, { name, value }) => ({ ...all, [name]: value }), {});
    let url = '/api/crud/'+plural;

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

const possibleDescriptionForData = (data) => {
  const fieldsToUse = description?.fields?.filter(field => ![
    'id', 'name', 'user_id', 'created_at', 'updated_at', 'icon', 'href', 'order', 'value,'
  ]?.includes(field) && !field.endsWith('_id') && typeof data[field] !== 'boolean')
      .filter(field => data[field]);

  if (description?.fields?.includes('cloudflare_id')) {
      return data.cloudflare_id
  }


  return data[fieldsToUse[0] ?? 0] ?? ''
}
const possibleRelations = (data) => {
  const fieldsToUse = description?.fields?.filter(field => field.endsWith('id') && typeof data[field.replace('_id', '')] == "object")

  return fieldsToUse;
}
const log = console.log;
</script>

<template>
  <Manage
      :title="title"
      sub-title="Manage"
      home="/-/manage"
  >
    <div>
        <div class="grid grid-cols-6 gap-4">
            <MetricApiCard url="/api/crud/people?action=count&filter[relative]=user" title="people" />
            <MetricApiCard url="/api/crud/credentials?action=count&filter[relative]=user" title="credentials" />
            <MetricApiCard url="/api/crud/external_rss_feeds?action=count&filter[relative]=user" title="rss feeds" />
            <MetricApiCard url="/api/crud/accounts?action=count&filter[relative]=user" title="accounts" />
            <MetricApiCard url="/api/crud/pages?action=count" title="pages" />
            <MetricApiCard url="/api/crud/projects?action=count&filter[relative]=user" title="projects" />
            <MetricApiCard url="/api/crud/threads?action=count&filter[relative]=user" title="threads" />
            <MetricApiCard url="/api/crud/navigations?action=count" title="navigations" />
            <MetricApiCard url="/api/crud/domains?action=count&filter[relative]=user" title="domains" />
            <MetricApiCard url="/api/crud/transactions?action=count&filter[relative]=user" title="transactions" />
            <MetricApiCard url="/api/crud/research?action=count&filter[relative]=user" title="research" />
            <MetricApiCard url="/api/crud/scripts?action=count&filter[relative]=user" title="scripts" />
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-lg mt-4 p-4 bg-white dark:bg-stone-800 flex flex-col">
                <div v-for="item in metrics.data" :key="item">
                    <div class="flex flex-col">
                        <div class="flex flex-wrap gap-2 text-base items-center">
                          <pre class="truncate"><span class="text-sm pr-4">{{item.log_name}}</span><span class="text-sm pr-4">{{item.description}}</span>{{item.properties?.attributes?.name ?? item.properties?.attributes?.headline}}
                            </pre>
                        </div>
                        <div class="text-xs -mt-1 text-black dark:text-stone-200">
                            {{formatDateIso(item.created_at)}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-lg mt-4 p-4 bg-white dark:bg-stone-800 flex flex-col">
                <div v-for="item in metrics.data" :key="item">
                    <div class="flex flex-col">
                        <div class="flex flex-wrap gap-2 text-sm items-center">
                            <div>{{item.log_name}}</div>
                            <div>{{item.description}}</div>
                            <div class="text-xs">
                                {{item.properties?.attributes?.name ?? item.properties?.attributes?.headline}}
                            </div>
                        </div>
                        <div class="text-xs -mt-1 text-black dark:text-stone-200">
                            {{formatDateIso(item.created_at)}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </Manage>
</template>
