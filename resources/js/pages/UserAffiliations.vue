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
        href: '/user/affiliations',
    },
];

const props = defineProps({
    data : {
      type: Object,
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
var headers = ref(props.headers);
    delete headers.value.name['id'];
    delete headers.value.name['record_id'];
const dropdownOptions = props.dropdownOptions;

const ROUTE = 'user.affiliations';
const ROUTE_DESTROY = 'user.affiliations.destroy';
const ROWNAME = 'Affiliation';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });

const form = useForm({
    id: 0,
    affiliation: "",
    position: "",
    start_date: "",
    end_date: "",
    is_current: 0,
});

const show = ref(false)
</script>

<template>
    <Head title="User Affiliation History" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable v-if="data!=null" @showPasswordField="show = $event" :form="form" :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" :headers="headers" :dropdownOptions="dropdownOptions">
            <div class="grid auto-rows-min gap-4 lg:md:grid-cols-2 md:grid-cols-1 sm:grid-cols-1">
              <DropdownInput class=" col-span-full" column="state" :options="dropdownOptions.is_current" label="Is Current?" id="is_current"
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
        <div v-else class=" flex flex-col flex-grow gap-2 justify-center items-center select-none">
            <h1 class=" lg:text-5xl md:text-3xl sm:text-xl text-base font-semibold text-muted-foreground">
                Your account is not linked to a Record.
            </h1>
            <p class=" lg:text-3xl md:text-xl sm:text-sm text-xs font-normal text-muted-foreground">
                Please contact the system administrator.
            </p>
        </div>
    </AppLayout>
</template>
