<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Calendar, X } from 'lucide-vue-next';

interface Props {
    disabled?: boolean;
    label: string;
    id: string;
    required?: boolean;
    autofocus?: boolean;
    tabindex?: number;
    autocomplete?: boolean;
    min?: string;
    max?: string;
    placeholder?: string;
}

const model = defineModel<string>();
const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    required: false,
    autofocus: false,
    tabindex: 0,
    autocomplete: false,
    min: undefined,
    max: undefined,
    placeholder: ''
});

const handleDateChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    model.value = input.value;
};
</script>

<template>
    <div class="grid gap-2 relative">
        <div class="relative">
            <Input
                type="date"
                :id="id"
                v-model="model"
                :disabled="disabled"
                :required="required"
                :autofocus="autofocus"
                :autocomplete="autocomplete ? 'on' : 'off'"
                :tabindex="tabindex"
                :min="min"
                :max="max"
                :placeholder="placeholder"
                class="h-14 block px-2.5 pb-2 pt-4 w-full text-base rounded-lg border border-gray-300 
                appearance-none focus:outline-none focus:ring-0 focus:border-primary peer
                [&::-webkit-calendar-picker-indicator]:opacity-0
                [&::-webkit-calendar-picker-indicator]:absolute
                [&::-webkit-calendar-picker-indicator]:inset-0
                [&::-webkit-calendar-picker-indicator]:w-full
                [&::-webkit-calendar-picker-indicator]:h-full
                [&::-webkit-calendar-picker-indicator]:cursor-pointer"
            />
            
            <!-- Clear button (shown when a date is set and field is not required) -->
            <button
                v-if="!required && model"
                type="button"
                @click="model = ''"
                class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                tabindex="-1"
            >
                <X :size="16" />
            </button>

            <!-- Calendar Icon -->
            <Calendar
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                :size="20"
            />
            
            <label 
                :for="id" 
                class="select-none font-medium cursor-text absolute text-base text-gray-500 duration-200 transform 
                -translate-y-4 scale-80 top-4 origin-[0] px-2 peer-focus:px-2 
                peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 
                peer-focus:scale-80 peer-focus:-translate-y-4 left-1 peer-focus:font-semibold pointer-events-none
                peer-[&:not(:placeholder-shown)]:scale-80 peer-[&:not(:placeholder-shown)]:-translate-y-4"
            >
                {{ label }}
            </label>
        </div>
    </div>
</template>

<!-- <style scoped>
/* Hide the default date picker icon in different browsers while keeping functionality */
input[type="date"]::-webkit-inner-spin-button,
input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0;
    position: absolute;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

/* Firefox */
input[type="date"] {
    -moz-appearance: textfield;
}

/* For browsers that don't support the pseudo-class approach */
input[type="date"]:not(:placeholder-shown) {
    color: inherit;
}

/* Style the date input value when empty */
input[type="date"]:invalid {
    color: transparent;
}

input[type="date"]:valid {
    color: inherit;
}
</style> -->