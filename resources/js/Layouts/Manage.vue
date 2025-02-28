<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {computed} from 'vue'
import {Link, usePage} from "@inertiajs/vue3";
const page = usePage()
const { description, home, subTitle } = defineProps({
  title: String,
  subTitle: String,
  home: String,
  description: Object,
})
const availablePages = computed(() => page.props.subnavigation)

import DynamicIcon from "@/Components/DynamicIcon.vue";
</script>

<template>
    <AppLayout :title="title">
        <div class="py-8 w-full grid grid-cols-3 lg:grid-cols-5">
            <div class="col-span-1 lg:col-span-1 sm:px-6 lg:px-8 flex flex-col gap-4">
              <Link
                  :href="home"
                  class="text-white w-full text-2xl my-4 font-bold"
              >
                {{ subTitle }}
              </Link>
                <Link
                    v-for="page in availablePages"
                    :href="page.href"
                    class="text-white w-full flex flex-wrap gap-2"
                    :class="''"
                >
                    <DynamicIcon v-if="page.icon" :icon-name="page.icon" class="w-6 h-6 outline-current" :active="false" />
                    {{ page.name }}
                </Link>
            </div>


          <div class="col-span-2 lg:col-span-4 pl-8 pr-8 xl:pl-0 md:pr-8 text-black dark:text-white">
            <!-- We need to figure out a better way to get the crud actions. -->
            <slot />
          </div>
        </div>
    </AppLayout>
</template>
