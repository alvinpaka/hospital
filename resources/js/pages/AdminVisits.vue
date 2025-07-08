<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { CalendarIcon } from 'lucide-vue-next';
import { DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { cn } from '@/lib/utils';
import { Label } from '@/components/ui/label';
import { jsPDF } from 'jspdf';

interface Visit {
  id: number;
  date: string;
  doctor: string;
  department: string;
  reason: string;
  summary: string;
  patient?: { name: string } | null;
}

const allVisits = ref<Visit[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pageSize = 10;
const page = ref(1);
const searchQuery = ref('');
const sortColumn = ref<keyof Visit>('date');
const sortDirection = ref<'asc' | 'desc'>('desc');

const breadcrumbs = [
  { title: 'Visits', href: '/admin/visits' },
];

const user = computed(() => usePage().props.auth?.user || usePage().props.user);
const visits = usePage().props.visits;

const allPatients = ref([]);
const allStaff = ref([]);
const patientSearch = ref('');
const staffSearch = ref('');
const selectedPatientId = ref('');
const selectedStaffId = ref('');
const df = new DateFormatter('en-US', { dateStyle: 'long' });
const visitDate = ref(null);

onMounted(() => {
  if (!user.value || user.value.role !== 'admin') return;
  try {
    allVisits.value = Array.isArray(visits) ? visits : [];
  } catch (err) {
    error.value = 'Failed to load visits. Please try again later.';
    console.error('Error loading visits:', err);
  } finally {
    loading.value = false;
  }
});

onMounted(async () => {
  // Fetch all patients
  const resPatients = await fetch('/api/patients?per_page=1000', { credentials: 'include' });
  if (resPatients.ok) {
    const data = await resPatients.json();
    allPatients.value = data.data || [];
  }
  // Fetch all staff (doctors/nurses)
  const resStaff = await fetch('/api/staff?per_page=1000', { credentials: 'include' });
  if (resStaff.ok) {
    const data = await resStaff.json();
    allStaff.value = (data.data || []).filter(s => s.role === 'doctor' || s.role === 'nurse');
  }
});

const filteredVisits = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allVisits.value.filter(visit =>
    (visit.patient && visit.patient.name && visit.patient.name.toLowerCase().includes(query)) ||
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

// Dialog states for edit, delete, assign, export, summary
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showAssignDialog = ref(false);
const showExportDialog = ref(false);
const showSummaryDialog = ref(false);
const summaryDialogText = ref('');
const selectedVisit = ref<Visit | null>(null);

// Edit Visit form state
const editForm = useForm({
  date: '',
  reason: '',
  doctor: '',
  notes: '',
});
const editLoading = ref(false);
watch(showEditDialog, (open) => {
  if (open && selectedVisit.value) {
    editForm.date = selectedVisit.value.date;
    editForm.reason = selectedVisit.value.reason;
    editForm.doctor = selectedVisit.value.doctor;
    editForm.notes = selectedVisit.value.notes || '';
  }
});
async function handleEdit() {
  if (!selectedVisit.value) return;
  editLoading.value = true;
  editForm.clearErrors();
  try {
    await editForm.post(`/admin/visits/${selectedVisit.value.id}/update`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Visit updated successfully');
        showEditDialog.value = false;
        Object.assign(selectedVisit.value, editForm.data());
      },
      onError: () => {
        toast.error('Failed to update visit');
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

// Assign Visit form state
const assignForm = useForm({ doctor: '' });
const assignLoading = ref(false);
watch(showAssignDialog, (open) => {
  if (open && selectedVisit.value) {
    assignForm.doctor = selectedVisit.value.doctor;
  }
});
async function handleAssign() {
  if (!selectedVisit.value) return;
  assignLoading.value = true;
  assignForm.clearErrors();
  try {
    await assignForm.post(`/admin/visits/${selectedVisit.value.id}/assign`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Visit assigned successfully');
        showAssignDialog.value = false;
        selectedVisit.value.doctor = assignForm.doctor;
      },
      onError: () => {
        toast.error('Failed to assign visit');
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

// Delete Visit
const deleteLoading = ref(false);
async function handleDelete() {
  if (!selectedVisit.value) return;
  deleteLoading.value = true;
  try {
    await fetch(`/admin/visits/${selectedVisit.value.id}`, {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    });
    toast.success('Visit deleted');
    showDeleteDialog.value = false;
    allVisits.value = allVisits.value.filter(v => v.id !== selectedVisit.value?.id);
  } catch {
    toast.error('Failed to delete visit');
  } finally {
    deleteLoading.value = false;
  }
}

function openEditDialog(visit: Visit) {
  selectedVisit.value = visit;
  showEditDialog.value = true;
}
function openDeleteDialog(visit: Visit) {
  selectedVisit.value = visit;
  showDeleteDialog.value = true;
}
function openAssignDialog(visit: Visit) {
  selectedVisit.value = visit;
  showAssignDialog.value = true;
}
function openExportDialog() {
  showExportDialog.value = true;
}
function closeSummaryDialog() {
  showSummaryDialog.value = false;
  summaryDialogText.value = '';
}

// Bulk actions
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
  if (log.subject_type === 'Visit' && log.subject_id) {
    return `/admin/visits?id=${log.subject_id}`;
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

const showAddDialog = ref(false);
const addForm = useForm({
  date: '',
  reason: '',
  patient_id: '',
  doctor_id: '',
});
const addLoading = ref(false);

const filteredPatients = computed(() => {
  if (!patientSearch.value) return allPatients.value;
  const search = patientSearch.value.toLowerCase();
  return allPatients.value.filter(p => p.name.toLowerCase().includes(search) || (p.email && p.email.toLowerCase().includes(search)));
});
const filteredStaff = computed(() => {
  if (!staffSearch.value) return allStaff.value;
  const search = staffSearch.value.toLowerCase();
  return allStaff.value.filter(s => s.name.toLowerCase().includes(search) || s.role.toLowerCase().includes(search));
});

function openAddDialog() {
  showAddDialog.value = true;
  addForm.reset();
  selectedPatientId.value = '';
  selectedStaffId.value = '';
  visitDate.value = null;
  patientSearch.value = '';
  staffSearch.value = '';
}

async function handleAdd() {
  addLoading.value = true;
  addForm.clearErrors();
  try {
    const xsrfToken = getCookie('XSRF-TOKEN');
    addForm.date = visitDate.value ? df.format(visitDate.value.toDate ? visitDate.value.toDate(getLocalTimeZone()) : visitDate.value) : '';
    addForm.patient_id = selectedPatientId.value;
    addForm.doctor_id = selectedStaffId.value;
    const res = await fetch('/admin/visits', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
      },
      credentials: 'include',
      body: JSON.stringify({
        date: addForm.date,
        reason: addForm.reason,
        patient_id: addForm.patient_id,
        doctor_id: addForm.doctor_id,
      }),
    });
    const data = await res.json();
    if (res.ok && data.success) {
      allVisits.value.unshift(data.visit);
      showAddDialog.value = false;
      addForm.reset();
      toast.success('Visit added successfully');
    } else {
      if (data.error) toast.error(data.error);
      else toast.error('Failed to add visit');
    }
  } catch {
    toast.error('Failed to add visit');
  } finally {
    addLoading.value = false;
  }
}

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}

function handleExportCSV() {
  const visits = allVisits.value;
  if (!visits.length) {
    toast.error('No visits to export');
    return;
  }
  const headers = ['ID', 'Date', 'Doctor', 'Reason', 'Patient'];
  const rows = visits.map(v => [
    v.id,
    v.date,
    v.doctor,
    v.reason,
    v.patient?.name || ''
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'visits.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
}

function handleExportExcel() {
  // Fallback: export as CSV with .xlsx extension
  const visits = allVisits.value;
  if (!visits.length) {
    toast.error('No visits to export');
    return;
  }
  const headers = ['ID', 'Date', 'Doctor', 'Reason', 'Patient'];
  const rows = visits.map(v => [
    v.id,
    v.date,
    v.doctor,
    v.reason,
    v.patient?.name || ''
  ]);
  const csvContent = [headers, ...rows].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'visits.xlsx');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
}

function handleExportPDF() {
  const visits = allVisits.value;
  if (!visits.length) {
    toast.error('No visits to export');
    return;
  }
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Date', 'Doctor', 'Reason', 'Patient'];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Visits Export', 10, y);
    y += 10;
    // Table headers
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 40, y);
    });
    y += 8;
    // Table rows
    visits.forEach(v => {
      const row = [v.id, v.date, v.doctor, v.reason, v.patient?.name || ''];
      row.forEach((cell, i) => {
        doc.text(String(cell), 10 + i * 40, y);
      });
      y += 8;
      if (y > 270) {
        doc.addPage();
        y = 10;
      }
    });
    doc.save('visits.pdf');
    toast.success('PDF exported');
  } catch (e) {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
}

const showSingleExportDialog = ref(false);
const singleExportVisit = ref(null);

function handleExportSingle(visitId) {
  const visit = allVisits.value.find(v => v.id === visitId);
  if (!visit) {
    toast.error('Visit not found');
    return;
  }
  singleExportVisit.value = visit;
  showSingleExportDialog.value = true;
}

function handleExportSingleCSV() {
  const v = singleExportVisit.value;
  if (!v) return;
  const headers = ['ID', 'Date', 'Doctor', 'Reason', 'Patient'];
  const row = [v.id, v.date, v.doctor, v.reason, v.patient?.name || ''];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `visit-${v.id}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('CSV exported');
  showSingleExportDialog.value = false;
}

function handleExportSingleExcel() {
  const v = singleExportVisit.value;
  if (!v) return;
  const headers = ['ID', 'Date', 'Doctor', 'Reason', 'Patient'];
  const row = [v.id, v.date, v.doctor, v.reason, v.patient?.name || ''];
  const csvContent = [headers, row].map(row => row.map(String).map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `visit-${v.id}.xlsx`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  toast.success('Excel exported (CSV format)');
  showSingleExportDialog.value = false;
}

function handleExportSinglePDF() {
  const v = singleExportVisit.value;
  if (!v) return;
  try {
    const doc = new jsPDF();
    const headers = ['ID', 'Date', 'Doctor', 'Reason', 'Patient'];
    const row = [v.id, v.date, v.doctor, v.reason, v.patient?.name || ''];
    let y = 10;
    doc.setFontSize(12);
    doc.text('Visit Export', 10, y);
    y += 10;
    headers.forEach((header, i) => {
      doc.text(header, 10 + i * 40, y);
    });
    y += 8;
    row.forEach((cell, i) => {
      doc.text(String(cell), 10 + i * 40, y);
    });
    doc.save(`visit-${v.id}.pdf`);
    toast.success('PDF exported');
  } catch (e) {
    toast.error('PDF export failed. Please ensure jsPDF is installed.');
  }
  showSingleExportDialog.value = false;
}
</script>

<template>
  <Head title="Admin Visits" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8 min-h-[70vh]">
      <div v-if="user && user.role === 'admin'">
        <div class="space-y-2 mb-8">
          <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
              <span>All Visits</span>
              <Badge variant="secondary">Admin</Badge>
            </h1>
            <div class="flex gap-2">
              <Input
                v-model="searchQuery"
                placeholder="Search by patient, doctor, department, or reason..."
                class="max-w-sm"
              />
              <Button variant="outline" @click="openAddDialog">Add Visit</Button>
              <Button variant="outline" @click="openExportDialog">Export</Button>
            </div>
          </div>
          <p class="text-muted-foreground text-sm">View, edit, assign, delete, and export all visits in the system.</p>
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
            <p class="text-sm mt-2 text-muted-foreground">No visits in the system.</p>
          </CardContent>
        </Card>
        <Card v-else>
          <CardHeader>
            <CardTitle>All Visits</CardTitle>
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
                  <TableHead class="cursor-pointer" @click="toggleSort('reason')">
                    Reason
                    <span v-if="sortColumn === 'reason'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </TableHead>
                  <TableHead>Patient</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in paginatedVisits" :key="item.id" class="hover:bg-muted/50 transition-colors">
                  <TableCell>{{ formatDate(item.date) }}</TableCell>
                  <TableCell>{{ item.doctor || 'N/A' }}</TableCell>
                  <TableCell>{{ item.reason || 'N/A' }}</TableCell>
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
        <Button v-if="user && user.role === 'admin'" variant="outline" class="mb-4" @click="openActivityLog">View Activity Log</Button>
        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Visit</DialogTitle>
              <DialogDescription>
                Edit the details of the selected visit.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleEdit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Date</label>
                <Input v-model="editForm.date" type="date" required />
                <div v-if="editForm.errors.date" class="text-red-600 text-xs mt-1">{{ editForm.errors.date }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Reason</label>
                <Input v-model="editForm.reason" required />
                <div v-if="editForm.errors.reason" class="text-red-600 text-xs mt-1">{{ editForm.errors.reason }}</div>
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
              <DialogTitle>Assign Visit</DialogTitle>
              <DialogDescription>
                Assign this visit to a doctor.
              </DialogDescription>
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
              <DialogTitle>Delete Visit</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete this visit?
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
              <DialogTitle>Export Visits</DialogTitle>
              <DialogDescription>
                Export all visits as CSV, Excel, or PDF.
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
                    <td class="border px-2 py-1">
                      <div v-if="typeof log.details === 'object' && log.details !== null">
                        <table class="text-xs w-full">
                          <tr v-for="(value, key) in log.details" :key="key" v-if="value !== null && value !== undefined">
                            <td class="font-semibold align-top pr-2">{{ key }}:</td>
                            <td>
                              <span v-if="typeof value !== 'object'">{{ value }}</span>
                              <span v-else>
                                <table class="ml-2 border-l border-gray-200">
                                  <tr v-for="(subValue, subKey) in value" :key="subKey" v-if="subValue !== null && subValue !== undefined">
                                    <td class="font-semibold align-top pr-2">{{ subKey }}:</td>
                                    <td>{{ subValue }}</td>
                                  </tr>
                                </table>
                              </span>
                            </td>
                          </tr>
                        </table>
                      </div>
                      <span v-else>{{ log.details }}</span>
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
        <!-- Add Dialog -->
        <Dialog v-model:open="showAddDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Visit</DialogTitle>
              <DialogDescription>
                Enter the details for the new visit.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleAdd" class="space-y-4">
              <div>
                <Label>Date</Label>
                <Popover>
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      :class="cn('w-full justify-start text-left font-normal', !visitDate && 'text-muted-foreground')"
                    >
                      <CalendarIcon class="mr-2 h-4 w-4" />
                      {{ visitDate ? df.format(visitDate.toDate ? visitDate.toDate(getLocalTimeZone()) : visitDate) : 'Pick a date' }}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-auto p-0">
                    <Calendar v-model="visitDate" initial-focus />
                  </PopoverContent>
                </Popover>
              </div>
              <div>
                <Label>Patient</Label>
                <Input v-model="patientSearch" placeholder="Search patient by name..." class="w-full mb-2" />
                <div class="max-h-40 overflow-y-auto border rounded bg-white dark:bg-neutral-900">
                  <div v-if="filteredPatients.length === 0" class="p-2 text-muted-foreground">No patients found.</div>
                  <ul v-else>
                    <li v-for="p in filteredPatients" :key="p.id">
                      <button type="button" class="w-full text-left px-4 py-2 hover:bg-muted/50 focus:bg-muted/80 rounded flex justify-between items-center"
                        :class="{'bg-blue-100 dark:bg-blue-900': selectedPatientId === p.id}" @click="selectedPatientId = p.id">
                        <span>{{ p.name }} <span class="text-xs text-muted-foreground">{{ p.email }}</span></span>
                        <span v-if="selectedPatientId === p.id" class="text-blue-600 font-bold">Selected</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
              <div>
                <Label>Doctor/Nurse</Label>
                <Input v-model="staffSearch" placeholder="Search doctor or nurse..." class="w-full mb-2" />
                <div class="max-h-40 overflow-y-auto border rounded bg-white dark:bg-neutral-900">
                  <div v-if="filteredStaff.length === 0" class="p-2 text-muted-foreground">No staff found.</div>
                  <ul v-else>
                    <li v-for="s in filteredStaff" :key="s.id">
                      <button type="button" class="w-full text-left px-4 py-2 hover:bg-muted/50 focus:bg-muted/80 rounded flex justify-between items-center"
                        :class="{'bg-blue-100 dark:bg-blue-900': selectedStaffId === s.id}" @click="selectedStaffId = s.id">
                        <span>{{ s.name }} <span class="text-xs text-muted-foreground">({{ s.role }})</span></span>
                        <span v-if="selectedStaffId === s.id" class="text-blue-600 font-bold">Selected</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
              <div>
                <Label>Reason</Label>
                <Input v-model="addForm.reason" required />
                <div v-if="addForm.errors.reason" class="text-red-600 text-xs mt-1">{{ addForm.errors.reason }}</div>
              </div>
              <DialogFooter>
                <Button variant="outline" @click="showAddDialog = false" :disabled="addLoading">Cancel</Button>
                <Button variant="default" type="submit" :loading="addLoading">Add</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
        <!-- Single Export Dialog -->
        <Dialog v-model:open="showSingleExportDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Export Visit</DialogTitle>
              <DialogDescription>Export this visit as CSV, Excel, or PDF.</DialogDescription>
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
      <div v-else>
        <Card>
          <CardContent class="p-6 text-center text-red-600">
            You do not have permission to view this page.
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template> 