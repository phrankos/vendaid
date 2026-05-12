<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, onMounted, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import CustomCRUDTable from '@/components/ui/table/CustomCRUDTable.vue';
import { Checkbox } from '@/components/ui/checkbox';
import CheckboxInput from '@/components/ui/input/CheckboxInput.vue';
import DateInput from '@/components/ui/input/DateInput.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Prescriptions',
        href: 'prescriptions',
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
    medicines : {
      type: Object,
      required: false,
      default: () => []
    },
});

interface Medicine {
    id: number;
    name: string;
}

const data = ref(props.data);
const headers = ref(props.headers);
const dropdownOptions = props.dropdownOptions;

const ROUTE = 'prescriptions'
const ROUTE_DESTROY = 'prescriptions.destroy'
const ROWNAME = 'Prescription';

watch(() => props.data, (newData) => {
    data.value = newData;
}, { deep: true });

const selectedMedicines = ref<number[]>([]);

const medicinesList = computed(() => {
    return (props.medicines as Medicine[]) || [];
});

const computeBinaryString = () => {
    const medicines = medicinesList.value;
    if (!medicines || medicines.length === 0) return "";
    
    const bits = medicines.map((medicine: Medicine) => 
        selectedMedicines.value.includes(medicine.id) ? '1' : '0'
    );
    
    return bits.join('');
};

const parseBinaryString = (binaryString: string) => {
    const medicines = medicinesList.value;
    if (!binaryString || !medicines || medicines.length === 0) return [];
    
    const selected: number[] = [];
    const bits = binaryString.split('');
    
    bits.forEach((bit, index) => {
        if (bit === '1' && medicines[index]) {
            selected.push(medicines[index].id);
        }
    });
    
    return selected;
};

const form = useForm({
    id: 0,
    patient_id: 1,
    medicines_binary: "0000000000",
    expires_at: "",
});

watch(() => form.medicines_binary, (newValue) => {
    if (newValue && newValue.length > 0) {
        const parsed = parseBinaryString(newValue);
        if (parsed.length > 0 || selectedMedicines.value.length === 0) {
            selectedMedicines.value = parsed;
        }
    }
}, { immediate: true });

watch(selectedMedicines, (newValue) => {
    form.medicines_binary = computeBinaryString();
}, { deep: true });

const toggleMedicine = (medicineId: number) => {
    const index = selectedMedicines.value.indexOf(medicineId);
    if (index === -1) {
        selectedMedicines.value.push(medicineId);
    } else {
        selectedMedicines.value.splice(index, 1);
    }
};

const isMedicineSelected = (medicineId: number) => {
    return selectedMedicines.value.includes(medicineId);
};

watch(medicinesList, (medicines) => {
    if (medicines && medicines.length > 0 && form.medicines_binary) {
        if (selectedMedicines.value.length === 0) {
            selectedMedicines.value = parseBinaryString(form.medicines_binary);
        }
    }
}, { immediate: true });

onMounted(() => {
    if (form.medicines_binary && medicinesList.value.length > 0) {
        selectedMedicines.value = parseBinaryString(form.medicines_binary);
    }
});
</script>

<template>
    <Head title="Prescriptions" />
    
    <AppLayout variant="full" :breadcrumbs="breadcrumbs">
        <CustomCRUDTable :form="form" 
        :rowName="ROWNAME" :route="ROUTE" :route_destroy="ROUTE_DESTROY" :data="data" 
        :headers="headers" :dropdownOptions="dropdownOptions" :medicines="medicines">
            <div class="grid auto-rows-min gap-4 min-w-1xl lg:md:grid-cols-1 md:grid-cols-1 sm:grid-cols-1">
                <DropdownInput column="patient_name" :options="dropdownOptions.patient_id" label="Patient" id="Patient"
                required :tabindex=1 v-model="form.patient_id"/>
                <DateInput v-model="form.expires_at" label="Expires At" id="expires_at" 
                :required="true":tabindex="1"></DateInput>
                
                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-3">Medicines</h3>
                    <div class="grid grid-cols-2 gap-x-20 gap-3">
                        <CheckboxInput 
                            v-for="medicine in medicinesList"
                            :key="medicine.id"
                            :id="`medicine_${medicine.id}`"
                            :label="medicine.name"
                            :model-value="isMedicineSelected(medicine.id)"
                            @update:model-value="toggleMedicine(medicine.id)"
                            :required="false"
                        />
                    </div>
                </div>
            </div>
        </CustomCRUDTable>
    </AppLayout>
</template>