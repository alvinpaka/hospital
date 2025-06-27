<template>
  <div>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
});
const emit = defineEmits(['update:modelValue']);

const activeTab = ref(props.modelValue);

provide('activeTab', activeTab);
provide('setActiveTab', (val: string) => {
  activeTab.value = val;
  emit('update:modelValue', val);
});
</script> 