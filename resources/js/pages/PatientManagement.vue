<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { type BreadcrumbItem } from '@/types';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Management',
        href: '/patient-management',
    },
];

// State
const patients = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showDetailsModal = ref(false);
const selectedPatient = ref(null);
const csrfFetched = ref(false);

// Pagination state
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
    from: 0,
    to: 0
});
const currentPage = ref(1);
const perPage = ref(5);

// Form data
const patientForm = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    country_code: '+256'
});

const editForm = ref({
    name: '',
    email: '',
    phone: '',
    country_code: '+256',
});

// Computed
const filteredPatients = computed(() => {
    return patients.value;
});

// Methods
onMounted(async () => {
    await fetchPatients();
});

async function ensureCsrfCookie() {
    if (!csrfFetched.value) {
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        csrfFetched.value = true;
    }
}

async function fetchPatients() {
    await ensureCsrfCookie();
    loading.value = true;
    error.value = '';
    try {
        const params = new URLSearchParams({
            page: currentPage.value.toString(),
            per_page: perPage.value.toString(),
        });
        
        if (searchQuery.value) {
            params.append('search', searchQuery.value);
        }
        
        const res = await fetch(`/api/patients?${params}`, { credentials: 'include' });
        if (res.ok) {
            const data = await res.json();
            patients.value = data.data;
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                per_page: data.per_page,
                total: data.total,
                from: data.from,
                to: data.to
            };
        } else {
            error.value = 'Failed to fetch patients.';
        }
    } catch {
        error.value = 'Failed to fetch patients.';
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString();
}

function openAddModal() {
    patientForm.value = {
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        country_code: '+256'
    };
    showAddModal.value = true;
}

function openEditModal(patient) {
    selectedPatient.value = patient;
    // Extract country code and phone
    const match = (patient.phone || '').match(/^(\+\d{3})(\d{7,})$/);
    editForm.value = {
        name: patient.name,
        email: patient.email,
        phone: match ? match[2] : patient.phone,
        country_code: match ? match[1] : '+256',
    };
    showEditModal.value = true;
}

function openDeleteModal(patient) {
    selectedPatient.value = patient;
    showDeleteModal.value = true;
}

async function openDetailsModal(patient) {
    selectedPatient.value = patient;
    showDetailsModal.value = true;
    // Fetch detailed patient data
    try {
        const res = await fetch(`/api/patients/${patient.id}`, { credentials: 'include' });
        if (res.ok) {
            selectedPatient.value = await res.json();
        }
    } catch {
        console.error('Failed to fetch patient details');
    }
}

async function addPatient() {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        // Combine country code and phone
        const payload = {
            ...patientForm.value,
            phone: `${patientForm.value.country_code}${patientForm.value.phone}`,
        };
        delete payload.country_code;
        const res = await fetch('/api/patients', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
            },
            credentials: 'include',
            body: JSON.stringify(payload),
        });
        
        if (res.ok) {
            showAddModal.value = false;
            await fetchPatients();
        } else {
            const data = await res.json();
            error.value = data.message || 'Failed to add patient.';
        }
    } catch {
        error.value = 'Failed to add patient.';
    }
}

async function updatePatient() {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        // Combine country code and phone
        const payload = {
            ...editForm.value,
            phone: `${editForm.value.country_code}${editForm.value.phone}`,
        };
        delete payload.country_code;
        const res = await fetch(`/api/patients/${selectedPatient.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
            },
            credentials: 'include',
            body: JSON.stringify(payload),
        });
        
        if (res.ok) {
            showEditModal.value = false;
            await fetchPatients();
        } else {
            const data = await res.json();
            error.value = data.message || 'Failed to update patient.';
        }
    } catch {
        error.value = 'Failed to update patient.';
    }
}

async function deletePatient() {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        const res = await fetch(`/api/patients/${selectedPatient.value.id}`, {
            method: 'DELETE',
            headers: {
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
            },
            credentials: 'include',
        });
        
        if (res.ok) {
            showDeleteModal.value = false;
            await fetchPatients();
        } else {
            const data = await res.json();
            error.value = data.error || 'Failed to delete patient.';
        }
    } catch {
        error.value = 'Failed to delete patient.';
    }
}

async function verifyPatient(patient) {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        const res = await fetch(`/api/patients/${patient.id}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
            },
            credentials: 'include',
        });
        if (res.ok) {
            await fetchPatients();
        } else {
            const data = await res.json();
            error.value = data.message || 'Failed to verify patient.';
        }
    } catch {
        error.value = 'Failed to verify patient.';
    }
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

// Pagination functions
async function goToPage(page) {
    currentPage.value = page;
    await fetchPatients();
}

async function changePerPage(newPerPage) {
    perPage.value = newPerPage;
    currentPage.value = 1; // Reset to first page
    await fetchPatients();
}

// Search function
async function handleSearch() {
    currentPage.value = 1; // Reset to first page when searching
    await fetchPatients();
}

// Watch for search query changes
watch(searchQuery, () => {
    // Debounce search to avoid too many API calls
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(handleSearch, 500);
});

let searchTimeout = null;

// Pagination helper function
function getVisiblePages() {
    const pages = [];
    const totalPages = pagination.value.last_page;
    const current = pagination.value.current_page;
    
    if (totalPages <= 7) {
        // Show all pages if total is 7 or less
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);
        
        if (current > 4) {
            pages.push('...');
        }
        
        // Show pages around current page
        const start = Math.max(2, current - 1);
        const end = Math.min(totalPages - 1, current + 1);
        
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        
        if (current < totalPages - 3) {
            pages.push('...');
        }
        
        // Always show last page
        if (totalPages > 1) {
            pages.push(totalPages);
        }
    }
    
    return pages;
}
</script>

<template>
    <Head title="Patient Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8 min-h-[70vh]">
            <div class="space-y-2 mb-8">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
                        <span>Patient Management</span>
                        <Badge variant="secondary">Admin</Badge>
                    </h1>
                    <Button @click="openAddModal" size="lg">Add New Patient</Button>
                </div>
                <p class="text-muted-foreground text-sm">Manage patient accounts and view their medical records.</p>
            </div>

            <!-- Search and Filters -->
            <div class="mb-6">
                <Input
                    v-model="searchQuery"
                    placeholder="Search patients by name, email, or phone..."
                    class="max-w-md"
                />
            </div>

            <!-- Error Display -->
            <Card v-if="error" class="mb-6">
                <CardContent class="p-4 text-center text-red-600">
                    {{ error }}
                </CardContent>
            </Card>

            <!-- Patients Table -->
            <Card v-if="loading">
                <CardContent class="p-6 space-y-4">
                    <div class="animate-pulse">
                        <div class="h-8 bg-gray-200 rounded w-1/4 mb-4"></div>
                        <div class="space-y-3">
                            <div class="h-4 bg-gray-200 rounded"></div>
                            <div class="h-4 bg-gray-200 rounded"></div>
                            <div class="h-4 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-else-if="filteredPatients.length === 0">
                <CardContent class="p-6 text-center flex flex-col items-center">
                    <svg width="80" height="80" fill="none" viewBox="0 0 80 80" class="mb-4">
                        <rect width="80" height="80" rx="16" fill="#e3f2fd"/>
                        <path d="M24 40h32M40 24v32" stroke="#1976d2" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                    <p class="text-lg font-semibold">No patients found.</p>
                    <p class="text-sm mt-2 text-muted-foreground">
                        {{ searchQuery ? 'Try adjusting your search terms.' : 'Add your first patient to get started.' }}
                    </p>
                </CardContent>
            </Card>

            <Card v-else>
                <CardHeader>
                    <CardTitle>
                        All Patients 
                        <span class="text-sm font-normal text-muted-foreground">
                            ({{ pagination.total }} total, showing {{ pagination.from }}-{{ pagination.to }})
                        </span>
                    </CardTitle>
                </CardHeader>
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Joined</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="patient in filteredPatients" :key="patient.id" class="hover:bg-muted/50">
                                <TableCell class="font-medium">{{ patient.name }}</TableCell>
                                <TableCell>{{ patient.email }}</TableCell>
                                <TableCell>{{ patient.phone }}</TableCell>
                                <TableCell>
                                    <Badge :variant="patient.email_verified_at ? 'default' : 'secondary'">
                                        {{ patient.email_verified_at ? 'Verified' : 'Unverified' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(patient.created_at) }}</TableCell>
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
                                                <DropdownMenuItem @click="openDetailsModal(patient)">
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View Details
                                                </DropdownMenuItem>
                                                
                                                <!-- Edit Patient -->
                                                <DropdownMenuItem @click="openEditModal(patient)">
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit Patient
                                                </DropdownMenuItem>
                                                
                                                <!-- Delete Patient -->
                                                <DropdownMenuItem 
                                                    @click="openDeleteModal(patient)"
                                                    class="text-red-600 focus:text-red-600"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete Patient
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="!patient.email_verified_at" @click="verifyPatient(patient)" class="text-green-600 focus:text-green-600">
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Verify Patient
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                
                <!-- Pagination Controls -->
                <div class="flex items-center justify-between px-6 py-4 border-t">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">Show:</span>
                            <select 
                                v-model="perPage" 
                                @change="changePerPage(perPage)"
                                class="border rounded px-2 py-1 text-sm"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                            <span class="text-sm text-muted-foreground">per page</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage <= 1"
                        >
                            Previous
                        </Button>
                        
                        <div class="flex items-center gap-1">
                            <template v-for="page in getVisiblePages()" :key="page">
                                <Button 
                                    v-if="page !== '...'" 
                                    :variant="page === currentPage ? 'default' : 'outline'"
                                    size="sm"
                                    @click="goToPage(page)"
                                    class="w-8 h-8 p-0"
                                >
                                    {{ page }}
                                </Button>
                                <span v-else class="px-2 text-muted-foreground">...</span>
                            </template>
                        </div>
                        
                        <Button 
                            variant="outline" 
                            size="sm" 
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage >= pagination.last_page"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Add Patient Modal -->
        <Dialog v-model:open="showAddModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Patient</DialogTitle>
                    <DialogDescription>Create a new patient account with login credentials.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="addPatient" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div class="space-y-2">
                            <Label>Name</Label>
                            <Input v-model="patientForm.name" required placeholder="Full name" />
                        </div>
                        <div class="space-y-2">
                            <Label>Email</Label>
                            <Input v-model="patientForm.email" required type="email" placeholder="email@example.com" />
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <Label>Phone number</Label>
                            <div class="flex gap-2">
                                <Select v-model="patientForm.country_code">
                                    <SelectTrigger class="w-28">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="+256">🇺🇬 +256</SelectItem>
                                        <SelectItem value="+254">🇰🇪 +254</SelectItem>
                                        <SelectItem value="+255">🇹🇿 +255</SelectItem>
                                        <SelectItem value="+250">🇷🇼 +250</SelectItem>
                                        <SelectItem value="+211">🇸🇸 +211</SelectItem>
                                        <SelectItem value="+243">🇨🇩 +243</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Input v-model="patientForm.phone" required type="tel" placeholder="712345678" autocomplete="tel" class="flex-1" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Password</Label>
                            <Input v-model="patientForm.password" required type="password" placeholder="Password" autocomplete="new-password" />
                        </div>
                        <div class="space-y-2">
                            <Label>Confirm Password</Label>
                            <Input v-model="patientForm.password_confirmation" required type="password" placeholder="Confirm password" autocomplete="new-password" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddModal = false">Cancel</Button>
                        <Button type="submit">Add Patient</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Patient Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Patient</DialogTitle>
                    <DialogDescription>Update patient information.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="updatePatient" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div class="space-y-2">
                            <Label>Name</Label>
                            <Input v-model="editForm.name" required placeholder="Full name" />
                        </div>
                        <div class="space-y-2">
                            <Label>Email</Label>
                            <Input v-model="editForm.email" required type="email" placeholder="email@example.com" />
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <Label>Phone number</Label>
                            <div class="flex gap-2">
                                <Select v-model="editForm.country_code">
                                    <SelectTrigger class="w-28">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="+256">🇺🇬 +256</SelectItem>
                                        <SelectItem value="+254">🇰🇪 +254</SelectItem>
                                        <SelectItem value="+255">🇹🇿 +255</SelectItem>
                                        <SelectItem value="+250">🇷🇼 +250</SelectItem>
                                        <SelectItem value="+211">🇸🇸 +211</SelectItem>
                                        <SelectItem value="+243">🇨🇩 +243</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Input v-model="editForm.phone" required type="tel" placeholder="712345678" autocomplete="tel" class="flex-1" />
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditModal = false">Cancel</Button>
                        <Button type="submit">Update Patient</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Patient</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{{ selectedPatient?.name }}</strong>? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteModal = false">Cancel</Button>
                    <Button type="button" variant="destructive" @click="deletePatient">Delete Patient</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Patient Details Modal -->
        <Dialog v-model:open="showDetailsModal" class="max-w-4xl">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Patient Details - {{ selectedPatient?.name }}</DialogTitle>
                    <DialogDescription>View patient information and medical records.</DialogDescription>
                </DialogHeader>
                <div v-if="selectedPatient" class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm text-muted-foreground">Name</Label>
                                <p class="font-medium">{{ selectedPatient.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Email</Label>
                                <p class="font-medium">{{ selectedPatient.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Phone</Label>
                                <p class="font-medium">{{ selectedPatient.phone }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Status</Label>
                                <Badge :variant="selectedPatient.email_verified_at ? 'default' : 'secondary'">
                                    {{ selectedPatient.email_verified_at ? 'Verified' : 'Unverified' }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Medical Records Summary -->
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Medical Records Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ selectedPatient.appointments?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Appointments</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ selectedPatient.lab_results?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Lab Results</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ selectedPatient.prescriptions?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Prescriptions</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ selectedPatient.visits?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Visits</div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div v-if="selectedPatient.appointments && selectedPatient.appointments.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Recent Appointments</h3>
                        <div class="space-y-2">
                            <div v-for="appointment in selectedPatient.appointments.slice(0, 5)" :key="appointment.id" 
                                 class="flex justify-between items-center p-3 bg-muted rounded-lg">
                                <div>
                                    <div class="font-medium">{{ formatDate(appointment.date) }} at {{ appointment.time }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ appointment.doctor?.name || 'Unassigned' }}
                                    </div>
                                </div>
                                <Badge :variant="appointment.status === 'confirmed' ? 'default' : appointment.status === 'pending' ? 'secondary' : 'destructive'">
                                    {{ appointment.status }}
                                </Badge>
                            </div>
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