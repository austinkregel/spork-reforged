<script setup>
import ContextMenuButton from "@/Components/ContextMenus/ContextMenuButton.vue";
import SmallTag from "@/Components/Spork/Atoms/SmallTag.vue";
import ContextMenu from "@/Components/ContextMenus/ContextMenu.vue";
import {ref} from "vue";
import ApplyTagModal from "@/Components/Spork/Molecules/ApplyTagModal.vue";
import ContextMenuLink from "@/Components/ContextMenus/ContextMenuLink.vue";

const { transactions } = defineProps({
  transactions: {
    type: Array,
    required: true
  }
})

const dateFormat = (value) => dayjs(value).format('YYYY-MM-DD')

const applyingTags = ref(false);
</script>

<template>
  <div class="divide-y divide-gray-100 dark:divide-stone-700 gap-2 flex flex-col">
    <ContextMenu v-for="transaction in transactions" class="pt-2 text-sm" :key="'transaction.'+transaction.id">
        <template #default="{ open }">
          <div class="flex flex-row justify-between gap-2 px-2 items-center">
            <div class="rounded-full overflow-hidden"><img class="w-8 h-8 bg-white  p-1" :src="transaction.personal_finance_icon" :alt="transaction.personal_finance_category"/></div>
            <div class="flex flex-col flex-grow">
              <div class="text-stone-800 dark:text-stone-50">
                {{transaction.name}}
              </div>
              <div class="text-stone-900 dark:text-stone-400">{{ dateFormat(transaction.date) }}</div>
            </div>
            <div class="flex flex-col text-right justify-end">
              <div class="text- stone-800 dark:text-stone-100 text-xl">
                ${{ transaction.amount }}
              </div>
              <div  class="text-xs w-full text-right">{{ transaction?.account?.name }}</div>
            </div>
          </div>
          <div class="flex flex-wrap text-xs gap-2 mx-2">
            <SmallTag v-for="tag in transaction.tags.map(tag => tag.name.en)" :key="'tags.'+tag" class="bg-blue-900 px-1 py-0.5 rounded-lg text-blue-100" :tag="tag"></SmallTag>
          </div>
        </template>

        <template #items="{ close }">
          <ContextMenuLink :href="'/-/banking/transactions/'+transaction.id">Inspect Transaction</ContextMenuLink>
        </template>
    </ContextMenu>
  </div>
</template>
