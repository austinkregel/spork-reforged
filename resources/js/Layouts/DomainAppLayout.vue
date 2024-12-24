<script setup>
import { ref, computed } from 'vue';
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import GlobalChat from "@/Components/GlobalChat.vue";
import {
    Dialog,
    DialogPanel,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue'
import {
    Bars3Icon,
    BellIcon,
    CalendarIcon,
    ChartPieIcon,
    DocumentDuplicateIcon,
    FolderIcon,
    HomeIcon,
    UsersIcon,
    XMarkIcon,
    CpuChipIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid'
import DynamicIcon from "@/Components/DynamicIcon.vue";
import Search from "@/Components/Spork/Molecules/Search.vue";
const page = usePage()
defineProps({
    title: String,
});
</script>

<template>
    <div class="min-h-screen">
        <Head :title="title" />
        <Banner />


        <main class=""  style="max-height: calc(100vh - 65px); min-height: calc(100vh - 65px);">
            <slot/>
        </main>
    </div>
</template>
