<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Records',
        href: '/admin/records',
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

const ROUTE = 'admin.records'
const ROUTE_DESTROY = 'admin.records.destroy'
const ROWNAME = 'Record';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });


const form = useForm({
    id: 0,
    first_name: '',
    middle_name: '',
    last_name: '',
    maiden_name: '',
    suffix_id: 1,
    sex_id: 1,
    address: '',
    phone_number: '',
    email: '',
    alt_email: '',
    student_number: '',
    type_id: 1,
    batch: '',
    latin_honors_id: 1,
});
</script>

<template>
    <Head title="Admin Records" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable :form="form" :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" :headers="headers" :dropdownOptions="dropdownOptions">
            <div class="grid auto-rows-min gap-4 lg:md:grid-cols-3 md:grid-cols-1 sm:grid-cols-1">
                <LabeledInput :maxlength=255 label="First Name" id="firstName" type="string" 
                required autofocus :tabindex=1 v-model="form.first_name" />
                <LabeledInput :maxlength=255 label="Middle Name" id="middleName" type="string" 
                required :tabindex=2 v-model="form.middle_name"  />
                <LabeledInput :maxlength=255 label="Last Name" id="lastName" type="string" 
                required :tabindex=3 v-model="form.last_name"  />
                <DropdownInput column="suffix" :options="dropdownOptions.suffix_id" label="Suffix" id="suffix_id"
                required :tabindex=4 v-model="form.suffix_id"/>
                <DropdownInput column="sex" :options="dropdownOptions.sex_id" label="Sex" id="sex_id"
                required :tabindex=5 v-model="form.sex_id"/>
                <LabeledInput :maxlength=255 label="Maiden Name" id="maidenName" type="string" 
                required :tabindex=6 v-model="form.maiden_name"  />
                <LabeledInput :maxlength=255 class=" col-span-full" label="Address" id="address" type="string" 
                required :tabindex=7 v-model="form.address"  />
                <LabeledInput :maxlength="10" label="Mobile Phone Number" id="mobileNumber" type="string" 
                numeric required :tabindex=8 v-model="form.phone_number"  />
                <LabeledInput :maxlength=100 label="Email Address" id="email" for="email" type="email" 
                required :tabindex=9 v-model="form.email"  />
                <LabeledInput :maxlength=100 label="Alternate Email Address" id="alt_email" type="email" 
                required :tabindex=10 v-model="form.alt_email"  />
                <div class=" col-span-full grid lg:md:grid-cols-4 md:grid-cols-1 sm:grid-cols-1 gap-4">
                    <LabeledInput :maxlength=9 label="Student Number" id="studentNumber" type="string" 
                    numeric required :tabindex=11 v-model="form.student_number"  />
                    <LabeledInput :maxlength=4 label="Batch" id="batch" type="string" 
                    numeric required :tabindex=12 v-model="form.batch"  />
                    <DropdownInput column="type" :options="dropdownOptions.type_id" label="Student Type" id="student_type"
                    required :tabindex=13 v-model="form.type_id"/>
                    <DropdownInput column="honor" :options="dropdownOptions.latin_honors_id" label="Latin Honors" id="latin_honors_id"
                    required :tabindex=14 v-model="form.latin_honors_id"/>
                </div>
            </div>
        </CustomCRUDTable>
    </AppLayout>
</template>
