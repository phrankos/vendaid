<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const props = defineProps({
    headers : {
      type: Object,
      required: true,
    },
    columnOrder : {
      type: Object,
      required: true,
    },
    visibleColumnsMap : {
      type: Object,
      required: true,
    },
});
const headers = props.headers;
const columnOrder = props.columnOrder;
const visibleColumnsMap = props.visibleColumnsMap;
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger>
            <Button
                variant="default"
                size="icon"
                class="select-none cursor-pointer size-10 w-auto p-1 px-2 focus-within:ring-2 focus-within:ring-primary"
            >
                Columns
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent >
            <label v-for="column in columnOrder" :key="column" class="flex items-center gap-1 select-none">
                <input type="checkbox" v-model="visibleColumnsMap[column]" class=" peer"/>
                <div class=" text-foreground/60  line-through peer-checked:text-foreground/80 peer-checked:no-underline">
                    {{ headers[column as keyof typeof headers] }}
                </div>
            </label>
        </DropdownMenuContent>
    </DropdownMenu>
</template>