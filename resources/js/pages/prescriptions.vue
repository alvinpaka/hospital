<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

interface Prescription {
  id: number;
  medication: string;
  dosage: string;
  instructions: string;
  doctor: string;
  start_date: string;
  end_date: string;
}

const allPrescriptions = ref<Prescription[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 5;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof Prescription>('start_date');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Prescriptions', href: '/prescriptions' },
];

const prescriptions = usePage().props.prescriptions;

onMounted(() => {
  try {
    allPrescriptions.value = Array.isArray(prescriptions) ? prescriptions : [];
  } catch (err) {
    error.value = 'Failed to load prescriptions. Please try again later.';
    console.error('Error loading prescriptions:', err);
  } finally {
    loading.value = false;
  }
});

const filteredPrescriptions = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allPrescriptions.value.filter(prescription =>
    prescription.medication.toLowerCase().includes(query) ||
    prescription.doctor.toLowerCase().includes(query) ||
    prescription.dosage.toLowerCase().includes(query)
  );
});

const sortedPrescriptions = computed(() => {
  return [...filteredPrescriptions.value].sort((a, b) => {
    const aValue = a[sortColumn.value];
    const bValue = b[sortColumn.value];
    if (sortColumn.value === 'start_date' || sortColumn.value === 'end_date') {
      return sortDirection.value === 'asc'
        ? new Date(aValue).getTime() - new Date(bValue).getTime()
        : new Date(bValue).getTime() - new Date(aValue).getTime();
    }
    return sortDirection.value === 'asc'
      ? aValue.localeCompare(bValue)
      : bValue.localeCompare(aValue);
  });
});

const paginatedPrescriptions = computed(() => {
  const start = (page.value - 1) * pageSize;
  return sortedPrescriptions.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredPrescriptions.value.length / pageSize));

function formatDate(dateStr: string) {
  if (!dateStr) return 'N/A';
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  } catch {
    return 'Invalid Date';
  }
}

function toggleSort(column: keyof Prescription) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  page.value = 1;
}

function goToPage(pageNumber: number) {
  if (pageNumber >= 1 && pageNumber <= totalPages.value) {
    page.value = pageNumber;
  }
}

function getPageNumbers() {
  const pages = [];
  const maxPagesToShow = 5;
  const startPage = Math.max(1, page.value - Math.floor(maxPagesToShow / 2));
  const endPage = Math.min(totalPages.value, startPage + maxPagesToShow - 1);
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
}

// Instructions dialog state
const showInstructionsDialog = ref(false);
const instructionsDialogText = ref('');
function openInstructionsDialog(text: string) {
  instructionsDialogText.value = text;
  showInstructionsDialog.value = true;
}
function closeInstructionsDialog() {
  showInstructionsDialog.value = false;
  instructionsDialogText.value = '';
}
function truncateInstructions(text: string, maxLength = 30) {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text;
}
</script>

<template>
  <Head title="Prescriptions" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8 min-h-[70vh]">
      <div class="space-y-2 mb-8">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <span>My Prescriptions</span>
            <Badge variant="secondary">Patient</Badge>
          </h1>
          <Input
            v-model="searchQuery"
            placeholder="Search by medication, doctor, or dosage..."
            class="max-w-sm"
          />
        </div>
        <p class="text-muted-foreground text-sm">View and manage your prescriptions.</p>
      </div>
      <Card v-if="error">
        <CardContent class="p-6 text-center text-red-600">
          {{ error }}
        </CardContent>
      </Card>
      <Card v-else-if="loading">
        <CardContent class="p-6 space-y-4">
          <Skeleton class="h-8 w-full" />
          <Skeleton class="h-8 w-full" />
          <Skeleton class="h-8 w-full" />
        </CardContent>
      </Card>
      <Card v-else-if="filteredPrescriptions.length === 0">
        <CardContent class="p-6 text-center flex flex-col items-center">
          <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
            <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
            <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
          </svg>
          <p class="text-lg font-semibold">No prescriptions found.</p>
          <p class="text-sm mt-2 text-muted-foreground">Your prescriptions will appear here.</p>
        </CardContent>
      </Card>
      <Card v-else>
        <CardHeader>
          <CardTitle>Prescription History</CardTitle>
        </CardHeader>
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="cursor-pointer" @click="toggleSort('medication')">
                  Medication
                  <span v-if="sortColumn === 'medication'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('dosage')">
                  Dosage
                  <span v-if="sortColumn === 'dosage'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead>Instructions</TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('doctor')">
                  Doctor
                  <span v-if="sortColumn === 'doctor'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('start_date')">
                  Start Date
                  <span v-if="sortColumn === 'start_date'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('end_date')">
                  End Date
                  <span v-if="sortColumn === 'end_date'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in paginatedPrescriptions" :key="item.id" class="hover:bg-muted/50 transition-colors">
                <TableCell>{{ item.medication || 'N/A' }}</TableCell>
                <TableCell>{{ item.dosage || 'N/A' }}</TableCell>
                <TableCell>
                  <span v-if="item.instructions && item.instructions.length > 30">
                    {{ truncateInstructions(item.instructions) }}
                    <Button variant="link" size="sm" class="px-1 py-0 h-auto align-baseline" @click="openInstructionsDialog(item.instructions)">View More</Button>
                  </span>
                  <span v-else>{{ item.instructions }}</span>
                </TableCell>
                <TableCell>{{ item.doctor || 'N/A' }}</TableCell>
                <TableCell>{{ formatDate(item.start_date) }}</TableCell>
                <TableCell>{{ formatDate(item.end_date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t gap-4">
          <span class="text-sm text-muted-foreground">
            Showing {{ (page - 1) * pageSize + 1 }} to {{ Math.min(page * pageSize, filteredPrescriptions.length) }} of {{ filteredPrescriptions.length }} prescriptions
          </span>
          <div class="flex gap-2 flex-wrap justify-center">
            <Button 
              :disabled="page === 1" 
              variant="outline" 
              size="sm" 
              @click="page--"
            >
              Prev
            </Button>
            <Button
              v-for="pageNumber in getPageNumbers()"
              :key="pageNumber"
              :variant="page === pageNumber ? 'default' : 'outline'"
              size="sm"
              @click="goToPage(pageNumber)"
            >
              {{ pageNumber }}
            </Button>
            <Button 
              :disabled="page === totalPages || totalPages === 0" 
              variant="outline" 
              size="sm" 
              @click="page++"
            >
              Next
            </Button>
          </div>
        </div>
      </Card>
      <Dialog v-model:open="showInstructionsDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Prescription Instructions</DialogTitle>
          </DialogHeader>
          <div class="whitespace-pre-line text-base">{{ instructionsDialogText }}</div>
          <DialogFooter>
            <Button variant="outline" @click="closeInstructionsDialog">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
