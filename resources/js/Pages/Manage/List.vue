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
      :sub-title="'Manage'"
      home="/-/manage"
  >
    <!-- We need to figure out a better way to get the crud actions. -->
    <crud-view
        :form="form"
        :singular="singular"
        :plural="plural"
        :description="description"
        @destroy="onDelete"
        @destroy-many="onDeleteMany"
        @index="fetchData"
        @execute="onExecute"
        @save="onSave"
        @clear-form="() => { form = FillableArrayToDynamicForm(description.fillable); }"
        :api-link="apiLink"
        :data="data"
        :paginator="paginator"
    >
      <template #modal-title>
        <div>
          Upsert {{ singular }}
        </div>
      </template>
      <template #data="{ data, openModal }">
        <div class="w-full grid grid-cols-6 relative z-0">
          <div class="col-span-5">
            <div class="flex flex-col">
              <Link :href="route('manage.show', [plural])" class="text-lg text-left flex items-center gap-2">
                  <img v-if="data?.personal_finance_icon" :src="data?.personal_finance_icon" class="h-5 w-5" />
                {{ data.name }}
              </Link>
              <div class="flex flex-col gap-2">
                <div class="text-xs dark:text-stone-300">
                    {{ possibleDescriptionForData(data) }}
                </div>
                  <div class="text-xs dark:text-blue-50 flex flex-wrap gap-2">
                      <div v-for="tag in data?.tags" :key="tag.name"
                           class="py-1 px-2 rounded-full bg-blue-300 dark:bg-blue-600"
                           :class="colors(tag.type)"
                      >
                          {{ tag.name.en }}
                      </div>
                  </div>
              </div>
            </div>
          </div>
          <div class="col-span-1 flex gap-4 items-center justify-end px-4">
              <button @click="(e) => { form = (DynamicFormToFillableArray(data)); openModal(); }">
                  <DynamicIcon icon-name="PencilIcon" class="h-5 w-5 fill-current text-green-400" />
              </button>

              <button @click="() => onDelete(data)">
                  <DynamicIcon icon-name="TrashIcon" class="h-5 w-5 fill-current text-red-400" />
              </button>
          </div>
        </div>

      </template>
      <template #no-data>
        <div class="w-full p-4 italic text-center px-4 dark:text-white">No {{ singular }} data</div>
      </template>

      <template #form="{ openModal }">
        <div class="flex flex-col -mt-4">
          <div v-for="(field, i) in form">
              <SporkDynamicInput
                  :key="i+'.form-value'"
                  v-model="form[i]"
                  v-if="description.types[field.name]"
                  :type="description.types[field.name].type ?? 'text'"
                  :disabled-input="!description.fillable.includes(field.name)"
                  :editable-label="false"
                  :errors="errors?.[field.name]"
                  class="mt-4"
              />
          </div>
        </div>
      </template>

    </crud-view>
  </Manage>
</template>
