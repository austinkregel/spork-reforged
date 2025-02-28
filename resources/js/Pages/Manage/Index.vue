<script setup>
import {usePage, Link, router} from '@inertiajs/vue3'
import { ref } from 'vue';
import DynamicIcon from "@/Components/DynamicIcon.vue";
import CrudView from "@/Components/Spork/CrudView.vue";
import { buildUrl } from '@kbco/query-builder';
import Manage from "@/Layouts/Manage.vue";
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import MetricApiCard from "@/Components/Spork/Molecules/MetricApiCard.vue";
import MetricCard from "@/Components/Spork/Atoms/MetricCard.vue";
const {
  title,
  activity,
  metrics
} = defineProps({
  title: String,
  metrics: Array,
  activity: Object,
})


const formatDateIso = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
}
</script>

<template>
  <Manage
      :title="title"
      sub-title="Manage"
      home="/-/manage"
  >
    <div>
        <div class="grid grid-cols-6 gap-4">
          <MetricCard
              v-for="(value, metric) in metrics"
              :title="metric"
              :value="value"
              :loading="false"
              :sub-title="''"
          />

        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-lg mt-4 p-4 bg-white dark:bg-stone-800 flex flex-col">
                <div v-for="item in activity.data" :key="item">
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
        </div>
    </div>
  </Manage>
</template>
