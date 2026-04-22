<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Affiliation History',
        href: '/admin/affiliations',
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

const ROUTE = 'admin.affiliations';
const ROUTE_DESTROY = 'admin.affiliations.destroy';
const ROWNAME = 'Affiliation';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });

const form = useForm({
    id: 0,
    record_id: 1,
    affiliation: "",
    position: "",
    start_date: "",
    end_date: "",
    is_current: 0,
});

const show = ref(false)
</script>

<template>
    <Head title="Admin Affiliation History" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable @showPasswordField="show = $event" :form="form" :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" :headers="headers" :dropdownOptions="dropdownOptions">
            <div class="grid auto-rows-min gap-4 lg:md:grid-cols-2 md:grid-cols-1 sm:grid-cols-1">
              <LabeledInput label="Record ID" id="record_id" type="number" 
              numeric required :tabindex=1 v-model="form.record_id"  />
              <DropdownInput column="state" :options="dropdownOptions.is_current" label="Is Current?" id="is_current"
              required :tabindex=2 v-model="form.is_current"/>
              <LabeledInput label="Affiliation" id="affiliation" type="string" 
              required :tabindex=3 v-model="form.affiliation" />
              <LabeledInput label="Position" id="position" type="string" 
              required :tabindex=4 v-model="form.position" />
              <LabeledInput label="Start Date" id="start_date" type="date" 
              required :tabindex=5 v-model="form.start_date" />
              <LabeledInput label="End Date" id="end_date" type="date" 
              required :tabindex=6 v-model="form.end_date" />
            </div>
        </CustomCRUDTable>
    </AppLayout>
</template>
