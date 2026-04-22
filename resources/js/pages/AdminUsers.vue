<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Users',
        href: '/admin/users',
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

const ROUTE = 'admin.users';
const ROUTE_DESTROY = 'admin.users.destroy';
const ROWNAME = 'User';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });

const form = useForm({
    id: 0,
    name: '',
    email: '',
    role_id: 1,
    record_id: 1,
    password: ''
});

const show = ref(false)
</script>

<template>
    <Head title="Admin Users" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable @showPasswordField="show = $event" :form="form" :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" :headers="headers" :dropdownOptions="dropdownOptions">
            <div class="grid auto-rows-min gap-4 lg:md:grid-cols-2 md:grid-cols-1 sm:grid-cols-1">
              <LabeledInput label="Name" id="name" type="string" 
              required autofocus :tabindex=1 v-model="form.name" />
              <LabeledInput label="Email Address" id="email" for="email" type="email" 
              required :tabindex=2 v-model="form.email"  />
              <LabeledInput label="Record ID" id="record_id" type="number" 
              required :tabindex=3 v-model="form.record_id"  />
              <DropdownInput column="role" :options="dropdownOptions.role_id" label="Role" id="role"
              required :tabindex=4 v-model="form.role_id"/>
              <LabeledInput class=" col-span-full" v-if="show" label="Password" id="password" type="password" 
              required autofocus :tabindex=1 v-model="form.password" />
              <LabeledInput class=" col-span-full" v-else label="Password" id="password" type="password" 
               autofocus :tabindex=1 v-model="form.password" />
            </div>
        </CustomCRUDTable>
    </AppLayout>
</template>
