<script setup lang="ts">
import ColumnDropdown from './ColumnDropdown.vue';
import Button from '@/components/ui/button/Button.vue';

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
    openDialog : {
      type: Function,
      required: true,
    },
    displayOnly: {
      type: Boolean,
      required: false
    }
});
const headers = props.headers;
const columnOrder = props.columnOrder;
const visibleColumnsMap = props.visibleColumnsMap;
</script>

<template>
    <div class="flex flex-row p-4 justify-between sticky top-0 left-0 gap-x-4 z-1 bg-background">
        <ColumnDropdown :headers="headers" :visible-columns-map="visibleColumnsMap" :column-order="columnOrder" />
        <Button v-if="!props.displayOnly"
            @click="openDialog(null,'create')"
            variant="accent"
            size="icon"
            class=" select-none cursor-pointer relative size-10 w-auto p-1 px-2 focus-within:ring-2 focus-within:ring-accent-hover"
        >
            Create
        </Button>
    </div>
</template>