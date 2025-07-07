<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
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
const user = computed(() => page.props.user);
const stats = computed(() => page.props.stats);
const recentActivities = computed(() => page.props.recentActivities);
const todayAppointments = computed(() => page.props.todayAppointments);
const recentLabResults = computed(() => page.props.recentLabResults);
const recentPrescriptions = computed(() => page.props.recentPrescriptions);
const monthlyStats = computed(() => page.props.monthlyStats);
const appointmentStatusStats = computed(() => page.props.appointmentStatusStats);
const analytics = computed(() => page.props.analytics);

// Dialog state for pending appointments
const showPendingAppointments = ref(false);

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

// Function to handle clicking on Total Appointments card
function handleTotalAppointmentsClick() {
    if (stats.value?.pendingAppointments > 0) {
        showPendingAppointments.value = true;
    }
}

</script>

<template>
    <Head title="Admin Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
                    <p class="text-muted-foreground">Welcome back, {{ user?.name }}. Here's what's happening in your hospital.</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Patients</CardTitle>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.totalPatients || 0 }}</div>
                        <p class="text-xs text-muted-foreground">Registered patients</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Staff</CardTitle>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.totalStaff || 0 }}</div>
                        <p class="text-xs text-muted-foreground">Doctors, nurses & admins</p>
                    </CardContent>
                </Card>

                <Card 
                    :class="[
                        'transition-all duration-200',
                        stats?.pendingAppointments > 0 ? 'cursor-pointer hover:shadow-lg hover:scale-[1.02]' : ''
                    ]"
                    @click="handleTotalAppointmentsClick"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Appointments</CardTitle>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.totalAppointments || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-orange-600">{{ stats?.pendingAppointments || 0 }} pending</span>
                            <span v-if="stats?.pendingAppointments > 0" class="ml-1 text-xs text-blue-600">(click to view)</span>
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ monthlyStats?.appointments || 0 }}</div>
                        <p class="text-xs text-muted-foreground">Appointments this month</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Stats Row -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Medical Records</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm">Total Visits</span>
                            <span class="font-medium">{{ stats?.totalVisits || 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm">Lab Results</span>
                            <span class="font-medium">{{ stats?.totalLabResults || 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm">Prescriptions</span>
                            <span class="font-medium">{{ stats?.totalPrescriptions || 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm">Vitals Records</span>
                            <span class="font-medium">{{ stats?.totalVitals || 0 }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Today's Appointments -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Today's Appointments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="todayAppointments?.length > 0" class="space-y-3">
                            <div v-for="appointment in todayAppointments" :key="appointment.id" class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium">{{ appointment.patient?.name || 'Unknown Patient' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatTime(appointment.time) }} - {{ appointment.doctor?.name || 'No doctor assigned' }}</div>
                                </div>
                                <Badge :variant="appointment.status === 'confirmed' ? 'default' : 'secondary'">
                                    {{ appointment.status }}
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="text-sm text-muted-foreground">No appointments today</div>
                    </CardContent>
                </Card>

                <!-- Recent Activities -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Recent Activities</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentActivities?.length > 0" class="space-y-3">
                            <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start gap-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <div class="text-sm">{{ activity.action }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ activity.user?.name || 'System' }} • {{ formatDate(activity.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-muted-foreground">No recent activities</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Data Section -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Lab Results -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Recent Lab Results</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentLabResults?.length > 0" class="space-y-3">
                            <div v-for="result in recentLabResults" :key="result.id" class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium">{{ result.patient?.name || 'Unknown Patient' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ result.type }}: {{ result.result }}</div>
                                </div>
                                <div class="text-xs text-muted-foreground">{{ formatDate(result.date) }}</div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-muted-foreground">No recent lab results</div>
                    </CardContent>
                </Card>

                <!-- Recent Prescriptions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Recent Prescriptions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentPrescriptions?.length > 0" class="space-y-3">
                            <div v-for="prescription in recentPrescriptions" :key="prescription.id" class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium">{{ prescription.patient?.name || 'Unknown Patient' }}</div>
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
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">Analytics & Reports</h2>
                        <p class="text-muted-foreground">Gain insights with real-time analytics and reports for patients and staff.</p>
                    </div>
                </div>

                <!-- Patient Demographics -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Patient Demographics</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ analytics?.patientDemographics?.total || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Total Patients</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ analytics?.patientDemographics?.newThisMonth || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">New This Month</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ analytics?.patientDemographics?.verified || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Email Verified</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ analytics?.patientDemographics?.unverified || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Pending Verification</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center">
                                <PatientDemographicsChart :data="analytics?.patientDemographics" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Appointment Analytics -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Appointment Analytics</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Total Appointments</span>
                                    <span class="font-bold">{{ analytics?.appointmentAnalytics?.total || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">This Month</span>
                                    <span class="font-bold text-blue-600">{{ analytics?.appointmentAnalytics?.thisMonth || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Avg. Per Day</span>
                                    <span class="font-bold text-green-600">{{ Math.round(analytics?.appointmentAnalytics?.avgPerDay || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Confirmed</span>
                                    <span class="font-bold text-green-600">{{ analytics?.appointmentAnalytics?.confirmed || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Pending</span>
                                    <span class="font-bold text-orange-600">{{ analytics?.appointmentAnalytics?.pending || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Canceled</span>
                                    <span class="font-bold text-red-600">{{ analytics?.appointmentAnalytics?.canceled || 0 }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Staff Performance -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Staff Performance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Total Doctors</span>
                                    <span class="font-bold text-blue-600">{{ analytics?.staffPerformance?.doctors || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Active Doctors</span>
                                    <span class="font-bold text-green-600">{{ analytics?.staffPerformance?.activeDoctors || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Total Nurses</span>
                                    <span class="font-bold text-purple-600">{{ analytics?.staffPerformance?.nurses || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Administrators</span>
                                    <span class="font-bold text-orange-600">{{ analytics?.staffPerformance?.admins || 0 }}</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <StaffPerformanceChart :data="analytics?.staffPerformance" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Medical Records Analytics -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Medical Records Analytics</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ analytics?.medicalRecordsAnalytics?.visits?.total || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Total Visits</div>
                                <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.visits?.thisMonth || 0 }} this month</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ analytics?.medicalRecordsAnalytics?.labResults?.total || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Lab Results</div>
                                <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.labResults?.thisMonth || 0 }} this month</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ analytics?.medicalRecordsAnalytics?.prescriptions?.total || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Prescriptions</div>
                                <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.prescriptions?.thisMonth || 0 }} this month</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ analytics?.medicalRecordsAnalytics?.vitals?.total || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Vitals Records</div>
                                <div class="text-xs text-green-600">+{{ analytics?.medicalRecordsAnalytics?.vitals?.thisMonth || 0 }} this month</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Trend Analysis -->
                <Card>
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

                <!-- Top Performance Metrics -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
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
                                    <span class="font-bold text-purple-600">{{ analytics.topMetrics.mostActiveDoctor.doctor?.name || 'Unknown' }}</span>
                                </div>
                                <div v-if="analytics?.topMetrics?.mostActiveDoctor" class="flex justify-between items-center">
                                    <span class="text-sm">Appointments by Top Doctor</span>
                                    <span class="font-bold text-orange-600">{{ analytics.topMetrics.mostActiveDoctor.count }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Reports -->
                    <Card>
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

                <!-- Appointment Trends Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Appointment Trends (6 Months)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <AppointmentTrendChart :data="analytics?.trends" />
                    </CardContent>
                </Card>

                <!-- Billing Analytics & Reports Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">Billing Analytics & Reports</h2>
                            <p class="text-muted-foreground">Financial insights and revenue analytics for your hospital.</p>
                        </div>
                    </div>

                    <!-- Billing Statistics Cards -->
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-green-600">UGX {{ analytics?.billingAnalytics?.totalRevenue?.toLocaleString() || 0 }}</div>
                                <p class="text-xs text-muted-foreground">All time revenue</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">This Month</CardTitle>
                                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-blue-600">UGX {{ analytics?.billingAnalytics?.revenueThisMonth?.toLocaleString() || 0 }}</div>
                                <p class="text-xs text-muted-foreground">Revenue this month</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Total Bills</CardTitle>
                                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ analytics?.billingAnalytics?.totalBills || 0 }}</div>
                                <p class="text-xs text-muted-foreground">All bills generated</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Collection Rate</CardTitle>
                                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-green-600">{{ analytics?.billingAnalytics?.collectionRate || 0 }}%</div>
                                <p class="text-xs text-muted-foreground">Paid vs total bills</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Billing Status Overview -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Billing Status Overview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ analytics?.billingAnalytics?.paidBills || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Paid Bills</div>
                                    <div class="text-xs text-green-600">UGX {{ analytics?.billingAnalytics?.paidAmount?.toLocaleString() || 0 }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ analytics?.billingAnalytics?.pendingBills || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Pending Bills</div>
                                    <div class="text-xs text-orange-600">UGX {{ analytics?.billingAnalytics?.pendingAmount?.toLocaleString() || 0 }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600">{{ analytics?.billingAnalytics?.overdueBills || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Overdue Bills</div>
                                    <div class="text-xs text-red-600">UGX {{ analytics?.billingAnalytics?.overdueAmount?.toLocaleString() || 0 }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ analytics?.billingAnalytics?.averageBillAmount || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Average Bill</div>
                                    <div class="text-xs text-blue-600">UGX per bill</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Revenue Trends Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Revenue Trends (6 Months)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <BillingAnalyticsChart :data="analytics?.billingTrends" />
                        </CardContent>
                    </Card>

                    <!-- Top Paying Patients & Services -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">Top Paying Patients</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="analytics?.topPayingPatients?.length > 0" class="space-y-3">
                                    <div v-for="(patient, index) in analytics.topPayingPatients" :key="index" class="flex items-center justify-between p-2 bg-muted/30 rounded">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">
                                                {{ index + 1 }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium">{{ patient.name }}</div>
                                                <div class="text-xs text-muted-foreground">{{ patient.bills }} bills</div>
                                            </div>
                                        </div>
                                        <div class="text-sm font-bold text-green-600">
                                            UGX {{ patient.totalPaid?.toLocaleString() || 0 }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-muted-foreground text-center py-4">
                                    No billing data available
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">Top Revenue Services</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="analytics?.topRevenueServices?.length > 0" class="space-y-3">
                                    <div v-for="(service, index) in analytics.topRevenueServices" :key="index" class="flex items-center justify-between p-2 bg-muted/30 rounded">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs font-bold">
                                                {{ index + 1 }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium">{{ service.description }}</div>
                                                <div class="text-xs text-muted-foreground">{{ service.count }} times</div>
                                            </div>
                                        </div>
                                        <div class="text-sm font-bold text-green-600">
                                            UGX {{ service.totalRevenue?.toLocaleString() || 0 }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-muted-foreground text-center py-4">
                                    No service data available
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Monthly Revenue Comparison -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Monthly Revenue Comparison</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">
                                        UGX {{ analytics?.billingAnalytics?.currentMonthRevenue?.toLocaleString() || 0 }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">This Month</div>
                                </div>
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">
                                        UGX {{ analytics?.billingAnalytics?.lastMonthRevenue?.toLocaleString() || 0 }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">Last Month</div>
                                </div>
                                <div class="text-center p-4 bg-purple-50 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">
                                        {{ analytics?.billingAnalytics?.growthRate || 0 }}%
                                    </div>
                                    <div class="text-sm text-muted-foreground">Growth Rate</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Billing Reports Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">Billing Reports & Actions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-3 md:grid-cols-2">
                                <Button variant="outline" asChild class="w-full justify-start">
                                    <Link href="/admin/billings">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        View All Bills
                                    </Link>
                                </Button>
                                <Button variant="outline" asChild class="w-full justify-start">
                                    <Link href="/admin/billings/create">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Create New Bill
                                    </Link>
                                </Button>
                                <Button variant="outline" asChild class="w-full justify-start">
                                    <Link href="/admin/billings?status=pending">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Pending Bills
                                    </Link>
                                </Button>
                                <Button variant="outline" asChild class="w-full justify-start">
                                    <Link href="/admin/billings?status=overdue">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        Overdue Bills
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Pending Appointments Dialog -->
    <Dialog v-model:open="showPendingAppointments">
        <DialogContent class="max-w-4xl max-h-[80vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Pending Appointments ({{ stats?.pendingAppointments || 0 }})</DialogTitle>
                <DialogDescription>
                  List of all pending appointments. You can view details or take action on each appointment.
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-4">
                <div v-if="appointmentStatusStats?.pending?.length > 0" class="space-y-3">
                    <div v-for="appointment in appointmentStatusStats.pending" :key="appointment.id" 
                         class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <div class="font-medium">{{ appointment.patient?.name || 'Unknown Patient' }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ formatDate(appointment.date) }} at {{ formatTime(appointment.time) }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        Doctor: {{ appointment.doctor?.name || 'No doctor assigned' }}
                                    </div>
                                    <div v-if="appointment.notes" class="text-sm text-muted-foreground mt-1">
                                        Notes: {{ appointment.notes }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge variant="secondary">Pending</Badge>
                                    <Button size="sm" variant="outline" asChild>
                                        <Link href="/admin/appointments">
                                            View Details
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-muted-foreground">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-2 text-sm">No pending appointments found</p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

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