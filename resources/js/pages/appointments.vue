<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import Datepicker from 'vue3-datepicker';
import Timepicker from 'vue3-timepicker';
import 'vue3-timepicker/dist/VueTimepicker.css';
import { format } from 'date-fns';

const appointments = ref([]);
const loading = ref(true);
const showBooking = ref(false);
const doctors = ref([]);
const bookingForm = ref({ date: '', time: '', notes: '' });
const csrfFetched = ref(false);
const activeTab = ref('active');
const showCancelModal = ref(false);
const cancelReason = ref('');
const cancelError = ref('');
const cancelingAppointmentId = ref(null);
const pageSize = 5;
const activePage = ref(1);
const cancelledPage = ref(1);

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

onMounted(async () => {
  await fetchAppointments();
  const res = await fetch('/api/doctors', { credentials: 'include' });
  if (res.ok) {
    doctors.value = await res.json();
  }
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

async function bookAppointment() {
  await ensureCsrfCookie();
  bookingError.value = '';
  try {
    const xsrfToken = getCookie('XSRF-TOKEN');
    // Format date and time for MySQL
    const formattedDate = bookingForm.value.date
      ? format(new Date(bookingForm.value.date), 'yyyy-MM-dd')
      : '';
    const formattedTime = bookingForm.value.time
      ? format(new Date(`1970-01-01T${bookingForm.value.time}`), 'HH:mm:ss')
      : '';
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
      const data = await res.json();
      bookingError.value = data.message || 'Booking failed.';
      return;
    }
    showBooking.value = false;
    bookingForm.value = { doctor_id: '', date: '', time: '', notes: '' };
    await fetchAppointments();
  } catch (e) {
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
  } catch (e) {
    cancelError.value = 'Failed to cancel appointment.';
  }
}

async function cancelAppointment(appointmentId) {
  openCancelModal(appointmentId);
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
    <Head title="My Appointments" />
    <div class="max-w-screen-xl mx-auto py-4 px-4 min-h-[70vh]">
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

      <!-- Booking Modal -->
      <Dialog v-model:open="showBooking">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Book Appointment</DialogTitle>
            <DialogDescription>Fill in the details to book a new appointment.</DialogDescription>
          </DialogHeader>
          <form @submit.prevent="bookAppointment" class="space-y-4">
            <div class="space-y-2">
              <Label>Doctor</Label>
              <select
                v-model="bookingForm.doctor_id"
                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required
              >
                <option value="" disabled selected>Select a doctor</option>
                <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                  {{ doc.name }}
                </option>
              </select>
            </div>
            <div class="space-y-2">
              <Label>Date</Label>
              <Datepicker 
                v-model="bookingForm.date" 
                :input-class="'w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'" 
                :format="'yyyy-MM-dd'" 
                placeholder="YYYY-MM-DD" 
                required 
              />
            </div>
            <div class="space-y-2">
              <Label>Time</Label>
              <Timepicker 
                v-model="bookingForm.time" 
                :format="'HH:MM'" 
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
  </AppLayout>
</template>