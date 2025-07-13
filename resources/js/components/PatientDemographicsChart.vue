<template>
    <div class="w-full h-48">
        <svg class="w-full h-full" viewBox="0 0 300 200">
            <g v-for="(slice, index) in pieSlices" :key="index">
                <path :d="slice.path" :fill="slice.color" />
                <text :x="slice.labelX" :y="slice.labelY" 
                      text-anchor="middle" font-size="12" :fill="textColor">
                    {{ slice.percentage }}%
                </text>
            </g>
        </svg>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';

interface Props {
    data?: {
        total?: number;
        verified?: number;
        unverified?: number;
        newThisMonth?: number;
    };
}

const props = defineProps<Props>();

const isDark = ref(false);

function updateTheme() {
    isDark.value = document.documentElement.classList.contains('dark');
}

let observer: MutationObserver | null = null;

onMounted(() => {
    updateTheme();
    observer = new MutationObserver(() => updateTheme());
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
onUnmounted(() => {
    if (observer) observer.disconnect();
});

const textColor = computed(() => (isDark.value ? '#fff' : '#222'));

const pieSlices = computed(() => {
    if (!props.data) return [];
    const total = props.data.total || 1;
    const verified = props.data.verified || 0;
    const unverified = props.data.unverified || 0;
    const newThisMonth = props.data.newThisMonth || 0;
    
    const centerX = 150;
    const centerY = 100;
    const radius = 60;
    
    let currentAngle = 0;
    const slices = [];
    
    // Verified patients
    const verifiedAngle = (verified / total) * 2 * Math.PI;
    slices.push({
        path: createPieSlice(centerX, centerY, radius, currentAngle, currentAngle + verifiedAngle),
        color: '#10b981',
        percentage: Math.round((verified / total) * 100),
        labelX: centerX + Math.cos(currentAngle + verifiedAngle / 2) * (radius * 0.7),
        labelY: centerY + Math.sin(currentAngle + verifiedAngle / 2) * (radius * 0.7) + 4
    });
    currentAngle += verifiedAngle;
    
    // Unverified patients
    const unverifiedAngle = (unverified / total) * 2 * Math.PI;
    slices.push({
        path: createPieSlice(centerX, centerY, radius, currentAngle, currentAngle + unverifiedAngle),
        color: '#f59e0b',
        percentage: Math.round((unverified / total) * 100),
        labelX: centerX + Math.cos(currentAngle + unverifiedAngle / 2) * (radius * 0.7),
        labelY: centerY + Math.sin(currentAngle + unverifiedAngle / 2) * (radius * 0.7) + 4
    });
    currentAngle += unverifiedAngle;
    
    // New this month
    const newAngle = (newThisMonth / total) * 2 * Math.PI;
    slices.push({
        path: createPieSlice(centerX, centerY, radius, currentAngle, currentAngle + newAngle),
        color: '#8b5cf6',
        percentage: Math.round((newThisMonth / total) * 100),
        labelX: centerX + Math.cos(currentAngle + newAngle / 2) * (radius * 0.7),
        labelY: centerY + Math.sin(currentAngle + newAngle / 2) * (radius * 0.7) + 4
    });
    
    return slices;
});

function createPieSlice(cx: number, cy: number, radius: number, startAngle: number, endAngle: number): string {
    const x1 = cx + radius * Math.cos(startAngle);
    const y1 = cy + radius * Math.sin(startAngle);
    const x2 = cx + radius * Math.cos(endAngle);
    const y2 = cy + radius * Math.sin(endAngle);
    
    const largeArcFlag = endAngle - startAngle > Math.PI ? 1 : 0;
    
    return `M ${cx} ${cy} L ${x1} ${y1} A ${radius} ${radius} 0 ${largeArcFlag} 1 ${x2} ${y2} Z`;
}
</script> 