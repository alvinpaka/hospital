<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const dashboardUrl = computed(() => {
    if (user.value?.role === 'admin') return '/admin/dashboard';
    if (['doctor', 'nurse'].includes(user.value?.role)) return '/staff/dashboard';
    return '/dashboard';
});
</script>

<template>
    <Link :href="dashboardUrl">
        <div class="flex items-center">
            <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
            </div>
            <span class="ml-2 truncate leading-tight font-semibold text-sm">Medix Hospital System</span>
        </div>
    </Link>
</template>
