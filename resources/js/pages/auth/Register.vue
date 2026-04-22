<script setup lang="ts">
import InputError from '@/components/InputError.vue';
// import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import Header from "@/components/Header.vue";

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <Header class=" sticky top-0" />
        <div class="flex-grow flex items-center justify-center ">
            <AuthBase class="shadow-md/20 text-primary" title="Create an account" description="Enter your details below to create your account">
                <Head title="Register" />

                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <!-- <Label for="name">Name</Label> -->
                            <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <!-- <Label for="email">Email address</Label> -->
                            <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <!-- <Label for="password">Password</Label> -->
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="3"
                                autocomplete="new-password"
                                v-model="form.password"
                                placeholder="Password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <!-- <Label for="password_confirmation">Confirm password</Label> -->
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Confirm password"
                            />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Create account
                        </Button>
                    </div>

                    <!-- <div class="text-center text-sm text-muted-foreground">
                        Already have an account?
                        <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
                    </div> -->
                    <div class=" text-left text-sm text-muted-foreground select-none">
                        Already have an account?
                        <Link :href="route('login')">
                            <Button type="button" class="w-full" variant="outline" :tabindex="4">
                                Log in
                            </Button>
                        </Link>
                        <!-- <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink> -->
                    </div>
                </form>
            </AuthBase>
            
        </div>
    </div>
</template>
