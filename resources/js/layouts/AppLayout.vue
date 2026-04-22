<script setup lang="ts">
// import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    variant?: 'header' | 'sidebar' | 'full';
    class?: string;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :variant="variant" :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
