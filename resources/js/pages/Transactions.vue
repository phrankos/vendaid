<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: 'transactions',
    },
];

const props = defineProps({
    data : {
      type: Object,
      required: true,
    },
    headers : {
      type: Object,
      required: true,
    },
    dropdownOptions : {
      type: Object,
      required: true,
    },
});
const data = ref(props.data);
const headers = ref(props.headers);
const dropdownOptions = props.dropdownOptions;

const ROUTE = 'transactions'
const ROUTE_DESTROY = 'transactions.destroy'
const ROWNAME = 'Transactions';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });


const form = useForm({
    id: 0,
    name: '',
    amount_left: 1
});

</script>

<template>
    <Head title="Medicines" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable :form="form" :displayOnly=true
        :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" 
        :headers="headers" :dropdownOptions="dropdownOptions">
        </CustomCRUDTable>
    </AppLayout>
</template>
