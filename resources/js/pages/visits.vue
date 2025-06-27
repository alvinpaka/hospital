<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';

interface Visit {
  id: number;
  date: string;
  doctor: string;
  department: string;
  reason: string;
  summary: string;
}

const allVisits = ref<Visit[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 5;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof Visit>('date');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Visits', href: '/visits' },
];

const visits = usePage().props.visits;

onMounted(() => {
  try {
    allVisits.value = Array.isArray(visits) ? visits : [];
  } catch (err) {
    error.value = 'Failed to load visits. Please try again later.';
    console.error('Error loading visits:', err);
  } finally {
    loading.value = false;
  }
});

const filteredVisits = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allVisits.value.filter(visit =>
    visit.doctor.toLowerCase().includes(query) ||
    visit.department.toLowerCase().includes(query) ||
    visit.reason.toLowerCase().includes(query)
  );
});

const sortedVisits = computed(() => {
  return [...filteredVisits.value].sort((a, b) => {
    const aValue = a[sortColumn.value];
    const bValue = b[sortColumn.value];
    if (sortColumn.value === 'date') {
      return sortDirection.value === 'asc'
        ? new Date(aValue).getTime() - new Date(bValue).getTime()
        : new Date(bValue).getTime() - new Date(aValue).getTime();
    }
    return sortDirection.value === 'asc'
      ? aValue.localeCompare(bValue)
      : bValue.localeCompare(aValue);
  });
});

const paginatedVisits = computed(() => {
  const start = (page.value - 1) * pageSize;
  return sortedVisits.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredVisits.value.length / pageSize));

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

function toggleSort(column: keyof Visit) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  page.value = 1; // Reset to first page on sort
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

// Summary dialog state
const showSummaryDialog = ref(false);
const summaryDialogText = ref('');
function openSummaryDialog(text: string) {
  summaryDialogText.value = text;
  showSummaryDialog.value = true;
}
function closeSummaryDialog() {
  showSummaryDialog.value = false;
  summaryDialogText.value = '';
}
function truncateSummary(text: string, maxLength = 40) {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text;
}
</script>

<template>
  <Head title="Visits" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8 min-h-[70vh]">
      <div class="space-y-2 mb-8">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <span>My Visits</span>
            <Badge variant="secondary">Patient</Badge>
          </h1>
          <Input
            v-model="searchQuery"
            placeholder="Search by doctor, department, or reason..."
            class="max-w-sm"
          />
        </div>
        <p class="text-muted-foreground text-sm">View and manage your past and upcoming medical visits.</p>
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
      <Card v-else-if="filteredVisits.length === 0">
        <CardContent class="p-6 text-center flex flex-col items-center">
          <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
            <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
            <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
          </svg>
          <p class="text-lg font-semibold">No visits found.</p>
          <p class="text-sm mt-2 text-muted-foreground">Your medical visits will appear here.</p>
        </CardContent>
      </Card>
      <Card v-else>
        <CardHeader>
          <CardTitle>Visit History</CardTitle>
        </CardHeader>
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="cursor-pointer" @click="toggleSort('date')">
                  Date
                  <span v-if="sortColumn === 'date'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('doctor')">
                  Doctor
                  <span v-if="sortColumn === 'doctor'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('department')">
                  Department
                  <span v-if="sortColumn === 'department'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead class="cursor-pointer" @click="toggleSort('reason')">
                  Reason
                  <span v-if="sortColumn === 'reason'" class="ml-1">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead>Summary</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in paginatedVisits" :key="item.id" class="hover:bg-muted/50 transition-colors">
                <TableCell>{{ formatDate(item.date) }}</TableCell>
                <TableCell>{{ item.doctor || 'N/A' }}</TableCell>
                <TableCell>{{ item.department || 'N/A' }}</TableCell>
                <TableCell>{{ item.reason || 'N/A' }}</TableCell>
                <TableCell>
                  <span v-if="item.summary && item.summary.length > 40">
                    {{ truncateSummary(item.summary) }}
                    <Button variant="link" size="sm" class="px-1 py-0 h-auto align-baseline" @click="openSummaryDialog(item.summary)">View More</Button>
                  </span>
                  <span v-else>{{ item.summary }}</span>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t gap-4">
          <span class="text-sm text-muted-foreground">
            Showing {{ (page - 1) * pageSize + 1 }} to {{ Math.min(page * pageSize, filteredVisits.length) }} of {{ filteredVisits.length }} visits
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
      <Dialog v-model:open="showSummaryDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Visit Summary</DialogTitle>
          </DialogHeader>
          <div class="whitespace-pre-line text-base">{{ summaryDialogText }}</div>
          <DialogFooter>
            <Button variant="outline" @click="closeSummaryDialog">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
