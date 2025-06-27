<template>
  <span :class="badgeClass">
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (v: string) => ['default', 'secondary', 'destructive', 'outline'].includes(v),
  },
});

const badgeClass = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800';
    case 'destructive':
      return 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700';
    case 'outline':
      return 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border border-gray-300 text-gray-800';
    default:
      return 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700';
  }
});
</script> 