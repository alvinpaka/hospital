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
        title: 'My Vitals',
        href: '/staff/vitals',
    },
];




// State
const vitals = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');

// Pagination
const currentPage = ref(1);
const pageSize = 10;

// Dialog state
const showDetailsDialog = ref(false);
const selectedVital = ref(null);

// Fetch vitals
async function fetchVitals() {
    loading.value = true;
    try {
        const response = await fetch('/api/staff/vitals', {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        if (response.ok) {
            const data = await response.json();
            vitals.value = data.vitals || [];
        } else {
            error.value = 'Failed to load vitals';
        }
    } catch (err) {
        error.value = 'Failed to load vitals';
        console.error('Error fetching vitals:', err);
    } finally {
        loading.value = false;
    }
}

// Computed properties
const filteredVitals = computed(() => {
    if (!searchQuery.value) return vitals.value;
    const query = searchQuery.value.toLowerCase();
    return vitals.value.filter(vital =>
        vital.patient?.name?.toLowerCase().includes(query)
    );
});

const paginatedVitals = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredVitals.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredVitals.value.length / pageSize));

// Functions
function formatDate(dateStr: string) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function openDetailsDialog(vital: any) {
    selectedVital.value = vital;
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
    fetchVitals();
});
</script>

<template>
    <Head title="My Vitals" />
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
                                <h1 class="text-4xl font-bold text-white">My Vitals</h1>
                                <p class="text-blue-100 text-lg">Staff Panel</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            View and manage vitals you've recorded for patients.
                        </p>
                    </div>
                </div>
                <!-- Search -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by patient name..."
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
                <!-- Vitals Table -->
                <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardHeader>
                        <CardTitle>Vitals ({{ filteredVitals.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="filteredVitals.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">No vitals found</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Patient</TableHead>
                                        <TableHead>Temperature</TableHead>
                                        <TableHead>Blood Pressure</TableHead>
                                        <TableHead>Heart Rate</TableHead>
                                        <TableHead>Weight</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="vital in paginatedVitals" :key="vital.id" class="hover:bg-muted/50">
                                        <TableCell>{{ formatDate(vital.recorded_at) }}</TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ vital.patient?.name || 'Unknown Patient' }}</span>
                                                <span v-if="vital.patient?.email" class="text-xs text-muted-foreground">{{ vital.patient.email }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>{{ vital.temperature ? `${vital.temperature}°C` : '-' }}</TableCell>
                                        <TableCell>{{ vital.blood_pressure || '-' }}</TableCell>
                                        <TableCell>{{ vital.heart_rate ? `${vital.heart_rate} bpm` : '-' }}</TableCell>
                                        <TableCell>{{ vital.weight ? `${vital.weight} kg` : '-' }}</TableCell>
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
                                                    <DropdownMenuItem @click="openDetailsDialog(vital)">
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
                                Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredVitals.length) }} of {{ filteredVitals.length }} vitals
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
    <!-- Vital Details Dialog -->
    <Dialog v-model:open="showDetailsDialog">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Vital Details</DialogTitle>
          <DialogDescription>
            Detailed information about this vital record.
          </DialogDescription>
        </DialogHeader>
        <div v-if="selectedVital" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Patient</label>
              <p class="text-sm font-medium">{{ selectedVital.patient?.name || 'Unknown Patient' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Recorded At</label>
              <p class="text-sm">{{ formatDate(selectedVital.recorded_at) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Temperature</label>
              <p class="text-sm">{{ selectedVital.temperature ? `${selectedVital.temperature}°C` : 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Blood Pressure</label>
              <p class="text-sm">{{ selectedVital.blood_pressure || 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Heart Rate</label>
              <p class="text-sm">{{ selectedVital.heart_rate ? `${selectedVital.heart_rate} bpm` : 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Respiratory Rate</label>
              <p class="text-sm">{{ selectedVital.respiratory_rate ? `${selectedVital.respiratory_rate} bpm` : 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Oxygen Saturation</label>
              <p class="text-sm">{{ selectedVital.oxygen_saturation ? `${selectedVital.oxygen_saturation}%` : 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Weight</label>
              <p class="text-sm">{{ selectedVital.weight ? `${selectedVital.weight} kg` : 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Height</label>
              <p class="text-sm">{{ selectedVital.height ? `${selectedVital.height} cm` : 'Not recorded' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Fasting</label>
              <p class="text-sm">
                <Badge :variant="selectedVital.fasting ? 'default' : 'secondary'">
                  {{ selectedVital.fasting ? 'Yes' : 'No' }}
                </Badge>
              </p>
            </div>
          </div>
          <div v-if="selectedVital.patient">
            <label class="text-sm font-medium text-muted-foreground">Patient Information</label>
            <div class="text-sm space-y-1">
              <p><strong>Email:</strong> {{ selectedVital.patient.email || 'Not provided' }}</p>
              <p><strong>Phone:</strong> {{ selectedVital.patient.phone || 'Not provided' }}</p>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
</template> 