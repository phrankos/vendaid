<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medicines',
        href: 'medicines',
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

const ROUTE = 'medicines'
const ROUTE_DESTROY = 'medicines.destroy'
const ROWNAME = 'Medicine';

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
        <CustomCRUDTable :form="form" 
        :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" 
        :headers="headers" :dropdownOptions="dropdownOptions">
            <div class="grid auto-rows-min gap-4 lg:md:grid-cols-2 md:grid-cols-1 sm:grid-cols-1">
                <LabeledInput :maxlength=255 label="Name" id="name" type="string" 
                required :tabindex=1 v-model="form.name" />
                <LabeledInput :min=0 numeric label="Amount Left" id="amount_left" type="number" 
                required :tabindex=2 v-model="form.amount_left"  />
            </div>
        </CustomCRUDTable>
    </AppLayout>
</template>
