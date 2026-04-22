<script setup lang="ts">
import InputError from '@/components/InputError.vue';
// import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
// import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import Header from "@/components/Header.vue";


defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <Header />
        <div class="flex-grow flex items-center justify-center ">
            <AuthBase class="shadow-md/20 text-primary" title="Vend-Aid" description="Patient Prescription Management System">
                <Head title="Log in" />

                <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class=" text-foreground flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2 relative">
                            <!-- <Label for="email">Email address</Label> -->
                            <Input
                                class=" h-14 block px-2.5 pb-2 pt-4 w-full text-base rounded-lg border border-gray-300 
                                appearance-none focus:outline-none focus:ring-0 focus:border-primary peer"
                                id="email"
                                type="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder=""
                            />
                            <InputError :message="form.errors.email" />
                            <label 
                                for="email" 
                                class=" select-none font-medium cursor-text absolute text-base text-gray-500 duration-300 transform 
                                -translate-y-4 scale-80 top-4 z-10 origin-[0] px-2 peer-focus:px-2 
                                peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 
                                peer-focus:scale-80 peer-focus:-translate-y-4 left-1 peer-focus:font-semibold"
                            >
                                Email Address
                            </label>
                        </div>
                        
                        <div class="grid gap-2 relative">
                            <!-- <div class="flex items-center justify-between">
                                <Label for="password">Password</Label>
                                <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                                    Forgot password?
                                </TextLink>
                            </div> -->    
                            <Input
                                class=" h-14 block px-2.5 pb-2 pt-4 w-full text- rounded-lg border border-gray-300 
                                appearance-none focus:outline-none focus:ring-0 focus:border-primary peer"
                                id="password"
                                type="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder=""
                            />
                            <InputError :message="form.errors.password" />
                            <label 
                                for="password" 
                                class=" select-none font-medium cursor-text absolute text-base text-gray-500 duration-300 transform 
                                -translate-y-4 scale-80 top-4 z-10 origin-[0] px-2 peer-focus:px-2 
                                peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 
                                peer-focus:scale-80 peer-focus:-translate-y-4 left-1 peer-focus:font-semibold peer-autofill:scale-80"
                            >
                                Password
                            </label>
                        </div>

                        <!-- <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3">
                                <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                                <span>Remember me</span>
                            </Label>
                        </div> -->

                        <Button type="submit" class="w-full bg-primary" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Log in
                        </Button>
                    </div>

                    <div class=" text-left text-sm text-muted-foreground select-none">
                        Don't have an account?
                        <Link :href="route('register')">
                            <Button type="button" class="w-full" variant="outline" :tabindex="4">
                                Signup
                            </Button>
                        </Link>
                        <!-- <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink> -->
                    </div>
                </form>
            </AuthBase>
        </div>
    </div>
</template>
