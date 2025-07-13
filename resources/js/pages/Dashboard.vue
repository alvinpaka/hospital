<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import VitalsChart from '../components/VitalsChart.vue';
import LabResultsChart from '../components/LabResultsChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const page = usePage();
const user = computed(() => page.props.user);
const nextAppointment = computed(() => page.props.nextAppointment);
const recentVisit = computed(() => page.props.recentVisit);
const latestLabResult = computed(() => page.props.latestLabResult);
const currentPrescription = computed(() => page.props.currentPrescription);
const latestVitals = computed(() => page.props.latestVitals);
const vitalsTrend = computed(() => page.props.vitalsTrend || []);
const labResultsTrend = computed(() => page.props.labResultsTrend || []);

function getGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
}

function getCurrentDate() {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
            <div class="max-w-screen-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 rounded-3xl p-8 mb-8 shadow-2xl">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                    <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-white opacity-5 rounded-full blur-xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-4xl font-bold text-white">{{ getGreeting() }}, {{ user?.name }}</h1>
                                <p class="text-blue-100 text-lg">{{ getCurrentDate() }}</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            Welcome back! Here is your latest health overview.
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-2xl font-bold flex items-center gap-2">
                            <span class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </span>
                            Quick Actions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <Link href="/appointments" class="block group">
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ nextAppointment?.date ? 1 : 0 }}</div>
                                            <div class="text-blue-100 text-sm">Upcoming</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Appointments</div>
                                    <div class="text-blue-100 text-sm">View your schedule</div>
                                </div>
                            </Link>
                            <Link href="/visits" class="block group">
                                <div class="bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ recentVisit?.date ? 1 : 0 }}</div>
                                            <div class="text-green-100 text-sm">Recent</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Visits</div>
                                    <div class="text-green-100 text-sm">See your visit history</div>
                                </div>
                            </Link>
                            <Link href="/prescriptions" class="block group">
                                <div class="bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ currentPrescription?.medication ? 1 : 0 }}</div>
                                            <div class="text-purple-100 text-sm">Current</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Prescriptions</div>
                                    <div class="text-purple-100 text-sm">Your medications</div>
                                </div>
                            </Link>
                            <Link href="/vitals" class="block group">
                                <div class="bg-gradient-to-br from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ latestVitals?.blood_pressure ? 1 : 0 }}</div>
                                            <div class="text-orange-100 text-sm">Latest</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Vitals</div>
                                    <div class="text-orange-100 text-sm">Your vital signs</div>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Key Metrics -->
                <div class="grid gap-6 md:grid-cols-3 mb-8">
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                Next Appointment
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-blue-600 mb-2">{{ nextAppointment?.date || 'None' }}</div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4" v-if="nextAppointment">at {{ nextAppointment.time }}</p>
                            <div class="text-xs text-muted-foreground" v-if="nextAppointment">{{ nextAppointment.doctor?.name || nextAppointment.doctor }}</div>
                            <div class="text-xs text-muted-foreground" v-else>No upcoming appointments</div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                Recent Visit
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600 mb-2">{{ recentVisit?.date || 'None' }}</div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4" v-if="recentVisit">with {{ recentVisit.doctor?.name || recentVisit.doctor }}</p>
                            <div class="text-xs text-muted-foreground" v-if="recentVisit">{{ recentVisit.summary }}</div>
                            <div class="text-xs text-muted-foreground" v-else>No recent visits</div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Latest Lab Result
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-purple-600 mb-2" v-if="latestLabResult">{{ latestLabResult.type }}: {{ latestLabResult.result }}</div>
                            <div class="text-xs text-muted-foreground" v-if="latestLabResult">Date: {{ latestLabResult.date }}</div>
                            <div class="text-xs text-muted-foreground" v-else>No lab results</div>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-6 md:grid-cols-2 mb-8">
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                Current Prescription
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-orange-600 mb-2" v-if="currentPrescription">
                                {{ currentPrescription.drug?.generic_name || currentPrescription.medication }}
                                <span v-if="currentPrescription.dosage"> ({{ currentPrescription.dosage }})</span>
                            </div>
                            <div class="text-xs text-muted-foreground" v-if="currentPrescription">Prescribed by {{ currentPrescription.doctor?.name || currentPrescription.doctor }} on {{ currentPrescription.start_date }}</div>
                            <div class="text-xs text-muted-foreground" v-else>No prescriptions</div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-pink-500 to-pink-600 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                Latest Vitals
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-pink-600 mb-2" v-if="latestVitals">BP: {{ latestVitals.blood_pressure }}, HR: {{ latestVitals.heart_rate }}, Temp: {{ latestVitals.temperature }}°C</div>
                            <div class="text-xs text-muted-foreground" v-if="latestVitals">Date: {{ latestVitals.date || latestVitals.recorded_at }}</div>
                            <div class="text-xs text-muted-foreground" v-else>No vitals</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Graphs Section -->
                <div class="grid gap-6 md:grid-cols-2 mt-6">
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                Vitals Trend
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <VitalsChart :data="vitalsTrend" />
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Lab Results Trend
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <LabResultsChart :data="labResultsTrend" />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
