<template>
    <dual-menu-panel
        name="Basement"
        :navigation="routes"
        redirect="/basement"
        store-query-action="queryBasement"
    />
</template>

<script>
    import DualMenuPanel from "../../components/DualMenuPanel";
    import {HomeIcon, ServerIcon, DocumentAddIcon, CogIcon, LinkIcon } from "@heroicons/vue/outline";

    export default {
        name: "Basement",
        components: {
            DualMenuPanel,
        },

        computed: {
            routes() {
                return [
                    {
                        name: 'Dashboard',
                        href: '/basement/dashboard',
                        icon: HomeIcon,
                        current: false,
                    },
                    {
                        name: 'Domains',
                        href: '/basement/domains',
                        icon: LinkIcon,
                        current: false,
                    },
                    {
                        name: 'Infrastructure',
                        href: '/basement/servers',
                        icon: ServerIcon,
                        current: false,
                    },
                    {
                        name: 'Settings',
                        href: '/basement/settings',
                        icon: CogIcon,
                        current: false,
                    },
                ]
            }
        },
        mounted() {
        }
    }

</script>

<style scoped>

</style>
