<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-white to-blue-200 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 p-6 lg:p-8">
    <div class="w-full max-w-6xl mx-auto">
      <!-- Gradient Header -->
      <div class="rounded-t-2xl px-8 py-8 text-center bg-gradient-to-r from-blue-500 via-sky-400 to-cyan-400 dark:from-blue-900 dark:via-blue-800 dark:to-cyan-900 shadow-lg">
        <div class="mb-2 inline-block rounded-full bg-blue-200 px-4 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-200">For Patients & Healthcare Professionals</div>
        <h1 class="mb-3 text-3xl font-bold text-white tracking-tight">Welcome to Medix Hospital Management System</h1>
        <p class="mb-2 text-blue-100 dark:text-blue-200 font-light max-w-2xl mx-auto">
          Empowering patients and healthcare teams to manage appointments, access medical records, and streamline clinic operations—all in one secure platform.
        </p>
      </div>
      <!-- Card -->
      <div class="bg-white dark:bg-slate-900 shadow-xl rounded-b-2xl px-8 py-10 flex flex-col lg:flex-row gap-0 lg:gap-12">
        <div class="flex-1 flex flex-col justify-center">
          <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2">
            <!-- Patient Records -->
            <div class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-blue-950 cursor-pointer">
              <span class="inline-flex items-center justify-center rounded-full bg-blue-600 p-2">
                <!-- Medical file SVG -->
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><path d="M7 3h6l4 4v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" fill="#1976d2"/><path d="M13 3v5h5" fill="#1976d2"/><path d="M12 11v4m2-2h-4" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
              </span>
              <div>
                <div class="font-semibold text-base text-blue-700 dark:text-blue-200">Patient Records</div>
                <div class="text-xs text-slate-600 dark:text-slate-300">Patients and staff can securely view and manage medical records online.</div>
              </div>
            </div>
            <!-- Appointments -->
            <div class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-blue-950 cursor-pointer">
              <span class="inline-flex items-center justify-center rounded-full bg-green-600 p-2">
                <!-- Calendar SVG -->
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><rect x="4" y="5" width="16" height="16" rx="2" fill="#43a047"/><path d="M8 2v4M16 2v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><rect x="4" y="9" width="16" height="2" fill="#fff"/></svg>
              </span>
              <div>
                <div class="font-semibold text-base text-blue-700 dark:text-blue-200">Appointments</div>
                <div class="text-xs text-slate-600 dark:text-slate-300">Book, schedule, and track appointments—patients and staff, all in one place.</div>
              </div>
            </div>
            <!-- Billing & Invoicing -->
            <div class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-blue-950 cursor-pointer">
              <span class="inline-flex items-center justify-center rounded-full bg-yellow-400 p-2">
                <!-- Billing SVG -->
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><rect x="4" y="5" width="16" height="16" rx="2" fill="#ffb300"/><path d="M8 13h8M8 17h8M8 9h8" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
              </span>
              <div>
                <div class="font-semibold text-base text-blue-700 dark:text-blue-200">Billing & Invoicing</div>
                <div class="text-xs text-slate-600 dark:text-slate-300">Easily manage payments and billing for patients and clinics.</div>
              </div>
            </div>
            <!-- Analytics & Reports -->
            <div class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-blue-950 cursor-pointer">
              <span class="inline-flex items-center justify-center rounded-full bg-sky-700 p-2">
                <!-- Analytics SVG -->
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><path d="M7 17v-4m4 4V7m4 10v-2" stroke="#0288d1" stroke-width="2" stroke-linecap="round"/></svg>
              </span>
              <div>
                <div class="font-semibold text-base text-blue-700 dark:text-blue-200">Analytics & Reports</div>
                <div class="text-xs text-slate-600 dark:text-slate-300">Gain insights with real-time analytics and reports for patients and staff.</div>
              </div>
            </div>
          </div>
          <!-- Login/Register for mobile (visible on mobile, hidden on lg+) -->
          <div class="flex flex-col items-center w-full mt-8 lg:hidden">
            <div class="mb-4 text-lg font-semibold text-blue-700 dark:text-blue-200 text-center">Ready to get started?</div>
            <div class="flex flex-col gap-4 w-full">
              <Link
                :href="route('login')"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-700 bg-blue-700 min-w-[140px] px-6 py-2 text-base font-semibold leading-normal text-white shadow-md hover:scale-105 hover:bg-blue-800 hover:border-blue-800 transition-all duration-200 dark:border-blue-200 dark:bg-blue-200 dark:text-slate-900 dark:hover:bg-blue-400 dark:hover:border-blue-400"
              >
                <span>Log in</span>
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </Link>
              <Link
                :href="route('register')"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-700 min-w-[140px] px-6 py-2 text-base font-semibold leading-normal text-blue-700 bg-white shadow-md hover:scale-105 hover:bg-blue-50 hover:border-blue-800 transition-all duration-200 dark:border-blue-200 dark:text-blue-200 dark:bg-slate-900 dark:hover:bg-slate-800 dark:hover:border-blue-400"
              >
                <span>Register</span>
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M19 8v6M22 11h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </Link>
            </div>
          </div>
        </div>
        <!-- Divider for large screens -->
        <div class="hidden lg:block w-px bg-blue-100 dark:bg-blue-950 mx-2"></div>
        <!-- Medical Illustration and login/register for desktop (hidden on mobile) -->
        <div class="hidden lg:flex flex-col w-[480px] items-center justify-center bg-blue-50 p-8 lg:px-16 dark:bg-blue-950 rounded-xl">
          <!-- Hospital SVG illustration (undraw style) -->
          <svg width="220" height="160" viewBox="0 0 320 240" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="40" y="80" width="240" height="120" rx="16" fill="#fff" stroke="#1976d2" stroke-width="3"/>
            <rect x="80" y="120" width="40" height="80" rx="8" fill="#e3f2fd" stroke="#1976d2" stroke-width="2"/>
            <rect x="200" y="120" width="40" height="80" rx="8" fill="#e3f2fd" stroke="#1976d2" stroke-width="2"/>
            <rect x="140" y="100" width="40" height="100" rx="8" fill="#bbdefb" stroke="#1976d2" stroke-width="2"/>
            <rect x="150" y="140" width="20" height="40" rx="4" fill="#fff" stroke="#1976d2" stroke-width="2"/>
            <rect x="155" y="155" width="10" height="10" rx="2" fill="#1976d2"/>
            <rect x="120" y="90" width="80" height="20" rx="6" fill="#1976d2"/>
            <rect x="145" y="95" width="30" height="10" rx="2" fill="#fff"/>
            <circle cx="160" cy="60" r="24" fill="#fff" stroke="#1976d2" stroke-width="3"/>
            <rect x="152" y="52" width="16" height="16" rx="4" fill="#1976d2"/>
            <rect x="156" y="56" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="158" y="68" width="4" height="8" rx="2" fill="#1976d2"/>
          </svg>
          <div class="mt-10 flex flex-col items-center w-full">
            <div class="mb-4 text-lg font-semibold text-blue-700 dark:text-blue-200 text-center">Ready to get started?</div>
            <div class="flex flex-row items-center justify-center gap-6 w-full">
              <Link
                :href="route('login')"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-700 bg-blue-700 min-w-[140px] px-6 py-2 text-base font-semibold leading-normal text-white shadow-md hover:scale-105 hover:bg-blue-800 hover:border-blue-800 transition-all duration-200 dark:border-blue-200 dark:bg-blue-200 dark:text-slate-900 dark:hover:bg-blue-400 dark:hover:border-blue-400"
              >
                <span>Log in</span>
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </Link>
              <Link
                :href="route('register')"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-700 min-w-[140px] px-6 py-2 text-base font-semibold leading-normal text-blue-700 bg-white shadow-md hover:scale-105 hover:bg-blue-50 hover:border-blue-800 transition-all duration-200 dark:border-blue-200 dark:text-blue-200 dark:bg-slate-900 dark:hover:bg-slate-800 dark:hover:border-blue-400"
              >
                <span>Register</span>
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M19 8v6M22 11h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
