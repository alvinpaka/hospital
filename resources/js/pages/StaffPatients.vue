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
        title: 'My Patients',
        href: '/staff/patients',
    },
];




// State
const patients = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');

// Dialog state
const showDetailsDialog = ref(false);
const selectedPatient = ref(null);

// Fetch patients
async function fetchPatients() {
    loading.value = true;
    try {
        const response = await fetch('/api/staff/patients', {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        if (response.ok) {
            const data = await response.json();
            patients.value = data.patients.data || [];
        } else {
            error.value = 'Failed to load patients';
        }
    } catch (err) {
        error.value = 'Failed to load patients';
        console.error('Error fetching patients:', err);
    } finally {
        loading.value = false;
    }
}

// Computed properties
const filteredPatients = computed(() => {
    if (!searchQuery.value) return patients.value;
    const query = searchQuery.value.toLowerCase();
    return patients.value.filter(patient =>
        patient.name?.toLowerCase().includes(query) ||
        patient.email?.toLowerCase().includes(query)
    );
});

// Functions
function openDetailsDialog(patient: any) {
    selectedPatient.value = patient;
    showDetailsDialog.value = true;
}

function formatDate(dateStr: string) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// Lifecycle
onMounted(() => {
    fetchPatients();
});
</script>

<template>
    <Head title="My Patients" />
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-4xl font-bold text-white">My Patients</h1>
                                <p class="text-blue-100 text-lg">Staff Panel</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            View and manage your patients.
                        </p>
                    </div>
                </div>
                <!-- Search -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by patient name or email..."
                            class="max-w-md"
                        />
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
                <!-- Patients Table -->
                <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardHeader>
                        <CardTitle>All Patients</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="filteredPatients.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">No patients found</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Phone</TableHead>
                                        <TableHead>Joined</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="patient in filteredPatients" :key="patient.id" class="hover:bg-muted/50">
                                        <TableCell>
                                            <div class="font-medium">{{ patient.name }}</div>
                                        </TableCell>
                                        <TableCell>{{ patient.email || 'Not provided' }}</TableCell>
                                        <TableCell>{{ patient.phone || 'Not provided' }}</TableCell>
                                        <TableCell>{{ formatDate(patient.created_at) }}</TableCell>
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
                                                    <DropdownMenuItem @click="$inertia.visit(`/staff/patients/${patient.id}`)">
                                                        View Profile
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="openDetailsDialog(patient)">
                                                        View Details
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
            </div>
        </div>
    </AppLayout>
    <!-- Patient Details Dialog -->
    <Dialog v-model:open="showDetailsDialog">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Patient Details</DialogTitle>
          <DialogDescription>
            Detailed information about this patient.
          </DialogDescription>
        </DialogHeader>
        <div v-if="selectedPatient" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Name</label>
              <p class="text-sm font-medium">{{ selectedPatient.name }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Email</label>
              <p class="text-sm">{{ selectedPatient.email || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Phone</label>
              <p class="text-sm">{{ selectedPatient.phone || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Joined</label>
              <p class="text-sm">{{ formatDate(selectedPatient.created_at) }}</p>
            </div>
          </div>
          <div>
            <label class="text-sm font-medium text-muted-foreground">Email Verification</label>
            <p class="text-sm">
              <Badge :variant="selectedPatient.email_verified_at ? 'default' : 'secondary'">
                {{ selectedPatient.email_verified_at ? 'Verified' : 'Not Verified' }}
              </Badge>
            </p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
</template> 