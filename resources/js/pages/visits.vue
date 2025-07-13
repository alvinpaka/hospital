<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';

interface Visit {
  id: number;
  date: string;
  doctor: string | { name: string };
  reason: string;
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

// Replace Inertia props with API fetch
async function fetchVisits() {
  loading.value = true;
  error.value = null;
  try {
    const response = await fetch('/api/visits', { credentials: 'include' });
    if (response.ok) {
      const data = await response.json();
      allVisits.value = Array.isArray(data) ? data : (data.visits || []);
    } else {
      error.value = 'Failed to load visits. Please try again later.';
    }
  } catch (err) {
    error.value = 'Failed to load visits. Please try again later.';
    console.error('Error loading visits:', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchVisits();
});

const filteredVisits = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allVisits.value.filter(visit => {
    const doctorName = typeof visit.doctor === 'string' ? visit.doctor : visit.doctor?.name || '';
    return doctorName.toLowerCase().includes(query) ||
           (visit.reason ?? '').toLowerCase().includes(query);
  });
});

const sortedVisits = computed(() => {
  return [...filteredVisits.value].sort((a, b) => {
    let aValue = a[sortColumn.value];
    let bValue = b[sortColumn.value];
    
    // Handle doctor field which might be an object
    if (sortColumn.value === 'doctor') {
      aValue = typeof aValue === 'string' ? aValue : aValue?.name || '';
      bValue = typeof bValue === 'string' ? bValue : bValue?.name || '';
    }
    
    if (sortColumn.value === 'date') {
      return sortDirection.value === 'asc'
        ? new Date(aValue).getTime() - new Date(bValue).getTime()
        : new Date(bValue).getTime() - new Date(aValue).getTime();
    }
    return sortDirection.value === 'asc'
      ? String(aValue).localeCompare(String(bValue))
      : String(bValue).localeCompare(String(aValue));
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

</script>

<template>
  <Head title="Visits" />
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
                <h1 class="text-4xl font-bold text-white">My Visits</h1>
                <p class="text-blue-100 text-lg">Patient Panel</p>
              </div>
            </div>
            <p class="text-blue-100 text-lg">
              View and manage your past and upcoming medical visits.
            </p>
          </div>
        </div>
        <!-- Search -->
        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
          <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
            <Input
              v-model="searchQuery"
              placeholder="Search by doctor, or reason..."
              class="max-w-md"
            />
          </CardContent>
        </Card>
        <!-- Error Display -->
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
        <!-- Empty State -->
        <Card v-else-if="filteredVisits.length === 0" class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardContent class="p-6 text-center flex flex-col items-center">
            <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
              <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
              <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <p class="text-lg font-semibold">No visits found.</p>
            <p class="text-sm mt-2 text-muted-foreground">Your medical visits will appear here.</p>
          </CardContent>
        </Card>
        <!-- Visits Table -->
        <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardHeader>
            <CardTitle>Visit History</CardTitle>
          </CardHeader>
          <CardContent>
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
                    <TableHead class="cursor-pointer" @click="toggleSort('reason')">
                      Reason
                      <span v-if="sortColumn === 'reason'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="item in paginatedVisits" :key="item.id" class="hover:bg-muted/50 transition-colors">
                    <TableCell>{{ formatDate(item.date) }}</TableCell>
                    <TableCell>{{ typeof item.doctor === 'string' ? item.doctor : item.doctor?.name || 'N/A' }}</TableCell>
                    <TableCell>{{ item.reason || 'N/A' }}</TableCell>
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
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
