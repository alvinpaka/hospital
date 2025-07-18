<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import AppointmentTrendChart from '@/components/AppointmentTrendChart.vue';
import PatientDemographicsChart from '@/components/PatientDemographicsChart.vue';
import StaffPerformanceChart from '@/components/StaffPerformanceChart.vue';
import BillingAnalyticsChart from '@/components/BillingAnalyticsChart.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
];

const page = usePage();

// Add interfaces for all relevant data structures
interface User {
  id: number;
  name: string;
}

interface Patient {
  id: number;
  name: string;
}

interface Doctor {
  id: number;
  name: string;
}

interface Appointment {
  id: number;
  patient: Patient | string;
  doctor?: Doctor | string | null;
  status: string;
  time: string;
}

interface LabResult {
  id: number;
  patient: Patient | string;
  type: string;
  result: string;
  date: string;
}

interface Prescription {
  id: number;
  patient: Patient | string;
  medication: string;
  dosage: string;
  start_date: string;
}

interface Activity {
  id: number;
  action: string;
  user?: User | null;
  created_at: string;
}

interface Stats {
  totalVisits: number;
  totalLabResults: number;
  totalPrescriptions: number;
  totalVitals: number;
}

interface AppointmentAnalytics {
  total: number;
  thisMonth: number;
  avgPerDay: number;
  confirmed: number;
  pending: number;
  canceled: number;
}

interface StaffPerformance {
  doctors: number;
  activeDoctors: number;
  nurses: number;
  admins: number;
}

interface MedicalRecordsAnalytics {
  visits: { total: number; thisMonth: number };
  labResults: { total: number; thisMonth: number };
  prescriptions: { total: number; thisMonth: number };
  vitals: { total: number; thisMonth: number };
}

interface TopMetrics {
  busiestDay?: { date: string; count: number };
  mostActiveDoctor?: { doctor: Doctor | string; count: number };
}

interface Trend {
  month: string;
  appointments: number;
  visits: number;
  newPatients: number;
}

interface Analytics {
  appointmentAnalytics?: AppointmentAnalytics;
  staffPerformance?: StaffPerformance;
  medicalRecordsAnalytics?: MedicalRecordsAnalytics;
  topMetrics?: TopMetrics;
  trends?: Trend[];
  billingTrends?: any;
  patientDemographics?: any;
}

// Use these interfaces in computed properties
const user = computed<User | null>(() => {
  const u = page.props.user;
  if (u && typeof u === 'object' && 'id' in u && 'name' in u) {
    return u as User;
  }
  return null;
});

const stats = computed<Stats | null>(() => {
  const s = page.props.stats;
  if (s && typeof s === 'object' && 'totalVisits' in s && 'totalLabResults' in s && 'totalPrescriptions' in s && 'totalVitals' in s) {
    return s as Stats;
  }
  return null;
});

const recentActivities = computed<Activity[]>(() => {
  const arr = page.props.recentActivities;
  if (Array.isArray(arr)) {
    return arr.filter(a => a && typeof a === 'object' && 'id' in a && 'action' in a && 'created_at' in a);
  }
  return [];
});

const todayAppointments = computed<Appointment[]>(() => {
  const arr = page.props.todayAppointments;
  if (Array.isArray(arr)) {
    return arr.filter(a => a && typeof a === 'object' && 'id' in a && 'status' in a && 'time' in a);
  }
  return [];
});

const recentLabResults = computed<LabResult[]>(() => {
  const arr = page.props.recentLabResults;
  if (Array.isArray(arr)) {
    return arr.filter(r => r && typeof r === 'object' && 'id' in r && 'type' in r && 'result' in r && 'date' in r);
  }
  return [];
});

const recentPrescriptions = computed<Prescription[]>(() => {
  const arr = page.props.recentPrescriptions;
  if (Array.isArray(arr)) {
    return arr.filter(p => p && typeof p === 'object' && 'id' in p && 'medication' in p && 'dosage' in p && 'start_date' in p);
  }
  return [];
});
const analytics = computed<Analytics | null>(() => {
  const a = page.props.analytics;
  if (a && typeof a === 'object') {
    return a as Analytics;
  }
  return null;
});



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
    return new Date(`2000-01-01T${timeStr}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
}



</script>

<template>
    <Head title="Admin Dashboard" />
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-4xl font-bold text-white">Welcome, {{ user?.name }}</h1>
                                <p class="text-blue-100 text-lg">Admin Dashboard</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            Here are your hospital's latest stats and analytics.
                        </p>
                    </div>
                </div>

                <!-- Quick Stats Row -->
                <div class="grid gap-6 md:grid-cols-3 mb-8">
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2"></div>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                Medical Records
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm">Total Visits</span>
                                <span class="font-medium">{{ stats?.totalVisits ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Lab Results</span>
                                <span class="font-medium">{{ stats?.totalLabResults ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Prescriptions</span>
                                <span class="font-medium">{{ stats?.totalPrescriptions ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Vitals Records</span>
                                <span class="font-medium">{{ stats?.totalVitals ?? 0 }}</span>
                            </div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2"></div>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                Today's Appointments
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="todayAppointments?.length > 0" class="space-y-3">
                                <div v-for="appointment in todayAppointments" :key="appointment.id" class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium">{{ typeof appointment.patient === 'object' && appointment.patient !== null ? appointment.patient.name : appointment.patient || 'Unknown Patient' }}</div>
                                        <div class="text-xs text-muted-foreground">{{ formatTime(appointment.time) }} - {{ typeof appointment.doctor === 'object' && appointment.doctor !== null ? appointment.doctor.name : appointment.doctor || 'No doctor assigned' }}</div>
                                    </div>
                                    <Badge :variant="appointment.status === 'confirmed' ? 'default' : 'secondary'">
                                        {{ appointment.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">No appointments today</div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-2"></div>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Recent Activities
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentActivities?.length > 0" class="space-y-3">
                                <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start gap-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    <div class="flex-1">
                                        <div class="text-sm">{{ activity.action }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ typeof activity.user === 'object' && activity.user !== null ? activity.user.name : activity.user || 'System' }} • {{ formatDate(activity.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">No recent activities</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Data Section -->
                <div class="grid gap-6 md:grid-cols-2 mb-8">
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2"></div>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                                Recent Lab Results
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentLabResults?.length > 0" class="space-y-3">
                                <div v-for="result in recentLabResults" :key="result.id" class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium">{{ typeof result.patient === 'object' && result.patient !== null ? result.patient.name : result.patient || 'Unknown Patient' }}</div>
                                        <div class="text-xs text-muted-foreground">{{ result.type }}: {{ result.result }}</div>
                                    </div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(result.date) }}</div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">No recent lab results</div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2"></div>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Recent Prescriptions
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentPrescriptions?.length > 0" class="space-y-3">
                                <div v-for="prescription in recentPrescriptions" :key="prescription.id" class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium">{{ typeof prescription.patient === 'object' && prescription.patient !== null ? prescription.patient.name : prescription.patient || 'Unknown Patient' }}</div>
                                        <div class="text-xs text-muted-foreground">{{ prescription.medication }} ({{ prescription.dosage }})</div>
                                    </div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(prescription.start_date) }}</div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">No recent prescriptions</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Analytics & Reports Section -->
                <div class="space-y-8">
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Patient Demographics</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <PatientDemographicsChart :data="analytics?.patientDemographics" />
                        </CardContent>
                    </Card>
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">Appointment Analytics</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <!-- ... existing analytics content ... -->
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Total Appointments</span>
                                        <span class="font-bold">{{ analytics?.appointmentAnalytics?.total ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">This Month</span>
                                        <span class="font-bold text-blue-600">{{ analytics?.appointmentAnalytics?.thisMonth ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Avg. Per Day</span>
                                        <span class="font-bold text-green-600">{{ Math.round(analytics?.appointmentAnalytics?.avgPerDay ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Confirmed</span>
                                        <span class="font-bold text-green-600">{{ analytics?.appointmentAnalytics?.confirmed ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Pending</span>
                                        <span class="font-bold text-orange-600">{{ analytics?.appointmentAnalytics?.pending ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Canceled</span>
                                        <span class="font-bold text-red-600">{{ analytics?.appointmentAnalytics?.canceled ?? 0 }}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">Staff Performance</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Total Doctors</span>
                                        <span class="font-bold text-blue-600">{{ analytics?.staffPerformance?.doctors ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Active Doctors</span>
                                        <span class="font-bold text-green-600">{{ analytics?.staffPerformance?.activeDoctors ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Total Nurses</span>
                                        <span class="font-bold text-purple-600">{{ analytics?.staffPerformance?.nurses ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Administrators</span>
                                        <span class="font-bold text-orange-600">{{ analytics?.staffPerformance?.admins ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <StaffPerformanceChart :data="analytics?.staffPerformance" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Medical Records Analytics</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ analytics?.medicalRecordsAnalytics?.visits?.total ?? 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Total Visits</div>
                                    <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.visits?.thisMonth ?? 0 }} this month</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ analytics?.medicalRecordsAnalytics?.labResults?.total ?? 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Lab Results</div>
                                    <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.labResults?.thisMonth ?? 0 }} this month</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ analytics?.medicalRecordsAnalytics?.prescriptions?.total ?? 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Prescriptions</div>
                                    <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.prescriptions?.thisMonth ?? 0 }} this month</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ analytics?.medicalRecordsAnalytics?.vitals?.total ?? 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Vitals Records</div>
                                    <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.vitals?.thisMonth ?? 0 }} this month</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">6-Month Trend Analysis</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="(trend, index) in analytics?.trends" :key="index" class="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                    <div class="flex items-center gap-4">
                                        <div class="text-sm font-medium">{{ trend.month }}</div>
                                        <div class="flex gap-4 text-xs">
                                            <span class="text-blue-600">{{ trend.appointments }} appointments</span>
                                            <span class="text-green-600">{{ trend.visits }} visits</span>
                                            <span class="text-purple-600">{{ trend.newPatients }} new patients</span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Total: {{ trend.appointments + trend.visits + trend.newPatients }}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">Top Performance Metrics</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div v-if="analytics?.topMetrics?.busiestDay" class="flex justify-between items-center">
                                        <span class="text-sm">Busiest Day This Month</span>
                                        <span class="font-bold text-blue-600">{{ formatDate(analytics.topMetrics.busiestDay.date) }}</span>
                                    </div>
                                    <div v-if="analytics?.topMetrics?.busiestDay" class="flex justify-between items-center">
                                        <span class="text-sm">Appointments on Busiest Day</span>
                                        <span class="font-bold text-green-600">{{ analytics.topMetrics.busiestDay.count }}</span>
                                    </div>
                                    <div v-if="analytics?.topMetrics?.mostActiveDoctor" class="flex justify-between items-center">
                                        <span class="text-sm">Most Active Doctor</span>
                                        <span class="font-bold text-purple-600">{{ typeof analytics.topMetrics.mostActiveDoctor.doctor === 'object' && analytics.topMetrics.mostActiveDoctor.doctor !== null ? analytics.topMetrics.mostActiveDoctor.doctor.name : analytics.topMetrics.mostActiveDoctor.doctor || 'Unknown' }}</span>
                                    </div>
                                    <div v-if="analytics?.topMetrics?.mostActiveDoctor" class="flex justify-between items-center">
                                        <span class="text-sm">Appointments by Top Doctor</span>
                                        <span class="font-bold text-orange-600">{{ analytics.topMetrics.mostActiveDoctor.count }}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">Quick Reports</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <Button variant="outline" asChild class="w-full justify-start">
                                        <Link href="/admin/prescriptions">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Prescription Report
                                        </Link>
                                    </Button>
                                    <Button variant="outline" asChild class="w-full justify-start">
                                        <Link href="/admin/lab-results">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                            </svg>
                                            Lab Results Report
                                        </Link>
                                    </Button>
                                    <Button variant="outline" asChild class="w-full justify-start">
                                        <Link href="/admin/visits">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Visit Report
                                        </Link>
                                    </Button>
                                    <Button variant="outline" asChild class="w-full justify-start">
                                        <Link href="/patient-management">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                            </svg>
                                            Patient Report
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Appointment Trends (6 Months)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <AppointmentTrendChart :data="analytics?.trends" />
                        </CardContent>
                    </Card>
                    <!-- Billing Analytics & Reports Section -->
                    <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Billing Analytics & Reports</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <!-- ... existing billing analytics content ... -->
                            <BillingAnalyticsChart :data="analytics?.billingTrends" />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
    <!-- ... existing dialogs and modals ... -->
</template>

<!-- The <script setup> section remains unchanged except for possible import adjustments. -->

export default {
    components: {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        Badge,
        Button,
        AppointmentTrendChart,
        PatientDemographicsChart,
        StaffPerformanceChart,
        BillingAnalyticsChart
    },
} 