<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';

import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { jsPDF } from 'jspdf';

interface LabResult {
  id: number;
  date: string;
  type: string;
  result: string;
  doctor: string;
  patient: string;
  notes: string;
}

const allLabResults = ref<LabResult[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 10;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof LabResult>('date');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Lab Results', href: '/admin/lab-results' },
];

const user = computed(() => usePage().props.auth?.user || usePage().props.user);
const labResults = usePage().props.labResults;

onMounted(() => {
  if (!user.value || user.value.role !== 'admin') return;
  try {
    allLabResults.value = Array.isArray(labResults) ? labResults : [];
  } catch {
    error.value = 'Failed to load lab results. Please try again later.';
    console.error('Error loading lab results:');
  } finally {
    loading.value = false;
  }
});

const filteredLabResults = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allLabResults.value.filter(result =>
    result.type.toLowerCase().includes(query) ||
    result.doctor.toLowerCase().includes(query) ||
    result.patient?.toLowerCase().includes(query) ||
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
      ? String(aValue).localeCompare(String(bValue))
      : String(bValue).localeCompare(String(aValue));
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

// Dialog states for edit, delete, assign, export, notes
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showAssignDialog = ref(false);
const showExportDialog = ref(false);
const showNotesDialog = ref(false);
const notesDialogText = ref('');
const selectedLabResult = ref<LabResult | null>(null);

function openEditDialog(result: LabResult) {
  selectedLabResult.value = result;
  showEditDialog.value = true;
}
function openDeleteDialog(result: LabResult) {
  selectedLabResult.value = result;
  showDeleteDialog.value = true;
}
function openAssignDialog(result: LabResult) {
  selectedLabResult.value = result;
  showAssignDialog.value = true;
}
function openExportDialog() {
  showExportDialog.value = true;
}
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

// Edit Lab Result form state
const editForm = useForm({
  date: '',
  type: '',
  result: '',
  doctor: '',
  notes: '',
});
const editLoading = ref(false);

watch(showEditDialog, (open) => {
  if (open && selectedLabResult.value) {
    editForm.date = selectedLabResult.value.date;
    editForm.type = selectedLabResult.value.type;
    editForm.result = selectedLabResult.value.result;
    editForm.doctor = selectedLabResult.value.doctor;
    editForm.notes = selectedLabResult.value.notes;
  }
});

async function handleEdit() {
  if (!selectedLabResult.value) return;
  editLoading.value = true;
  editForm.clearErrors();
  try {
    await editForm.post(`/admin/lab-results/${selectedLabResult.value.id}/update`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Lab result updated successfully');
        showEditDialog.value = false;
        // Update local data
        Object.assign(selectedLabResult.value, editForm.data());
      },
      onError: () => {
        toast.error('Failed to update lab result');
      },
      onFinish: () => {
        editLoading.value = false;
      },
    });
  } catch {
    toast.error('Unexpected error');
    editLoading.value = false;
  }
}

// Assign Lab Result form state
const assignForm = useForm({
  doctor: '',
});
const assignLoading = ref(false);
watch(showAssignDialog, (open) => {
  if (open && selectedLabResult.value) {
    assignForm.doctor = selectedLabResult.value.doctor;
  }
});
async function handleAssign() {
  if (!selectedLabResult.value) return;
  assignLoading.value = true;
  assignForm.clearErrors();
  try {
    await assignForm.post(`/admin/lab-results/${selectedLabResult.value.id}/assign`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Lab result assigned successfully');
        showAssignDialog.value = false;
        selectedLabResult.value.doctor = assignForm.doctor;
      },
      onError: () => {
        toast.error('Failed to assign lab result');
      },
      onFinish: () => {
        assignLoading.value = false;
      },
    });
  } catch {
    toast.error('Unexpected error');
    assignLoading.value = false;
  }
}

// Delete Lab Result
const deleteLoading = ref(false);
async function handleDelete() {
  if (!selectedLabResult.value) return;
  deleteLoading.value = true;
  try {
    await fetch(`/admin/lab-results/${selectedLabResult.value.id}`, {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    });
    toast.success('Lab result deleted');
    showDeleteDialog.value = false;
    // Remove from local data
    allLabResults.value = allLabResults.value.filter(l => l.id !== selectedLabResult.value?.id);
  } catch {
    toast.error('Failed to delete lab result');
  } finally {
    deleteLoading.value = false;
  }
}

// Export dialog state for single record
const showSingleExportDialog = ref(false);
const singleExportLabResult = ref(null);

function handleExportSingle(id) {
  const record = allLabResults.value.find(v => v.id === id);
  if (!record) {
    toast.error('Lab result not found');
    return;
  }
  singleExportLabResult.value = record;
  showSingleExportDialog.value = true;
}

function handleExportCSV() {
  const results = allLabResults.value;
  if (!results.length) {
    toast.error('No lab results to export');
    return;
  }
  const headers = ['ID', 'Date', 'Type', 'Result', 'Doctor', 'Patient', 'Notes'];
  const rows = results.map(v => [
    v.id,
    v.date,
    v.type,
    v.result,
    v.doctor,
    v.patient,
    v.notes
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'lab-results.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showExportDialog.value = false;
}

function handleExportExcel() {
  const results = allLabResults.value;
  if (!results.length) {
    toast.error('No lab results to export');
    return;
  }
  const headers = ['ID', 'Date', 'Type', 'Result', 'Doctor', 'Patient', 'Notes'];
  const rows = results.map(v => [
    v.id,
    v.date,
    v.type,
    v.result,
    v.doctor,
    v.patient,
    v.notes
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'lab-results.xlsx');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showExportDialog.value = false;
}

function handleExportPDF() {
  const results = allLabResults.value;
  if (!results.length) {
    toast.error('No lab results to export');
    return;
  }
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Date', 'Type', 'Result', 'Doctor', 'Patient', 'Notes'];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Lab Results Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 30, y);
    });
    y += 8;
    results.forEach(v => {
      const row = [v.id, v.date, v.type, v.result, v.doctor, v.patient, v.notes];
      row.forEach((cell, i) => {
        doc.text(String(cell), 10 + i * 30, y);
      });
      y += 8;
      if (y > 270) {
        doc.addPage();
        y = 10;
      }
    });
    doc.save('lab-results.pdf');
    toast.success('PDF exported');
  } catch {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showExportDialog.value = false;
}

function handleExportSingleCSV() {
  const v = singleExportLabResult.value;
  if (!v) return;
  const headers = ['ID', 'Date', 'Type', 'Result', 'Doctor', 'Patient', 'Notes'];
  const row = [v.id, v.date, v.type, v.result, v.doctor, v.patient, v.notes];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `lab-result-${v.id}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showSingleExportDialog.value = false;
}

function handleExportSingleExcel() {
  const v = singleExportLabResult.value;
  if (!v) return;
  const headers = ['ID', 'Date', 'Type', 'Result', 'Doctor', 'Patient', 'Notes'];
  const row = [v.id, v.date, v.type, v.result, v.doctor, v.patient, v.notes];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `lab-result-${v.id}.xlsx`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showSingleExportDialog.value = false;
}

function handleExportSinglePDF() {
  const v = singleExportLabResult.value;
  if (!v) return;
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Date', 'Type', 'Result', 'Doctor', 'Patient', 'Notes'];
    const row = [v.id, v.date, v.type, v.result, v.doctor, v.patient, v.notes];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Lab Result Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 30, y);
    });
    y += 8;
    row.forEach((cell, i) => {
      doc.text(String(cell), 10 + i * 30, y);
    });
    doc.save(`lab-result-${v.id}.pdf`);
    toast.success('PDF exported');
  } catch {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showSingleExportDialog.value = false;
}

const selectedIds = ref<number[]>([]);
const allSelected = computed(() => selectedIds.value.length === paginatedLabResults.value.length && paginatedLabResults.value.length > 0);
function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = paginatedLabResults.value.map(r => r.id);
  }
}
function toggleSelect(id: number) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter(i => i !== id);
  } else {
    selectedIds.value.push(id);
  }
}
function clearSelection() { selectedIds.value = []; }

// Bulk actions
const showBulkAssignDialog = ref(false);
const showBulkDeleteDialog = ref(false);
const bulkAssignForm = useForm({ doctor: '' });
const bulkAssignLoading = ref(false);
const bulkDeleteLoading = ref(false);
function openBulkAssignDialog() { showBulkAssignDialog.value = true; bulkAssignForm.doctor = ''; }
function openBulkDeleteDialog() { showBulkDeleteDialog.value = true; }
async function handleBulkAssign() {
  bulkAssignLoading.value = true;
  try {
    await Promise.all(selectedIds.value.map(id =>
      bulkAssignForm.post(`/admin/lab-results/${id}/assign`, {
        preserveScroll: true,
        onError: () => {},
        onFinish: () => {},
      })
    ));
    toast.success('Assigned doctor to selected lab results');
    showBulkAssignDialog.value = false;
    clearSelection();
  } catch {
    toast.error('Bulk assign failed');
  } finally {
    bulkAssignLoading.value = false;
  }
}
async function handleBulkDelete() {
  bulkDeleteLoading.value = true;
  try {
    await Promise.all(selectedIds.value.map(id =>
      fetch(`/admin/lab-results/${id}`, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
      })
    ));
    toast.success('Deleted selected lab results');
    allLabResults.value = allLabResults.value.filter(r => !selectedIds.value.includes(r.id));
    showBulkDeleteDialog.value = false;
    clearSelection();
  } catch {
    toast.error('Bulk delete failed');
  } finally {
    bulkDeleteLoading.value = false;
  }
}
function handleBulkExport() {
  // Export selected as CSV
  const selected = allLabResults.value.filter(r => selectedIds.value.includes(r.id));
  let csv = 'ID,Patient,Date,Type,Result,Doctor,Notes\n';
  for (const l of selected) {
    csv += `"${l.id}","${l.patient?.name || ''}","${l.date}","${l.type}","${l.result}","${l.doctor}","${l.notes?.replace(/"/g, '""') || ''}"\n`;
  }
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'lab_results_selected.csv';
  a.click();
  URL.revokeObjectURL(url);
  toast.success('Exported selected lab results');
}

// Activity Log Page
const showActivityLog = ref(false);
const activityLogs = ref([]);
const activityLoading = ref(false);
const activitySearch = ref('');
const filteredActivityLogs = computed(() => {
  const q = activitySearch.value.toLowerCase();
  return activityLogs.value.filter(log => {
    return (
      (log.user?.name || '').toLowerCase().includes(q) ||
      (log.action || '').toLowerCase().includes(q) ||
      (log.subject_type || '').toLowerCase().includes(q) ||
      (log.subject_id + '').includes(q) ||
      JSON.stringify(log.details).toLowerCase().includes(q)
    );
  });
});
function activityLogSummary(log) {
  if (log.action === 'assign' && log.details?.old_doctor !== undefined) {
    return `Doctor changed from ${log.details.old_doctor} to ${log.details.new_doctor}`;
  }
  if (log.action === 'update' && log.details?.old && log.details?.new) {
    return 'Record updated';
  }
  if (log.action === 'delete') {
    return 'Record deleted';
  }
  if (log.action === 'export') {
    return 'Exported records';
  }
  return '';
}
function subjectLink(log) {
  if (log.subject_type === 'LabResult' && log.subject_id) {
    return `/admin/lab-results?id=${log.subject_id}`;
  }
  return null;
}
async function fetchActivityLogs() {
  activityLoading.value = true;
  try {
    const res = await fetch('/admin/activity-logs');
    activityLogs.value = await res.json();
  } catch {
    toast.error('Failed to load activity logs');
  } finally {
    activityLoading.value = false;
  }
}
function openActivityLog() {
  showActivityLog.value = true;
  fetchActivityLogs();
}
function closeActivityLog() {
  showActivityLog.value = false;
}

// Bulk Export confirmation
const showBulkExportDialog = ref(false);
function openBulkExportDialog() { showBulkExportDialog.value = true; }
function confirmBulkExport() { handleBulkExport(); showBulkExportDialog.value = false; }
</script>

<template>
  <Head title="Admin Lab Results" />
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M24 40h32M40 24v32" />
                </svg>
              </div>
              <div>
                <h1 class="text-4xl font-bold text-white">All Lab Results</h1>
                <p class="text-blue-100 text-lg">Admin Panel</p>
              </div>
            </div>
            <p class="text-blue-100 text-lg">
              View, edit, assign, delete, and export all lab results in the system.
            </p>
          </div>
        </div>
        <!-- Search and Actions -->
        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
          <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
            <Input
              v-model="searchQuery"
              placeholder="Search by type, doctor, patient, or result..."
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
        <!-- Lab Results Table -->
        <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardHeader>
            <CardTitle>All Lab Results</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="w-full overflow-x-auto">
              <div v-if="selectedIds.length > 0" class="mb-4 flex gap-2 items-center bg-muted/30 p-2 rounded">
                <span class="font-medium">Bulk Actions:</span>
                <Button size="sm" variant="outline" @click="openBulkExportDialog" :disabled="selectedIds.length === 0">Export</Button>
                <Button size="sm" variant="outline" @click="openBulkAssignDialog" :disabled="selectedIds.length === 0">Assign</Button>
                <Button size="sm" variant="destructive" @click="openBulkDeleteDialog" :disabled="selectedIds.length === 0">Delete</Button>
                <span class="ml-2 text-xs text-muted-foreground">{{ selectedIds.length }} selected</span>
              </div>
              <Table class="min-w-[1100px] w-full">
                <TableHeader>
                  <TableRow>
                    <TableHead>
                      <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                    </TableHead>
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
                    <TableHead class="cursor-pointer" @click="toggleSort('patient')">
                      Patient
                    </TableHead>
                    <TableHead>Notes</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="item in paginatedLabResults" :key="item.id" class="hover:bg-muted/50 transition-colors">
                    <TableCell>
                      <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="() => toggleSelect(item.id)" />
                    </TableCell>
                    <TableCell>{{ formatDate(item.date) }}</TableCell>
                    <TableCell>{{ item.type || 'N/A' }}</TableCell>
                    <TableCell>{{ item.result || 'N/A' }}</TableCell>
                    <TableCell>{{ item.doctor || 'N/A' }}</TableCell>
                    <TableCell>{{ item.patient?.name || 'N/A' }}</TableCell>
                    <TableCell>
                      <span v-if="item.notes && item.notes.length > 60">
                        {{ truncateNotes(item.notes) }}
                        <Button variant="link" size="sm" class="px-1 py-0 h-auto align-baseline" @click="openNotesDialog(item.notes)">View More</Button>
                      </span>
                      <span v-else>{{ item.notes }}</span>
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
                        <DropdownMenuContent align="end" class="w-48">
                          <DropdownMenuLabel>Actions</DropdownMenuLabel>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem @click="openEditDialog(item)">Edit</DropdownMenuItem>
                          <DropdownMenuItem @click="openAssignDialog(item)">Assign</DropdownMenuItem>
                          <DropdownMenuItem @click="() => handleExportSingle(item.id)">Export</DropdownMenuItem>
                          <DropdownMenuItem @click="openDeleteDialog(item)" class="text-red-600 focus:text-red-600">Delete</DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
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
        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Lab Result</DialogTitle>
              <DialogDescription>
                Edit the details of the selected lab result.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleEdit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Date</label>
                <Input v-model="editForm.date" type="date" required />
                <div v-if="editForm.errors.date" class="text-red-600 text-xs mt-1">{{ editForm.errors.date }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <Input v-model="editForm.type" required />
                <div v-if="editForm.errors.type" class="text-red-600 text-xs mt-1">{{ editForm.errors.type }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Result</label>
                <Input v-model="editForm.result" required />
                <div v-if="editForm.errors.result" class="text-red-600 text-xs mt-1">{{ editForm.errors.result }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Doctor</label>
                <Input v-model="editForm.doctor" required />
                <div v-if="editForm.errors.doctor" class="text-red-600 text-xs mt-1">{{ editForm.errors.doctor }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <Input v-model="editForm.notes" />
                <div v-if="editForm.errors.notes" class="text-red-600 text-xs mt-1">{{ editForm.errors.notes }}</div>
              </div>
              <DialogFooter>
                <Button variant="outline" @click="showEditDialog = false" :disabled="editLoading">Cancel</Button>
                <Button variant="default" type="submit" :loading="editLoading">Save</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
        <!-- Assign Dialog -->
        <Dialog v-model:open="showAssignDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Assign Lab Result</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="handleAssign" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Doctor</label>
                <Input v-model="assignForm.doctor" required placeholder="Doctor name" />
                <div v-if="assignForm.errors.doctor" class="text-red-600 text-xs mt-1">{{ assignForm.errors.doctor }}</div>
              </div>
              <DialogFooter>
                <Button variant="outline" @click="showAssignDialog = false" :disabled="assignLoading">Cancel</Button>
                <Button variant="default" type="submit" :loading="assignLoading">Assign</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
        <!-- Delete Dialog -->
        <Dialog v-model:open="showDeleteDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Delete Lab Result</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete this lab result?
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" @click="showDeleteDialog = false" :disabled="deleteLoading">Cancel</Button>
              <Button variant="destructive" @click="handleDelete" :loading="deleteLoading">Delete</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Export Dialog -->
        <Dialog v-model:open="showExportDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Export Lab Results</DialogTitle>
              <DialogDescription>
                Export all lab results as CSV or Excel file.
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
        <!-- Notes Dialog -->
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
        <!-- Bulk Assign Dialog -->
        <Dialog v-model:open="showBulkAssignDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Assign Doctor to Selected Lab Results</DialogTitle>
              <DialogDescription>
                Assign a doctor to the selected lab results.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleBulkAssign" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Doctor</label>
                <Input v-model="bulkAssignForm.doctor" required placeholder="Doctor name" />
                <div v-if="bulkAssignForm.errors.doctor" class="text-red-600 text-xs mt-1">{{ bulkAssignForm.errors.doctor }}</div>
              </div>
              <DialogFooter>
                <Button variant="outline" @click="showBulkAssignDialog = false" :disabled="bulkAssignLoading">Cancel</Button>
                <Button variant="default" type="submit" :loading="bulkAssignLoading">Assign</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
        <!-- Bulk Delete Dialog -->
        <Dialog v-model:open="showBulkDeleteDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Delete Selected Lab Results</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete the selected lab results?
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" @click="showBulkDeleteDialog = false" :disabled="bulkDeleteLoading">Cancel</Button>
              <Button variant="destructive" @click="handleBulkDelete" :loading="bulkDeleteLoading">Delete</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Bulk Export Confirmation Dialog -->
        <Dialog v-model:open="showBulkExportDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Export Selected Lab Results</DialogTitle>
              <DialogDescription>
                Are you sure you want to export the selected lab results as CSV?
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" @click="showBulkExportDialog = false">Cancel</Button>
              <Button variant="default" @click="confirmBulkExport">Export</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Activity Log Dialog -->
        <Dialog v-model:open="showActivityLog">
          <DialogContent class="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Admin Activity Log</DialogTitle>
            </DialogHeader>
            <div class="mb-2 flex gap-2 items-center">
              <Input v-model="activitySearch" placeholder="Search logs..." class="max-w-xs" />
            </div>
            <div v-if="activityLoading" class="p-4">Loading...</div>
            <div v-else>
              <table class="w-full text-sm border">
                <thead>
                  <tr>
                    <th class="border px-2 py-1">Time</th>
                    <th class="border px-2 py-1">User</th>
                    <th class="border px-2 py-1">Action</th>
                    <th class="border px-2 py-1">Subject</th>
                    <th class="border px-2 py-1">Summary</th>
                    <th class="border px-2 py-1">Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in filteredActivityLogs" :key="log.id">
                    <td class="border px-2 py-1">{{ new Date(log.created_at).toLocaleString() }}</td>
                    <td class="border px-2 py-1">{{ log.user?.name || 'System' }}</td>
                    <td class="border px-2 py-1">{{ log.action }}</td>
                    <td class="border px-2 py-1">
                      <a v-if="subjectLink(log)" :href="subjectLink(log)" class="text-blue-600 underline" target="_blank">{{ log.subject_type }} #{{ log.subject_id }}</a>
                      <span v-else>{{ log.subject_type }} #{{ log.subject_id }}</span>
                    </td>
                    <td class="border px-2 py-1">{{ activityLogSummary(log) }}</td>
                    <td class="border px-2 py-1">
                      <pre class="whitespace-pre-wrap text-xs">{{ JSON.stringify(log.details, null, 2) }}</pre>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <DialogFooter>
              <Button variant="outline" @click="closeActivityLog">Close</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Single Export Dialog -->
        <Dialog v-model:open="showSingleExportDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Export Lab Result</DialogTitle>
              <DialogDescription>Export this lab result as CSV, Excel, or PDF.</DialogDescription>
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
        <!-- Add a sidebar or header button for Activity Log (admin only) -->
        <Button v-if="user && user.role === 'admin'" variant="outline" class="mb-4" @click="openActivityLog">View Activity Log</Button>
      </div>
    </div>
  </AppLayout>
</template> 