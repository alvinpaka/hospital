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
  patient?: { name: string } | null;
}

const allVitals = ref<VitalsRecord[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 10;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof VitalsRecord>('recorded_at');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Vitals', href: '/admin/vitals' },
];

const user = computed(() => usePage().props.auth?.user || usePage().props.user);
const vitals = usePage().props.vitals as VitalsRecord[];

onMounted(() => {
  if (!user.value || user.value.role !== 'admin') return;
  try {
    allVitals.value = Array.isArray(vitals) ? vitals : [];
  } catch {
    error.value = 'Failed to load vitals. Please try again later.';
    console.error('Error loading vitals:');
  } finally {
    loading.value = false;
  }
});

const filteredVitals = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allVitals.value.filter(record =>
    (record.patient && record.patient.name && record.patient.name.toLowerCase().includes(query)) ||
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

// Dialog states for edit, delete, assign, export, view
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showAssignDialog = ref(false);
const showExportDialog = ref(false);
const showViewDialog = ref(false);
const selectedVitals = ref<VitalsRecord | null>(null);
const viewVitals = ref<VitalsRecord | null>(null);

function openEditDialog(record: VitalsRecord) {
  selectedVitals.value = record;
  showEditDialog.value = true;
}
function openDeleteDialog(record: VitalsRecord) {
  selectedVitals.value = record;
  showDeleteDialog.value = true;
}
function openAssignDialog(record: VitalsRecord) {
  selectedVitals.value = record;
  showAssignDialog.value = true;
}
function openExportDialog() {
  showExportDialog.value = true;
}
function openViewDialog(record: VitalsRecord) {
  viewVitals.value = record;
  showViewDialog.value = true;
}

// Edit Vitals form state
const editForm = useForm({
  date: '',
  type: '',
  value: '',
  notes: '',
});
const editLoading = ref(false);
watch(showEditDialog, (open) => {
  if (open && selectedVitals.value) {
    editForm.date = selectedVitals.value.recorded_at;
    editForm.type = selectedVitals.value.type || '';
    editForm.value = selectedVitals.value.value || '';
    editForm.notes = selectedVitals.value.notes || '';
  }
});
async function handleEdit() {
  if (!selectedVitals.value) return;
  editLoading.value = true;
  editForm.clearErrors();
  try {
    await editForm.post(`/admin/vitals/${selectedVitals.value.id}/update`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Vitals updated successfully');
        showEditDialog.value = false;
        Object.assign(selectedVitals.value, editForm.data());
      },
      onError: () => {
        toast.error('Failed to update vitals');
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

// Assign Vitals form state
const assignForm = useForm({ nurse: '' });
const assignLoading = ref(false);
watch(showAssignDialog, (open) => {
  if (open && selectedVitals.value) {
    assignForm.nurse = selectedVitals.value.recorded_by || '';
  }
});
async function handleAssign() {
  if (!selectedVitals.value) return;
  assignLoading.value = true;
  assignForm.clearErrors();
  try {
    await assignForm.post(`/admin/vitals/${selectedVitals.value.id}/assign`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Vitals assigned successfully');
        showAssignDialog.value = false;
        selectedVitals.value.recorded_by = assignForm.nurse;
      },
      onError: () => {
        toast.error('Failed to assign vitals');
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

// Delete Vitals
const deleteLoading = ref(false);
async function handleDelete() {
  if (!selectedVitals.value) return;
  deleteLoading.value = true;
  try {
    await fetch(`/admin/vitals/${selectedVitals.value.id}`, {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    });
    toast.success('Vitals deleted');
    showDeleteDialog.value = false;
    allVitals.value = allVitals.value.filter(v => v.id !== selectedVitals.value?.id);
  } catch {
    toast.error('Failed to delete vitals');
  } finally {
    deleteLoading.value = false;
  }
}

// Export dialog state for single record
const showSingleExportDialog = ref(false);
const singleExportVitals = ref(null);

function handleExportSingle(id) {
  const record = allVitals.value.find(v => v.id === id);
  if (!record) {
    toast.error('Vitals record not found');
    return;
  }
  singleExportVitals.value = record;
  showSingleExportDialog.value = true;
}

function handleExportCSV() {
  const vitals = allVitals.value;
  if (!vitals.length) {
    toast.error('No vitals to export');
    return;
  }
  const headers = ['ID', 'Patient', 'Temperature', 'Blood Pressure', 'Heart Rate', 'Respiratory Rate', 'Oxygen Saturation', 'Weight', 'Height', 'Fasting', 'Recorded By', 'Recorded At'];
  const rows = vitals.map(v => [
    v.id,
    v.patient?.name || '',
    v.temperature,
    v.blood_pressure,
    v.heart_rate,
    v.respiratory_rate,
    v.oxygen_saturation,
    v.weight,
    v.height,
    v.fasting ? 'Yes' : 'No',
    v.recorded_by,
    v.recorded_at
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'vitals.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showExportDialog.value = false;
}

function handleExportExcel() {
  const vitals = allVitals.value;
  if (!vitals.length) {
    toast.error('No vitals to export');
    return;
  }
  const headers = ['ID', 'Patient', 'Temperature', 'Blood Pressure', 'Heart Rate', 'Respiratory Rate', 'Oxygen Saturation', 'Weight', 'Height', 'Fasting', 'Recorded By', 'Recorded At'];
  const rows = vitals.map(v => [
    v.id,
    v.patient?.name || '',
    v.temperature,
    v.blood_pressure,
    v.heart_rate,
    v.respiratory_rate,
    v.oxygen_saturation,
    v.weight,
    v.height,
    v.fasting ? 'Yes' : 'No',
    v.recorded_by,
    v.recorded_at
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'vitals.xlsx');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showExportDialog.value = false;
}

function handleExportPDF() {
  const vitals = allVitals.value;
  if (!vitals.length) {
    toast.error('No vitals to export');
    return;
  }
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Patient', 'Temperature', 'Blood Pressure', 'Heart Rate', 'Respiratory Rate', 'Oxygen Saturation', 'Weight', 'Height', 'Fasting', 'Recorded By', 'Recorded At'];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Vitals Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 30, y);
    });
    y += 8;
    vitals.forEach(v => {
      const row = [v.id, v.patient?.name || '', v.temperature, v.blood_pressure, v.heart_rate, v.respiratory_rate, v.oxygen_saturation, v.weight, v.height, v.fasting ? 'Yes' : 'No', v.recorded_by, v.recorded_at];
      row.forEach((cell, i) => {
        doc.text(String(cell), 10 + i * 30, y);
      });
      y += 8;
      if (y > 270) {
        doc.addPage();
        y = 10;
      }
    });
    doc.save('vitals.pdf');
    toast.success('PDF exported');
  } catch {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showExportDialog.value = false;
}

function handleExportSingleCSV() {
  const v = singleExportVitals.value;
  if (!v) return;
  const headers = ['ID', 'Patient', 'Temperature', 'Blood Pressure', 'Heart Rate', 'Respiratory Rate', 'Oxygen Saturation', 'Weight', 'Height', 'Fasting', 'Recorded By', 'Recorded At'];
  const row = [v.id, v.patient?.name || '', v.temperature, v.blood_pressure, v.heart_rate, v.respiratory_rate, v.oxygen_saturation, v.weight, v.height, v.fasting ? 'Yes' : 'No', v.recorded_by, v.recorded_at];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `vitals-${v.id}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showSingleExportDialog.value = false;
}

function handleExportSingleExcel() {
  const v = singleExportVitals.value;
  if (!v) return;
  const headers = ['ID', 'Patient', 'Temperature', 'Blood Pressure', 'Heart Rate', 'Respiratory Rate', 'Oxygen Saturation', 'Weight', 'Height', 'Fasting', 'Recorded By', 'Recorded At'];
  const row = [v.id, v.patient?.name || '', v.temperature, v.blood_pressure, v.heart_rate, v.respiratory_rate, v.oxygen_saturation, v.weight, v.height, v.fasting ? 'Yes' : 'No', v.recorded_by, v.recorded_at];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `vitals-${v.id}.xlsx`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showSingleExportDialog.value = false;
}

function handleExportSinglePDF() {
  const v = singleExportVitals.value;
  if (!v) return;
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Patient', 'Temperature', 'Blood Pressure', 'Heart Rate', 'Respiratory Rate', 'Oxygen Saturation', 'Weight', 'Height', 'Fasting', 'Recorded By', 'Recorded At'];
    const row = [v.id, v.patient?.name || '', v.temperature, v.blood_pressure, v.heart_rate, v.respiratory_rate, v.oxygen_saturation, v.weight, v.height, v.fasting ? 'Yes' : 'No', v.recorded_by, v.recorded_at];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Vitals Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 30, y);
    });
    y += 8;
    row.forEach((cell, i) => {
      doc.text(String(cell), 10 + i * 30, y);
    });
    doc.save(`vitals-${v.id}.pdf`);
    toast.success('PDF exported');
  } catch {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showSingleExportDialog.value = false;
}

const selectedIds = ref<number[]>([]);
const allSelected = computed(() => selectedIds.value.length === paginatedVitals.value.length && paginatedVitals.value.length > 0);
function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = paginatedVitals.value.map(r => r.id);
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
const bulkAssignForm = useForm({ nurse: '' });
const bulkAssignLoading = ref(false);
const bulkDeleteLoading = ref(false);
function openBulkAssignDialog() { showBulkAssignDialog.value = true; bulkAssignForm.nurse = ''; }
function openBulkDeleteDialog() { showBulkDeleteDialog.value = true; }
async function handleBulkAssign() {
  bulkAssignLoading.value = true;
  try {
    await Promise.all(selectedIds.value.map(id =>
      bulkAssignForm.post(`/admin/vitals/${id}/assign`, {
        preserveScroll: true,
        onError: () => {},
        onFinish: () => {},
      })
    ));
    toast.success('Assigned nurse to selected vitals');
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
      fetch(`/admin/vitals/${id}`, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
      })
    ));
    toast.success('Deleted selected vitals');
    allVitals.value = allVitals.value.filter(r => !selectedIds.value.includes(r.id));
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
  const selected = allVitals.value.filter(r => selectedIds.value.includes(r.id));
  let csv = 'ID,Patient,Date,Type,Value,Nurse,Notes\n';
  for (const v of selected) {
    csv += `"${v.id}","${v.patient?.name || ''}","${v.recorded_at}","${v.type || ''}","${v.value || ''}","${v.recorded_by || ''}","${v.notes?.replace(/"/g, '""') || ''}"\n`;
  }
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'vitals_selected.csv';
  a.click();
  URL.revokeObjectURL(url);
  toast.success('Exported selected vitals');
}

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
  if (log.action === 'assign' && log.details?.old_nurse !== undefined) {
    return `Nurse changed from ${log.details.old_nurse} to ${log.details.new_nurse}`;
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
  if (log.subject_type === 'Vital' && log.subject_id) {
    return `/admin/vitals?id=${log.subject_id}`;
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
</script>

<template>
  <Head title="Admin Vitals" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
      <div class="max-w-screen-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="w-full overflow-x-auto">
          <!-- Header and Table Scrollable Wrapper -->
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
                  <h1 class="text-4xl font-bold text-white">All Vitals</h1>
                  <p class="text-blue-100 text-lg">Admin Panel</p>
                </div>
              </div>
              <p class="text-blue-100 text-lg">
                View, edit, assign, delete, and export all vitals in the system.
              </p>
            </div>
          </div>
          <!-- Search and Actions -->
          <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8 min-w-[1100px]">
            <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
              <Input
                v-model="searchQuery"
                placeholder="Search by patient, nurse, value, or date..."
                class="max-w-md"
              />
              <Button variant="outline" @click="openExportDialog" class="ml-auto">Export</Button>
            </CardContent>
          </Card>
          <!-- Error Display -->
          <Card v-if="error" class="rounded-3xl border-0 shadow-xl bg-red-50 dark:bg-red-900/80 mb-6 min-w-[1100px]">
            <CardContent class="p-4 text-center text-red-600">
              {{ error }}
            </CardContent>
          </Card>
          <!-- Loading State -->
          <Card v-else-if="loading" class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6 min-w-[1100px]">
            <CardContent class="p-6 space-y-4">
              <Skeleton class="h-8 w-full" />
              <Skeleton class="h-8 w-full" />
              <Skeleton class="h-8 w-full" />
            </CardContent>
          </Card>
          <!-- Vitals Table -->
          <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6 min-w-[1100px]">
            <CardHeader>
              <CardTitle>All Vitals</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="w-full overflow-x-auto">
                <div v-if="selectedIds.length > 0" class="mb-4 flex gap-2 items-center bg-muted/30 p-2 rounded">
                  <span class="font-medium">Bulk Actions:</span>
                  <Button size="sm" variant="outline" @click="handleBulkExport">Export</Button>
                  <Button size="sm" variant="outline" @click="openBulkAssignDialog">Assign</Button>
                  <Button size="sm" variant="destructive" @click="openBulkDeleteDialog">Delete</Button>
                  <span class="ml-2 text-xs text-muted-foreground">{{ selectedIds.length }} selected</span>
                </div>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>
                        <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                      </TableHead>
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
                      <TableHead>Patient</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="item in paginatedVitals" :key="item.id" class="hover:bg-muted/50 transition-colors">
                      <TableCell>
                        <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="() => toggleSelect(item.id)" />
                      </TableCell>
                      <TableCell>{{ formatDate(item.recorded_at) }}</TableCell>
                      <TableCell>{{ item.temperature }}</TableCell>
                      <TableCell>{{ item.blood_pressure }}</TableCell>
                      <TableCell>{{ item.heart_rate }}</TableCell>
                      <TableCell>{{ item.respiratory_rate }}</TableCell>
                      <TableCell>{{ item.oxygen_saturation }}</TableCell>
                      <TableCell>{{ item.weight }}</TableCell>
                      <TableCell>{{ item.height }}</TableCell>
                      <TableCell>{{ item.fasting ? 'Yes' : 'No' }}</TableCell>
                      <TableCell>{{ item.patient?.name || 'N/A' }}</TableCell>
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
                            <DropdownMenuItem @click="openViewDialog(item)">View</DropdownMenuItem>
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
        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Vitals</DialogTitle>
              <DialogDescription>
                Edit the details of the selected vitals record.
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
                <label class="block text-sm font-medium mb-1">Value</label>
                <Input v-model="editForm.value" required />
                <div v-if="editForm.errors.value" class="text-red-600 text-xs mt-1">{{ editForm.errors.value }}</div>
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
              <DialogTitle>Assign Vitals</DialogTitle>
              <DialogDescription>
                Assign this vitals record to a nurse.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleAssign" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Nurse</label>
                <Input v-model="assignForm.nurse" required placeholder="Nurse name" />
                <div v-if="assignForm.errors.nurse" class="text-red-600 text-xs mt-1">{{ assignForm.errors.nurse }}</div>
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
              <DialogTitle>Delete Vitals</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete this vitals record?
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
              <DialogTitle>Export Vitals</DialogTitle>
              <DialogDescription>
                Export all vitals as CSV, Excel, or PDF.
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
        <!-- Bulk Assign Dialog -->
        <Dialog v-model:open="showBulkAssignDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Assign Nurse to Selected Vitals</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="handleBulkAssign" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Nurse</label>
                <Input v-model="bulkAssignForm.nurse" required placeholder="Nurse name" />
                <div v-if="bulkAssignForm.errors.nurse" class="text-red-600 text-xs mt-1">{{ bulkAssignForm.errors.nurse }}</div>
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
              <DialogTitle>Delete Selected Vitals</DialogTitle>
            </DialogHeader>
            <div>Are you sure you want to delete the selected vitals?</div>
            <DialogFooter>
              <Button variant="outline" @click="showBulkDeleteDialog = false" :disabled="bulkDeleteLoading">Cancel</Button>
              <Button variant="destructive" @click="handleBulkDelete" :loading="bulkDeleteLoading">Delete</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <Button v-if="user && user.role === 'admin'" variant="outline" class="mb-4" @click="openActivityLog">View Activity Log</Button>
      </div>
    </div>
  </AppLayout>
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
  <Dialog v-model:open="showSingleExportDialog">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Export Vitals Record</DialogTitle>
        <DialogDescription>Export this vitals record as CSV, Excel, or PDF.</DialogDescription>
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
  <!-- Enhanced Landscape Vitals Assessment -->
  <Dialog v-model:open="showViewDialog">
    <DialogContent class="max-w-7xl w-full max-h-[95vh] overflow-y-auto">
      <DialogHeader class="pb-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <DialogTitle class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
              Vitals Assessment
            </DialogTitle>
            <DialogDescription class="text-slate-600 mt-1">
              Complete vital signs record and measurements
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <div v-if="viewVitals" class="space-y-6">
        <!-- Patient & Date Info Header -->
        <div class="bg-gradient-to-r from-violet-500/10 via-purple-500/10 to-fuchsia-500/10 rounded-2xl p-6 border border-violet-200/50 backdrop-blur-sm">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-violet-600">Patient</p>
                <p class="font-bold text-slate-900">{{ viewVitals.patient?.name || 'N/A' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-emerald-600">Recorded</p>
                <p class="font-bold text-slate-900">{{ formatDate(viewVitals.recorded_at) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-amber-600">Status</p>
                <p class="font-bold text-slate-900">Complete</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Primary Vital Signs -->
        <div>
          <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-pink-600 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
            Primary Vital Signs
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Temperature -->
            <div class="bg-gradient-to-br from-red-50 to-rose-100 rounded-2xl p-5 border border-red-200/50 shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-105">
              <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-red-700 mb-1">Temperature</p>
                <p class="text-2xl font-bold text-red-900">{{ viewVitals.temperature }}</p>
                <p class="text-xs text-red-600">°C / °F</p>
              </div>
            </div>

            <!-- Blood Pressure -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-5 border border-blue-200/50 shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-105">
              <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-blue-700 mb-1">Blood Pressure</p>
                <p class="text-2xl font-bold text-blue-900">{{ viewVitals.blood_pressure }}</p>
                <p class="text-xs text-blue-600">mmHg</p>
              </div>
            </div>

            <!-- Heart Rate -->
            <div class="bg-gradient-to-br from-pink-50 to-rose-100 rounded-2xl p-5 border border-pink-200/50 shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-105">
              <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-pink-700 mb-1">Heart Rate</p>
                <p class="text-2xl font-bold text-pink-900">{{ viewVitals.heart_rate }}</p>
                <p class="text-xs text-pink-600">BPM</p>
              </div>
            </div>

            <!-- Respiratory Rate -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl p-5 border border-emerald-200/50 shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-105">
              <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-emerald-700 mb-1">Respiratory Rate</p>
                <p class="text-2xl font-bold text-emerald-900">{{ viewVitals.respiratory_rate }}</p>
                <p class="text-xs text-emerald-600">per minute</p>
              </div>
            </div>

            <!-- Oxygen Saturation -->
            <div class="bg-gradient-to-br from-cyan-50 to-blue-100 rounded-2xl p-5 border border-cyan-200/50 shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-105">
              <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-cyan-700 mb-1">Oxygen Saturation</p>
                <p class="text-2xl font-bold text-cyan-900">{{ viewVitals.oxygen_saturation }}</p>
                <p class="text-xs text-cyan-600">%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Two Column Layout for Physical Measurements and Additional Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Physical Measurements -->
          <div>
            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
              <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
              Physical Measurements
            </h3>
            <div class="space-y-4">
              <!-- Weight -->
              <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-2xl p-6 border border-purple-200/50 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-1m-3 1l-3-1"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-purple-700 mb-1">Weight</p>
                    <p class="text-3xl font-bold text-purple-900">{{ viewVitals.weight }}</p>
                    <p class="text-sm text-purple-600">kg / lbs</p>
                  </div>
                </div>
              </div>

              <!-- Height -->
              <div class="bg-gradient-to-br from-indigo-50 to-blue-100 rounded-2xl p-6 border border-indigo-200/50 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21l3-3m-3 3l-3-3m3 3V9m0 0L4 6m3 3l3-3M4 6h16M7 3l3 3m-3-3l-3 3m3-3v12"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-indigo-700 mb-1">Height</p>
                    <p class="text-3xl font-bold text-indigo-900">{{ viewVitals.height }}</p>
                    <p class="text-sm text-indigo-600">cm / ft</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div>
            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
              <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              Additional Information
            </h3>
            <div class="space-y-4">
              <!-- Fasting Status -->
              <div class="bg-gradient-to-br from-amber-50 to-yellow-100 rounded-2xl p-6 border border-amber-200/50 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-700 mb-1">Fasting Status</p>
                    <p class="text-3xl font-bold text-amber-900">{{ viewVitals.fasting ? 'Yes' : 'No' }}</p>
                    <p class="text-sm text-amber-600">{{ viewVitals.fasting ? 'Patient was fasting' : 'Patient was not fasting' }}</p>
                  </div>
                </div>
              </div>

              <!-- Recorded By -->
              <div class="bg-gradient-to-br from-teal-50 to-cyan-100 rounded-2xl p-6 border border-teal-200/50 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-teal-700 mb-1">Recorded By</p>
                    <p class="text-3xl font-bold text-teal-900">{{ viewVitals.recorded_by }}</p>
                    <p class="text-sm text-teal-600">Healthcare Professional</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter class="mt-8 pt-6 border-t border-slate-200">
        <div class="flex gap-3 w-full">
          <Button variant="outline" @click="showViewDialog = false" class="flex-1 h-12 text-slate-700 border-slate-300 hover:bg-slate-50">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </Button>
          <Button class="flex-1 h-12 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Report
          </Button>
          <Button variant="secondary" class="flex-1 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
            </svg>
            Share
          </Button>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 