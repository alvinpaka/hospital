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

interface LabResult {
  id: number;
  date: string;
  type: string;
  result: string;
  doctor: string;
  notes: string;
}

const allLabResults = ref<LabResult[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 5;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof LabResult>('date');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Lab Results', href: '/lab-results' },
];

const labResults = usePage().props.labResults;

onMounted(() => {
  try {
    allLabResults.value = Array.isArray(labResults) ? labResults : [];
  } catch (err) {
    error.value = 'Failed to load lab results. Please try again later.';
    console.error('Error loading lab results:', err);
  } finally {
    loading.value = false;
  }
});

const filteredLabResults = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allLabResults.value.filter(result =>
    result.type.toLowerCase().includes(query) ||
    result.doctor.toLowerCase().includes(query) ||
    result.result.toLowerCase().includes(query)
  );
});

const sortedLabResults = computed(() => {
  return [...filteredLabResults.value].sort((a, b) => {
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

const paginatedLabResults = computed(() => {
  const start = (page.value - 1) * pageSize;
  return sortedLabResults.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredLabResults.value.length / pageSize));

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

function toggleSort(column: keyof LabResult) {
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

// Notes dialog state
const showNotesDialog = ref(false);
const notesDialogText = ref('');
function openNotesDialog(text: string) {
  notesDialogText.value = text;
  showNotesDialog.value = true;
}
function closeNotesDialog() {
  showNotesDialog.value = false;
  notesDialogText.value = '';
}
function truncateNotes(text: string, maxLength = 60) {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text;
}
</script>

<template>
  <Head title="Lab Results" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8 min-h-[70vh]">
      <div class="space-y-2 mb-8">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <span>My Lab Results</span>
            <Badge variant="secondary">Patient</Badge>
          </h1>
          <Input
            v-model="searchQuery"
            placeholder="Search by type, doctor, or result..."
            class="max-w-sm"
          />
        </div>
        <p class="text-muted-foreground text-sm">View and manage your lab results.</p>
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
      <Card v-else-if="filteredLabResults.length === 0">
        <CardContent class="p-6 text-center flex flex-col items-center">
          <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
            <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
            <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
          </svg>
          <p class="text-lg font-semibold">No lab results found.</p>
          <p class="text-sm mt-2 text-muted-foreground">Your lab results will appear here.</p>
        </CardContent>
      </Card>
      <Card v-else class="w-full">
        <CardHeader>
          <CardTitle>Lab Results History</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
          <div class="w-full overflow-x-auto">
            <Table class="min-w-[1100px] w-full">
              <TableHeader>
                <TableRow>
                  <TableHead class="cursor-pointer" @click="toggleSort('date')">
                    Date
                    <span v-if="sortColumn === 'date'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </TableHead>
                  <TableHead class="cursor-pointer" @click="toggleSort('type')">
                    Type
                    <span v-if="sortColumn === 'type'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </TableHead>
                  <TableHead class="cursor-pointer" @click="toggleSort('result')">
                    Result
                    <span v-if="sortColumn === 'result'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </TableHead>
                  <TableHead class="cursor-pointer" @click="toggleSort('doctor')">
                    Doctor
                    <span v-if="sortColumn === 'doctor'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </TableHead>
                  <TableHead>Notes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in paginatedLabResults" :key="item.id" class="hover:bg-muted/50 transition-colors">
                  <TableCell>{{ formatDate(item.date) }}</TableCell>
                  <TableCell>{{ item.type || 'N/A' }}</TableCell>
                  <TableCell>{{ item.result || 'N/A' }}</TableCell>
                  <TableCell>{{ item.doctor || 'N/A' }}</TableCell>
                  <TableCell>
                    <span v-if="item.notes && item.notes.length > 60">
                      {{ truncateNotes(item.notes) }}
                      <Button variant="link" size="sm" class="px-1 py-0 h-auto align-baseline" @click="openNotesDialog(item.notes)">View More</Button>
                    </span>
                    <span v-else>{{ item.notes }}</span>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
        <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t gap-4">
          <span class="text-sm text-muted-foreground">
            Showing {{ (page - 1) * pageSize + 1 }} to {{ Math.min(page * pageSize, filteredLabResults.length) }} of {{ filteredLabResults.length }} lab results
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
      <Dialog v-model:open="showNotesDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Lab Result Notes</DialogTitle>
          </DialogHeader>
          <div class="whitespace-pre-line text-base">{{ notesDialogText }}</div>
          <DialogFooter>
            <Button variant="outline" @click="closeNotesDialog">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
