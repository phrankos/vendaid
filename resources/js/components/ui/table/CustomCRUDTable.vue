<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue'
import draggable from 'vuedraggable/src/vuedraggable.js';
import { ChevronUp, ChevronDown, Trash2, SquarePen, Table } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { DialogRoot } from 'reka-ui'
import { DialogClose, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogScrollContent } from '@/components/ui/dialog';
import TableControls from './TableControls.vue';

const props = defineProps({
    rowName : {
      type: String,
      required: true
    },
    headers : {
      type: Object,
      required: true,
    },
    data : {
      type: Object,
      required: true,
    },
    dropdownOptions : {
      type: Object,
      required: true,
    },
    route : {
      type: String,
      required: true,
    },
    route_destroy : {
      type: String,
      required: true,
    },
    form : {
      type: Object,
      required: true,
    },
    medicines : {
      type: Object,
      required: false,
    },
});

const form = ref(props.form).value;
const rowName = props.rowName;
const data = ref(props.data);
const dropdownOptions = props.dropdownOptions;

const ROUTE = props.route;
const ROUTE_DELETE = props.route_destroy;

const headers = {...props.headers.name, ...{actions: "Actions"}};
const headerTypes = props.headers.type;
const headerDropdownabbles = props.headers.dropdown;

const keys = Object.keys(headers);
const columnOrder = ref([...keys]);

const visibleColumnsMap = ref(
    Object.fromEntries(columnOrder.value.map((c) => [c, true]))
);
const sortColumn = ref('')
const sortDirection = ref('asc')

const orderedVisibleColumns = computed(() =>
    columnOrder.value.filter((col) => visibleColumnsMap.value[col])
);

const sortedData = computed(() => {
    if (!sortColumn.value) return data.value;
    return data.value.toSorted((a:any, b:any) => {
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

// function editRow(id:number) {   // what is this?
//     dialog.value = 'edit';
//     currentRow.value = data.value[id-1];
//     form.id = currentRow.value.id;
// }
const currentRow = ref(data.value[0]);
const deleteForm = useForm({id:0});
const open = ref(false);
const dialog = ref('');
const closeModal = () => {
    open.value=false;
    form.clearErrors();
    form.reset();
};
const openDialog = (id: number, type: string) => {
    // console.log("---")
    // console.log(form)
    // console.log("---")
    form.clearErrors();
    form.reset();
    dialog.value = type;
    if (type === 'edit') {
        emit('showPasswordField', false);
        currentRow.value = data.value.find((row: { id: number; })=>row.id===id);
        form.id = currentRow.value.id;
        Object.assign(form, currentRow.value);
    }
    else if (type === 'create') {
        emit('showPasswordField', true);
    }
    else if (type === 'delete') {
        emit('showPasswordField', false);
        deleteForm.id = id;
    }
    open.value=true;
};

const submit = () => {
    if (dialog.value === 'edit') {
        form.put(route(ROUTE), {
            preserveScroll: true,
            onSuccess: () => {
                // setUnsavedChanges(false)
                closeModal();
                form.clearErrors();
                form.reset();
                data.value = props.data;
            },
            onError: () => console.log("ERROR")
            // onFinish: () => form.reset('password'),
        });
    }
    else if (dialog.value === 'create') {
        form.post(route(ROUTE), {
            preserveScroll: true,
            onSuccess: () => {
                // setUnsavedChanges(false)
                closeModal();
                data.value = props.data;
            },
            onError: () => console.log("ERROR")
            // onFinish: () => form.reset('password'),
        });
    }
};

const submitDelete = () => {
    var id = deleteForm.id;
    deleteForm.delete(route(ROUTE_DELETE, {id}), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            data.value = props.data;
        },
        onError: () => console.log("ERROR")
        // onFinish: () => form.reset('password'),
    });
};

const getNamesFromIds = (bitmapString: string, itemsArray: any[]) => {
    return itemsArray
        .filter((_, index) => bitmapString[index] === '1')
        .map(item => item.name)
        .join(', ');
}

const emit = defineEmits<{
  (e: 'showPasswordField', value: boolean): void
}>()

export interface Identifiable {
  id: number;
}

</script>

<template>
    <TableControls :open-dialog="openDialog" :headers="headers" :visible-columns-map="visibleColumnsMap" :column-order="columnOrder"  />
    <div class="max-w-screen gap-x-4">
        <table class="min-w-screen table-auto shadow-sm bg-background">
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
                        <DialogHeader v-if="dialog==='edit'" class="space-y-3 select-none">
                            <DialogTitle>Edit {{ rowName }}</DialogTitle>
                            <DialogDescription>Please double check if the data is correct.</DialogDescription>
                        </DialogHeader>
                        <DialogHeader v-if="dialog==='delete'" class="space-y-3 select-none">
                            <DialogTitle>Delete {{ rowName }}</DialogTitle>
                            <DialogDescription>Please double check if the {{ rowName }} you want to delete is correct.</DialogDescription>
                        </DialogHeader>
                        <DialogHeader v-if="dialog==='create'" class="space-y-3 select-none">
                            <DialogTitle>Create New {{ rowName }}</DialogTitle>
                            <DialogDescription>Please double check if the data is correct.</DialogDescription>
                        </DialogHeader>
                        <div v-if="dialog === 'delete'" class="flex flex-1 flex-col gap-4 p-4 ">
                            {{ data.find((row:Identifiable)=>row.id===deleteForm.id) }}
                        </div>
                        <div v-if="['edit','create'].includes(dialog)" class="flex flex-1 flex-col gap-4 p-4 ">
                            <slot />
                        </div>

                        <DialogFooter class="mt-4 gap-2">
                            <DialogClose as-child>
                                <Button variant="outline" @click="closeModal"> Cancel </Button>
                            </DialogClose>
                            <Button v-if="dialog==='edit'" type="submit" variant="default" :disabled="form.processing"> Save Changes </Button>
                            <Button v-if="dialog==='create'" type="submit" variant="accent" :disabled="form.processing"> Create {{ rowName }} </Button>
                            <Button v-if="dialog==='delete'" @click="submitDelete" type="button" variant="default" :disabled="form.processing"> Delete {{ rowName }} </Button>
                        </DialogFooter>
                    </form>
                </DialogScrollContent>
                <tr class="even:bg-gray-200/60 hover:bg-primary/10" v-for="row in sortedData" :key="row.id">
                    <td class="text-nowrap px-3 py-2 whitespace-no-wrap " 
                    :class="{'sticky right-0 bg-background/70 hover:bg-background':header === 'actions', 
                    'hover:bg-primary/10':header !== 'actions', 'font-mono': headerTypes[header] === 'numeric' }" v-for="header in orderedVisibleColumns" :key="header">
                        <span v-if="headerTypes[header] === 'datetime'">{{ new Date(row[header]).toLocaleString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true }) }}</span>
                        <span v-else-if="headerTypes[header] === 'date'">{{ new Date(row[header]).toLocaleString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'}) }}</span>
                        <span v-else-if="Object.keys(dropdownOptions).includes(header)">{{ dropdownOptions[header].find((ROW:Identifiable)=>ROW.id===row[header])[headerDropdownabbles[header]] }}</span>
                        <span v-else-if="headerTypes[header] === 'bin_loop'">{{ 
                            getNamesFromIds(row[header], props[headerDropdownabbles[header]])
                        }}</span>
                        <span v-else>{{ row[header] }}</span>
                        <div v-if="header=='actions'" class="flex flex-row gap-x-2 items-center justify-center">
                            <Button @click="openDialog(row.id, 'edit')" variant="default" size="sm" 
                            class=" bg-accent2 text-accent-foreground hover:bg-accent2-darken"
                            >
                                <component v-if="SquarePen" :is="SquarePen" class="rounded-md inline-flex w-4" />
                                <!-- Edit -->
                            </Button>
                            <Button :disabled="form.processing" @click="openDialog(row.id, 'delete')" class="bg-primary text-primary-foreground hover:bg-primary-hover" size="sm" variant="default">
                                <component v-if="Trash2" :is="Trash2" class="rounded-md inline-flex w-4" />
                                <!-- Delete -->
                            </Button>
                        </div>
                    </td>
                </tr>
            </DialogRoot>
        </tbody>
        </table>
    </div>
</template>
