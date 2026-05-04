<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: 'patients',
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

console.log(data)

const ROUTE = 'patients'
const ROUTE_DESTROY = 'patients.destroy'
const ROWNAME = 'Patient';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });


const form = useForm({
    id: 0,
    scan_id: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    sex_id: 1,
    created_by: 0,
    updated_by: 0,
});

</script>

<template>
    <Head title="Patients" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable :form="form" 
        :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" 
        :headers="headers" :dropdownOptions="dropdownOptions">
            <div class="grid auto-rows-min gap-4 lg:md:grid-cols-3 md:grid-cols-1 sm:grid-cols-1">
                <LabeledInput :maxlength=255 label="First Name" id="firstName" type="string" 
                required autofocus :tabindex=1 v-model="form.first_name" />
                <LabeledInput :maxlength=255 label="Middle Name" id="middleName" type="string" 
                required :tabindex=2 v-model="form.middle_name"  />
                <LabeledInput :maxlength=255 label="Last Name" id="lastName" type="string" 
                required :tabindex=3 v-model="form.last_name"  />
                <LabeledInput class="col-span-2" :maxlength=255 label="Scan ID" id="scan_id" type="number" 
                required autofocus :tabindex=4 v-model="form.scan_id" />
                <DropdownInput column="sex" :options="dropdownOptions.sex_id" label="Sex" id="sex_id"
                required :tabindex=5 v-model="form.sex_id"/>
            </div>
        </CustomCRUDTable>
    </AppLayout>
</template>
