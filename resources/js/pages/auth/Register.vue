<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
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
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-white to-blue-200 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="w-full max-w-xl mx-auto">
      <!-- Gradient Header -->
      <div class="rounded-t-2xl px-8 py-6 text-center bg-gradient-to-r from-blue-500 via-sky-400 to-cyan-400 dark:from-blue-900 dark:via-blue-800 dark:to-cyan-900 shadow-lg">
        <h1 class="text-2xl font-bold text-white tracking-tight">Create an account</h1>
        <p class="text-sm text-blue-100 mt-1">Enter your details below to create your account</p>
      </div>
      <!-- Card -->
      <div class="bg-white dark:bg-slate-900 shadow-xl rounded-b-2xl px-8 py-8">
        <Head title="Register" />
        <div class="flex flex-col items-center mb-6">
          <span class="inline-block rounded-full bg-gradient-to-r from-blue-200 via-sky-200 to-cyan-200 dark:from-blue-950 dark:via-blue-900 dark:to-cyan-950 px-4 py-1 text-xs font-semibold text-blue-700 dark:text-blue-200 shadow">Patient Registration Only</span>
        </div>
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
          <div class="flex flex-col gap-4 mt-4">
            <Button type="submit" class="w-full" tabindex="5" :disabled="form.processing">
              <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
              Create account
            </Button>
            <div class="border-t border-muted my-2"></div>
            <div class="text-center text-sm text-muted-foreground">
              Already have an account?
              <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
