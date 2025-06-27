<template>
  <textarea
    v-bind="$attrs"
    :class="[
      'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
      $attrs.class
    ]"
    v-model="modelValueProxy"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
});
const emit = defineEmits(['update:modelValue']);

const modelValueProxy = computed({
  get: () => props.modelValue,
  set: (val: string) => emit('update:modelValue', val),
});
</script> 