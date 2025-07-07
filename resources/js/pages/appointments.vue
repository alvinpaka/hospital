<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import Timepicker from 'vue3-timepicker';
import 'vue3-timepicker/dist/VueTimepicker.css';
import { format } from 'date-fns';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import {
  DateFormatter,
  type DateValue,
  getLocalTimeZone,
} from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import { cn } from '@/lib/utils'

const appointments = ref([]);
const loading = ref(true);
const showBooking = ref(false);
const bookingError = ref('');
const csrfFetched = ref(false);
const activeTab = ref('active');
const showCancelModal = ref(false);
const cancelReason = ref('');
const cancelError = ref('');
const cancelingAppointmentId = ref(null);
const pageSize = 5;
const activePage = ref(1);
const cancelledPage = ref(1);
const adminPage = ref(1);
const adminPageSize = 5;
const adminTotalPages = computed(() => Math.ceil(filteredAdminAppointments.value.length / adminPageSize));

// Admin search and filter functionality
const adminSearch = ref('');
const adminStatusFilter = ref('');
const adminDateRange = ref({ start: '', end: '' });

// Filtered appointments for admin
const filteredAdminAppointments = computed(() => {
  let filtered = appointments.value;
  
  // Search by patient name
  if (adminSearch.value) {
    const search = adminSearch.value.toLowerCase();
    filtered = filtered.filter(appt => 
      appt.patient?.name?.toLowerCase().includes(search) ||
      appt.doctor?.name?.toLowerCase().includes(search) ||
      appt.id.toString().includes(search)
    );
  }
  
  // Filter by status
  if (adminStatusFilter.value) {
    filtered = filtered.filter(appt => appt.status === adminStatusFilter.value);
  }
  
  // Filter by date range
  if (adminDateRange.value.start && adminDateRange.value.end) {
    const startDate = new Date(adminDateRange.value.start);
    const endDate = new Date(adminDateRange.value.end);
    filtered = filtered.filter(appt => {
      const apptDate = new Date(appt.date);
      return apptDate >= startDate && apptDate <= endDate;
    });
  }
  
  return filtered;
});

const paginatedAdminAppointments = computed(() => {
  const start = (adminPage.value - 1) * adminPageSize;
  return filteredAdminAppointments.value.slice(start, start + adminPageSize);
});

// Admin pagination helpers
const adminPageNumbers = computed(() => {
  const total = adminTotalPages.value;
  const current = adminPage.value;
  const pages = [];
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    } else if (current >= total - 3) {
      pages.push(1);
      pages.push('...');
      for (let i = total - 4; i <= total; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    }
  }
  
  return pages;
});

// Reset admin filters
function resetAdminFilters() {
  adminSearch.value = '';
  adminStatusFilter.value = '';
  adminDateRange.value = { start: '', end: '' };
  adminPage.value = 1;
}

// Clear admin search
function clearAdminSearch() {
  adminSearch.value = '';
  adminPage.value = 1;
}

// SVG icons
const icons = {
  active: `<svg class='inline-block mr-1' width='18' height='18' fill='none' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' fill='#1976d2' opacity='0.1'/><path d='M12 7v5l3 3' stroke='#1976d2' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/></svg>`,
  cancelled: `<svg class='inline-block mr-1' width='18' height='18' fill='none' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' fill='#e53935' opacity='0.1'/><path d='M15 9l-6 6M9 9l6 6' stroke='#e53935' stroke-width='2' stroke-linecap='round'/></svg>`,
  calendar: `<svg class='inline-block mr-1' width='18' height='18' fill='none' viewBox='0 0 24 24'><rect x='3' y='5' width='18' height='16' rx='2' fill='#1976d2' opacity='0.1'/><rect x='3' y='9' width='18' height='2' fill='#1976d2' opacity='0.2'/><path d='M8 2v4M16 2v4' stroke='#1976d2' stroke-width='2' stroke-linecap='round'/></svg>`,
  clock: `<svg class='inline-block mr-1' width='18' height='18' fill='none' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' fill='#1976d2' opacity='0.1'/><path d='M12 6v6l4 2' stroke='#1976d2' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/></svg>`
};

const activeAppointments = computed(() => appointments.value.filter(a => a.status !== 'canceled'));
const cancelledAppointments = computed(() => appointments.value.filter(a => a.status === 'canceled'));

const paginatedActive = computed(() => {
  const start = (activePage.value - 1) * pageSize;
  return activeAppointments.value.slice(start, start + pageSize);
});
const paginatedCancelled = computed(() => {
  const start = (cancelledPage.value - 1) * pageSize;
  return cancelledAppointments.value.slice(start, start + pageSize);
});
const activeTotalPages = computed(() => Math.ceil(activeAppointments.value.length / pageSize));
const cancelledTotalPages = computed(() => Math.ceil(cancelledAppointments.value.length / pageSize));

const page = usePage();
const user = computed(() => page.props.auth.user);

const showAdminCancelModal = ref(false);
const showAssignModal = ref(false);
const showDetailsModal = ref(false);
const selectedAppointment = ref(null);
const adminCancelReason = ref('');
const adminCancelError = ref('');
const assigningAppointmentId = ref(null);
const assigningDoctorId = ref('');
const staffList = ref([]);
const adminActionLoading = ref(false);

const staffSearch = ref('');
const filteredStaffList = computed(() => {
  if (!Array.isArray(staffList.value)) return [];
  const validStaff = staffList.value.filter(
    s => s && typeof s.name === 'string' && typeof s.role === 'string' && s.role.toLowerCase() !== 'admin'
  );
  if (!staffSearch.value) return validStaff;
  const search = staffSearch.value.toLowerCase();
  return validStaff.filter(s =>
    s.name.toLowerCase().includes(search) ||
    s.role.toLowerCase().includes(search)
  );
});

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
})

const value = ref<DateValue>()

onMounted(async () => {
  await fetchAppointments();
});

async function ensureCsrfCookie() {
  if (!csrfFetched.value) {
    await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
    csrfFetched.value = true;
  }
}

async function fetchAppointments() {
  await ensureCsrfCookie();
  loading.value = true;
  try {
    const res = await fetch('/api/appointments', { credentials: 'include' });
    if (res.ok) {
      appointments.value = await res.json();
    }
  } finally {
    loading.value = false;
  }
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString();
}

function statusBadgeVariant(status) {
  if (status === 'pending') return 'secondary';
  if (status === 'confirmed') return 'default';
  if (status === 'canceled') return 'destructive';
  return 'outline';
}

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}

const bookingForm = ref({ date: '', time: '', notes: '' });

async function bookAppointment() {
  await ensureCsrfCookie();
  bookingError.value = '';
  if (value.value) {
    bookingForm.value.date = df.format(value.value.toDate(getLocalTimeZone()));
  } else {
    bookingForm.value.date = '';
  }
  try {
    const xsrfToken = getCookie('XSRF-TOKEN');
    // Format date and time for MySQL
    const formattedDate = bookingForm.value.date
      ? format(new Date(bookingForm.value.date), 'yyyy-MM-dd')
      : '';
    let formattedTime = '';
    if (bookingForm.value.time instanceof Date && !isNaN(bookingForm.value.time)) {
      formattedTime = format(bookingForm.value.time, 'HH:mm:ss');
    } else if (typeof bookingForm.value.time === 'string' && bookingForm.value.time.trim() !== '') {
      formattedTime = bookingForm.value.time;
    }
    const res = await fetch('/api/appointments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
      },
      credentials: 'include',
      body: JSON.stringify({
        ...bookingForm.value,
        date: formattedDate,
        time: formattedTime,
      }),
    });
    if (!res.ok) {
      let data;
      try {
        data = await res.json();
      } catch (e) {
        data = null;
      }
      if (data && data.errors) {
        // Show validation errors in a readable way
        bookingError.value = Object.values(data.errors).flat().join(' ');
      } else if (data && data.message) {
        bookingError.value = data.message;
      } else {
        bookingError.value = 'Booking failed.';
      }
      return;
    }
    showBooking.value = false;
    bookingForm.value = { date: '', time: null, notes: '' };
    value.value = undefined;
    await fetchAppointments();
  } catch (e) {
    console.error(e);
    bookingError.value = 'Booking failed.';
  }
}

async function openCancelModal(appointmentId) {
  cancelingAppointmentId.value = appointmentId;
  cancelReason.value = '';
  cancelError.value = '';
  showCancelModal.value = true;
}

async function confirmCancelAppointment() {
  if (!cancelReason.value.trim()) {
    cancelError.value = 'Please provide a reason for cancellation.';
    return;
  }
  await ensureCsrfCookie();
  const xsrfToken = getCookie('XSRF-TOKEN');
  try {
    const res = await fetch(`/api/appointments/${cancelingAppointmentId.value}/cancel`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
      },
      credentials: 'include',
      body: JSON.stringify({ cancellation_reason: cancelReason.value }),
    });
    if (!res.ok) {
      cancelError.value = 'Failed to cancel appointment.';
    } else {
      showCancelModal.value = false;
      await fetchAppointments();
    }
  } catch {
    cancelError.value = 'Failed to cancel appointment.';
  }
}

async function cancelAppointment(appointmentId) {
  openCancelModal(appointmentId);
}

async function acceptAppointment(id) {
  adminActionLoading.value = true;
  await ensureCsrfCookie();
  const xsrfToken = getCookie('XSRF-TOKEN');
  await fetch(`/api/appointments/${id}/accept`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
    }
  });
  await fetchAppointments();
  adminActionLoading.value = false;
}

function openAdminCancelModal(id) {
  assigningAppointmentId.value = id;
  adminCancelReason.value = '';
  adminCancelError.value = '';
  showAdminCancelModal.value = true;
}

async function confirmAdminCancel() {
  if (!adminCancelReason.value.trim()) {
    adminCancelError.value = 'Please provide a reason for cancellation.';
    return;
  }
  adminActionLoading.value = true;
  await ensureCsrfCookie();
  const xsrfToken = getCookie('XSRF-TOKEN');
  await fetch(`/api/appointments/${assigningAppointmentId.value}/admin-cancel`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
    },
    credentials: 'include',
    body: JSON.stringify({ cancellation_reason: adminCancelReason.value }),
  });
  showAdminCancelModal.value = false;
  await fetchAppointments();
  adminActionLoading.value = false;
}

function openAssignModal(id) {
  assigningAppointmentId.value = id;
  assigningDoctorId.value = '';
  showAssignModal.value = true;
  fetchStaffList().catch((e) => {
    console.error('Failed to fetch staff list', e);
    showAssignModal.value = true;
  });
}

async function fetchStaffList() {
  const res = await fetch('/api/staff-list', { credentials: 'include' });
  if (res.ok) {
    const data = await res.json();
    staffList.value = Array.isArray(data) ? data.filter(s => s) : [];
  }
}

async function confirmAssign() {
  if (!assigningDoctorId.value) return;
  adminActionLoading.value = true;
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  await fetch(`/api/appointments/${assigningAppointmentId.value}/assign`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    credentials: 'include',
    body: JSON.stringify({ doctor_id: assigningDoctorId.value }),
  });
  showAssignModal.value = false;
  await fetchAppointments();
  adminActionLoading.value = false;
}

// View appointment details function
function viewAppointmentDetails(appointmentId) {
  // Find the appointment
  const appointment = appointments.value.find(appt => appt.id === appointmentId);
  if (!appointment) {
    alert('Appointment not found');
    return;
  }
  
  // Set the selected appointment and show modal
  selectedAppointment.value = appointment;
  showDetailsModal.value = true;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointments',
        href: '/appointments',
    },
];
</script>

<template>
  <Head title="Appointments" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Appointments" />
    <div class="max-w-screen-xl mx-auto py-4 px-4 min-h-[70vh]">
      <div v-if="user && user.role === 'admin'">
        <div class="space-y-1 mb-8">
          <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <span>All Appointments</span>
            <Badge variant="secondary">Admin</Badge>
          </h1>
          <p class="text-muted-foreground">View, accept, cancel, and assign doctors/nurses to any appointment.</p>
        </div>
        
        <!-- Admin Search and Filters -->
        <Card class="mb-6">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              <span>Search & Filters</span>
              <Button variant="outline" size="sm" @click="resetAdminFilters">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Filters
              </Button>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Search Input -->
              <div class="space-y-2">
                <Label for="admin-search">Search</Label>
                <div class="relative">
                  <Input 
                    id="admin-search"
                    v-model="adminSearch" 
                    placeholder="Search by patient, doctor, or ID..."
                    class="pr-10"
                  />
                  <button 
                    v-if="adminSearch" 
                    @click="clearAdminSearch"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Status Filter -->
              <div class="space-y-2">
                <Label for="status-filter">Status</Label>
                <select 
                  id="status-filter"
                  v-model="adminStatusFilter" 
                  class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="canceled">Canceled</option>
                </select>
              </div>
              
              <!-- Date Range Start -->
              <div class="space-y-2">
                <Label for="date-start">Date From</Label>
                <input
                  id="date-start"
                  type="date"
                  v-model="adminDateRange.start"
                  class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Start Date"
                />
              </div>
              
              <!-- Date Range End -->
              <div class="space-y-2">
                <Label for="date-end">Date To</Label>
                <input
                  id="date-end"
                  type="date"
                  v-model="adminDateRange.end"
                  class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="End Date"
                />
              </div>
            </div>
            
            <!-- Results Summary -->
            <div class="mt-4 pt-4 border-t">
              <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                  Showing {{ paginatedAdminAppointments.length }} of {{ filteredAdminAppointments.length }} appointments
                  <span v-if="adminSearch || adminStatusFilter || adminDateRange.start || adminDateRange.end">
                    (filtered)
                  </span>
                </span>
                <span>Page {{ adminPage }} of {{ adminTotalPages }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader>
            <CardTitle>All Appointments</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="loading" class="text-center py-8">
              <div class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading appointments...
              </div>
            </div>
            <div v-else-if="filteredAdminAppointments.length === 0" class="text-center py-8">
              <div class="flex flex-col items-center">
                <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
                  <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
                  <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <p class="text-lg font-semibold">No appointments found.</p>
                <p class="text-sm mt-2 text-muted-foreground">
                  {{ adminSearch || adminStatusFilter || adminDateRange.start || adminDateRange.end 
                    ? 'Try adjusting your search criteria.' 
                    : 'No appointments have been created yet.' }}
                </p>
              </div>
            </div>
            <Table v-else>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Patient</TableHead>
                  <TableHead>Date</TableHead>
                  <TableHead>Time</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Doctor/Nurse</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="appt in paginatedAdminAppointments" :key="appt.id" class="hover:bg-muted/50">
                  <TableCell class="font-mono text-sm">#{{ appt.id }}</TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ appt.patient?.name || 'N/A' }}</span>
                      <span class="text-xs text-muted-foreground">{{ appt.patient?.email || 'No email' }}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center">
                      <span v-html="icons.calendar" class="mr-1"></span>
                      {{ formatDate(appt.date) }}
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center">
                      <span v-html="icons.clock" class="mr-1"></span>
                      {{ appt.time }}
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="statusBadgeVariant(appt.status)" class="capitalize">
                      <span v-if="appt.status === 'pending'" class="inline-block w-2 h-2 rounded-full bg-yellow-400 mr-1"></span>
                      <span v-if="appt.status === 'confirmed'" class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span>
                      <span v-if="appt.status === 'canceled'" class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                      {{ appt.status }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ appt.doctor?.name || 'Unassigned' }}</span>
                      <span class="text-xs text-muted-foreground">{{ appt.doctor?.role || 'No role' }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end">
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
                          
                          <!-- View Details -->
                          <DropdownMenuItem @click="viewAppointmentDetails(appt.id)">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Details
                          </DropdownMenuItem>
                          
                          <!-- Accept Appointment (only for pending) -->
                          <DropdownMenuItem 
                            v-if="appt.status === 'pending'"
                            @click="acceptAppointment(appt.id)"
                            :disabled="adminActionLoading"
                            class="text-green-600 focus:text-green-600"
                          >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Accept Appointment
                          </DropdownMenuItem>
                          
                          <!-- Assign Staff -->
                          <DropdownMenuItem 
                            v-if="appt.status !== 'canceled'"
                            @click="openAssignModal(appt.id)"
                            :disabled="adminActionLoading"
                          >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            Assign Staff
                          </DropdownMenuItem>
                          
                          <!-- Cancel Appointment (only for non-canceled) -->
                          <DropdownMenuItem 
                            v-if="appt.status !== 'canceled'"
                            @click="openAdminCancelModal(appt.id)"
                            :disabled="adminActionLoading"
                            class="text-red-600 focus:text-red-600"
                          >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel Appointment
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <!-- Enhanced Pagination -->
            <div v-if="adminTotalPages > 1" class="flex items-center justify-between p-4 border-t">
              <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <span>Showing {{ (adminPage - 1) * adminPageSize + 1 }} to {{ Math.min(adminPage * adminPageSize, filteredAdminAppointments.length) }} of {{ filteredAdminAppointments.length }} results</span>
              </div>
              
              <div class="flex items-center gap-2">
                <!-- Previous Button -->
                <Button 
                  :disabled="adminPage === 1" 
                  variant="outline" 
                  size="sm" 
                  @click="adminPage--"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                  Previous
                </Button>
                
                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                  <Button 
                    v-for="pageNum in adminPageNumbers" 
                    :key="pageNum"
                    :variant="pageNum === adminPage ? 'default' : 'outline'"
                    size="sm"
                    :disabled="pageNum === '...'"
                    @click="pageNum !== '...' ? adminPage = pageNum : null"
                    class="min-w-[40px]"
                  >
                    {{ pageNum }}
                  </Button>
                </div>
                
                <!-- Next Button -->
                <Button 
                  :disabled="adminPage === adminTotalPages || adminTotalPages === 0" 
                  variant="outline" 
                  size="sm" 
                  @click="adminPage++"
                >
                  Next
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
      <div v-else>
        <div class="space-y-1">
          <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <span>My Appointments</span>
            <Badge variant="secondary">Patient</Badge>
          </h1>
          <p class="text-muted-foreground">View and manage your upcoming and past appointments. You can book a new appointment at any time.</p>
        </div>
        
        <div class="flex items-center gap-4 my-6">
          <Button @click="showBooking = true" size="lg">Book New Appointment</Button>
          <div class="flex gap-2 ml-auto bg-muted rounded-full p-1">
            <Button 
              :variant="activeTab === 'active' ? 'default' : 'ghost'" 
              class="rounded-full flex items-center gap-1" 
              @click="activeTab = 'active'"
            >
              <span v-html="icons.active"></span> Active
            </Button>
            <Button 
              :variant="activeTab === 'cancelled' ? 'destructive' : 'ghost'" 
              class="rounded-full flex items-center gap-1" 
              @click="activeTab = 'cancelled'"
            >
              <span v-html="icons.cancelled"></span> Cancelled
            </Button>
          </div>
        </div>
        
        <Separator class="my-6" />

        <Card v-if="loading">
          <CardContent class="p-6 text-center text-muted-foreground">
            Loading appointments...
          </CardContent>
        </Card>
        
        <Card v-else-if="appointments.length === 0">
          <CardContent class="p-6 text-center flex flex-col items-center">
            <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4"><rect width="80" height="80" rx="16" fill="#e3f2fd"/><path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/></svg>
            <p class="text-lg font-semibold">No appointments found.</p>
            <p class="text-sm mt-2 text-muted-foreground">Your upcoming and past appointments will appear here.</p>
          </CardContent>
        </Card>
        
        <div v-else>
          <div v-show="activeTab === 'active'">
            <Card v-if="activeAppointments.length === 0">
              <CardContent class="p-6 text-center flex flex-col items-center">
                <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4"><rect width="80" height="80" rx="16" fill="#e3f2fd"/><path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/></svg>
                <p class="text-lg font-semibold">No active appointments.</p>
                <p class="text-sm mt-2 text-muted-foreground">Book a new appointment to get started.</p>
              </CardContent>
            </Card>
            
            <Card v-else>
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Date</TableHead>
                      <TableHead>Time</TableHead>
                      <TableHead>Doctor</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Notes</TableHead>
                      <TableHead class="text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="appt in paginatedActive" :key="appt.id" class="hover:bg-muted/50">
                      <TableCell class="font-medium">
                        <span class="inline-flex items-center">
                          <span v-html="icons.calendar" class="mr-1"></span> 
                          {{ formatDate(appt.date) }}
                        </span>
                      </TableCell>
                      <TableCell>
                        <span class="inline-flex items-center">
                          <span v-html="icons.clock" class="mr-1"></span> 
                          {{ appt.time }}
                        </span>
                      </TableCell>
                      <TableCell>{{ appt.doctor?.name || 'N/A' }}</TableCell>
                      <TableCell>
                        <Badge :variant="statusBadgeVariant(appt.status)">
                          <span v-if="appt.status === 'pending'" class="inline-block w-2 h-2 rounded-full bg-yellow-400 mr-1"></span>
                          <span v-if="appt.status === 'confirmed'" class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span>
                          <span v-if="appt.status === 'canceled'" class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                          {{ appt.status.charAt(0).toUpperCase() + appt.status.slice(1) }}
                        </Badge>
                      </TableCell>
                      <TableCell class="text-sm">{{ appt.notes }}</TableCell>
                      <TableCell class="text-right">
                        <Button 
                          v-if="appt.status !== 'canceled'" 
                          variant="destructive" 
                          size="sm" 
                          @click="cancelAppointment(appt.id)"
                        >
                          Cancel
                        </Button>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div class="flex justify-between items-center p-4 border-t">
                <span class="text-sm text-muted-foreground">Page {{ activePage }} of {{ activeTotalPages }}</span>
                <div class="flex gap-2">
                  <Button 
                    :disabled="activePage === 1" 
                    variant="outline" 
                    size="sm" 
                    @click="activePage--"
                  >
                    Prev
                  </Button>
                  <Button 
                    :disabled="activePage === activeTotalPages || activeTotalPages === 0" 
                    variant="outline" 
                    size="sm" 
                    @click="activePage++"
                  >
                    Next
                  </Button>
                </div>
              </div>
            </Card>
          </div>
          
          <div v-show="activeTab === 'cancelled'">
            <Card v-if="cancelledAppointments.length === 0">
              <CardContent class="p-6 text-center flex flex-col items-center">
                <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4"><rect width="80" height="80" rx="16" fill="#ffebee"/><path d="M24 40h32M40 24v32" stroke="#e53935" stroke-width="4" stroke-linecap="round"/></svg>
                <p class="text-lg font-semibold text-destructive">No cancelled appointments.</p>
                <p class="text-sm mt-2 text-muted-foreground">Cancelled appointments will appear here.</p>
              </CardContent>
            </Card>
            
            <Card v-else>
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Date</TableHead>
                      <TableHead>Time</TableHead>
                      <TableHead>Doctor</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Notes</TableHead>
                      <TableHead>Reason</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="appt in paginatedCancelled" :key="appt.id" class="hover:bg-muted/50 opacity-80">
                      <TableCell class="font-medium">
                        <span class="inline-flex items-center">
                          <span v-html="icons.calendar" class="mr-1"></span> 
                          {{ formatDate(appt.date) }}
                        </span>
                      </TableCell>
                      <TableCell>
                        <span class="inline-flex items-center">
                          <span v-html="icons.clock" class="mr-1"></span> 
                          {{ appt.time }}
                        </span>
                      </TableCell>
                      <TableCell>{{ appt.doctor?.name || 'N/A' }}</TableCell>
                      <TableCell>
                        <Badge variant="destructive">
                          <span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                          {{ appt.status.charAt(0).toUpperCase() + appt.status.slice(1) }}
                        </Badge>
                      </TableCell>
                      <TableCell class="text-sm">{{ appt.notes }}</TableCell>
                      <TableCell class="text-sm text-destructive font-semibold max-w-xs truncate">
                        {{ appt.cancellation_reason }}
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div class="flex justify-between items-center p-4 border-t">
                <span class="text-sm text-muted-foreground">Page {{ cancelledPage }} of {{ cancelledTotalPages }}</span>
                <div class="flex gap-2">
                  <Button 
                    :disabled="cancelledPage === 1" 
                    variant="outline" 
                    size="sm" 
                    @click="cancelledPage--"
                  >
                    Prev
                  </Button>
                  <Button 
                    :disabled="cancelledPage === cancelledTotalPages || cancelledTotalPages === 0" 
                    variant="outline" 
                    size="sm" 
                    @click="cancelledPage++"
                  >
                    Next
                  </Button>
                </div>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
    <!-- Move all Dialog modals here, outside of v-if/v-else -->
    <!-- Assign Modal -->
    <Dialog v-model:open="showAssignModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Assign Doctor or Nurse</DialogTitle>
          <DialogDescription>Select a staff member to assign to this appointment.</DialogDescription>
        </DialogHeader>
        <div class="mb-4">
          <Label for="assign_staff_search">Search</Label>
          <Input id="assign_staff_search" v-model="staffSearch" placeholder="Search by name or role..." class="mb-2" />
          <div class="max-h-60 overflow-y-auto border rounded bg-white dark:bg-neutral-900">
            <div v-if="filteredStaffList.length === 0" class="p-4 text-muted-foreground">No staff found.</div>
            <ul v-else>
              <li v-for="person in filteredStaffList" :key="person.id">
                <button type="button" class="w-full text-left px-4 py-2 hover:bg-muted/50 focus:bg-muted/80 rounded flex justify-between items-center"
                  :class="{'bg-blue-100 dark:bg-blue-900': assigningDoctorId === person.id}" @click="assigningDoctorId = person.id">
                  <span>{{ person.name }} <span class="text-xs text-muted-foreground">({{ person.role }})</span></span>
                  <span v-if="assigningDoctorId === person.id" class="text-blue-600 font-bold">Selected</span>
                </button>
              </li>
            </ul>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showAssignModal = false">Close</Button>
          <Button variant="success" :disabled="!assigningDoctorId || adminActionLoading" @click="confirmAssign">Assign</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    <!-- Admin Cancel Modal -->
    <Dialog v-model:open="showAdminCancelModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Cancel Appointment</DialogTitle>
          <DialogDescription>Please provide a reason for cancelling this appointment:</DialogDescription>
        </DialogHeader>
        <Textarea v-model="adminCancelReason" placeholder="Reason for cancellation..." class="mb-2" />
        <div v-if="adminCancelError" class="text-destructive text-sm mb-2">{{ adminCancelError }}</div>
        <DialogFooter>
          <Button variant="outline" @click="showAdminCancelModal = false">Close</Button>
          <Button variant="destructive" :disabled="adminActionLoading" @click="confirmAdminCancel">Cancel Appointment</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    <!-- Booking Modal -->
    <Dialog v-model:open="showBooking">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Book Appointment</DialogTitle>
          <DialogDescription>Fill in the details to book a new appointment.</DialogDescription>
        </DialogHeader>
        <form @submit.prevent="bookAppointment" class="space-y-4">
          <div class="space-y-2">
            <Label>Date</Label>
            <Popover>
              <PopoverTrigger as-child>
                <Button
                  variant="outline"
                  :class="cn(
                    'w-full justify-start text-left font-normal',
                    !value && 'text-muted-foreground',
                  )"
                >
                  <CalendarIcon class="mr-2 h-4 w-4" />
                  {{ value ? df.format(value.toDate(getLocalTimeZone())) : "Pick a date" }}
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0">
                <Calendar v-model="value" initial-focus />
              </PopoverContent>
            </Popover>
          </div>
          <div class="space-y-2">
            <Label>Time</Label>
            <Timepicker 
              v-model="bookingForm.time" 
              :format="'HH:mm'" 
              :minute-interval="5" 
              :input-class="'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'" 
              required 
            />
          </div>
          <div class="space-y-2">
            <Label>Notes</Label>
            <Textarea 
              v-model="bookingForm.notes" 
              placeholder="Optional" 
              class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            />
          </div>
          <div v-if="bookingError" class="text-destructive text-sm">{{ bookingError }}</div>
          <DialogFooter>
            <Button type="submit" class="w-full">Book</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
    <!-- Cancel Modal -->
    <Dialog v-model:open="showCancelModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Cancel Appointment</DialogTitle>
          <DialogDescription>Please provide a reason for cancelling this appointment:</DialogDescription>
        </DialogHeader>
        <Textarea v-model="cancelReason" placeholder="Reason for cancellation..." class="mb-2" />
        <div v-if="cancelError" class="text-destructive text-sm mb-2">{{ cancelError }}</div>
        <DialogFooter>
          <Button variant="outline" @click="showCancelModal = false">Close</Button>
          <Button variant="destructive" @click="confirmCancelAppointment">Cancel Appointment</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    
    <!-- Appointment Details Modal -->
    <Dialog v-model:open="showDetailsModal">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Appointment Details</DialogTitle>
          <DialogDescription>Complete information about this appointment.</DialogDescription>
        </DialogHeader>
        <div v-if="selectedAppointment" class="space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="text-sm font-medium text-muted-foreground">Appointment ID</Label>
              <p class="font-mono text-lg">#{{ selectedAppointment.id }}</p>
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-medium text-muted-foreground">Status</Label>
              <Badge :variant="statusBadgeVariant(selectedAppointment.status)" class="capitalize">
                <span v-if="selectedAppointment.status === 'pending'" class="inline-block w-2 h-2 rounded-full bg-yellow-400 mr-1"></span>
                <span v-if="selectedAppointment.status === 'confirmed'" class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span>
                <span v-if="selectedAppointment.status === 'canceled'" class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                {{ selectedAppointment.status }}
              </Badge>
            </div>
          </div>
          
          <!-- Date and Time -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="text-sm font-medium text-muted-foreground">Date</Label>
              <div class="flex items-center">
                <span v-html="icons.calendar" class="mr-2"></span>
                <p class="text-lg">{{ formatDate(selectedAppointment.date) }}</p>
              </div>
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-medium text-muted-foreground">Time</Label>
              <div class="flex items-center">
                <span v-html="icons.clock" class="mr-2"></span>
                <p class="text-lg">{{ selectedAppointment.time }}</p>
              </div>
            </div>
          </div>
          
          <!-- Patient Information -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-muted-foreground">Patient Information</Label>
            <div class="bg-muted/50 p-4 rounded-lg">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-muted-foreground">Name</p>
                  <p class="font-medium">{{ selectedAppointment.patient?.name || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Phone</p>
                  <p class="font-medium">{{ selectedAppointment.patient?.phone || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Doctor/Nurse Information -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-muted-foreground">Assigned Staff</Label>
            <div class="bg-muted/50 p-4 rounded-lg">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-muted-foreground">Name</p>
                  <p class="font-medium">{{ selectedAppointment.doctor?.name || 'Unassigned' }}</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Role</p>
                  <p class="font-medium">{{ selectedAppointment.doctor?.role || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notes -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
            <div class="bg-muted/50 p-4 rounded-lg">
              <p class="text-sm">{{ selectedAppointment.notes || 'No notes provided' }}</p>
            </div>
          </div>
          
          <!-- Cancellation Reason (if applicable) -->
          <div v-if="selectedAppointment.cancellation_reason" class="space-y-2">
            <Label class="text-sm font-medium text-muted-foreground">Cancellation Reason</Label>
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
              <p class="text-sm text-red-700 dark:text-red-300">{{ selectedAppointment.cancellation_reason }}</p>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDetailsModal = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>