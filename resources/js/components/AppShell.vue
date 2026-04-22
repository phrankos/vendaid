<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';

interface Props {
    variant?: 'header' | 'sidebar' | 'full';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'full'" class="flex h-screen w-screen flex-col">
        <slot />
    </div>
    <SidebarProvider v-else-if="variant === 'sidebar'" :default-open="isOpen">
        <slot />
    </SidebarProvider>
    <div v-else class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
</template>
