<template>
    <div class="w-full h-64">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

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
  Filler,
} from 'chart.js';
import { computed, onMounted, ref } from 'vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler);

interface Props {
    data?: Array<{
        month: string;
        revenue: number;
        bills: number;
        paid: number;
        collectionRate: number;
    }>;
}

const props = defineProps<Props>();
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
    if (!props.data || props.data.length === 0) {
        return {
            labels: [],
            datasets: []
        };
    }

    const colors = getThemeColors();
    const labels = props.data.map(item => item.month);
    
    return {
        labels,
        datasets: [
            {
                label: 'Revenue (UGX)',
                data: props.data.map(item => item.revenue),
                borderColor: colors.chart1,
                backgroundColor: colors.chart1,
                tension: 0.4,
                fill: false,
                pointBackgroundColor: colors.chart1,
                pointBorderColor: colors.chart1,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
                yAxisID: 'y',
            },
            {
                label: 'Total Bills',
                data: props.data.map(item => item.bills),
                borderColor: colors.chart2,
                backgroundColor: colors.chart2,
                tension: 0.4,
                fill: false,
                pointBackgroundColor: colors.chart2,
                pointBorderColor: colors.chart2,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
                yAxisID: 'y1',
            },
            {
                label: 'Paid Bills',
                data: props.data.map(item => item.paid),
                borderColor: colors.chart3,
                backgroundColor: colors.chart3,
                tension: 0.4,
                fill: false,
                pointBackgroundColor: colors.chart3,
                pointBorderColor: colors.chart3,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
                yAxisID: 'y1',
            },
            {
                label: 'Collection Rate (%)',
                data: props.data.map(item => item.collectionRate),
                borderColor: colors.chart4,
                backgroundColor: colors.chart4,
                tension: 0.4,
                fill: false,
                pointBackgroundColor: colors.chart4,
                pointBorderColor: colors.chart4,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
                yAxisID: 'y2',
            },
        ],
    };
});

const chartOptions = computed(() => {
    const colors = getThemeColors();
    
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: { size: 12 },
                    color: colors.muted,
                    usePointStyle: true,
                    padding: 20,
                },
            },
            title: {
                display: true,
                text: 'Billing Analytics Trends (6 Months)',
                font: { size: 16, weight: 'bold' },
                color: colors.text,
                padding: { top: 10, bottom: 20 },
            },
            tooltip: {
                enabled: true,
                mode: 'index',
                intersect: false,
                backgroundColor: isDark.value ? 'rgba(0,0,0,0.9)' : 'rgba(255,255,255,0.95)',
                titleColor: isDark.value ? '#ffffff' : colors.text,
                bodyColor: isDark.value ? '#ffffff' : colors.text,
                borderColor: colors.chart1,
                borderWidth: 1,
                callbacks: {
                    label: function(context: any) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) {
                            if (label.includes('Revenue')) {
                                label += `UGX ${context.parsed.y.toLocaleString()}`;
                            } else if (label.includes('Rate')) {
                                label += `${context.parsed.y}%`;
                            } else {
                                label += context.parsed.y;
                            }
                        }
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
                    text: 'Month',
                    font: { size: 12, weight: 'bold' },
                    color: colors.muted,
                },
                ticks: { 
                    color: colors.muted,
                    font: { size: 11 },
                },
                grid: {
                    color: `${colors.border}40`,
                    drawBorder: false,
                },
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (UGX)',
                    font: { size: 12, weight: 'bold' },
                    color: colors.muted,
                },
                ticks: { 
                    color: colors.muted,
                    font: { size: 11 },
                    callback: function(value: any) {
                        return `UGX ${value.toLocaleString()}`;
                    },
                },
                grid: {
                    color: `${colors.border}40`,
                    drawBorder: false,
                },
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Bill Count',
                    font: { size: 12, weight: 'bold' },
                    color: colors.muted,
                },
                ticks: { 
                    color: colors.muted,
                    font: { size: 11 },
                },
                grid: {
                    drawOnChartArea: false,
                },
            },
            y2: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Collection Rate (%)',
                    font: { size: 12, weight: 'bold' },
                    color: colors.muted,
                },
                ticks: { 
                    color: colors.muted,
                    font: { size: 11 },
                    callback: function(value: any) {
                        return `${value}%`;
                    },
                },
                grid: {
                    drawOnChartArea: false,
                },
                min: 0,
                max: 100,
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