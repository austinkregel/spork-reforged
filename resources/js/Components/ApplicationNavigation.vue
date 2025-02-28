<script setup>

import {Dialog, DialogPanel, TransitionChild, TransitionRoot} from "@headlessui/vue";
import {CpuChipIcon, XMarkIcon} from "@heroicons/vue/24/outline/index.js";
import DynamicIcon from "@/Components/DynamicIcon.vue";
import {Link} from "@inertiajs/vue3";
import {ref} from "vue";

const sidebarOpen = ref(false)


const appNavigation = {
  '': [
    { name: 'Dashboard', href: '/-/dashboard', icon: 'HomeIcon', active: false, },
  ].map(i => {
    i.current = i.href === location.pathname;
    return i;
  }),
  communication: [
    { name: 'Email', href: '/-/postal', icon: 'EnvelopeOpenIcon', active: false, },
    { name: 'Messaging', href: '/-/inbox', icon: 'ChatBubbleLeftRightIcon', active: false, },
  ].map(i => {
    i.current = i.href === location.pathname;
    return i;
  }),
  core: [
    { name: 'CRUD', href: '/-/manage', icon: 'CircleStackIcon', active: false, },
    { name: 'Tags', href: '/-/tag-manager', icon: 'TagIcon', active: false, },
  ].map(i => {
    i.current = i.href === location.pathname;
    return i;
  }),
  tools: [
    { name: 'Banking', href: '/-/banking', icon: 'WalletIcon', active: false, },
    { name: 'RSS Feeds', href: '/-/rss-feeds', icon: 'RssIcon', active: false, },
    { name: 'Infrastructure', href: '/-/servers', icon: 'ServerStackIcon', active: false, },
    { name: 'File Management', href: '/-/file-manager', icon: 'ServerIcon', active: false, },
    { name: 'Projects', href: '/-/projects', icon: 'ClipboardIcon', active: false, },
  ].map(i => {
    i.current = i.href === location.pathname;
    return i;
  }),
};
</script>

<template>
<div>
  <TransitionRoot as="template" :show="sidebarOpen">
    <Dialog as="div" class="relative z-0 lg:hidden" @close="sidebarOpen = false">
      <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-stone-900/80" />
      </TransitionChild>

      <div class="fixed inset-0 flex">
        <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-transtone-x-full" enter-to="transtone-x-0" leave="transition ease-in-out duration-300 transform" leave-from="transtone-x-0" leave-to="-transtone-x-full">
          <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
            <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
              <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                  <span class="sr-only">Close sidebar</span>
                  <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </button>
              </div>
            </TransitionChild>
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-stone-900 px-6 pb-2 ring-1 ring-white/10">
              <div class="flex h-16 shrink-0 items-center">
                <img class="h-8 w-auto" src="https://tailwindui.com/img/logos/mark.svg?color=slate&shade=500" alt="Your Company" />
              </div>
              <nav class="flex flex-col">
                <div v-for="(group, index) in appNavigation" class="mt-6 w-full flex-1 space-y-1 px-2 flex flex-col">
                  <div class="px-2 text-xs font-semibold text-stone-400 uppercase tracking-wider xl:w-12 w-8 truncate">{{ index }}</div>
                  <Link v-for="item in group" :key="item.name" :href="item?.href ?? '#'" :class="[item.current ? 'bg-slate-800 text-white' : 'text-slate-100 hover:bg-slate-800 hover:text-white', 'group flex w-full flex-wrap gap-2 rounded-md text-xs font-medium px-2 py-1.5 xl:justify-start']" :aria-current="item.current ? 'page' : undefined">
                    <DynamicIcon :icon-name="item.icon"  :active="item.current"  :class="[item.current ? 'text-white' : 'text-slate-300 group-hover:text-white', 'xl:h-5 xl:w-5 w-6 h-6']" aria-hidden="true" />
                    <span class="text-base">{{ item.name }}</span>
                  </Link>
                </div>
              </nav>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Static sidebar for desktop -->
  <div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-10 lg:flex lg:flex-col lg:justify-between lg:w-20 xl:w-64 lg:bg-stone-950 lg:pb-4 relative">
    <div>
      <div class="mt-8 -mb-4">
        <Link
            :href="route('dashboard')"
            class="flex shrink-0 items-center justify-center lg:justify-start xl:items-start pl-4 flex-wrap"
        >
          <CpuChipIcon class="h-12 w-12 xl:h-16 xl:w-16 text-slate-500" />
        </Link>
      </div>

      <nav class="mt-8">
        <div v-for="(group, index) in appNavigation" class="mt-6 w-full flex-1 space-y-1 px-2 flex flex-col">
          <div class="px-2 text-xs font-semibold text-stone-400 uppercase text-center xl:text-left tracking-wider truncate">{{ index }}</div>
          <Link v-for="item in group" :key="item.name" :href="item?.href ?? '#'" :class="[item.current ? 'bg-slate-800 text-white' : 'text-slate-100 hover:bg-slate-800 hover:text-white', 'group flex w-full flex-wrap gap-2 rounded-md text-xs font-medium px-2 py-1.5 items-center justify-center xl:justify-start']" :aria-current="item.current ? 'page' : undefined">
            <DynamicIcon :icon-name="item.icon"  :active="item.current"  :class="[item.current ? 'text-white' : 'text-slate-300 group-hover:text-white',
                        'xl:h-5 xl:w-5 w-6 h-6']" aria-hidden="true" />
            <span class="text-base hidden xl:block">{{ item.name }}</span>
          </Link>
        </div>
      </nav>
    </div>
    <div>
      <Link href="/-/settings" :class="[ 'group flex w-full flex-wrap gap-2 rounded-md text-xs font-medium px-2 py-4 items-center justify-center xl:justify-start']">
        <DynamicIcon :icon-name="'Cog6ToothIcon'"  :active="false"  :class="['xl:h-5 xl:w-5 w-6 h-6']" aria-hidden="true" />
        <span class="text-base hidden xl:block">Settings</span>
      </Link>

    </div>
  </div>
</div>
</template>

<style scoped>

</style>