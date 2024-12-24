import './bootstrap';
import '../css/app.css';
import Toaster from "@meforma/vue-toaster";
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist';
import { createStore } from "vuex";
import Hero from './Builder/Components/Heros/Classic.vue';
import BuilderButton from './Builder/Components/BuilderButton.vue';
import BuilderText from './Builder/Components/BuilderText.vue';
import Notifications from 'notiwind';
import Grid from './Components/Grid.vue';
import TitleAndFooterTextCard from "./Builder/Components/Cards/TitleAndFooterTextCard.vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import 'dayjs/locale/en';
import utc from 'dayjs/plugin/utc';

dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.locale('en');

window.dayjs = dayjs;
const registeredComponents = [];
const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

window.Spork = {

};
const playSound = (name) => {
    // glitch-sound
    // error-sound
    // success-sound
    // notification-sound
    const v = document.getElementById(name+'-sound');
    v.volume = 0.15;
    v.currentTime = 0;
    v.play();
}
window.playSound = playSound;
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const store = createStore({
            modules: {

            },
            getters: {
                appName(getters, rootGetters) {
                    return 'Spork App';
                },
                components(getters, rootGetters) {
                    return registeredComponents;
                }
            },
            actions: {
                playSound(context, name) {
                    playSound(name);
                }
            },
        })

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(Notifications)
            .use(store)
            .use(Toaster)
            .use(ZiggyVue, Ziggy);
        const addComponentToRegistry = (component) => {
            const name = (component.__name ?? component.name ?? component.__file ?? '').split('/').pop().split('.')[0];
            registeredComponents.push({
                id: (component.__id ?? component.id ?? component.__file ?? '').split('/').pop().split('.')[0],
                name,
                svg: component?.icon ?? `<svg data-darkreader-inline-stroke="" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"></path>
</svg>`,
                props: Object.keys(component.props).reduce((obj, key) => {
                    const defaultValue = component.props[key]?.default ?? null;

                    if (typeof defaultValue === 'function') {
                        return {
                            ...obj,
                            [key]: defaultValue()
                        }
                    }

                    return {
                        ...obj,
                        [key]: defaultValue
                }
                }, {}),
                children: component.children ?? [],
            })
            app.component(name, component);

            return app;
        }
        Spork.store = store;
        Spork.addComponentToRegistry = addComponentToRegistry;
        Spork.app = app;

        addComponentToRegistry(Hero);
        addComponentToRegistry(BuilderButton);
        addComponentToRegistry(BuilderText);
        addComponentToRegistry(Grid);
        addComponentToRegistry(TitleAndFooterTextCard);

        if (props?.initialPage?.props?.auth?.user?.id) {
            const userId = props?.initialPage?.props?.auth?.user?.id;
            Echo.private(`App.Models.User.${userId}`)
                .listen('Models.Message.MessageCreated', (e) => {
                    playSound('notification');
                    router.reload({
                        only: ['messages', 'unread_email_count'],
                    });
                })
                .listen('Models.Account.AccountUpdated', e => {
                    router.reload({
                        only: ['accounts', 'unread_email_count']
                    })
                })
                .listen('Models.JobBatch.JobBatchCreated', e => {
                    router.reload({
                        only: ['job_batches', 'news']
                    })
                })
                .listen('Models.JobBatch.JobBatchUpdated', e => {
                    router.reload({
                        only: ['job_batches', 'news']
                    })
                })
                .listen('Models.Server.ServerCreated', e => {
                    router.reload({
                        only: ['servers', 'data', 'server']
                    })
                })
                .listen('Models.Server.ServerUpdating', e => {
                    router.reload({
                        only: ['servers', 'data', 'server']
                    })
                })
                .listen('Models.Server.ServerUpdated', e => {
                    router.reload({
                        only: ['servers', 'data', 'server']
                    })
                })
                .listen('Models.Server.ServerDeleted', e => {
                    router.reload({
                        only: ['servers', 'data', 'server']
                    })
                })
                .notification((notification) => {
                    playSound('notification');
                    console.log('notification', notification);
                })
                .error((error) => {
                    console.error('non-fatal error', error);
                })
        }

        if (props?.initialPage?.props.auth.user?.person?.id) {
            Echo.private('App.Models.Person.'+props?.initialPage?.props.auth.user?.person?.id)
                .listen('message', (data) => {
                    console.log('Message from server', data);
                    playSound('notification');
                    router.reload({
                        only: ['messages', 'unread_email_count'],
                    });

                })
                .error((error) => {
                    console.error(error);
                });
        }

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
