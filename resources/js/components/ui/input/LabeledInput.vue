<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Label from '../label/Label.vue';

// label, id, type, required, autofocus, tabindex, autocomplete, v-model
interface Props {
    disabled?: boolean;
    label: string;
    id: string;
    // value: string;
    type: string;
    required?: boolean;
    autofocus?: boolean;
    tabindex: number;
    autocomplete?: boolean;
    maxlength?: number;
    min?: number;
    numeric?: boolean;
}
const model:any = defineModel();
const props = defineProps<Props>();

const maxlen=props.maxlength;
const minNum=props.min;

const handleNumericInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    // Remove all non-digit characters
    let value = input.value.replace(/\D/g, '');
    
    // Apply maxlength if specified
    if (props.maxlength) {
        value = value.slice(0, props.maxlength);
    }
    
    // Update the input value and model
    input.value = value;
    model.value = value;
};

</script>

<template>
        <div class="grid gap-2 relative">
            <Input
                v-if="numeric"
                class=" h-14 block px-2.5 pb-2 pt-4 w-full text-base rounded-lg border border-gray-300 
                appearance-none focus:outline-none focus:ring-0 focus:border-primary peer"
                @keyup="handleNumericInput"
                :disabled=disabled :required=required :autofocus=autofocus :autocomplete="autocomplete"
                :id="id" v-model="model"
                :maxlength="maxlength"
                :min="minNum"
                :type="type"
                :tabindex="tabindex"
                placeholder=""
            />
            <Input
                v-else
                class=" h-14 block px-2.5 pb-2 pt-4 w-full text-base rounded-lg border border-gray-300 
                appearance-none focus:outline-none focus:ring-0 focus:border-primary peer"
                :disabled=disabled :required=required :autofocus=autofocus :autocomplete="autocomplete"
                :id="id"
                v-model="model"
                :maxlength="maxlength"
                :type="type"
                :tabindex="tabindex"
                placeholder=""
            />
            <label 
                :for="id" 
                class=" select-none font-medium cursor-text absolute text-base text-gray-500 duration-200 transform 
                -translate-y-4 scale-80 top-4 origin-[0] px-2 peer-focus:px-2 
                peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 
                peer-focus:scale-80 peer-focus:-translate-y-4 left-1 peer-focus:font-semibold pointer-events-none"
            >
                {{ label }}
            </label>
        </div>
</template>