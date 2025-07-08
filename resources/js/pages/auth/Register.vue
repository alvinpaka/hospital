<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const form = useForm({
    name: '',
    email: '',
    country_code: '+256',
    phone: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <div class="flex flex-col items-center min-h-screen justify-center py-8 bg-background">
            <div class="mb-6 inline-block rounded-full bg-[#e3f2fd] px-4 py-1 text-xs font-semibold text-[#1976d2] dark:bg-[#22304a] dark:text-[#90caf9]">Patient Registration Only</div>

            <Card class="w-full max-w-xl shadow-lg">
                <CardHeader class="text-center pb-2">
                    <CardTitle>Create an account</CardTitle>
                    <CardDescription>Enter your details below to create your account</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div class="flex flex-col gap-6">
                                <div class="flex flex-col gap-2">
                                    <Label for="name">Name</Label>
                                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" class="w-full" />
                                    <InputError :message="form.errors.name" />
                                </div>
                            </div>
                            <div class="flex flex-col gap-6">
                                <div class="flex flex-col gap-2">
                                    <Label for="email">Email address</Label>
                                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" class="w-full" />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 md:col-span-2">
                                <Label for="phone">Phone number</Label>
                                <div class="flex gap-2">
                                    <Select v-model="form.country_code">
                                        <SelectTrigger class="w-28">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="+256">🇺🇬 +256 </SelectItem>
                                            <SelectItem value="+254">🇰🇪 +254 </SelectItem>
                                            <SelectItem value="+255">🇹🇿 +255 </SelectItem>
                                            <SelectItem value="+250">🇷🇼 +250 </SelectItem>
                                            <SelectItem value="+211">🇸🇸 +211 </SelectItem>
                                            <SelectItem value="+243">🇨🇩 +243 </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        required
                                        :tabindex="2"
                                        autocomplete="tel"
                                        v-model="form.phone"
                                        placeholder="712345678"
                                        class="flex-1"
                                    />
                                </div>
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div class="flex flex-col gap-2">
                                <Label for="password">Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        required
                                        :tabindex="3"
                                        autocomplete="new-password"
                                        v-model="form.password"
                                        placeholder="Password"
                                        class="w-full"
                                    />
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" tabindex="-1">
                                        <Eye v-if="!showPassword" class="w-5 h-5" />
                                        <EyeOff v-else class="w-5 h-5" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <Label for="password_confirmation">Confirm password</Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        :type="showPasswordConfirm ? 'text' : 'password'"
                                        required
                                        :tabindex="4"
                                        autocomplete="new-password"
                                        v-model="form.password_confirmation"
                                        placeholder="Confirm password"
                                        class="w-full"
                                    />
                                    <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" tabindex="-1">
                                        <Eye v-if="!showPasswordConfirm" class="w-5 h-5" />
                                        <EyeOff v-else class="w-5 h-5" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                        <CardFooter class="flex flex-col gap-4 mt-4">
                            <Button type="submit" class="w-full" tabindex="5" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                Create account
                            </Button>
                            <Separator />
                            <div class="text-center text-sm text-muted-foreground">
                                Already have an account?
                                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
                            </div>
                        </CardFooter>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthBase>
</template>
