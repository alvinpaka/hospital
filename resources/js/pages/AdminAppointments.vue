<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogDescription } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import { Input } from '@/components/ui/input';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Appointments',
        href: '/admin/appointments',
    },
];

const page = usePage();
const user = computed(() => page.props.user);
const appointments = computed(() => page.props.appointments || []);
const staff = computed(() => page.props.staff || []);

// Dialog states
const showAssignDialog = ref(false);
const showCancelDialog = ref(false);
const selectedAppointment = ref<any>(null);
const selectedDoctor = ref<string>('');
const cancellationReason = ref('');

// Search and filter
const search = ref('');
const statusFilter = ref('');

// Filtered appointments
const filteredAppointments = computed(() => {
    let filtered = appointments.value;
    
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(appt => 
            appt.patient?.name?.toLowerCase().includes(searchTerm) ||
            appt.doctor?.name?.toLowerCase().includes(searchTerm) ||
            appt.id.toString().includes(searchTerm)
        );
    }
    
    if (statusFilter.value) {
        filtered = filtered.filter(appt => appt.status === statusFilter.value);
    }
    
    return filtered;
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

function getStatusColor(status: string) {
    switch (status) {
        case 'confirmed':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'canceled':
            return 'destructive';
        default:
            return 'secondary';
    }
}

function handleAccept(appointment: any) {
    router.post(`/admin/appointments/${appointment.id}/accept`, {});
}

function handleAssign(appointment: any) {
    selectedAppointment.value = appointment;
    selectedDoctor.value = '';
    showAssignDialog.value = true;
}

function confirmAssign() {
    if (!selectedDoctor.value) return;
    
    router.post(`/admin/appointments/${selectedAppointment.value.id}/assign`, {
        doctor_id: selectedDoctor.value,
    }, {
        onSuccess: () => {
            showAssignDialog.value = false;
        },
    });
}

function handleCancel(appointment: any) {
    selectedAppointment.value = appointment;
    cancellationReason.value = '';
    showCancelDialog.value = true;
}

function confirmCancel() {
    router.post(`/admin/appointments/${selectedAppointment.value.id}/cancel`, {
        cancellation_reason: cancellationReason.value,
    }, {
        onSuccess: () => {
            showCancelDialog.value = false;
        },
    });
}

const pendingAppointments = computed(() => 
    appointments.value.filter(a => a.status === 'pending')
);

const confirmedAppointments = computed(() => 
    appointments.value.filter(a => a.status === 'confirmed')
);

const canceledAppointments = computed(() => 
    appointments.value.filter(a => a.status === 'canceled')
);
</script>

<template>
    <Head title="Admin Appointments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Appointment Management</h1>
                    <p class="text-muted-foreground">Manage all appointments in the hospital.</p>
                </div>
                <div class="flex gap-2">
                    <Badge variant="secondary">{{ appointments.length }} Total</Badge>
                    <Badge variant="secondary" class="text-orange-600">{{ pendingAppointments.length }} Pending</Badge>
                    <Badge variant="default" class="text-green-600">{{ confirmedAppointments.length }} Confirmed</Badge>
                </div>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <Input 
                                v-model="search" 
                                placeholder="Search by patient name, doctor, or ID..." 
                                class="w-full"
                            />
                        </div>
                        <div class="w-48">
                            <select 
                                v-model="statusFilter"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="canceled">Canceled</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Appointments Table -->
            <Card>
                <CardHeader>
                    <CardTitle>All Appointments</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Time</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Doctor/Nurse</TableHead>
                                    <TableHead>Notes</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="appointment in filteredAppointments" :key="appointment.id" class="hover:bg-muted/50">
                                    <TableCell class="font-mono text-sm">#{{ appointment.id }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ appointment.patient?.name || 'Unknown Patient' }}</span>
                                            <span class="text-xs text-muted-foreground">{{ appointment.patient?.email || 'No email' }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ formatDate(appointment.date) }}</TableCell>
                                    <TableCell>{{ formatTime(appointment.time) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusColor(appointment.status)">
                                            {{ appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ appointment.doctor?.name || 'No doctor assigned' }}</TableCell>
                                    <TableCell class="text-sm max-w-xs truncate">{{ appointment.notes || 'No notes' }}</TableCell>
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
                                            <DropdownMenuContent align="end" class="w-48">
                                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                <DropdownMenuSeparator />
                                                
                                                <!-- Accept Appointment (only for pending) -->
                                                <DropdownMenuItem 
                                                    v-if="appointment.status === 'pending'"
                                                    @click="handleAccept(appointment)"
                                                    class="text-green-600 focus:text-green-600"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Accept Appointment
                                                </DropdownMenuItem>
                                                
                                                <!-- Assign Staff -->
                                                <DropdownMenuItem 
                                                    v-if="appointment.status !== 'canceled'"
                                                    @click="handleAssign(appointment)"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                                    </svg>
                                                    {{ appointment.status === 'confirmed' ? 'Reassign' : 'Assign' }} Staff
                                                </DropdownMenuItem>
                                                
                                                <!-- Cancel Appointment (only for non-canceled) -->
                                                <DropdownMenuItem 
                                                    v-if="appointment.status !== 'canceled'"
                                                    @click="handleCancel(appointment)"
                                                    class="text-red-600 focus:text-red-600"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Cancel Appointment
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- No Appointments -->
            <Card v-if="filteredAppointments.length === 0">
                <CardContent class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-muted-foreground">No appointments found</p>
                </CardContent>
            </Card>
        </div>

        <!-- Assign Doctor Dialog -->
        <Dialog v-model:open="showAssignDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Assign Staff</DialogTitle>
                    <DialogDescription>
                      Select a doctor or nurse from the dropdown and assign to the appointment.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div v-if="selectedAppointment">
                        <p class="text-sm text-muted-foreground">
                            Assign staff to: <strong>{{ selectedAppointment.patient?.name }}</strong>
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Date: {{ formatDate(selectedAppointment.date) }} at {{ formatTime(selectedAppointment.time) }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Select Staff Member</label>
                        <select 
                            v-model="selectedDoctor"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="" disabled>Choose a staff member</option>
                            <option v-for="member in staff" :key="member.id" :value="member.id.toString()">
                                {{ member.name }} ({{ member.role }})
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" @click="showAssignDialog = false">Cancel</Button>
                        <Button @click="confirmAssign" :disabled="!selectedDoctor">Assign Staff</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Cancel Appointment Dialog -->
        <Dialog v-model:open="showCancelDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Cancel Appointment</DialogTitle>
                    <DialogDescription>
                      Optionally provide a reason for cancellation. This will be visible to the patient.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div v-if="selectedAppointment">
                        <p class="text-sm text-muted-foreground">
                            Cancel appointment for: <strong>{{ selectedAppointment.patient?.name }}</strong>
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Date: {{ formatDate(selectedAppointment.date) }} at {{ formatTime(selectedAppointment.time) }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Cancellation Reason (Optional)</label>
                        <textarea 
                            v-model="cancellationReason"
                            class="w-full p-2 border rounded-md"
                            rows="3"
                            placeholder="Enter reason for cancellation..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" @click="showCancelDialog = false">Cancel</Button>
                        <Button variant="destructive" @click="confirmCancel">Cancel Appointment</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template> 