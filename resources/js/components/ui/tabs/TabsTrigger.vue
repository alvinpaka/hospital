<template>
  <button
    :class="[
      'px-4 py-2 text-sm font-medium focus:outline-none',
      isActive ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-blue-600'
    ]"
    role="tab"
    :aria-selected="isActive"
    @click="setActiveTab(value)"
    v-bind="$attrs"
  >
    <slot />
  </button>
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
const setActiveTab = inject('setActiveTab') as (val: string) => void;

const isActive = computed(() => activeTab?.value === props.value);
</script> 