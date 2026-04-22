<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import Heading from '@/components/Heading.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Heading class=" self-center mt-8" :title="breadcrumbs[0].title" :description="breadcrumbs[0].href"></Heading>
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div v-for="index in Array(36)" :key="index" class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern :patterncolor="Math.floor(Math.random()*4)" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
