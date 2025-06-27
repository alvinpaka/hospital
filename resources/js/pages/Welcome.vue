<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#f6fbfd] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">   
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0 flex-col lg:flex-row lg:gap-12">
            <main class="flex w-full max-w-[335px] flex-col overflow-hidden rounded-lg lg:max-w-6xl lg:flex-row bg-white shadow-lg dark:bg-[#161c24]">
                <div class="flex-1 rounded-br-lg rounded-bl-lg bg-[#fafdff] p-8 pb-12 text-[15px] leading-[22px] lg:rounded-tl-lg lg:rounded-br-none lg:px-16 lg:py-14 dark:bg-[#181c20] dark:text-[#EDEDEC]">
                    <div class="mb-2 inline-block rounded-full bg-[#e3f2fd] px-4 py-1 text-xs font-semibold text-[#1976d2] dark:bg-[#22304a] dark:text-[#90caf9]">For Patients & Healthcare Professionals</div>
                    <h1 class="mb-3 text-3xl font-bold text-[#1976d2] dark:text-[#90caf9]">Welcome to Medix Hospital Management System</h1>
                    <p class="mb-10 text-[#4f5b62] dark:text-[#A1A09A] font-light">
                        Empowering patients and healthcare teams to manage appointments, access medical records, and streamline clinic operations—all in one secure platform.
                    </p>
                    <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2">
                        <!-- Patient Records -->
                        <div class="flex items-center gap-3 rounded-xl bg-[#e3f2fd] p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-[#22304a] cursor-pointer">
                            <span class="inline-flex items-center justify-center rounded-full bg-[#1976d2] p-2">
                                <!-- Medical file SVG -->
                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><path d="M7 3h6l4 4v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" fill="#1976d2"/><path d="M13 3v5h5" fill="#1976d2"/><path d="M12 11v4m2-2h-4" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-base text-[#1976d2] dark:text-[#90caf9]">Patient Records</div>
                                <div class="text-xs text-[#4f5b62] dark:text-[#b0bec5]">Patients and staff can securely view and manage medical records online.</div>
                            </div>
                        </div>
                        <!-- Appointments -->
                        <div class="flex items-center gap-3 rounded-xl bg-[#e3f2fd] p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-[#22304a] cursor-pointer">
                            <span class="inline-flex items-center justify-center rounded-full bg-[#43a047] p-2">
                                <!-- Calendar SVG -->
                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><rect x="4" y="5" width="16" height="16" rx="2" fill="#43a047"/><path d="M8 2v4M16 2v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><rect x="4" y="9" width="16" height="2" fill="#fff"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-base text-[#1976d2] dark:text-[#90caf9]">Appointments</div>
                                <div class="text-xs text-[#4f5b62] dark:text-[#b0bec5]">Book, schedule, and track appointments—patients and staff, all in one place.</div>
                            </div>
                        </div>
                        <!-- Billing & Invoicing -->
                        <div class="flex items-center gap-3 rounded-xl bg-[#e3f2fd] p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-[#22304a] cursor-pointer">
                            <span class="inline-flex items-center justify-center rounded-full bg-[#ffb300] p-2">
                                <!-- Billing SVG -->
                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><rect x="4" y="5" width="16" height="16" rx="2" fill="#ffb300"/><path d="M8 13h8M8 17h8M8 9h8" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-base text-[#1976d2] dark:text-[#90caf9]">Billing & Invoicing</div>
                                <div class="text-xs text-[#4f5b62] dark:text-[#b0bec5]">Easily manage payments and billing for patients and clinics.</div>
                            </div>
                        </div>
                        <!-- Analytics & Reports -->
                        <div class="flex items-center gap-3 rounded-xl bg-[#e3f2fd] p-3 shadow transition-all duration-200 hover:scale-105 hover:shadow-lg dark:bg-[#22304a] cursor-pointer">
                            <span class="inline-flex items-center justify-center rounded-full bg-[#0288d1] p-2">
                                <!-- Analytics SVG -->
                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="12" fill="#fff"/><path d="M7 17v-4m4 4V7m4 10v-2" stroke="#0288d1" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-base text-[#1976d2] dark:text-[#90caf9]">Analytics & Reports</div>
                                <div class="text-xs text-[#4f5b62] dark:text-[#b0bec5]">Gain insights with real-time analytics and reports for patients and staff.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Divider for large screens -->
                <div class="hidden lg:block w-px bg-[#e3e8ee] dark:bg-[#23272e] mx-2"></div>
                <!-- Medical Illustration -->
                <div class="hidden lg:flex flex-col w-[480px] items-center justify-center bg-[#e3f2fd] p-8 lg:px-16 dark:bg-[#22304a]">
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
                        <div class="mb-4 text-lg font-semibold text-[#1976d2] dark:text-[#90caf9] text-center">Ready to get started?</div>
                        <div class="flex flex-row items-center justify-center gap-6 w-full">
                            <Link
                                :href="route('login')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#1976d2] bg-[#1976d2] min-w-[140px] px-6 py-2 text-base font-semibold leading-normal text-white shadow-md hover:scale-105 hover:bg-[#1565c0] hover:border-[#1565c0] transition-all duration-200 dark:border-[#90caf9] dark:bg-[#90caf9] dark:text-[#0a1929] dark:hover:bg-[#64b5f6] dark:hover:border-[#64b5f6]"
                            >
                                <span>Log in</span>
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </Link>
                            <Link
                                :href="route('register')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#1976d2] min-w-[140px] px-6 py-2 text-base font-semibold leading-normal text-[#1976d2] bg-white shadow-md hover:scale-105 hover:bg-[#e3f2fd] hover:border-[#1565c0] transition-all duration-200 dark:border-[#90caf9] dark:text-[#90caf9] dark:bg-[#161c24] dark:hover:bg-[#1e293b] dark:hover:border-[#64b5f6]"
                            >
                                <span>Register</span>
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M19 8v6M22 11h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
