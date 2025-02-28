<script setup>

import {Menu, MenuButton, MenuItem, MenuItems} from "@headlessui/vue";
import {Bars3Icon, BellIcon} from "@heroicons/vue/24/outline/index.js";
import NotificationBody from "@/Components/NotificationBody.vue";
import {ChevronDownIcon} from "@heroicons/vue/20/solid/index.js";
import Search from "@/Components/Spork/Molecules/Search.vue";
import {Link} from "@inertiajs/vue3";

const { user, notifications, notificationCount, } = defineProps({
  user: Object,
  notifications: Array,
  notificationCount: Number,
});


const userNavigation = [
  { name: 'Your profile', href: '/user/profile' },
  { name: 'Query Builder', href: '/user/api-query' },
  { name: 'Sign out', href: '#' },
]
</script>

<template>
  <div class="shadow-lg sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-stone-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
    <!-- Separator -->
    <div class="h-6 w-px bg-stone-900/10 lg:hidden" aria-hidden="true" />

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
      <Search />
      <div class="flex items-center gap-x-4 lg:gap-x-6">
        <Menu as="div" class="relative z-20">
          <MenuButton class="-m-1.5 flex items-center p-1.5">
            <span class="sr-only">View notifications</span>
            <BellIcon class="h-6 w-6" aria-hidden="true" />
            <span v-if="notificationCount > 0" class="bg-red-500 absolute top-0 right-0 rounded-full text-xs text-white py-0.5 px-1">
              {{ notificationCount }}
            </span>
          </MenuButton>

          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-0 mt-2.5 w-96 origin-top-right rounded-md bg-white dark:bg-stone-800 py-2 shadow-lg ring-1 ring-stone-900/5 focus:outline-none">
              <MenuItem class="flex items-start px-2" v-for="notification in notifications" :key="notification">
                <NotificationBody :notification="notification" />
              </MenuItem>
            </MenuItems>
          </transition>
        </Menu>

        <!-- Separator -->
        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-stone-900/10" aria-hidden="true" />

        <!-- Profile dropdown -->
        <Menu as="div" class="relative z-20">
          <MenuButton class="-m-1.5 flex items-center p-1.5">
            <span class="sr-only">Open user menu</span>
            <img class="h-8 w-8 rounded-full bg-stone-50" :src="user.profile_photo_url" alt="" />
            <span class="hidden lg:flex lg:items-center">
                                  <span class="ml-4 text-sm font-semibold leading-6 text-stone-900 dark:text-neutral-50" aria-hidden="true">{{ user.name }}</span>
                                  <ChevronDownIcon class="ml-2 h-5 w-5 text-stone-400" aria-hidden="true" />
                                </span>
          </MenuButton>
          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute min-w-48 right-0 z-0 mt-2.5 w-32 origin-top-right rounded-md bg-white dark:bg-stone-800 py-2 shadow-lg ring-1 ring-stone-900/5 focus:outline-none">
              <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                <Link :href="item.href" :class="[active ? 'bg-stone-50 dark:bg-stone-700' : '', 'block px-3 py-1 text-base leading-6 dark:text-stone-50 text-stone-900']">{{ item.name }}</Link>
              </MenuItem>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>