<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        :class="className"
        v-bind="$attrs"
        width="220"
        height="160"
        viewBox="0 0 320 240"
        fill="none"
    >
        <rect x="40" y="80" width="240" height="120" rx="16" fill="#fff" stroke="#1976d2" stroke-width="3"/>
        <rect x="80" y="120" width="40" height="80" rx="8" fill="#e3f2fd" stroke="#1976d2" stroke-width="2"/>
        <rect x="200" y="120" width="40" height="80" rx="8" fill="#e3f2fd" stroke="#1976d2" stroke-width="2"/>
        <rect x="140" y="100" width="40" height="100" rx="8" fill="#bbdefb" stroke="#1976d2" stroke-width="2"/>
        <rect x="150" y="140" width="20" height="40" rx="4" fill="#fff" stroke="#1976d2" stroke-width="2"/>
        <rect x="155" y="155" width="10" height="10" rx="2" fill="#1976d2"/>
        <rect x="120" y="90" width="80" height="20" rx="6" fill="#1976d2"/>
        <rect x="145" y="95" width="30" height="10" rx="2" fill="#fff"/>
        <circle cx="160" cy="60" r="24" fill="#fff" stroke="#1976d2" stroke-width="3"/>
        <rect x="152" y="52" width="16" height="16" rx="4" fill="#1976d2"/>
        <rect x="156" y="56" width="8" height="8" rx="2" fill="#fff"/>
        <rect x="158" y="68" width="4" height="8" rx="2" fill="#1976d2"/>
    </svg>
</template>
