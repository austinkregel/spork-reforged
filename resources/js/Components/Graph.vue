<template>
  <component
      :is="Line"
      :data="{ labels, datasets }"
      :options="options"
      class="h-64"
      style="max-height:200px;"
  ></component>
</template>
<script setup>
import { Line } from 'vue-chartjs';
import {onMounted, ref} from "vue";
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const {
  labels,
  datasets
} = defineProps(['labels', 'datasets']);

const options = ref({});

onMounted(() => {

});
</script>