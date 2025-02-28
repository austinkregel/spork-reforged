<template>
    <AppLayout title="Dashboard">
    <div class="w-full border-b dark:border-slate-700 dark:bg-stone-950">
            <div  class="max-w-7xl mx-auto px-8 py-4 flex items-center gap-2 font-semibold text-2xl text-stone-800 dark:text-stone-200 leading-tight">
                <Link href="/-/projects" class="underline">
                    Projects
                </Link>
                <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-stone-400" aria-hidden="true" />
                <span>Create</span>
            </div>
        </div>

        <div class="max-w-4xl w-full mx-auto py-8 px-4  gap-4">
            <div class="rounded-lg p-4 bg-stone-300 dark:bg-stone-800 shadow-lg">
              <pre>{{ errors }}</pre>
                <form @submit.prevent="createProject">
                    <div v-for="(field, i) in form">
                        <SporkDynamicInput
                            :key="i+'.form-value'"
                            v-model="form[i]"
                            type="text"
                            :disabled-input="!description.fillable.includes(field.name)"
                            :editable-label="false"
                            :errors="errors?.[field.name]"
                            class="mt-4"
                        />
                    </div>
                  <div>

                    <div class="mt-4 px-4 text-gray-300 lowercase text-sm">Type</div>
                    <SporkSelect v-model="type" class="mt-1">
                      <template #options>
                        <optgroup label="Deployments">
                          <option value="laravel_deployment">Laravel Deployment</option>
                          <option value="github_pages_deployment">Github Pages Deployment</option>
                          <option value="static_site_deployment">Static Site Deployment</option>
                        </optgroup>
                        <optgroup label="Research">
                          <option value="goal_oriented_research">Goal Oriented Research</option>
                          <option value="exploratory_research">Exploratory Research</option>
                        </optgroup>
                        <optgroup label="Perpetual">
                          <option value="perpetual">Perpetual</option>
                        </optgroup>
                      </template>

                    </SporkSelect>
                  </div>

                    <button
                        class="inline-flex items-center border shadow-sm font-medium rounded-md focus:outline-none px-2 py-1 mt-4"
                        type="submit"
                    >
                        Create
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import {usePage, Link, router} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ChevronRightIcon, PlusIcon } from "@heroicons/vue/24/solid";
import { ref } from "vue";
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import SporkSelect from "@/Components/Spork/SporkSelect.vue";
import SporkLabel from "@/Components/Spork/SporkLabel.vue";

const $page = usePage()

const { description } = defineProps({
    description: Object,
})

const form = ref(['name', 'settings'].map(field => ({
    name: field,
    value: ''
})));
const type = ref(null);
const errors = ref(null);
const createProject = () => {
    router.post('/-/projects', form.value.reduce((carry, f) => ({
      ...carry,
      [f.name]: f.value
    }), {
      user_id: $page?.props?.auth?.user?.id
    }), {
        preserveScroll: true,
        onSuccess: () => Promise.all([

        ]),
        onError: (error) => {
            errors.value = Object.keys(error).reduce((acc, key) => {
                return {
                    ...acc,
                    [key]:[error[key]]
                };
            }, {});
        },
        onFinish: () => {
            console.log('finished')
        },
    })
}

</script>
