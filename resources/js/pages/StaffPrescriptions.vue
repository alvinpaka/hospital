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
        title: 'My Prescriptions',
        href: '/staff/prescriptions',
    },
];




// State
const prescriptions = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');
const statusFilter = ref('all');

// Pagination
const currentPage = ref(1);
const pageSize = 10;

// Dialog state
const showDetailsDialog = ref(false);
const showInstructionsDialog = ref(false);
const selectedPrescription = ref(null);
const instructionsDialogText = ref('');

// Fetch prescriptions
async function fetchPrescriptions() {
    loading.value = true;
    try {
        const response = await fetch('/api/staff/prescriptions', {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        if (response.ok) {
            const data = await response.json();
            prescriptions.value = data.prescriptions || [];
        } else {
            error.value = 'Failed to load prescriptions';
        }
    } catch (err) {
        error.value = 'Failed to load prescriptions';
        console.error('Error fetching prescriptions:', err);
    } finally {
        loading.value = false;
    }
}

// Computed properties
const filteredPrescriptions = computed(() => {
    let result = prescriptions.value;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(prescription =>
            prescription.patient?.name?.toLowerCase().includes(query) ||
            prescription.drug?.name?.toLowerCase().includes(query) ||
            prescription.dosage?.toLowerCase().includes(query)
        );
    }

    // Filter by status (active/expired)
    if (statusFilter.value === 'active') {
        const today = new Date().toISOString().split('T')[0];
        result = result.filter(prescription => prescription.end_date >= today);
    } else if (statusFilter.value === 'expired') {
        const today = new Date().toISOString().split('T')[0];
        result = result.filter(prescription => prescription.end_date < today);
    }

    return result;
});

const paginatedPrescriptions = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredPrescriptions.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredPrescriptions.value.length / pageSize));

// Functions
function formatDate(dateStr: string) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getStatusBadge(prescription: any) {
    const today = new Date().toISOString().split('T')[0];
    const isActive = prescription.end_date >= today;
    return {
        variant: isActive ? 'default' : 'secondary',
        text: isActive ? 'Active' : 'Expired'
    };
}

function openDetailsDialog(prescription: any) {
    selectedPrescription.value = prescription;
    showDetailsDialog.value = true;
}

function openInstructionsDialog(prescription: any) {
    instructionsDialogText.value = prescription.instructions || 'No instructions available';
    showInstructionsDialog.value = true;
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
    fetchPrescriptions();
});
</script>

<template>
    <Head title="My Prescriptions" />
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
                                <h1 class="text-4xl font-bold text-white">My Prescriptions</h1>
                                <p class="text-blue-100 text-lg">Staff Panel</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            Manage and view all prescriptions you've written.
                        </p>
                    </div>
                </div>
                <!-- Filters -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by patient name, medication, or dosage..."
                            class="max-w-md"
                        />
                        <select v-model="statusFilter" class="border rounded-md px-3 py-2">
                            <option value="all">All Prescriptions</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
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
                <!-- Prescriptions Table -->
                <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardHeader>
                        <CardTitle>Prescriptions ({{ filteredPrescriptions.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="filteredPrescriptions.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">No prescriptions found</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Patient</TableHead>
                                        <TableHead>Medication</TableHead>
                                        <TableHead>Dosage</TableHead>
                                        <TableHead>Start Date</TableHead>
                                        <TableHead>End Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="prescription in paginatedPrescriptions" :key="prescription.id" class="hover:bg-muted/50">
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ prescription.patient?.name || 'Unknown Patient' }}</span>
                                                <span v-if="prescription.patient?.email" class="text-xs text-muted-foreground">{{ prescription.patient.email }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>{{ prescription.drug?.name || prescription.drug?.generic_name || 'N/A' }}</TableCell>
                                        <TableCell>{{ prescription.dosage }}</TableCell>
                                        <TableCell>{{ formatDate(prescription.start_date) }}</TableCell>
                                        <TableCell>{{ formatDate(prescription.end_date) }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusBadge(prescription).variant">
                                                {{ getStatusBadge(prescription).text }}
                                            </Badge>
                                        </TableCell>
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
                                                    <DropdownMenuItem @click="openDetailsDialog(prescription)">
                                                        View Details
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="openInstructionsDialog(prescription)">
                                                        View Instructions
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
                                Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredPrescriptions.length) }} of {{ filteredPrescriptions.length }} prescriptions
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

        <!-- Prescription Details Dialog -->
        <Dialog v-model:open="showDetailsDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Prescription Details</DialogTitle>
                    <DialogDescription>
                        Detailed information about this prescription.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedPrescription" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Patient</label>
                            <p class="text-sm font-medium">{{ selectedPrescription.patient?.name || 'Unknown Patient' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Medication</label>
                            <p class="text-sm">{{ selectedPrescription.drug?.name || selectedPrescription.drug?.generic_name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Dosage</label>
                            <p class="text-sm">{{ selectedPrescription.dosage }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <p class="text-sm">
                                <Badge :variant="getStatusBadge(selectedPrescription).variant">
                                    {{ getStatusBadge(selectedPrescription).text }}
                                </Badge>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Start Date</label>
                            <p class="text-sm">{{ formatDate(selectedPrescription.start_date) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">End Date</label>
                            <p class="text-sm">{{ formatDate(selectedPrescription.end_date) }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Instructions</label>
                        <p class="text-sm">{{ selectedPrescription.instructions || 'No instructions available' }}</p>
                    </div>
                    <div v-if="selectedPrescription.patient">
                        <label class="text-sm font-medium text-muted-foreground">Patient Information</label>
                        <div class="text-sm space-y-1">
                            <p><strong>Email:</strong> {{ selectedPrescription.patient.email || 'Not provided' }}</p>
                            <p><strong>Phone:</strong> {{ selectedPrescription.patient.phone || 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Instructions Dialog -->
        <Dialog v-model:open="showInstructionsDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Prescription Instructions</DialogTitle>
                    <DialogDescription>
                        Detailed instructions for this prescription.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="bg-muted p-4 rounded-lg">
                        <p class="text-sm whitespace-pre-wrap">{{ instructionsDialogText }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showInstructionsDialog = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template> 