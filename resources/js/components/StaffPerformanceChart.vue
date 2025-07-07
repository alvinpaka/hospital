<template>
    <div class="w-full h-48">
        <svg class="w-full h-full" viewBox="0 0 400 200">
            <g v-for="(bar, index) in bars" :key="index">
                <rect :x="bar.x" :y="bar.y" :width="bar.width" :height="bar.height" 
                      :fill="bar.color" rx="2" />
                <text :x="bar.x + bar.width/2" :y="bar.y - 5" 
                      text-anchor="middle" font-size="10" fill="#6b7280">
                    {{ bar.label }}
                </text>
                <text :x="bar.x + bar.width/2" :y="bar.y + bar.height/2 + 4" 
                      text-anchor="middle" font-size="12" fill="white" font-weight="bold">
                    {{ bar.value }}
                </text>
            </g>
        </svg>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    data?: {
        doctors?: number;
        nurses?: number;
        admins?: number;
        activeDoctors?: number;
    };
}

const props = defineProps<Props>();

const bars = computed(() => {
    if (!props.data) return [];
    const maxValue = Math.max(
        props.data.doctors || 0, 
        props.data.nurses || 0, 
        props.data.admins || 0, 
        props.data.activeDoctors || 0
    );
    const width = 400;
    const height = 200;
    const padding = 60;
    const barWidth = (width - 2 * padding) / 4;
    
    return [
        {
            x: padding,
            y: height - padding - ((props.data.doctors || 0) / maxValue) * (height - 2 * padding),
            width: barWidth - 10,
            height: ((props.data.doctors || 0) / maxValue) * (height - 2 * padding),
            color: '#3b82f6',
            label: 'Doctors',
            value: props.data.doctors || 0
        },
        {
            x: padding + barWidth,
            y: height - padding - ((props.data.nurses || 0) / maxValue) * (height - 2 * padding),
            width: barWidth - 10,
            height: ((props.data.nurses || 0) / maxValue) * (height - 2 * padding),
            color: '#8b5cf6',
            label: 'Nurses',
            value: props.data.nurses || 0
        },
        {
            x: padding + barWidth * 2,
            y: height - padding - ((props.data.admins || 0) / maxValue) * (height - 2 * padding),
            width: barWidth - 10,
            height: ((props.data.admins || 0) / maxValue) * (height - 2 * padding),
            color: '#f59e0b',
            label: 'Admins',
            value: props.data.admins || 0
        },
        {
            x: padding + barWidth * 3,
            y: height - padding - ((props.data.activeDoctors || 0) / maxValue) * (height - 2 * padding),
            width: barWidth - 10,
            height: ((props.data.activeDoctors || 0) / maxValue) * (height - 2 * padding),
            color: '#10b981',
            label: 'Active',
            value: props.data.activeDoctors || 0
        }
    ];
});
</script> 