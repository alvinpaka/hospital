<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="flex w-full items-center justify-between px-4 md:max-w-7xl mx-auto">
        <div class="flex-1">
            <div v-if="props.breadcrumbs.length > 1" class="flex border-b border-sidebar-border/70">
                <div class="flex h-12 w-full items-center justify-start text-neutral-500">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
            </div>
        </div>
    </div>
</template>
