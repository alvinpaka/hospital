<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Staff Dashboard',
        href: '/staff/dashboard',
    },
];

const page = usePage();
const user = computed(() => page.props.user);
const todayAppointments = computed(() => page.props.todayAppointments || []);
const recentVisits = computed(() => page.props.recentVisits || []);
const recentPrescriptions = computed(() => page.props.recentPrescriptions || []);
const stats = computed(() => page.props.stats || {});
const patientCount = computed(() => page.props.patientCount || 0);

function formatDate(dateStr: string) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {            
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatTime(timeStr: string) {
    if (!timeStr) return '';
    return timeStr;
}



function getStatusColor(status: string) {
    const colors = {
        'confirmed': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'canceled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'completed': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
}

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
    <Head title="Staff Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
            <div class="max-w-screen-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <!-- Enhanced Header with Gradient -->
                <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 rounded-3xl p-8 mb-8 shadow-2xl">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                    <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-white opacity-5 rounded-full blur-xl"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-4">
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
                            <div class="flex items-center gap-3">
                                <Badge class="bg-white bg-opacity-20 text-white border-white border-opacity-30 text-lg px-4 py-2">
                                    {{ user?.role === 'doctor' ? '👨‍⚕️ Doctor' : '👩‍⚕️ Nurse' }}
                                </Badge>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            You have <span class="font-semibold text-white">{{ todayAppointments.length }}</span> appointments today
                        </p>
                    </div>
                </div>

                <!-- Enhanced Quick Actions with Hover Effects -->
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
                            <Link href="/staff/appointments" class="block group">
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ todayAppointments.length }}</div>
                                            <div class="text-blue-100 text-sm">Today</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Appointments</div>
                                    <div class="text-blue-100 text-sm">Manage your schedule</div>
                                </div>
                            </Link>
                            
                            <Link href="/staff/patients" class="block group">
                                <div class="bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ patientCount }}</div>
                                            <div class="text-green-100 text-sm">Active</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">My Patients</div>
                                    <div class="text-green-100 text-sm">View patient records</div>
                                </div>
                            </Link>
                            
                            <Link href="/staff/prescriptions" class="block group">
                                <div class="bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ stats.totalPrescriptions || 0 }}</div>
                                            <div class="text-purple-100 text-sm">Total</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Prescriptions</div>
                                    <div class="text-purple-100 text-sm">Manage medications</div>
                                </div>
                            </Link>
                            
                            <Link href="/staff/vitals" class="block group">
                                <div class="bg-gradient-to-br from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-2xl p-6 text-white transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ stats.totalVisits || 0 }}</div>
                                            <div class="text-orange-100 text-sm">This Month</div>
                                        </div>
                                    </div>
                                    <div class="font-semibold">Record Vitals</div>
                                    <div class="text-orange-100 text-sm">Patient vital signs</div>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Enhanced Key Metrics -->
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
                                Today's Schedule
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold text-blue-600 mb-2">{{ todayAppointments.length }}</div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Appointments scheduled</p>
                            <div v-if="stats.pendingAppointments > 0" class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></div>
                                <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">{{ stats.pendingAppointments }} pending approval</span>
                            </div>
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
                                Active Patients
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold text-green-600 mb-2">{{ patientCount }}</div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Under your care</p>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">{{ stats.thisMonthAppointments || 0 }} appointments this month</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2"></div>
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                Recent Activity
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold text-purple-600 mb-2">{{ stats.totalVisits || 0 }}</div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Patient visits this month</p>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-purple-400 rounded-full"></div>
                                <span class="text-sm text-purple-600 dark:text-purple-400 font-medium">{{ stats.totalPrescriptions || 0 }} prescriptions written</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Enhanced Today's Focus -->
                <div class="grid gap-8 lg:grid-cols-2 mb-8">
                    <!-- Today's Appointments -->
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-2xl font-bold flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span>Today's Schedule</span>
                                </div>
                                <Link href="/staff/appointments" class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                    View All →
                                </Link>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="todayAppointments.length === 0" class="text-center py-12">
                                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">No appointments today</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enjoy your free time! 🎉</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="appointment in todayAppointments.slice(0, 5)" :key="appointment.id" 
                                     class="group p-4 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-lg transition-all duration-300 bg-white dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold">
                                                {{ appointment.patient?.name?.charAt(0) || 'P' }}
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-lg">{{ appointment.patient?.name || 'Unknown Patient' }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ formatTime(appointment.time) }}
                                                </div>
                                                <div v-if="appointment.notes" class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ appointment.notes }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span :class="getStatusColor(appointment.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                                {{ appointment.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Activity -->
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-2xl font-bold flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <span>Recent Activity</span>
                                </div>
                                <div class="flex gap-3">
                                    <Link href="/staff/visits" class="text-sm text-green-600 hover:text-green-800 font-medium hover:underline">Visits</Link>
                                    <Link href="/staff/prescriptions" class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">Prescriptions</Link>
                                </div>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-6">
                                <!-- Recent Visits -->
                                <div v-if="recentVisits.length > 0" class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-semibold text-green-600 dark:text-green-400">Recent Visits</h4>
                                    </div>
                                    <div v-for="visit in recentVisits.slice(0, 3)" :key="visit.id" 
                                         class="p-3 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium">{{ visit.patient?.name || 'Unknown Patient' }}</div>
                                                <div class="text-sm text-green-600 dark:text-green-400">{{ formatDate(visit.date) }}</div>
                                            </div>
                                            <div class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recent Prescriptions -->
                                <div v-if="recentPrescriptions.length > 0" class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-semibold text-purple-600 dark:text-purple-400">Recent Prescriptions</h4>
                                    </div>
                                    <div v-for="prescription in recentPrescriptions.slice(0, 3)" :key="prescription.id" 
                                         class="p-3 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium">{{ prescription.patient?.name || 'Unknown Patient' }}</div>
                                                <div class="text-sm text-purple-600 dark:text-purple-400">{{ prescription.drug?.name || 'Medication' }}</div>
                                            </div>
                                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="recentVisits.length === 0 && recentPrescriptions.length === 0" class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-lg font-medium text-gray-600 dark:text-gray-300">No recent activity</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Activity will appear here once you start working</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Enhanced Quick Access with Modern Design -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
                    <CardHeader class="pb-6">
                        <CardTitle class="text-2xl font-bold flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span>Quick Access</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <Link href="/staff/appointments" class="block group">
                                <div class="relative p-6 rounded-2xl border-2 border-dashed border-blue-200 dark:border-blue-800 hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 text-center overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10">
                                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-lg mb-2">Appointments</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">Manage your schedule</div>
                                        <div class="mt-3 inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 text-sm font-medium">
                                            View All
                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                            
                            <Link href="/staff/patients" class="block group">
                                <div class="relative p-6 rounded-2xl border-2 border-dashed border-green-200 dark:border-green-800 hover:border-green-400 dark:hover:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-300 text-center overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10">
                                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-lg mb-2">Patients</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">View patient records</div>
                                        <div class="mt-3 inline-flex items-center gap-1 text-green-600 dark:text-green-400 text-sm font-medium">
                                            View All
                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                            
                            <Link href="/staff/prescriptions" class="block group">
                                <div class="relative p-6 rounded-2xl border-2 border-dashed border-purple-200 dark:border-purple-800 hover:border-purple-400 dark:hover:border-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all duration-300 text-center overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10">
                                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-lg mb-2">Prescriptions</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">Manage medications</div>
                                        <div class="mt-3 inline-flex items-center gap-1 text-purple-600 dark:text-purple-400 text-sm font-medium">
                                            View All
                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                            
                            <Link href="/staff/vitals" class="block group">
                                <div class="relative p-6 rounded-2xl border-2 border-dashed border-orange-200 dark:border-orange-800 hover:border-orange-400 dark:hover:border-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-all duration-300 text-center overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-red-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10">
                                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-lg mb-2">Record Vitals</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">Patient vital signs</div>
                                        <div class="mt-3 inline-flex items-center gap-1 text-orange-600 dark:text-orange-400 text-sm font-medium">
                                            View All
                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>