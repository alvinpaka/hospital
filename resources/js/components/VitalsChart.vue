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

const chartData = computed(() => {
  const colors = getThemeColors();
  const labels = props.data.map(v => v.recorded_at ? new Date(v.recorded_at).toLocaleDateString() : '');
  return {
    labels,
    datasets: [
      {
        label: 'Temperature (°C)',
        data: props.data.map(v => v.temperature),
        borderColor: colors.chart1,
        backgroundColor: `${colors.chart1}20`,
        tension: 0.4,
      },
      {
        label: 'Heart Rate',
        data: props.data.map(v => v.heart_rate),
        borderColor: colors.chart2,
        backgroundColor: `${colors.chart2}20`,
        tension: 0.4,
      },
      {
        label: 'Systolic BP',
        data: props.data.map(v => v.blood_pressure ? parseInt(v.blood_pressure.split('/')[0]) : null),
        borderColor: colors.chart3,
        backgroundColor: `${colors.chart3}20`,
        tension: 0.4,
      },
      {
        label: 'Diastolic BP',
        data: props.data.map(v => v.blood_pressure ? parseInt(v.blood_pressure.split('/')[1]) : null),
        borderColor: colors.chart4,
        backgroundColor: `${colors.chart4}20`,
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
        text: 'Vitals Over Time',
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
</template> 