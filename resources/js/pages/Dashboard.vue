<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import UserInfo from '../components/UserInfo.vue';
import { computed } from 'vue';
import VitalsChart from '../components/VitalsChart.vue';
import LabResultsChart from '../components/LabResultsChart.vue';
// import your chart component here (e.g., import VitalsChart from '../components/VitalsChart.vue')

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
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 max-w-5xl mx-auto">
            <!-- Patient Overview -->
            <!-- <div class="flex items-center gap-4 p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900">
                <UserInfo :user="user" :show-email="true" />
                <div class="ml-6">
                    <div class="text-xs text-muted-foreground">Patient since {{ new Date(user.created_at).toLocaleDateString() }}</div>
                    <div class="text-xs text-muted-foreground">Email {{ user.email_verified_at ? 'verified' : 'not verified' }}</div>
                </div>
            </div> -->

            <!-- Dashboard Widgets -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Upcoming Appointment -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4 flex flex-col justify-between">
                    <div>
                        <div class="font-semibold mb-1">Next Appointment</div>
                        <div class="text-sm">{{ nextAppointment?.date || 'None' }}<span v-if="nextAppointment"> at {{ nextAppointment.time }}</span></div>
                        <div class="text-xs text-muted-foreground" v-if="nextAppointment">{{ nextAppointment.doctor?.name || nextAppointment.doctor }}</div>
                        <div class="text-xs text-muted-foreground" v-else>No upcoming appointments</div>
                    </div>
                    <Link href="/appointments" class="mt-3 text-xs text-blue-600 hover:underline">See all appointments</Link>
                </div>
                <!-- Recent Visit -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4 flex flex-col justify-between">
                    <div>
                        <div class="font-semibold mb-1">Recent Visit</div>
                        <div class="text-sm">{{ recentVisit?.date || 'None' }}<span v-if="recentVisit"> with {{ recentVisit.doctor?.name || recentVisit.doctor }}</span></div>
                        <div class="text-xs text-muted-foreground" v-if="recentVisit">{{ recentVisit.summary }}</div>
                        <div class="text-xs text-muted-foreground" v-else>No recent visits</div>
                    </div>
                    <Link href="/visits" class="mt-3 text-xs text-blue-600 hover:underline">See all visits</Link>
                </div>
                <!-- Latest Lab Result -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4 flex flex-col justify-between">
                    <div>
                        <div class="font-semibold mb-1">Latest Lab Result</div>
                        <div class="text-sm" v-if="latestLabResult">{{ latestLabResult.type }}: {{ latestLabResult.result }}</div>
                        <div class="text-xs text-muted-foreground" v-if="latestLabResult">Date: {{ latestLabResult.date }}</div>
                        <div class="text-xs text-muted-foreground" v-else>No lab results</div>
                    </div>
                    <Link href="/lab-results" class="mt-3 text-xs text-blue-600 hover:underline">See all lab results</Link>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Current Prescription -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4 flex flex-col justify-between">
                    <div>
                        <div class="font-semibold mb-1">Current Prescription</div>
                        <div class="text-sm" v-if="currentPrescription">{{ currentPrescription.medication }} ({{ currentPrescription.dosage }})</div>
                        <div class="text-xs text-muted-foreground" v-if="currentPrescription">Prescribed by {{ currentPrescription.doctor?.name || currentPrescription.doctor }} on {{ currentPrescription.start_date }}</div>
                        <div class="text-xs text-muted-foreground" v-else>No prescriptions</div>
                    </div>
                    <Link href="/prescriptions" class="mt-3 text-xs text-blue-600 hover:underline">See all prescriptions</Link>
                </div>
                <!-- Latest Vitals -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4 flex flex-col justify-between">
                    <div>
                        <div class="font-semibold mb-1">Latest Vitals</div>
                        <div class="text-sm" v-if="latestVitals">BP: {{ latestVitals.blood_pressure }}, HR: {{ latestVitals.heart_rate }}, Temp: {{ latestVitals.temperature }}°C</div>
                        <div class="text-xs text-muted-foreground" v-if="latestVitals">Date: {{ latestVitals.date || latestVitals.recorded_at }}</div>
                        <div class="text-xs text-muted-foreground" v-else>No vitals</div>
                    </div>
                    <Link href="/vitals" class="mt-3 text-xs text-blue-600 hover:underline">See all vitals</Link>
                </div>
            </div>

            <!-- Graphs Section -->
            <div class="grid gap-4 md:grid-cols-2 mt-6">
                <!-- Vitals Trend Graph -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4">
                    <div class="font-semibold mb-2">Vitals Trend</div>
                    <VitalsChart :data="vitalsTrend" />
                </div>
                <!-- Lab Results Trend Graph -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-4">
                    <div class="font-semibold mb-2">Lab Results Trend</div>
                    <LabResultsChart :data="labResultsTrend" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
