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

const props = defineProps<{ data: Array<any> }>();

const chartData = computed(() => {
  const labels = props.data.map(v => v.recorded_at ? new Date(v.recorded_at).toLocaleDateString() : '');
  return {
    labels,
    datasets: [
      {
        label: 'Temperature (°C)',
        data: props.data.map(v => v.temperature),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,0.1)',
        tension: 0.4,
      },
      {
        label: 'Heart Rate',
        data: props.data.map(v => v.heart_rate),
        borderColor: '#f59e42',
        backgroundColor: 'rgba(245,158,66,0.1)',
        tension: 0.4,
      },
      {
        label: 'Systolic BP',
        data: props.data.map(v => v.blood_pressure ? parseInt(v.blood_pressure.split('/')[0]) : null),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16,185,129,0.1)',
        tension: 0.4,
      },
      {
        label: 'Diastolic BP',
        data: props.data.map(v => v.blood_pressure ? parseInt(v.blood_pressure.split('/')[1]) : null),
        borderColor: '#ef4444',
        backgroundColor: 'rgba(239,68,68,0.1)',
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
      text: 'Vitals Over Time',
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
</template> 