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
import { computed, onMounted, ref } from 'vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale);

const props = defineProps<{ data: Array<any> }>();
const isDark = ref(false);

onMounted(() => {
  // Check if dark mode is active
  isDark.value = document.documentElement.classList.contains('dark');
  
  // Listen for theme changes
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        isDark.value = document.documentElement.classList.contains('dark');
      }
    });
  });
  
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  });
});

const getThemeColors = () => {
  const style = getComputedStyle(document.documentElement);
  return {
    text: style.getPropertyValue('--foreground').trim(),
    muted: style.getPropertyValue('--muted-foreground').trim(),
    border: style.getPropertyValue('--border').trim(),
    chart1: style.getPropertyValue('--chart-1').trim(),
    chart2: style.getPropertyValue('--chart-2').trim(),
    chart3: style.getPropertyValue('--chart-3').trim(),
    chart4: style.getPropertyValue('--chart-4').trim(),
    chart5: style.getPropertyValue('--chart-5').trim(),
  };
};

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
  const colors = getThemeColors();
  const labels = numericResults.value.map(r => r.date ? new Date(r.date).toLocaleDateString() : '');
  return {
    labels,
    datasets: [
      {
        label: 'Lab Result',
        data: numericResults.value.map(r => r.numeric),
        borderColor: colors.chart1,
        backgroundColor: `${colors.chart1}20`,
        tension: 0.4,
      },
    ],
  };
});

const chartOptions = computed(() => {
  const colors = getThemeColors();
  
  return {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          font: { size: 12 },
          color: colors.muted,
        },
      },
      title: {
        display: true,
        text: 'Lab Results Over Time',
        font: { size: 16 },
        color: colors.text,
      },
      tooltip: {
        enabled: true,
        mode: 'index',
        intersect: false,
        backgroundColor: isDark.value ? 'rgba(0,0,0,0.9)' : 'rgba(255,255,255,0.95)',
        titleColor: isDark.value ? '#ffffff' : '#000000',
        bodyColor: isDark.value ? '#ffffff' : '#000000',
        borderColor: colors.chart1,
        borderWidth: 1,
        callbacks: {
          label: function(context: any) {
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
          color: colors.muted,
        },
        ticks: { color: colors.muted },
        grid: {
          color: `${colors.border}40`,
          drawBorder: false,
        },
      },
      y: {
        beginAtZero: false,
        title: {
          display: true,
          text: 'Value',
          font: { size: 12 },
          color: colors.muted,
        },
        ticks: { color: colors.muted },
        grid: {
          color: `${colors.border}40`,
          drawBorder: false,
        },
      },
    },
    elements: {
      point: {
        radius: 4,
        hoverRadius: 6,
        backgroundColor: isDark.value ? '#1f2937' : '#ffffff',
        borderWidth: 2,
      },
      line: {
        borderWidth: 3,
      },
    },
  };
});
</script>

<template>
  <Line :data="chartData" :options="chartOptions" />
  <div v-if="numericResults.length === 0" class="text-xs text-muted-foreground mt-2">No numeric lab results to plot.</div>
</template> 