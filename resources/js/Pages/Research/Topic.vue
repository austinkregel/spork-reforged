<style scoped lang="css">
.pslate {
    font-size: 0.875rem;
    line-height: 1.7142857;
}
@media (prefers-color-scheme: dark) {
    .dark\:pslate-invert > blockquote {
        border-color: #fff;
    }
}
</style>

<template>
<AppLayout title="Research">
  <div class="h-full" v-if="topic">
    <div class="flex w-full h-screen overflow-auto">
      <div class="w-1/2 flex flex-col items-center">
        <div class="w-full flex flex-wrap justify-between items-center font-medium text-stone-700 px-4 py-2 border-r border-stone-200 dark:border-stone-800">
          <div class="text-center">{{ topic.name }}</div>
          <button @click.prevent="stack = !stack" class="underline dark:text-stone-200">
            {{ stack ? 'Full Preview': 'Edit'}}
          </button>
        </div>
        <div v-if="topic.notes !== null" class="w-full relative flex flex-col" v-show="stack">
                <textarea
                    autofocus
                    @mouseenter="(e) => active = e.target"
                    @mouseleave="() => active = null"
                    @scroll="scrollSync"
                    @keydown.exact.tab="textareaTabMicro"
                    @keydown.ctrl="ctrlSSaving"
                    @paste.prevent="textareaPaste"
                    v-model="topic.notes"
                    class="w-full dark:border-stone-700 bg-white dark:bg-stone-950 sync-scrolling border-l-0 pb-5"
                    ref="textarea"
                    style="height: calc((100vh - 40px)/2);"
                >
                </textarea>

          <div class="bottom-0 w-32 text-center right-0 mr-2 -mt-6" :class="[showBold? 'font-bold': '']">
            <div class="bg-stone-900 rounded opacity-75 -mt-px">
              Resources [{{ topic?.source?.length }}]
            </div>
          </div>
        </div>
        <div v-if="topic.notes" @mouseenter="(e) => active = e.target" @mouseleave="() => active = null" @scroll="scrollSync" class="flex-grow dark:border-stone-700 w-full border-r border-t border-stone-200 overflow-auto w-full flex mx-auto text-left sync-scrolling" ref="markdownInput">
          <Markdown
              :html="true"
              :source="topic.notes"
              class="dark:text-gray-200 mx-auto prose dark:prose-invert"
              style="height: calc((100vh - 40px)/2);"
          />
        </div>
      </div>
      <div class="w-1/2 flex flex-col overflow-y-scroll bg-stone-50 dark:bg-stone-800 gap-2 " ref="research">
        <input @keyup="searchDebounce(() => searchForResearch({ search }), 250)" v-model="search" :placeholder="'Search: ' + topic.name" type="text" class="w-full px-4 py-2 bg-white dark:bg-stone-700 placeholder-stone-300 border-b border-t-0 border-l-0 border-r-0 border-stone-200 dark:border-stone-700">

        <div v-if="!loading" v-for="(item, $i) in []" :key="'research'+$i" class="relative rounded-lg border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-600 px-4 py-2 mx-2 shadow-sm flex items-center space-x-3 hover:border-stone-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-slate-500">
          <div class="flex-shrink-0">
            <img class="h-10 w-10 rounded-full mt-2" :src="item.image" alt="">
          </div>
          <div class="flex-1 min-w-0 relative" v-if="item.image">
            <button
                @click.prevent="() => {
                            if (topic.sources.includes(item.link)) {
                                topic.sources = topic.sources.filter(link => item.link !== link)
                            } else {
                                topic.sources.push(item.link)
                            }
                            saveResearch(topic);
                        }"
                class="w-8 h-8 absolute bottom-0 right-0 z-10 border rounded-full flex items-center justify-center bg-white dark:bg-stone-500 dark:border-stone-400 dark:text-stone-200">
              <PlusIcon v-if="!topic.sources.includes(item.link)" class="w-3 h-3"></PlusIcon>
              <CheckIcon v-else class="w-3 h-3"></CheckIcon>
            </button>

            <a :href="item.link" class="focus:outline-none" target="_blank">
              <span class="absolute inset-0" aria-hidden="true"></span>
              <p class="text-sm font-medium text-stone-900 dark:text-stone-50" v-html="item.title"></p>
              <p class="text-sm text-stone-500 dark:text-stone-300" v-html="item.snippet"></p>
              <a :href="item.link" class="text-sm text-green-500 dark:text-green-300" v-html="item.link"></a>
            </a>
          </div>
          <pre v-else>{{ item }}</pre>
        </div>
        <div v-else class="px-2 py-4 italics">
          Loading...
        </div>

        <div v-if="false" class="px-4 italic text-stone-600 dark:text-stone-300 dark:text-stone-300">
          No search results
        </div>

        <div v-if="0 > 0" class="flex w-full justify-between items-center mb-2 px-2">
          <button
              :disabled="!false"
              :class="[' rounded p-2 border border-stone-200 dark:border-stone-700', false ? 'dark:bg-stone-500 dark:text-stone-100 bg-stone-100 text-stone-500 no-cursor opacity-75' : 'dark:bg-stone-600 dark:text-stone-50 bg-white text-stone-800 underline']"
              @click="searchForResearch({ search, url: $store.getters.research.prev_page_url })"
          >&laquo; Previous</button>
          <button
              :disabled="!false"
              :class="[' rounded p-2 border border-stone-200 dark:border-stone-700', false ? 'dark:bg-stone-500 dark:text-stone-100 bg-stone-100 text-stone-500 no-cursor opacity-75' : 'dark:bg-stone-600 dark:text-stone-50 bg-white text-stone-800 underline']"
              @click="searchForResearch({ search, url: null })"
          >Next &raquo;</button>
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    hello
  </div>

</AppLayout>
</template>

<script setup>
import {PlusIcon, HomeIcon, PencilIcon, ShoppingCartIcon, CheckIcon} from "@heroicons/vue/24/outline";
import 'highlight.js/styles/monokai.css';
import Markdown from 'vue3-markdown-it';
import { ref, onMounted, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const { topic } = defineProps({
  'topic': Object,
})

async function saveResearch(topic) {
  await axios.put('/api/crud/research/'+topic.id, topic)
}

async function searchForResearch(query, debounceTimeout) {

}

function createDebounce() {
  let timeout = null;
  return function (fnc, delayMs) {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      fnc();
    }, delayMs || 100);
  };
}
const searchDebounce = ref(null);
onMounted(() => {
   searchDebounce.value = createDebounce();
});
const active = ref(null);
const textarea = ref(null);
const research = ref(null);
const search = ref('');
const markdownInput = ref(null);
const startOffset = ref(0);
const changes = ref([]);
const showBold = ref(false);
const stack = ref(true);
const loading = ref(true);

const textareaPaste = (e) => {
  e.preventDefault();

  let clipboard = e.clipboardData.getData('text/plain') ?? '';

  if (clipboard.startsWith('https://') || clipboard.startsWith('http://')) {
    const url = new URL(clipboard);
    topic.value = {
      ...topic.value,
      source: [ ... new Set([
              ...(topic?.source ? topic.source : []),
              clipboard
          ])
      ]
    }

    clipboard = '[' + url.hostname+url.pathname + '](' + clipboard + ')';
  }

  const textareaValue = textarea?.value?.value ?? ''

  const { start, end } = getCursorPos();

  textarea.value.value = textareaValue.substring(0, start) + clipboard + textareaValue.substring(end);
  // Minus 1 because the cursor is at the end of the inserted text
  textarea.value.selectionStart = textarea.value.selectionEnd = start + clipboard.length ;
  topic.notes = textareaValue;
};
const scrollSync = () => {
  [textarea.value, markdownInput.value].forEach(element => {
    if (!active) {
      return;
    }

    if (element.isSameNode(active.value)) {
      return;
    }
    element.scrollTop = active.value.scrollTop;
    element.scrollLeft = active.value.scrollLeft;
  });
};
const ctrlSSaving = (keyboardEvent) => {
  if (keyboardEvent.key === 's') {
    keyboardEvent.preventDefault();
    console.log('saving', topic)
    saveResearch(topic)
  }
};
/** @var KeyboardEvent keyboardEvent */
const textareaTabMicro = (keyboardEvent) => {
  if (keyboardEvent.shiftKey || keyboardEvent.keyCode === 9) {
    console.log(keyboardEvent, Object.keys(keyboardEvent))
    return;
  }
  keyboardEvent.preventDefault();

  const { start, end } = getCursorPos();
  textarea.value = textarea.value?.value?.substring(0, start) + "    " + textarea.value?.value?.substring(end);
  textarea.selectionStart = textarea.value.selectionEnd = start + 4;
};
const getCursorPos = () => {
  const textareaInput = textarea.value

  if ("selectionStart" in textareaInput && document.activeElement == textareaInput) {
    return {
      start: textareaInput.selectionStart,
      end: textareaInput.selectionEnd
    };
  }
  else if (textareaInput.createTextRange) {
    var sel = document.selection.createRange();
    if (sel.parentElement() === textareaInput) {
      var rng = textareaInput.createTextRange();
      rng.moveToBookmark(sel.getBookmark());
      for (var len = 0;
           rng.compareEndPoints("EndToStart", rng) > 0;
           rng.moveEnd("character", -1)) {
        len++;
      }
      rng.setEndPoint("StartToStart", textareaInput.createTextRange());
      for (var pos = { start: 0, end: len };
           rng.compareEndPoints("EndToStart", rng) > 0;
           rng.moveEnd("character", -1)) {
        pos.start++;
        pos.end++;
      }
      return pos;
    }
  }
  return -1;
}

const markdownWithLinks = computed(() => {
  return markdown;
})

</script>
