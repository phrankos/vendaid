<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
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
    <AppShell :variant="variant">
        <AppHeader/>
        <AppContent :variant="variant">
            <slot />
        </AppContent>
    </AppShell>
</template>
