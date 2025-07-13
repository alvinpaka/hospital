<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';

interface VitalsRecord {
  id: number;
  temperature: number;
  blood_pressure: string;
  heart_rate: number;
  respiratory_rate: number;
  oxygen_saturation: number;
  weight: number;
  height: number;
  fasting: boolean;
  recorded_by: string;
  recorded_at: string;
}

const allVitals = ref<VitalsRecord[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 5;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof VitalsRecord>('recorded_at');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Vitals', href: '/vitals' },
];

// Replace Inertia props with API fetch
async function fetchVitals() {
  loading.value = true;
  error.value = null;
  try {
    const response = await fetch('/api/vitals', { credentials: 'include' });
    if (response.ok) {
      const data = await response.json();
      allVitals.value = Array.isArray(data) ? data : (data.vitals || []);
    } else {
      error.value = 'Failed to load vitals. Please try again later.';
    }
  } catch (err) {
    error.value = 'Failed to load vitals. Please try again later.';
    console.error('Error loading vitals:', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchVitals();
});

const filteredVitals = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allVitals.value.filter(record =>
    record.recorded_by.toLowerCase().includes(query) ||
    record.blood_pressure.toLowerCase().includes(query) ||
    record.temperature.toString().includes(query) ||
    record.heart_rate.toString().includes(query) ||
    record.respiratory_rate.toString().includes(query) ||
    record.oxygen_saturation.toString().includes(query) ||
    record.weight.toString().includes(query) ||
    record.height.toString().includes(query) ||
    (record.fasting ? 'yes' : 'no').includes(query) ||
    record.recorded_at.toLowerCase().includes(query)
  );
});

const sortedVitals = computed(() => {
  return [...filteredVitals.value].sort((a, b) => {
    const aValue = a[sortColumn.value];
    const bValue = b[sortColumn.value];
    if (sortColumn.value === 'recorded_at') {
      return sortDirection.value === 'asc'
        ? new Date(aValue).getTime() - new Date(bValue).getTime()
        : new Date(bValue).getTime() - new Date(aValue).getTime();
    }
    if (typeof aValue === 'number' && typeof bValue === 'number') {
      return sortDirection.value === 'asc' ? aValue - bValue : bValue - aValue;
    }
    return sortDirection.value === 'asc'
      ? String(aValue).localeCompare(String(bValue))
      : String(bValue).localeCompare(String(aValue));
  });
});

const paginatedVitals = computed(() => {
  const start = (page.value - 1) * pageSize;
  return sortedVitals.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredVitals.value.length / pageSize));

function formatDate(dateStr: string) {
  if (!dateStr) return 'N/A';
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return 'Invalid Date';
  }
}

function toggleSort(column: keyof VitalsRecord) {
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
</script>

<template>
  <Head title="Vitals" />
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
                <p class="text-blue-100 text-lg">Patient Panel</p>
              </div>
            </div>
            <p class="text-blue-100 text-lg">
              View your health vitals recorded during clinic visits.
            </p>
          </div>
        </div>
        <!-- Search -->
        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
          <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
            <Input
              v-model="searchQuery"
              placeholder="Search by nurse, value, or date..."
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
        <Card v-else-if="filteredVitals.length === 0" class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardContent class="p-6 text-center flex flex-col items-center">
            <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
              <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
              <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <p class="text-lg font-semibold">No vitals found.</p>
            <p class="text-sm mt-2 text-muted-foreground">Your vitals will show here after a clinic visit.</p>
          </CardContent>
        </Card>
        <!-- Vitals Table -->
        <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardHeader>
            <CardTitle>Vitals History</CardTitle>
          </CardHeader>
          <CardContent class="p-0">
            <div class="w-full overflow-x-auto">
              <Table class="min-w-[1100px] w-full">
                <TableHeader>
                  <TableRow>
                    <TableHead class="cursor-pointer" @click="toggleSort('recorded_at')">
                      Date
                      <span v-if="sortColumn === 'recorded_at'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('temperature')">
                      Temp (°C)
                      <span v-if="sortColumn === 'temperature'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('blood_pressure')">
                      BP
                      <span v-if="sortColumn === 'blood_pressure'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('heart_rate')">
                      Pulse
                      <span v-if="sortColumn === 'heart_rate'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('respiratory_rate')">
                      Resp
                      <span v-if="sortColumn === 'respiratory_rate'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('oxygen_saturation')">
                      SpO₂
                      <span v-if="sortColumn === 'oxygen_saturation'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('weight')">
                      Wt (kg)
                      <span v-if="sortColumn === 'weight'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('height')">
                      Ht (cm)
                      <span v-if="sortColumn === 'height'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('fasting')">
                      Fasting
                      <span v-if="sortColumn === 'fasting'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('recorded_by')">
                      Updated By
                      <span v-if="sortColumn === 'recorded_by'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow 
                    v-for="record in paginatedVitals" 
                    :key="record.id"
                    class="hover:bg-muted/50 transition-colors"
                  >
                    <TableCell>{{ formatDate(record.recorded_at) }}</TableCell>
                    <TableCell>{{ record.temperature }}</TableCell>
                    <TableCell>{{ record.blood_pressure }}</TableCell>
                    <TableCell>{{ record.heart_rate }}</TableCell>
                    <TableCell>{{ record.respiratory_rate }}</TableCell>
                    <TableCell>{{ record.oxygen_saturation }}%</TableCell>
                    <TableCell>{{ record.weight }}</TableCell>
                    <TableCell>{{ record.height }}</TableCell>
                    <TableCell>
                      <Badge :variant="record.fasting ? 'default' : 'secondary'">
                        {{ record.fasting ? 'Yes' : 'No' }}
                      </Badge>
                    </TableCell>
                    <TableCell>{{ record.recorded_by }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
          <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t gap-4">
            <span class="text-sm text-muted-foreground">
              Showing {{ (page - 1) * pageSize + 1 }} to {{ Math.min(page * pageSize, filteredVitals.length) }} of {{ filteredVitals.length }} vitals
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
      </div>
    </div>
  </AppLayout>
</template>
