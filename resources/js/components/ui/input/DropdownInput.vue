<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';
    import { Head } from '@inertiajs/vue3';
    import InputError from '@/components/InputError.vue';
    import { Input } from '@/components/ui/input';
    import Label from '../label/Label.vue';
    import { computed } from 'vue';

    // label, id, type, required, autofocus, tabindex, autocomplete, v-model
    const props = defineProps({
        disabled: Boolean,
        label: String,
        id: String,
        column: {
            type: String,
            default: "",
        },
        required: Boolean,
        autofocus: Boolean,
        tabindex: Number,
        // vmodel: string | number | null;
        options: {
            type: Object,
            default: () => ({}),
        },
    });
    const model:any = defineModel();
    const options = props.options;
    const column = props.column;
    // currentRow.value = data.value.find((row: { id: number; })=>row.id===id);
    const hasValue = computed(() => {
        return options.find((row: { id: number; })=>row.id===model.value)[column] !== null && options.find((row: { id: number; })=>row.id===model.value)[column] !== undefined && options.find((row: { id: number; })=>row.id===model.value)[column] !== '';
    });
</script>

<template>
    <div class="relative">
        <select
            class=" cursor-pointer hover:bg-primary/5 h-14 block px-2.5 pb-2 pt-4 w-full text-base rounded-lg border border-gray-300 
            appearance-none focus:outline-none focus:ring-0 focus:border-primary peer select-none disabled:opacity-50"
            :disabled=disabled
            :id="id"
            :required="required"
            :autofocus="autofocus"
            :tabindex="tabindex"
            v-model="model" 
        >
            <!-- <option value="">Select Gender</option> -->
            <option 
                v-for="option in options" 
                :key="option['id']" 
                :value="option['id']"
            >
                {{ option[column] }}
            </option>
        </select>
        <label 
            :for="id" 
            class=" font-medium cursor-text absolute text-base text-gray-500 duration-200 transform 
            -translate-y-4 scale-80 top-4 origin-[0] px-2 peer-focus:px-2 
            peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 
            left-1 pointer-events-none select-none"
            :class="{
                'scale-100 translate-y-0': !hasValue,
                'scale-80 -translate-y-4 peer-focus:font-semibold': hasValue
            }"
        >
            {{ label }}
            <!-- <span v-if="required" class="text-primary">*</span> -->
        </label>
    </div>
</template>