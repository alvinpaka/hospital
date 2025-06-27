<script setup lang="ts">
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale);

// defineProps<{ data: Array<any> }>();

const props = defineProps<{ data: Array<any> }>();

// For demo, plot only numeric results (e.g., glucose, etc.)
const numericResults = computed(() =>
  props.data
    .map(r => ({
      ...r,
      numeric: !isNaN(parseFloat(r.result)) ? parseFloat(r.result) : null,
    }))
    .filter(r => r.numeric !== null)
);

const chartData = computed(() => {
  const labels = numericResults.value.map(r => r.date ? new Date(r.date).toLocaleDateString() : '');
  return {
    labels,
    datasets: [
      {
        label: 'Lab Result',
        data: numericResults.value.map(r => r.numeric),
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99,102,241,0.1)',
        tension: 0.4,
      },
    ],
  };
});

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        font: { size: 12 },
        color: '#6b7280',
      },
    },
    title: {
      display: true,
      text: 'Lab Results Over Time',
      font: { size: 16 },
      color: '#111827',
    },
    tooltip: {
      enabled: true,
      mode: 'index',
      intersect: false,
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) label += ': ';
          if (context.parsed.y !== null) label += context.parsed.y;
          return label;
        },
      },
    },
  },
  interaction: {
    mode: 'nearest',
    axis: 'x',
    intersect: false,
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Date',
        font: { size: 12 },
        color: '#6b7280',
      },
      ticks: { color: '#6b7280' },
    },
    y: {
      beginAtZero: false,
      title: {
        display: true,
        text: 'Value',
        font: { size: 12 },
        color: '#6b7280',
      },
      ticks: { color: '#6b7280' },
    },
  },
};
</script>

<template>
  <Line :data="chartData" :options="chartOptions" />
  <div v-if="numericResults.length === 0" class="text-xs text-muted-foreground mt-2">No numeric lab results to plot.</div>
</template> 