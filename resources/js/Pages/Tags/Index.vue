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
import SporkDynamicInput from "@/Components/Spork/SporkDynamicInput.vue";
import SporkInput from "@/Components/Spork/SporkInput.vue";
import SporkSelect from "@/Components/Spork/SporkSelect.vue";
import DynamicIcon from "@/Components/DynamicIcon.vue";
const page = usePage();

const { tags } = defineProps({
  // pagination
  tags: Object,
})

const { data, ...pagination } = tags;
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

const addCondition = () => {
  form.value.conditions.push({
    value: '',
    comparator: 'LIKE',
    parameter: 'name',
  })
};
const deleteCondition= () => {}
</script>

<template>
  <AppLayout title="Profile">
    <div>
      <div class="text-2xl my-8 mx-4">Tags and how they're used in your app</div>

      <div class="gap-4 grid grid-cols-1 lg:grid-cols-3 mx-4">
        <div v-for="tag in data" class="flex flex-col justify-between border border-slate-600 rounded-lg py-2">
            <div class="flex flex-wrap gap-2 items-center my-2 w-full">
                <span class="ml-6">
                    <WalletIcon class="w-6 h-6 text-blue-400" v-if="tag.type === 'finance'"/>
                    <ServerIcon class="w-6 h-6 text-amber-200" v-else-if="tag.type === 'server'"/>
                    <BoltIcon class="w-6 h-6 text-green-500" v-else-if="tag.type === 'automatic'" />
                    <TagIcon class="w-6 h-6 text-green-400" v-else />
                </span>

                <Link :href="'/-/tag-manager/' + tag.id" class="text-xl underline flex flex-wrap justify-between flex-grow items-center hover:text-stone-300">
                    <div>
                        {{ tag.name?.en }}
                    </div>
                    <div class="flex flex-grow text-right justify-end mr-6">
                        <DynamicIcon icon-name="ChevronRightIcon" class="w-5 h-5" />
                    </div>
                </Link>
            </div>
            <div class="flex gap-4 my-2 mx-6 text-xs">
                <div class="flex items-center gap-2">
                    <ServerIcon class="w-5 h-5" />
                    {{ tag.servers_count }}
                </div>
                <div class="flex items-center gap-2">
                    <LinkIcon class="w-5 h-5" />
                    {{ tag.domains_count }}
                </div>
                <div class="flex items-center gap-2">
                    <UserIcon class="w-5 h-5" />
                    {{ tag.people_count }}
                </div>
                <div class="flex items-center gap-2">
                    <WalletIcon class="w-5 h-5" />
                    ${{ round(tag.transactions_sum_amount).toLocaleString() }}
                </div>
                <div class="flex items-center gap-2">
                    <DynamicIcon icon-name="EmailIcon" class="w-5 h-5" />
                    {{ tag.messages_count }}
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
