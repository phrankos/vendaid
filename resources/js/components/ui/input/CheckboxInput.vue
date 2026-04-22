<script setup lang="ts">
interface Props {
    disabled?: boolean;
    label: string;
    id: string;
    autofocus?: boolean;
    tabindex?: number;
    checked?: boolean;
}

const model = defineModel<boolean>();
const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    autofocus: false,
    tabindex: 0,
    checked: false
});
</script>

<template>
    <div class="relative flex items-center">
        <div class="flex items-center">
            <input
                type="checkbox"
                :id="id"
                v-model="model"
                :disabled="disabled"
                :autofocus="autofocus"
                :tabindex="tabindex"
                class="w-4 h-4 text-primary accent-accent rounded color
                       disabled:opacity-50 disabled:cursor-not-allowed
                       transition-all duration-200"
            />
        </div>
        <div class="ml-3 text-base">
            <label 
                :for="id"
                class="select-none font-medium text-gray-700 peer-focus:text-primary
                       disabled:text-gray-400 cursor-pointer"
                :class="{ 'text-gray-400 cursor-not-allowed': disabled }"
            >
                {{ label }}
            </label>
        </div>
    </div>
</template>