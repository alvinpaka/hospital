<template>
  <div v-if="isActive" class="pt-2" role="tabpanel">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';

const props = defineProps({
  value: {
    type: String,
    required: true,
  },
});
const activeTab = inject('activeTab');
const isActive = computed(() => activeTab?.value === props.value);
</script> 