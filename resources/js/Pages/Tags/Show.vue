<script setup>
import { ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import {
  TagIcon,
  ServerIcon,
  LinkIcon,
    UserIcon,
  BuildingLibraryIcon,
  WalletIcon,
  BoltIcon
} from "@heroicons/vue/24/outline";
import SporkTable from "@/Components/Spork/Atoms/SporkTable.vue";
import ConditionsEditor from "@/Components/Spork/Molecules/ConditionsEditor.vue";
const page = usePage();

const { tag, type } = defineProps({
    // pagination
    tag: Object,
    type: String,
})

const form = ref({
  name: '',
  conditions: [],
})


const events = [
  'App\\Events\\Models\\Finance\\TransactionCreated',
  'App\\Events\\Models\\MessageCreated',

  'App\\Events\\Finance\\BudgetSpendExceededLimit',
  'App\\Events\\Finance\\BudgetReset',

];
const round = ( value) => {
    return Math.round(value * 100) / 100;
}
const date = (d) => {
  return dayjs(d).format('YYYY-MM-DD');
}
</script>

<template>
  <AppLayout title="Profile">
    <div>
      <div class="text-2xl my-8 mx-4">Tags and how they're used in your app</div>

      <div class="gap-4 grid grid-cols-1 mx-4">
        <div class="flex flex-wrap justify-between border border-slate-600 rounded-lg py-2">
            <div class="flex flex-wrap gap-2 items-center my-2 mx-6">
                <WalletIcon class="w-6 h-6 text-blue-400" v-if="tag.type === 'finance'"/>
                <ServerIcon class="w-6 h-6 text-amber-200" v-else-if="tag.type === 'server'"/>
                <BoltIcon class="w-6 h-6 text-green-500" v-else-if="tag.type === 'automatic'" />
                <TagIcon class="w-6 h-6 text-green-400" v-else />
                <Link :href="'/-/tag-manager/' + tag.id" class="text-xl">{{ tag.name?.en }}</Link>
            </div>
        </div>
          <div class="border border-slate-600 rounded-lg">
              <ConditionsEditor
                  :conditions="tag.conditions"
                  :type="type"
                  :id="tag?.id"
              />
              <SporkTable
                  class="-m-2"
                  :items="tag.conditions"
                  :headers="[
                    { name: 'ID', accessor: (item) => item.id },
                    { name: 'Parameter', accessor: (item) => item.parameter },
                    { name: 'Comparator', accessor: (item) => item.comparator },
                    { name: 'Value', accessor: (item) => item.value },
                    { name: 'Type', accessor: (item) => item.conditionable_type },
                    { name: 'Tag Id', accessor: (item) => item.conditionable_id },
                  ]"
                  header="All conditions under this tag"
                  description="Conditions"
              />
          </div>
          <div class="border border-slate-600 rounded-lg">
              <SporkTable
                  class="-m-2"
                  :items="tag.transactions"
                  :headers="[
                    { name: 'Name', accessor: (item) => item.name },
                    { name: 'Amount', accessor: (item) => item.amount },
                    { name: 'Pending', accessor: (item) => item.pending ? 'pending' : 'posted' },
                    { name: 'Date', accessor: (item) => date(item.date) },
                    { name: 'Category', accessor: (item) => item.personal_finance_category },
              ]"
                  header="All transactions under this tag"
                  description="Transactions"
              />
          </div>
          <div class="border border-slate-600 rounded-lg">
              <SporkTable
                  class="-m-2"
                  :items="tag.articles"
                  :headers="[
                    { name: 'Headline', accessor: (item) => item.headline },
              ]"
                  header="Articles under this tag"
                  description="recent articles"
              />
          </div>
          <div class="border border-slate-600 rounded-lg">
              <SporkTable
                  class="-m-2"
                  :items="tag.servers"
                  :headers="[
                    { name: 'Name', accessor: (item) => item.name },
              ]"
                  header="Servers under this tag"
                  description="servers"
              >
              </SporkTable>
          </div>
      </div>
    </div>
  </AppLayout>
</template>
