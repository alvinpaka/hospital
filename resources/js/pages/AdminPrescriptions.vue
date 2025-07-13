<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';

import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { jsPDF } from 'jspdf';

interface Prescription {
  id: number;
  medication: string;
  dosage: string;
  instructions: string;
  doctor: string;
  patient: string;
  start_date: string;
  end_date: string;
}

const allPrescriptions = ref<Prescription[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 10;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof Prescription>('start_date');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Prescriptions', href: '/admin/prescriptions' },
];

const user = computed(() => usePage().props.auth?.user || usePage().props.user);
const prescriptions = usePage().props.prescriptions;

onMounted(() => {
  if (!user.value || user.value.role !== 'admin') return;
  try {
    allPrescriptions.value = Array.isArray(prescriptions) ? prescriptions : [];
  } catch (err) {
    error.value = 'Failed to load prescriptions. Please try again later.';
    console.error('Error loading prescriptions:', err);
  } finally {
    loading.value = false;
  }
});

// Update filtering to use drug generic_name
const filteredPrescriptions = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allPrescriptions.value.filter(prescription =>
    (prescription.drug?.generic_name ?? '').toLowerCase().includes(query) ||
    (prescription.doctor ?? '').toLowerCase().includes(query) ||
    (prescription.patient ?? '').toLowerCase().includes(query) ||
    (prescription.dosage ?? '').toLowerCase().includes(query)
  );
});

// Update sorting to use drug generic_name if sorting by medication
const sortedPrescriptions = computed(() => {
  return [...filteredPrescriptions.value].sort((a, b) => {
    let aValue = a[sortColumn.value];
    let bValue = b[sortColumn.value];
    if (sortColumn.value === 'medication') {
      aValue = a.drug?.generic_name ?? '';
      bValue = b.drug?.generic_name ?? '';
    }
    if (sortColumn.value === 'start_date' || sortColumn.value === 'end_date') {
      return sortDirection.value === 'asc'
        ? new Date(aValue).getTime() - new Date(bValue).getTime()
        : new Date(bValue).getTime() - new Date(aValue).getTime();
    }
    return sortDirection.value === 'asc'
      ? String(aValue).localeCompare(String(bValue))
      : String(bValue).localeCompare(String(aValue));
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

// Dialog states for edit, delete, assign, export
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showAssignDialog = ref(false);
const showExportDialog = ref(false);
const showInstructionsDialog = ref(false);
const instructionsDialogText = ref('');
const selectedPrescription = ref<Prescription | null>(null);

// Export dialog state for single record
const showSingleExportDialog = ref(false);
const singleExportPrescription = ref(null);

function openEditDialog(prescription: Prescription) {
  selectedPrescription.value = prescription;
  showEditDialog.value = true;
}
function openDeleteDialog(prescription: Prescription) {
  selectedPrescription.value = prescription;
  showDeleteDialog.value = true;
}
function openAssignDialog(prescription: Prescription) {
  selectedPrescription.value = prescription;
  showAssignDialog.value = true;
}
function openExportDialog() {
  showExportDialog.value = true;
}
function openInstructionsDialog(text: string) {
  instructionsDialogText.value = text;
  showInstructionsDialog.value = true;
}
function closeInstructionsDialog() {
  showInstructionsDialog.value = false;
  instructionsDialogText.value = '';
}

// Dummy handlers for actions (to be implemented)
function handleEdit() { showEditDialog.value = false; }
function handleDelete() { showDeleteDialog.value = false; }
function handleAssign() { showAssignDialog.value = false; }

function handleExportSingle(id: number) {
  const record = allPrescriptions.value.find(v => v.id === id);
  if (!record) {
    toast.error('Prescription not found');
    return;
  }
  singleExportPrescription.value = record;
  showSingleExportDialog.value = true;
}

function handleExportCSV() {
  const prescriptions = allPrescriptions.value;
  if (!prescriptions.length) {
    toast.error('No prescriptions to export');
    return;
  }
  const headers = ['ID', 'Medication', 'Dosage', 'Instructions', 'Doctor', 'Patient', 'Start Date', 'End Date'];
  const rows = prescriptions.map(v => [
    v.id,
    v.drug?.generic_name ?? '',
    v.dosage,
    v.instructions,
    v.doctor,
    v.patient,
    v.start_date,
    v.end_date
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'prescriptions.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showExportDialog.value = false;
}

function handleExportExcel() {
  const prescriptions = allPrescriptions.value;
  if (!prescriptions.length) {
    toast.error('No prescriptions to export');
    return;
  }
  const headers = ['ID', 'Medication', 'Dosage', 'Instructions', 'Doctor', 'Patient', 'Start Date', 'End Date'];
  const rows = prescriptions.map(v => [
    v.id,
    v.drug?.generic_name ?? '',
    v.dosage,
    v.instructions,
    v.doctor,
    v.patient,
    v.start_date,
    v.end_date
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'prescriptions.xlsx');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showExportDialog.value = false;
}

function handleExportPDF() {
  const prescriptions = allPrescriptions.value;
  if (!prescriptions.length) {
    toast.error('No prescriptions to export');
    return;
  }
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Medication', 'Dosage', 'Instructions', 'Doctor', 'Patient', 'Start Date', 'End Date'];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Prescriptions Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 30, y);
    });
    y += 8;
    prescriptions.forEach(v => {
      const row = [v.id, v.drug?.generic_name ?? '', v.dosage, v.instructions, v.doctor, v.patient, v.start_date, v.end_date];
      row.forEach((cell, i) => {
        doc.text(String(cell), 10 + i * 30, y);
      });
      y += 8;
      if (y > 270) {
        doc.addPage();
        y = 10;
      }
    });
    doc.save('prescriptions.pdf');
    toast.success('PDF exported');
  } catch {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showExportDialog.value = false;
}

function handleExportSingleCSV() {
  const v = singleExportPrescription.value;
  if (!v) return;
  const headers = ['ID', 'Medication', 'Dosage', 'Instructions', 'Doctor', 'Patient', 'Start Date', 'End Date'];
  const row = [v.id, v.drug?.generic_name ?? '', v.dosage, v.instructions, v.doctor, v.patient, v.start_date, v.end_date];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `prescription-${v.id}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showSingleExportDialog.value = false;
}

function handleExportSingleExcel() {
  const v = singleExportPrescription.value;
  if (!v) return;
  const headers = ['ID', 'Medication', 'Dosage', 'Instructions', 'Doctor', 'Patient', 'Start Date', 'End Date'];
  const row = [v.id, v.drug?.generic_name ?? '', v.dosage, v.instructions, v.doctor, v.patient, v.start_date, v.end_date];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `prescription-${v.id}.xlsx`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showSingleExportDialog.value = false;
}

function handleExportSinglePDF() {
  const v = singleExportPrescription.value;
  if (!v) return;
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Medication', 'Dosage', 'Instructions', 'Doctor', 'Patient', 'Start Date', 'End Date'];
    const row = [v.id, v.drug?.generic_name ?? '', v.dosage, v.instructions, v.doctor, v.patient, v.start_date, v.end_date];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Prescription Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 30, y);
    });
    y += 8;
    row.forEach((cell, i) => {
      doc.text(String(cell), 10 + i * 30, y);
    });
    doc.save(`prescription-${v.id}.pdf`);
    toast.success('PDF exported');
  } catch {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showSingleExportDialog.value = false;
}
</script>

<template>
  <Head title="Admin Prescriptions" />
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
                <h1 class="text-4xl font-bold text-white">All Prescriptions</h1>
                <p class="text-blue-100 text-lg">Admin Panel</p>
              </div>
            </div>
            <p class="text-blue-100 text-lg">
              View, edit, assign, delete, and export all prescriptions in the system.
            </p>
          </div>
        </div>
        <!-- Search/Filters and Export -->
        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
          <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
            <Input
              v-model="searchQuery"
              placeholder="Search by medication, doctor, patient, or dosage..."
              class="max-w-md"
            />
            <Button variant="outline" @click="openExportDialog" class="ml-auto">Export</Button>
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
        <Card v-else-if="filteredPrescriptions.length === 0" class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardContent class="p-6 text-center flex flex-col items-center">
            <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
              <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
              <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <p class="text-lg font-semibold">No prescriptions found.</p>
            <p class="text-sm mt-2 text-muted-foreground">No prescriptions in the system.</p>
          </CardContent>
        </Card>
        <!-- Prescriptions Table -->
        <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardHeader>
            <CardTitle>All Prescriptions</CardTitle>
          </CardHeader>
          <CardContent>
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
                    <TableHead class="cursor-pointer" @click="toggleSort('doctor')">
                      Doctor
                      <span v-if="sortColumn === 'doctor'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                      </span>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="toggleSort('patient')">
                      Patient
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
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="item in paginatedPrescriptions" :key="item.id" class="hover:bg-muted/50 transition-colors">
                    <TableCell>{{ item.drug?.generic_name || 'N/A' }}</TableCell>
                    <TableCell>{{ item.dosage || 'N/A' }}</TableCell>
                    <TableCell>{{ item.doctor || 'N/A' }}</TableCell>
                    <TableCell>{{ item.patient?.name || 'N/A' }}</TableCell>
                    <TableCell>{{ formatDate(item.start_date) }}</TableCell>
                    <TableCell>{{ formatDate(item.end_date) }}</TableCell>
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
                          <DropdownMenuItem @click="openEditDialog(item)">Edit</DropdownMenuItem>
                          <DropdownMenuItem @click="openAssignDialog(item)">Assign</DropdownMenuItem>
                          <DropdownMenuItem @click="handleExportSingle(item.id)">Export</DropdownMenuItem>
                          <DropdownMenuItem @click="openInstructionsDialog(item.instructions)">View Instructions</DropdownMenuItem>
                          <DropdownMenuItem @click="openDeleteDialog(item)" class="text-red-600 focus:text-red-600">Delete</DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
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
          </CardContent>
        </Card>
        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Prescription</DialogTitle>
              <DialogDescription>
                Edit the details of the selected prescription.
              </DialogDescription>
            </DialogHeader>
            <div>Edit form goes here (to be implemented)</div>
            <DialogFooter>
              <Button variant="outline" @click="showEditDialog = false">Cancel</Button>
              <Button variant="default" @click="handleEdit">Save</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Assign Dialog -->
        <Dialog v-model:open="showAssignDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Assign Prescription</DialogTitle>
              <DialogDescription>
                Assign this prescription to a doctor or patient.
              </DialogDescription>
            </DialogHeader>
            <div>Assign form goes here (to be implemented)</div>
            <DialogFooter>
              <Button variant="outline" @click="showAssignDialog = false">Cancel</Button>
              <Button variant="default" @click="handleAssign">Assign</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Delete Dialog -->
        <Dialog v-model:open="showDeleteDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Delete Prescription</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete this prescription?
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
              <Button variant="destructive" @click="handleDelete">Delete</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Export Dialog -->
        <Dialog v-model:open="showExportDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Export Prescriptions</DialogTitle>
              <DialogDescription>
                Export all prescriptions as CSV or Excel file.
              </DialogDescription>
            </DialogHeader>
            <div class="flex flex-col gap-2">
              <Button variant="outline" @click="handleExportCSV">Export as CSV</Button>
              <Button variant="outline" @click="handleExportExcel">Export as Excel</Button>
              <Button variant="outline" @click="handleExportPDF">Export as PDF</Button>
            </div>
            <DialogFooter>
              <Button variant="outline" @click="showExportDialog = false">Close</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Instructions Dialog -->
        <Dialog v-model:open="showInstructionsDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Prescription Instructions</DialogTitle>
              <DialogDescription>
                Full instructions for the selected prescription.
              </DialogDescription>
            </DialogHeader>
            <div class="whitespace-pre-line text-base">{{ instructionsDialogText }}</div>
            <DialogFooter>
              <Button variant="outline" @click="closeInstructionsDialog">Close</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Single Export Dialog -->
        <Dialog v-model:open="showSingleExportDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Export Prescription</DialogTitle>
              <DialogDescription>
                Export the selected prescription as CSV, Excel, or PDF.
              </DialogDescription>
            </DialogHeader>
            <div class="flex flex-col gap-2">
              <Button variant="outline" @click="handleExportSingleCSV">Export as CSV</Button>
              <Button variant="outline" @click="handleExportSingleExcel">Export as Excel</Button>
              <Button variant="outline" @click="handleExportSinglePDF">Export as PDF</Button>
            </div>
            <DialogFooter>
              <Button variant="outline" @click="showSingleExportDialog = false">Close</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AppLayout>
</template> 