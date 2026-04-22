<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem} from '@/types';
import { LoaderCircle } from 'lucide-vue-next';
import { Head, useForm } from '@inertiajs/vue3';
import { LabeledInput, DropdownInput } from '@/components/ui/input';
import { ref } from 'vue';

const props = defineProps({
    record: {
      type: Object,
      default: () => ({}),
    },
    dropdownOptions : Object
});

const record = props.record
const dropdownOptions:any = props.dropdownOptions

const form = record
  ? useForm({
      first_name: record.first_name,
      middle_name: record.middle_name,
      last_name: record.last_name,
      maiden_name: record.maiden_name,
      suffix_id: record.suffix_id,
      sex_id: record.sex_id,
      address: record.address,
      phone_number: record.phone_number,
      email: record.email,
      alt_email: record.alt_email,
      student_number: record.student_number,
      type_id: record.type_id,
      batch: record.batch,
      latin_honors_id: record.latin_honors_id
    })
  : useForm({
      first_name: '',
      middle_name: '',
      last_name: '',
      maiden_name: '',
      suffix_id: 0,
      sex_id: 0,
      address: '',
      phone_number: '',
      email: '',
      alt_email: '',
      student_number: '',
      type_id: 0,
      batch: '',
      latin_honors_id: 0
    });

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Personal Information',
        href: '/user/information',
    },
];

// const isEditing = ref(true);
const isEditing = ref(false);
function edit() {
    isEditing.value = true;
    // console.log("EDIT");
}
function cancelEdit() {
    form.reset();
    isEditing.value = false;
    // console.log("CANCEL");
    // console.log(form);
}

const submit = () => {
    form.put(route('user.information'), {
        onSuccess: () => {
            isEditing.value = false;
            // setUnsavedChanges(false)
        },
        onError: () => console.log("ERROR")
        // onFinish: () => form.reset('password'),
    });
};

</script>

<template>
    <Head title="Personal Information" />
        <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div v-for="(item, index) in Array(36)" class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern :patterncolor="Math.floor(Math.random()*4)" />
                </div>
            </div> 
        </div> -->
        <div v-if="record!=null" class=" flex justify-center items-center my-auto">
            <form class="flex flex-1 flex-col shadow-xl/30 max-w-7xl bg-background border-2 rounded-none" @submit.prevent="submit">
            <!-- <form @change="setUnsavedChanges(true)" class="flex flex-1 flex-col shadow-xl/30 bg-background border-2 rounded-none" @submit.prevent="submit"> -->
                <div class="flex flex-1 flex-col gap-4 p-4 ">
                    <div class="grid auto-rows-min gap-4 lg:md:grid-cols-3 md:grid-cols-1 sm:grid-cols-1">
                        <!-- <input for="firstName" id="firstName" v-model="form.first_name"  /> -->
                        <LabeledInput :maxlength=255 :disabled=!isEditing label="First Name" id="firstName" type="string" 
                        required autofocus :tabindex=1 v-model="form.first_name" />
                        <LabeledInput :maxlength=255 :disabled=!isEditing label="Middle Name" id="middleName" type="string" 
                        required :tabindex=2 v-model="form.middle_name"  />
                        <LabeledInput :maxlength=255 :disabled=!isEditing label="Last Name" id="lastName" type="string" 
                        required :tabindex=3 v-model="form.last_name"  />
                        <DropdownInput column="suffix" :options="dropdownOptions.suffixes" :disabled=!isEditing label="Suffix" id="suffix_id"
                        required :tabindex=4 v-model="form.suffix_id"/>
                        <DropdownInput column="sex" :options="dropdownOptions.sexes" :disabled=!isEditing label="Sex" id="sex_id"
                        required :tabindex=5 v-model="form.sex_id"/>
                        <LabeledInput :maxlength=255 :disabled=!isEditing label="Maiden Name" id="maidenName" type="string" 
                        :tabindex=6 v-model="form.maiden_name"  />
                        <LabeledInput :maxlength=255 :disabled=!isEditing class=" col-span-full" label="Address" id="address" type="string" 
                        required :tabindex=7 v-model="form.address"  />
                        <LabeledInput :maxlength="10" :disabled=!isEditing label="Mobile Phone Number" id="mobileNumber" type="tel" 
                        numeric required :tabindex=8 v-model="form.phone_number"  />
                        <LabeledInput :maxlength=100 :disabled=!isEditing label="Email Address" id="email" for="email" type="email" 
                        required :tabindex=9 v-model="form.email"  />
                        <LabeledInput :maxlength=100 :disabled=!isEditing label="Alternate Email Address" id="alt_email" type="email" 
                        :tabindex=10 v-model="form.alt_email"  />
                        <div class=" col-span-full grid lg:md:grid-cols-4 md:grid-cols-1 sm:grid-cols-1 gap-4">
                            <LabeledInput :maxlength="9" :disabled=!isEditing label="Student Number" id="studentNumber" type="string" 
                            numeric required :tabindex=11 v-model="form.student_number"  />
                            <LabeledInput :maxlength="4" :disabled=!isEditing label="Batch" id="batch" type="string" 
                            numeric required :tabindex=12 v-model="form.batch"  />
                            <DropdownInput column="type" :options="dropdownOptions.types" :disabled=!isEditing label="Student Type" id="student_type"
                            required :tabindex=13 v-model="form.type_id"/>
                            <DropdownInput column="honor" :options="dropdownOptions.latin_honors" :disabled=!isEditing label="Latin Honors" id="latin_honors_id"
                            required :tabindex=14 v-model="form.latin_honors_id"/>
                        </div>
                    </div> 
                    <div class="grid lg:md:grid-cols-4 md:grid-cols-1 sm:grid-cols-1 gap-4 select-none">
                        <!-- {{ isEditing }} -->
                        <Button :disabled="form.processing" @click="cancelEdit" type="button" variant="outline" v-if="isEditing" class=" lg:col-start-3  font-semibold">
                            CANCEL
                        </Button>
                        <Button :disabled="form.processing" v-if="!isEditing" type="button" @click="edit()" class=" lg:col-start-4 font-semibold">EDIT</Button>
                        <Button :disabled="form.processing" v-if="isEditing" :type="submit" class=" lg:col-start-4 font-semibold" >
                            SAVE CHANGES
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        </Button>
                    </div>
                </div>
            </form>
        </div>
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
