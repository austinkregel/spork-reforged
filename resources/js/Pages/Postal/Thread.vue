<template>
    <AppLayout title="Dashboard">
        <!-- component -->
        <main class="grid grid-cols-3 overflow-hidden">
            <section class="flex flex-col pt-3 bg-stone-50 dark:bg-stone-900  overflow-y-scroll" style="height: calc(100vh - 65px);">
                <ul class="divide-y divide-stone-200 dark:divide-stone-700">
                    <li v-for="thread in page.props.threads.data" class="p-4 px-3 transition hover:bg-slate-100 dark:hover:bg-slate-600">
                        <Link :href="route('inbox.show', thread.id)" class="flex flex-col">
                            <h3 class="text-lg font-semibold dark:text-stone-50 truncate">{{ thread.name}}</h3>
                            <div class="text-sm truncate dark:text-stone-200">{{ thread.participants.map(p => p.name).join(", ") }}</div>
                        </Link>
                      <div class="flex flex-wrap pt-1">
                        <div class="text-md italic text-stone-400 dark:text-stone-200">{{ thread.description }}</div>
                        <p class="text-sm text-stone-400">{{ thread.human_timestamp}}</p>
                      </div>
                    </li>
                </ul>
            </section>
            <section class="relative border-l-2 dark:border-stone-800 col-span-2 flex flex-col bg-white dark:bg-stone-800 overflow-y-scroll" style="height: calc(100vh - 65px);">
                <div class="sticky z-0 bg-white dark:bg-stone-800 top-0 flex justify-between items-center h-24 border-b-2 dark:border-stone-700 p-4">
                    <div class="flex space-x-4 items-center">
                        <div class="isolate flex -space-x-2 overflow-hidden">
                            <img v-for="(participant, i) in thread.participants.slice(0, 10)" :class="'z-'+i+( i > 0? '0' : '')" class="relative  inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-stone-600"
                                 :src="participant?.photo_url ?? ('/storage/'+participant.id+'.png')" alt="" />
                        </div>
                        <div class="flex flex-col">
                            <h3 class="font-semibold text-lg dark:text-stone-50 truncate">{{
                                thread.participants.map(particpant =>
                                    particpant.name).join(', ')
                              }}</h3>
                            <p class="text-light text-stone-400 dark:text-stone-200">{{thread.topic}}</p>
                        </div>
                    </div>
                    <div>
                        <ul class="flex text-stone-400 space-x-4">
                            <li class="w-6 h-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                                </svg>
                            </li>
                            <li class="w-6 h-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </li>

                            <li class="w-6 h-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </li>
                            <li class="w-6 h-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </li>
                            <li class="w-6 h-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </li>
                        </ul>
                    </div>
                </div>
                <section class="flex-grow bg-gray-300 dark:bg-zinc-900">
                    <article class="px-4 mt-4 text-stone-500 dark:text-stone-50 leading-7 tracking-wider gap-1 flex flex-col-reverse">
                        <div v-for="message in thread.messages" class="w-full flex gap-4 flex-wrap">
                            <div class="mt-5">
                                <img :src="message?.from_person?.photo_url ?? ('/storage/'+message.from_person.id+'.png')" alt="" class="h-8 w-8 rounded-full" />
                            </div>

                            <div>
                                <div class="-my-1 text-xxs text-black dark:text-stone-400">{{message?.from_person?.name}} -- {{ formatDate(message.originated_at) }}</div>
                                <div
                                    :class="[message.is_user ? 'bg-indigo-600': ' bg-blue-600']"
                                    class="px-2 py-1 flex rounded-lg shadow"
                                >
                                    <img v-if="message.thumbnail_url || message.message?.startsWith('https://tenor.com')" :src="message.thumbnail_url ?? message.message" :alt="message.message" class="w-64"/>
                                    <div v-else-if="message.html_message" v-html="message.html_message"></div>
                                    <div v-else-if="message.thumbnail_url">{{message.thumbnail_url}}</div>
                                    <Markdown v-else :source="message.message" class="prose dark:prose-invert"></Markdown>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>
                <section class=" flex flex-col border dark:border-zinc-900 bg-stone-50 dark:bg-zinc-950">
                    <textarea class="bg-stone-50 dark:bg-stone-900 p-2  m-2 rounded-xl dark:border-stone-600" placeholder="Type your reply here..." rows="3"></textarea>
                    <div class="flex items-center justify-between p-2">
                        <button class="h-6 w-6 text-stone-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </button>
                        <button class="bg-purple-600 dark:bg-purple-700 text-white px-6 py-2 rounded-xl">Reply</button>
                    </div>
                </section>
            </section>
        </main>
    </AppLayout>
</template>


<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CrudView from "@/Components/Spork/CrudView.vue";
import SporkInput from "@/Components/Spork/SporkInput.vue";
import {buildUrl} from "@kbco/query-builder";
import Markdown from 'vue3-markdown-it';


const page = usePage();

const data = ref([]);
const pagination = ref({});
const thread = computed(() => page.props.thread);

const hasErrors = (error) => {
  if (!this.form.errors) {
    return '';
  }

  return this.form.errors[error] ?? null;
};
const save = async (form) => {
  if (!form.id) {
    await axios.post('/api/crud/servers', form);
  } else {
    console.log('No edit method defined')
  }
};
const onDelete = async (data) => {
  await axios.delete('/api/crud/servers/' + form.id);
};
const onExecute = async({ actionToRun, selectedItems}) => {
  try {
    await this.$store.dispatch('executeAction', {
      url: actionToRun.url,
      data: {
        selectedItems
      },
    });

  } catch (e) {
    console.log(e.message, 'error');
  }
}
const formatDate = (d) => dayjs(d * 1000).fromNow();
</script>

<style scoped>

</style>
