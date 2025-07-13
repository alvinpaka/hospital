<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Skeleton } from '@/components/ui/skeleton';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Staff Dashboard',
        href: '/staff/dashboard',
    },
    {
        title: 'My Appointments',
        href: '/staff/appointments',
    },
];




// State
const appointments = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');
const statusFilter = ref('all');
const dateFilter = ref('all');

// Pagination
const currentPage = ref(1);
const pageSize = 10;

// Dialog state
const showDetailsDialog = ref(false);
const selectedAppointment = ref(null);

// Fetch appointments
async function fetchAppointments() {
    loading.value = true;
    try {
        const response = await fetch('/api/staff/appointments', {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        if (response.ok) {
            const data = await response.json();
            appointments.value = data.appointments || [];
        } else {
            error.value = 'Failed to load appointments';
        }
    } catch (err) {
        error.value = 'Failed to load appointments';
        console.error('Error fetching appointments:', err);
    } finally {
        loading.value = false;
    }
}

// Computed properties
const filteredAppointments = computed(() => {
    let result = appointments.value;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(appointment =>
            appointment.patient?.name?.toLowerCase().includes(query) ||
            appointment.notes?.toLowerCase().includes(query)
        );
    }

    // Filter by status
    if (statusFilter.value !== 'all') {
        result = result.filter(appointment => appointment.status === statusFilter.value);
    }

    // Filter by date
    if (dateFilter.value === 'today') {
        const today = new Date().toISOString().split('T')[0];
        result = result.filter(appointment => appointment.date === today);
    } else if (dateFilter.value === 'upcoming') {
        const today = new Date().toISOString().split('T')[0];
        result = result.filter(appointment => appointment.date >= today);
    } else if (dateFilter.value === 'past') {
        const today = new Date().toISOString().split('T')[0];
        result = result.filter(appointment => appointment.date < today);
    }

    return result;
});

const paginatedAppointments = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredAppointments.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredAppointments.value.length / pageSize));

// Functions
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

function getStatusBadge(status: string) {
    const variants = {
        'confirmed': 'default',
        'pending': 'secondary',
        'canceled': 'destructive',
        'completed': 'default',
    };
    return variants[status] || 'secondary';
}

function openDetailsDialog(appointment: any) {
    selectedAppointment.value = appointment;
    showDetailsDialog.value = true;
}

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

function getPageNumbers() {
    const pages = [];
    const maxPagesToShow = 5;
    const startPage = Math.max(1, currentPage.value - Math.floor(maxPagesToShow / 2));
    const endPage = Math.min(totalPages.value, startPage + maxPagesToShow - 1);
    
    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }
    return pages;
}

// Lifecycle
onMounted(() => {
    fetchAppointments();
});
</script>

<template>
    <Head title="My Appointments" />
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-4xl font-bold text-white">My Appointments</h1>
                                <p class="text-blue-100 text-lg">Staff Panel</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            Manage and view all your patient appointments.
                        </p>
                    </div>
                </div>
                <!-- Filters -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by patient name or notes..."
                            class="max-w-md"
                        />
                        <select v-model="statusFilter" class="border rounded-md px-3 py-2">
                            <option value="all">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="canceled">Canceled</option>
                            <option value="completed">Completed</option>
                        </select>
                        <select v-model="dateFilter" class="border rounded-md px-3 py-2">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past</option>
                        </select>
                    </CardContent>
                </Card>
                <!-- Error State -->
                <Card v-if="error" class="rounded-3xl border-0 shadow-xl bg-red-50 dark:bg-red-900/80 mb-6">
                    <CardContent class="p-4 text-center text-red-600">
                        {{ error }}
                    </CardContent>
                </Card>
                <!-- Loading State -->
                <Card v-else-if="loading" class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardContent class="p-6 space-y-4">
                        <Skeleton class="h-8 w-full" />
                        <Skeleton class="h-8 w-full" />
                        <Skeleton class="h-8 w-full" />
                    </CardContent>
                </Card>
                <!-- Appointments Table -->
                <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardHeader>
                        <CardTitle>Appointments ({{ filteredAppointments.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="filteredAppointments.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">No appointments found</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Time</TableHead>
                                        <TableHead>Patient</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Notes</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="appointment in paginatedAppointments" :key="appointment.id" class="hover:bg-muted/50">
                                        <TableCell>{{ formatDate(appointment.date) }}</TableCell>
                                        <TableCell>{{ formatTime(appointment.time) }}</TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ appointment.patient?.name || 'Unknown Patient' }}</span>
                                                <span v-if="appointment.patient?.email" class="text-xs text-muted-foreground">{{ appointment.patient.email }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusBadge(appointment.status)">
                                                {{ appointment.status }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="max-w-xs truncate">{{ appointment.notes || '-' }}</TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                        </svg>
                                                        <span class="sr-only">Open menu</span>
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="openDetailsDialog(appointment)">
                                                        View Details
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="totalPages > 1" class="flex justify-between items-center mt-4">
                            <div class="text-sm text-muted-foreground">
                                Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredAppointments.length) }} of {{ filteredAppointments.length }} appointments
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="currentPage === 1"
                                    @click="goToPage(currentPage - 1)"
                                >
                                    Previous
                                </Button>
                                <div class="flex gap-1">
                                    <Button
                                        v-for="pageNum in getPageNumbers()"
                                        :key="pageNum"
                                        :variant="pageNum === currentPage ? 'default' : 'outline'"
                                        size="sm"
                                        @click="goToPage(pageNum)"
                                    >
                                        {{ pageNum }}
                                    </Button>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="currentPage === totalPages"
                                    @click="goToPage(currentPage + 1)"
                                >
                                    Next
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
    <!-- Appointment Details Dialog -->
    <Dialog v-model:open="showDetailsDialog">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Appointment Details</DialogTitle>
          <DialogDescription>
            Detailed information about this appointment.
          </DialogDescription>
        </DialogHeader>
        <div v-if="selectedAppointment" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Date</label>
              <p class="text-sm">{{ formatDate(selectedAppointment.date) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Time</label>
              <p class="text-sm">{{ formatTime(selectedAppointment.time) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Status</label>
              <p class="text-sm">
                <Badge :variant="getStatusBadge(selectedAppointment.status)">
                  {{ selectedAppointment.status }}
                </Badge>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Patient</label>
              <p class="text-sm">{{ selectedAppointment.patient?.name || 'Unknown Patient' }}</p>
            </div>
          </div>
          <div>
            <label class="text-sm font-medium text-muted-foreground">Notes</label>
            <p class="text-sm">{{ selectedAppointment.notes || 'No notes available' }}</p>
          </div>
          <div v-if="selectedAppointment.patient">
            <label class="text-sm font-medium text-muted-foreground">Patient Information</label>
            <div class="text-sm space-y-1">
              <p><strong>Email:</strong> {{ selectedAppointment.patient.email || 'Not provided' }}</p>
              <p><strong>Phone:</strong> {{ selectedAppointment.patient.phone || 'Not provided' }}</p>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
</template> 