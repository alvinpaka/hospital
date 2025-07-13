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
        title: 'My Lab Results',
        href: '/staff/lab-results',
    },
];




// State
const labResults = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');
const typeFilter = ref('all');

// Pagination
const currentPage = ref(1);
const pageSize = 10;

// Dialog state
const showDetailsDialog = ref(false);
const selectedLabResult = ref(null);

// Fetch lab results
async function fetchLabResults() {
    loading.value = true;
    try {
        const response = await fetch('/api/staff/lab-results', {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        if (response.ok) {
            const data = await response.json();
            labResults.value = data.labResults || [];
        } else {
            error.value = 'Failed to load lab results';
        }
    } catch (err) {
        error.value = 'Failed to load lab results';
        console.error('Error fetching lab results:', err);
    } finally {
        loading.value = false;
    }
}

// Computed properties
const filteredLabResults = computed(() => {
    let result = labResults.value;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(labResult =>
            labResult.patient?.name?.toLowerCase().includes(query) ||
            labResult.type?.toLowerCase().includes(query) ||
            labResult.result?.toLowerCase().includes(query)
        );
    }

    // Filter by type
    if (typeFilter.value !== 'all') {
        result = result.filter(labResult => labResult.type === typeFilter.value);
    }

    return result;
});

const paginatedLabResults = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredLabResults.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredLabResults.value.length / pageSize));

const uniqueTypes = computed(() => {
    const types = [...new Set(labResults.value.map(result => result.type))];
    return types.sort();
});

// Functions
function formatDate(dateStr: string) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function openDetailsDialog(labResult: any) {
    selectedLabResult.value = labResult;
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
    fetchLabResults();
});
</script>

<template>
    <Head title="My Lab Results" />
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
                                <h1 class="text-4xl font-bold text-white">My Lab Results</h1>
                                <p class="text-blue-100 text-lg">Staff Panel</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            View and manage lab results you've ordered or reviewed.
                        </p>
                    </div>
                </div>
                <!-- Filters -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by patient name, test type, or result..."
                            class="max-w-md"
                        />
                        <select v-model="typeFilter" class="border rounded-md px-3 py-2">
                            <option value="all">All Types</option>
                            <option v-for="type in uniqueTypes" :key="type" :value="type">
                                {{ type }}
                            </option>
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
                <!-- Lab Results Table -->
                <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardHeader>
                        <CardTitle>Lab Results ({{ filteredLabResults.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="filteredLabResults.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">No lab results found</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Patient</TableHead>
                                        <TableHead>Test Type</TableHead>
                                        <TableHead>Result</TableHead>
                                        <TableHead>Notes</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="labResult in paginatedLabResults" :key="labResult.id" class="hover:bg-muted/50">
                                        <TableCell>{{ formatDate(labResult.date) }}</TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ labResult.patient?.name || 'Unknown Patient' }}</span>
                                                <span v-if="labResult.patient?.email" class="text-xs text-muted-foreground">{{ labResult.patient.email }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ labResult.type }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ labResult.result }}</TableCell>
                                        <TableCell class="max-w-xs truncate">{{ labResult.notes || '-' }}</TableCell>
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
                                                    <DropdownMenuItem @click="openDetailsDialog(labResult)">
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
                                Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredLabResults.length) }} of {{ filteredLabResults.length }} lab results
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
    <!-- Lab Result Details Dialog -->
    <Dialog v-model:open="showDetailsDialog">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Lab Result Details</DialogTitle>
          <DialogDescription>
            Detailed information about this lab result.
          </DialogDescription>
        </DialogHeader>
        <div v-if="selectedLabResult" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Patient</label>
              <p class="text-sm font-medium">{{ selectedLabResult.patient?.name || 'Unknown Patient' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Test Type</label>
              <p class="text-sm">
                <Badge variant="outline">{{ selectedLabResult.type }}</Badge>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Result</label>
              <p class="text-sm font-medium">{{ selectedLabResult.result }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Date</label>
              <p class="text-sm">{{ formatDate(selectedLabResult.date) }}</p>
            </div>
          </div>
          <div>
            <label class="text-sm font-medium text-muted-foreground">Notes</label>
            <p class="text-sm">{{ selectedLabResult.notes || 'No notes available' }}</p>
          </div>
          <div v-if="selectedLabResult.patient">
            <label class="text-sm font-medium text-muted-foreground">Patient Information</label>
            <div class="text-sm space-y-1">
              <p><strong>Email:</strong> {{ selectedLabResult.patient.email || 'Not provided' }}</p>
              <p><strong>Phone:</strong> {{ selectedLabResult.patient.phone || 'Not provided' }}</p>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
</template> 