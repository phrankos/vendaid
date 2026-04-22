<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router} from '@inertiajs/vue3';
import { ref, computed } from 'vue'
import draggable from 'vuedraggable/src/vuedraggable.js';
import { ChevronUp, ChevronDown, Trash2, SquarePen } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import { DialogRoot } from 'reka-ui'

import {
    DialogClose,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogScrollContent,
} from '@/components/ui/dialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Records',
        href: '/admin/records',
    },
];

const props = defineProps({
    records: {
      type: Object,
      default: () => ({}),
    },
    dropdownOptions : {
      type: Object,
      required: true,
    },
    headers : {
      type: Object,
      required: true,
    },
});
const records = ref(props.records);
const headers = ref(props.headers);
const dropdownOptions:any = props.dropdownOptions
// console.log(records)

const keys = Object.keys(records.value[0]);
const columnOrder = ref([...keys,'actions']);

const visibleColumnsMap = ref(
    Object.fromEntries(columnOrder.value.map((c) => [c, false]))
);
{
visibleColumnsMap.value.student_number = true;
visibleColumnsMap.value.last_name = true;
visibleColumnsMap.value.first_name = true;
visibleColumnsMap.value.middle_name = true;
visibleColumnsMap.value.sex = true;
visibleColumnsMap.value.email = true;
visibleColumnsMap.value.phone_number = true;
visibleColumnsMap.value.address = true;
visibleColumnsMap.value.address = true;
visibleColumnsMap.value.student_type = true;
visibleColumnsMap.value.latin_honors = true;
visibleColumnsMap.value.actions = true;
}
const sortColumn = ref('')
const sortDirection = ref('asc')

const orderedVisibleColumns = computed(() =>
    columnOrder.value.filter((col) => visibleColumnsMap.value[col])
);

const sortedRecords = computed(() => {
    if (!sortColumn.value) return records.value;
    return records.value.toSorted((a:any, b:any) => {
        const valA = a[sortColumn.value];
        const valB = b[sortColumn.value];

        if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1
        if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1
            return 0;
    });
});

function sortBy(columnKey:any) {
    if (sortColumn.value === columnKey) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = columnKey;
        sortDirection.value = 'asc';
    }

}

function editRecord(id:number) {
    dialog.value = 'edit';
    currentRecord.value = records.value[id-1];
    Object.assign(form, currentRecord.value);
    open.value=true;
}
const currentRecord = ref(records.value[0]);
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
const deleteForm = useForm({id:0});
const open = ref(false);
const dialog = ref('');
const closeModal = () => {
    open.value=false;
    form.clearErrors();
    form.reset();
};
const openAddRecordDialog = () => {
    form.clearErrors();
    form.reset();
    dialog.value = 'create';
    open.value=true;
};


const submit = () => {
    form.put(route('admin.records'), {
        preserveScroll: true,
        onSuccess: () => {
            // setUnsavedChanges(false)
            closeModal();
            form.clearErrors();
            form.reset();
            router.reload({ only: ['records'] });
        },
        onError: () => console.log("ERROR")
        // onFinish: () => form.reset('password'),
    });
};

const submitCreate = () => {
    form.post(route('admin.records'), {
        preserveScroll: true,
        onSuccess: () => {
            // setUnsavedChanges(false)
            closeModal();
            router.reload({ only: ['records'] });
            records.value = props.records;
        },
        onError: () => console.log("ERROR")
        // onFinish: () => form.reset('password'),
    });
};

function deleteRecord(id:number) {
    dialog.value = 'delete';
    deleteForm.id = id;
    open.value=true;
}

const submitDelete = () => {
    const id = deleteForm.id;
    deleteForm.delete(route('admin.records.destroy', {id}), {
        preserveScroll: true,
        onSuccess: () => {
            // setUnsavedChanges(false)
            closeModal();
            router.reload({ only: ['records'] });
            records.value = props.records;
        },
        onError: () => console.log("ERROR")
        // onFinish: () => form.reset('password'),
    });
};

</script>

<template>
    <Head title="Admin Records" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <div class="flex flex-row p-4 justify-between sticky top-0 left-0 gap-x-4 z-1 bg-background">
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
            <Button
                @click="openAddRecordDialog"
                variant="accent"
                size="icon"
                class=" select-none cursor-pointer relative size-10 w-auto p-1 px-2 focus-within:ring-2 focus-within:ring-accent-hover"
            >
                Create New Record
            </Button>
        </div>
        
        <div class="max-w-screen gap-x-4">            
            <table class="table-auto shadow-sm bg-background">
            <thead> 
                <draggable 
                tag="tr"
                v-model="columnOrder"
                item-key="key"
                handle=""
                :animation="200"
                draggable=".drags"
                class="sticky top-18 z-1 bg-primary text-primary-foreground items-center select-none"
                >
                <template #item="{ element: header }">
                    <th 
                    v-if="visibleColumnsMap[header]"
                    scope="col" 
                    class="pl-3 pr-1 py-2 font-semibold text-left hover:bg-primary-foreground/10 whitespace-nowrap group"
                    :class="{'cursor-pointer': header !== 'actions', 'drags': header !== 'actions'}" 
                    @click="header!=='actions'?sortBy(header):()=>true;"
                    >
                    <div class="flex flex-row relative justify-between gap-x-5">
                        {{ headers[header as keyof typeof headers] }}
                        <component 
                        v-if="sortColumn === header && header !== 'actions'" 
                        :is=" sortDirection === 'asc' ? ChevronUp : ChevronDown " 
                        class="rounded-md inline-flex w-4 group-hover:bg-primary-foreground/10 cursor-pointer" 
                        />
                        <div 
                        v-if="sortColumn !== header && header !== 'actions'" 
                        class="rounded-md inline-flex w-4"
                        ></div>
                    </div>
                    </th>
                </template>
                </draggable>
            </thead>
            <tbody>
                <DialogRoot v-model:open="open" scrollable class="flex flex-col w-screen">
                    <DialogScrollContent class="flex flex-grow" >
                        <form class="flex flex-1 flex-col bg-background" @submit.prevent="submit">
                            <DialogHeader v-if="dialog==='edit'" class="space-y-3">
                                <DialogTitle>Edit Patient Record</DialogTitle>
                                <DialogDescription>Please double check if the data is correct.</DialogDescription>
                            </DialogHeader>
                            <DialogHeader v-if="dialog==='delete'" class="space-y-3">
                                <DialogTitle>Delete Patient Record</DialogTitle>
                                <DialogDescription>Please double check if the record you want to delete is correct.</DialogDescription>
                            </DialogHeader>
                            <DialogHeader v-if="dialog==='create'" class="space-y-3">
                                <DialogTitle>Create New Patient Record</DialogTitle>
                                <DialogDescription>Please double check if the data is correct.</DialogDescription>
                            </DialogHeader>
                            <div v-if="dialog === 'delete'" class="flex flex-1 flex-col gap-4 p-4 ">
                                {{ records[deleteForm.id-1] }}
                            </div>
                            <div v-if="['edit','create'].includes(dialog)" class="flex flex-1 flex-col gap-4 p-4 ">
                                <div class="grid auto-rows-min gap-4 lg:md:grid-cols-3 md:grid-cols-1 sm:grid-cols-1">
                                    <LabeledInput label="First Name" id="firstName" type="string" 
                                    required autofocus :tabindex=1 v-model="form.first_name" />
                                    <LabeledInput label="Middle Name" id="middleName" type="string" 
                                    required :tabindex=2 v-model="form.middle_name"  />
                                    <LabeledInput label="Last Name" id="lastName" type="string" 
                                    required :tabindex=3 v-model="form.last_name"  />
                                    <DropdownInput column="suffix" :options="dropdownOptions.suffix_id" label="Suffix" id="suffix_id"
                                    required :tabindex=4 v-model="form.suffix_id"/>
                                    <DropdownInput column="sex" :options="dropdownOptions.sex_id" label="Sex" id="sex_id"
                                    required :tabindex=5 v-model="form.sex_id"/>
                                    <LabeledInput label="Maiden Name" id="maidenName" type="string" 
                                    required :tabindex=6 v-model="form.maiden_name"  />
                                    <LabeledInput class=" col-span-full" label="Address" id="address" type="string" 
                                    required :tabindex=7 v-model="form.address"  />
                                    <LabeledInput label="Mobile Phone Number" id="mobileNumber" type="string" 
                                    required :tabindex=8 v-model="form.phone_number"  />
                                    <LabeledInput label="Email Address" id="email" for="email" type="string" 
                                    required :tabindex=9 v-model="form.email"  />
                                    <LabeledInput label="Alternate Email Address" id="alt_email" type="string" 
                                    required :tabindex=10 v-model="form.alt_email"  />
                                    <div class=" col-span-full grid lg:md:grid-cols-4 md:grid-cols-1 sm:grid-cols-1 gap-4">
                                        <LabeledInput label="Student Number" id="studentNumber" type="string" 
                                        required :tabindex=11 v-model="form.student_number"  />
                                        <LabeledInput label="Batch" id="batch" type="string" 
                                        required :tabindex=12 v-model="form.batch"  />
                                        <DropdownInput column="type" :options="dropdownOptions.type_id" label="Student Type" id="student_type"
                                        required :tabindex=13 v-model="form.type_id"/>
                                        <DropdownInput column="honor" :options="dropdownOptions.latin_honors_id" label="Latin Honors" id="latin_honors_id"
                                        required :tabindex=14 v-model="form.latin_honors_id"/>
                                    </div>
                                </div> 
                            </div>

                            <DialogFooter class="mt-4 gap-2">
                                <DialogClose as-child>
                                    <Button variant="outline" @click="closeModal"> Cancel </Button>
                                </DialogClose>
                                <Button v-if="dialog==='edit'" type="submit" variant="default" :disabled="form.processing"> Save Changes </Button>
                                <Button v-if="dialog==='create'" @click="submitCreate" type="button" variant="accent" :disabled="form.processing"> Create Record </Button>
                                <Button v-if="dialog==='delete'" @click="submitDelete" type="button" variant="default" :disabled="form.processing"> Delete Record </Button>
                            </DialogFooter>
                        </form>
                    </DialogScrollContent>
                    <tr class="even:bg-gray-200/60 hover:bg-primary/10" v-for="record in sortedRecords" :key="record.id">
                        <td class="text-nowrap px-3 py-2 whitespace-no-wrap " 
                        :class="{'sticky right-0 bg-background/70 hover:bg-background':header === 'actions', 
                        'hover:bg-primary/5':header !== 'actions', 'font-mono':['id','student_number','phone_number','batch'].includes(header)}" v-for="header in orderedVisibleColumns" :key="header">
                        {{ header === 'type_id' ? dropdownOptions[header][record[header]-1].type : 
                            header === 'sex_id' ? dropdownOptions[header][record[header]-1].sex : 
                            header === 'suffix_id' ? dropdownOptions[header][record[header]-1].suffix : 
                            header === 'latin_honors_id' ? dropdownOptions[header][record[header]-1].honor : record[header]  }}
                            <div v-if="header=='actions'" class="flex flex-row gap-x-2 items-center justify-end">
                                <Button @click="editRecord(record.id)" variant="default" size="sm" 
                                class=" bg-accent2 text-accent-foreground hover:bg-accent2-darken"
                                >
                                    <component v-if="SquarePen" :is="SquarePen" class="rounded-md inline-flex w-4" />
                                    <!-- Edit -->
                                </Button>
                                <div class="rounded-md bg-primary">
                                    <Button :disabled="form.processing" @click="deleteRecord(record.id)" class="bg-transparent text-primary-foreground hover:bg-primary-foreground/10" size="sm" variant="default">
                                    <component v-if="Trash2" :is="Trash2" class="rounded-md inline-flex w-4" />
                                    <!-- Delete -->
                                    </Button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </DialogRoot>
            </tbody>
            </table>
        </div>
    </AppLayout>
</template>
