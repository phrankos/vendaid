<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    honors: Array<{ id: number, honor: string }>,
    suffixes: Array<{ id: number, suffix: string }>,
    sexes: Array<{ id: number, sex: string }>,
    studentTypes: Array<{ id: number, type: string }>
}>();

// Latin Honors
const editHonorId = ref<number|null>(null);
const addHonorForm = useForm({ honor: '' });
const editHonorForm = useForm({ honor: '' });

function addHonor() {
    addHonorForm.post(route('admin.misc.store'), {
        onSuccess: () => addHonorForm.reset()
    });
}
function startEditHonor(honor: { id: number, honor: string }) {
    editHonorId.value = honor.id;
    editHonorForm.honor = honor.honor;
}
function updateHonor(id: number) {
    editHonorForm.put(route('admin.misc.update', id), {
        onSuccess: () => {
            editHonorId.value = null;
            editHonorForm.reset();
        }
    });
}
function deleteHonor(id: number) {
    if (confirm('Are you sure you want to delete this honor?')) {
        router.delete(route('admin.misc.destroy', id));
    }
}

// Name Suffixes
const editSuffixId = ref<number|null>(null);
const addSuffixForm = useForm({ suffix: '' });
const editSuffixForm = useForm({ suffix: '' });

function addSuffix() {
    addSuffixForm.post(route('admin.suffix.store'), {
        onSuccess: () => addSuffixForm.reset()
    });
}
function startEditSuffix(suffix: { id: number, suffix: string }) {
    editSuffixId.value = suffix.id;
    editSuffixForm.suffix = suffix.suffix;
}
function updateSuffix(id: number) {
    editSuffixForm.put(route('admin.suffix.update', id), {
        onSuccess: () => {
            editSuffixId.value = null;
            editSuffixForm.reset();
        }
    });
}
function deleteSuffix(id: number) {
    if (confirm('Are you sure you want to delete this suffix?')) {
        router.delete(route('admin.suffix.destroy', id));
    }
}

// Sexes
const editSexId = ref<number|null>(null);
const addSexForm = useForm({ sex: '' });
const editSexForm = useForm({ sex: '' });

function addSex() {
    addSexForm.post(route('admin.sex.store'), {
        onSuccess: () => addSexForm.reset()
    });
}
function startEditSex(sex: { id: number, sex: string }) {
    editSexId.value = sex.id;
    editSexForm.sex = sex.sex;
}
function updateSex(id: number) {
    editSexForm.put(route('admin.sex.update', id), {
        onSuccess: () => {
            editSexId.value = null;
            editSexForm.reset();
        }
    });
}
function deleteSex(id: number) {
    if (confirm('Are you sure you want to delete this sex?')) {
        router.delete(route('admin.sex.destroy', id));
    }
}

// Student Types
const editStudentTypeId = ref<number|null>(null);
const addStudentTypeForm = useForm({ type: '' });
const editStudentTypeForm = useForm({ type: '' });

function addStudentType() {
    addStudentTypeForm.post(route('admin.student-type.store'), {
        onSuccess: () => addStudentTypeForm.reset()
    });
}
function startEditStudentType(type: { id: number, type: string }) {
    editStudentTypeId.value = type.id;
    editStudentTypeForm.type = type.type;
}
function updateStudentType(id: number) {
    editStudentTypeForm.put(route('admin.student-type.update', id), {
        onSuccess: () => {
            editStudentTypeId.value = null;
            editStudentTypeForm.reset();
        }
    });
}
function deleteStudentType(id: number) {
    if (confirm('Are you sure you want to delete this student type?')) {
        router.delete(route('admin.student-type.destroy', id));
    }
}
</script>

<template>
    <AppLayout variant="full">
        <div class="p-6 grid lg:grid-cols-4 md:grid-cols-2 gap-y-8 justify-items-center">
            <!-- Latin Honors Table -->
            <div class="flex flex-col flex-1 h-fit w-[350px] shadow-xl/30 bg-background border-2">
                <table class="w-full border mb-4">
                    <thead>
                        <tr>
                            <th colspan="3" class="bg-primary text-primary-foreground px-4 py-2 text-center text-lg">
                                Latin Honors Table
                            </th>
                        </tr>
                        <tr class="bg-primary text-primary-foreground">
                            <th class="border px-2 py-1">ID</th>
                            <th class="border px-2 py-1">Honor</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="even:bg-gray-200/60 hover:bg-primary/10 *:hover:bg-primary/10" v-for="honor in props.honors" :key="honor.id">
                            <td class="border px-2 py-1">{{ honor.id }}</td>
                            <td class="border px-2 py-1 w-full">
                                <template v-if="editHonorId === honor.id">
                                    <input v-model="editHonorForm.honor" type="text" class="border border-black rounded px-2 py-1 w-full" />
                                </template>
                                <template v-else>
                                    {{ honor.honor }}
                                </template>
                            </td>
                            <td class="border px-2 py-1">
                                <template v-if="editHonorId === honor.id">
                                    <div class="flex gap-2">
                                        <button @click="updateHonor(honor.id)" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-2 py-1 rounded">Save</button>
                                        <button @click="editHonorId = null" class="bg-gray-300 px-2 py-1 rounded">Cancel</button>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex gap-2">
                                        <button @click="startEditHonor(honor)" class="bg-accent2 text-accent2-foreground hover:bg-accent2-darken cursor-pointer px-2 py-1 rounded">Edit</button>
                                        <button @click="deleteHonor(honor.id)" class="bg-primary text-primary-foreground hover:bg-primary-hover cursor-pointer px-2 py-1 rounded">Delete</button>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Add Honor Form -->
                <form @submit.prevent="addHonor" class="flex gap-2 p-2">
                    <input required v-model="addHonorForm.honor" type="text" placeholder="Add new honor" class="border border-black rounded px-2 py-1 w-full" />
                    <button type="submit" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-4 py-1 rounded">Add</button>
                </form>
            </div>

            <!-- Name Suffixes Table -->
            <div class="flex flex-col flex-1 h-fit w-[350px] shadow-xl/30 bg-background border-2">
                <table class="w-full border mb-4">
                    <thead>
                        <tr>
                            <th colspan="3" class="bg-primary text-primary-foreground px-4 py-2 text-center text-lg">
                                Name Suffix Table
                            </th>
                        </tr>
                        <tr class="bg-primary text-primary-foreground">
                            <th class="border px-2 py-1">ID</th>
                            <th class="border px-2 py-1">Suffix</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="even:bg-gray-200/60 hover:bg-primary/10 *:hover:bg-primary/10" v-for="suffix in props.suffixes" :key="suffix.id">
                            <td class="border px-2 py-1">{{ suffix.id }}</td>
                            <td class="border px-2 py-1 w-full">
                                <template v-if="editSuffixId === suffix.id">
                                    <input v-model="editSuffixForm.suffix" type="text" class="border rounded px-2 py-1 w-full" />
                                </template>
                                <template v-else>
                                    {{ suffix.suffix }}
                                </template>
                            </td>
                            <td class="border px-2 py-1">
                                <div v-if="editSuffixId === suffix.id" class="flex gap-2">
                                    <button @click="updateSuffix(suffix.id)" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-2 py-1 rounded">Save</button>
                                    <button @click="editSuffixId = null" class="bg-gray-300 px-2 py-1 rounded">Cancel</button>
                                </div>
                                <div v-else class="flex gap-2">
                                    <button @click="startEditSuffix(suffix)" class="bg-accent2 text-accent2-foreground hover:bg-accent2-darken cursor-pointer px-2 py-1 rounded">Edit</button>
                                    <button @click="deleteSuffix(suffix.id)" class="bg-primary text-primary-foreground hover:bg-primary-hover cursor-pointer px-2 py-1 rounded">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Add Suffix Form -->
                <form @submit.prevent="addSuffix" class="flex gap-2 p-2">
                    <input required v-model="addSuffixForm.suffix" type="text" placeholder="Add new suffix" class="border border-black rounded px-2 py-1 w-full" />
                    <button type="submit" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-4 py-1 rounded">Add</button>
                </form>
            </div>

            <!-- Sexes Table -->
            <div class="flex flex-col flex-1 h-fit w-[350px] shadow-xl/30 bg-background border-2">
                <table class="w-full border mb-4">
                    <thead>
                        <tr>
                            <th colspan="3" class="bg-primary text-primary-foreground px-4 py-2 text-center text-lg">
                                Sex Table
                            </th>
                        </tr>
                        <tr class="bg-primary text-primary-foreground">
                            <th class="border px-2 py-1">ID</th>
                            <th class="border px-2 py-1">Sex</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="even:bg-gray-200/60 hover:bg-primary/10 *:hover:bg-primary/10" v-for="sex in props.sexes" :key="sex.id">
                            <td class="border px-2 py-1">{{ sex.id }}</td>
                            <td class="border px-2 py-1 w-full">
                                <template v-if="editSexId === sex.id">
                                    <input v-model="editSexForm.sex" type="text" class="border rounded px-2 py-1 w-full" />
                                </template>
                                <template v-else>
                                    {{ sex.sex }}
                                </template>
                            </td>
                            <td class="border px-2 py-1">
                                <template v-if="editSexId === sex.id">
                                    <div class="flex gap-2">
                                        <button @click="updateSex(sex.id)" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-2 py-1 rounded">Save</button>
                                        <button @click="editSexId = null" class="bg-gray-300 px-2 py-1 rounded">Cancel</button>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex gap-2">
                                        <button @click="startEditSex(sex)" class="bg-accent2 text-accent2-foreground hover:bg-accent2-darken cursor-pointer px-2 py-1 rounded">Edit</button>
                                        <button @click="deleteSex(sex.id)" class="bg-primary text-primary-foreground hover:bg-primary-hover cursor-pointer px-2 py-1 rounded">Delete</button>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Add Sex Form -->
                <form @submit.prevent="addSex" class="flex gap-2 p-2">
                    <input required v-model="addSexForm.sex" type="text" placeholder="Add new sex" class="border border-black rounded px-2 py-1 w-full" />
                    <button type="submit" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-4 py-1 rounded">Add</button>
                </form>
            </div>

            <!-- Student Types Table -->
            <div class="flex flex-col flex-1 h-fit w-[350px] shadow-xl/30 bg-background border-2">
                <table class="w-full border mb-4">
                    <thead>
                        <tr>
                            <th colspan="3" class="bg-primary text-primary-foreground px-4 py-2 text-center text-lg">
                                Student Type Table
                            </th>
                        </tr>
                        <tr class="bg-primary text-primary-foreground">
                            <th class="border px-2 py-1">ID</th>
                            <th class="border px-2 py-1">Type</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="even:bg-gray-200/60 hover:bg-primary/10 *:hover:bg-primary/10" v-for="type in props.studentTypes" :key="type.id">
                            <td class="border px-2 py-1">{{ type.id }}</td>
                            <td class="border px-2 py-1 w-full">
                                <template v-if="editStudentTypeId === type.id">
                                    <input v-model="editStudentTypeForm.type" type="text" class="border rounded px-2 py-1 w-full" />
                                </template>
                                <template v-else>
                                    {{ type.type }}
                                </template>
                            </td>
                            <td class="border px-2 py-1">
                                <template v-if="editStudentTypeId === type.id">
                                    <div class="flex gap-2">
                                        <button @click="updateStudentType(type.id)" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-2 py-1 rounded">Save</button>
                                        <button @click="editStudentTypeId = null" class="bg-gray-300 px-2 py-1 rounded">Cancel</button>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex gap-2">
                                        <button @click="startEditStudentType(type)" class="bg-accent2 text-accent2-foreground hover:bg-accent2-darken cursor-pointer px-2 py-1 rounded">Edit</button>
                                        <button @click="deleteStudentType(type.id)" class="bg-primary text-primary-foreground hover:bg-primary-hover cursor-pointer px-2 py-1 rounded">Delete</button>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Add Student Type Form -->
                <form @submit.prevent="addStudentType" class="flex gap-2 p-2">
                    <input required v-model="addStudentTypeForm.type" type="text" placeholder="Add new student type" class="border border-black rounded px-2 py-1 w-full" />
                    <button type="submit" class="bg-accent text-accent-foreground hover:bg-accent-hover cursor-pointer px-4 py-1 rounded">Add</button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
