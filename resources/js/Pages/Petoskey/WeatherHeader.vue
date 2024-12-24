<script setup>
import dayjs from 'dayjs';
import { ref, onBeforeMount, onUnmounted } from 'vue';

defineProps({
    weather: Object,
    now: Object
})

</script>

<template>
    <div id="weather" v-if="weather" class="flex flex-wrap items-center gap-4 my-0 md:my-4 lg:my-6 w-full dark:text-white py-2">
        <div class="w-24 md:w-32 lg:w-64 justify-center items-center flex flex-col py-0">
            <div class="flex align-top items-center">
                <span id="temp" class="text-4xl lg:text-7xl ">{{weather.temperature}}</span>
                <span class="text-lg pb-4 pt-0 lg:pb-8 2 pl-2 whitespace-nowrap">&deg; f</span>
            </div>
        </div>
        <div class="h-16 w-px border-l"></div>
        <div class="text-sm md:text-base py-0 sm:pl-4 md:pl-8 lg:pl-10">
            {{now.format('dddd, MMMM D, YYYY')}} &mdash; {{now.format('h:mm a')}}<br/>
            <span class="text-xs md:text-sm flex flex-wrap items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                         aria-hidden="true" data-darkreader-inline-fill="">
                      <path clip-rule="evenodd" fill-rule="evenodd"
                            d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z"></path>
                    </svg>
                    {{ weather.address }}
                </span>
                <span class="hidden md:block md:text-sm">
                    {{ weather.condition }}
                </span>
        </div>
        <div class="text-3xl md:text-5xl xl:text-7xl px-8 text-right flex-grow">
            <div class="flex flex-col gap-2 items-end">
                <!-- Emoji based on weather -->
              <img v-if="weather.condition_url" :src="weather.condition_url" :alt="weather.condition"  class="w-24"/>
              <span v-else>{{ weather.condition_image }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
