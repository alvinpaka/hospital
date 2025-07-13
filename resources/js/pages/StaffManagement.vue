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
        title: 'Staff Management',
        href: '/staff-management',
    },
];

// State
const staff = ref([]);
const loading = ref(true);
const error = ref('');
const searchQuery = ref('');
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showDetailsModal = ref(false);
const selectedStaff = ref(null);
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
const staffForm = ref({
    name: '',
    email: '',
    phone: '',
    country_code: '+256',
    role: 'doctor',
    password: '',
    password_confirmation: ''
});

const editForm = ref({
    name: '',
    email: '',
    phone: '',
    country_code: '+256',
    role: ''
});

// Computed
const filteredStaff = computed(() => {
    return staff.value;
});

// Methods
onMounted(async () => {
    await fetchStaff();
});

async function ensureCsrfCookie() {
    if (!csrfFetched.value) {
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        csrfFetched.value = true;
    }
}

async function fetchStaff() {
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
        
        const res = await fetch(`/api/staff?${params}`, { credentials: 'include' });
        if (res.ok) {
            const data = await res.json();
            staff.value = data.data;
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                per_page: data.per_page,
                total: data.total,
                from: data.from,
                to: data.to
            };
        } else {
            error.value = 'Failed to fetch staff.';
        }
    } catch {
        error.value = 'Failed to fetch staff.';
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString();
}

function openAddModal() {
    staffForm.value = {
        name: '',
        email: '',
        phone: '',
        country_code: '+256',
        role: 'doctor',
        password: '',
        password_confirmation: ''
    };
    showAddModal.value = true;
}

function openEditModal(staffMember) {
    selectedStaff.value = staffMember;
    // Extract country code and phone
    const match = (staffMember.phone || '').match(/^(\+\d{3})(\d{7,})$/);
    editForm.value = {
        name: staffMember.name,
        email: staffMember.email,
        phone: match ? match[2] : staffMember.phone,
        country_code: match ? match[1] : '+256',
        role: staffMember.role
    };
    showEditModal.value = true;
}

function openDeleteModal(staffMember) {
    selectedStaff.value = staffMember;
    showDeleteModal.value = true;
}

async function openDetailsModal(staffMember) {
    selectedStaff.value = staffMember;
    showDetailsModal.value = true;
    try {
        const res = await fetch(`/api/staff/${staffMember.id}`, { credentials: 'include' });
        if (res.ok) {
            selectedStaff.value = await res.json();
        }
    } catch {
        console.error('Failed to fetch staff details');
    }
}

async function addStaff() {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        const payload = {
            ...staffForm.value,
            phone: `${staffForm.value.country_code}${staffForm.value.phone}`,
        };
        delete payload.country_code;
        const res = await fetch('/api/staff', {
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
            await fetchStaff();
        } else {
            const data = await res.json();
            error.value = data.message || 'Failed to add staff.';
        }
    } catch {
        error.value = 'Failed to add staff.';
    }
}

async function updateStaff() {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        // Combine country code and phone
        const payload = {
            ...editForm.value,
            phone: `${editForm.value.country_code}${editForm.value.phone}`,
        };
        delete payload.country_code;
        const res = await fetch(`/api/staff/${selectedStaff.value.id}`, {
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
            await fetchStaff();
        } else {
            const data = await res.json();
            error.value = data.message || 'Failed to update staff.';
        }
    } catch {
        error.value = 'Failed to update staff.';
    }
}

async function deleteStaff() {
    await ensureCsrfCookie();
    const xsrfToken = getCookie('XSRF-TOKEN');
    try {
        const res = await fetch(`/api/staff/${selectedStaff.value.id}`, {
            method: 'DELETE',
            headers: {
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
            },
            credentials: 'include',
        });
        
        if (res.ok) {
            showDeleteModal.value = false;
            await fetchStaff();
        } else {
            const data = await res.json();
            error.value = data.error || 'Failed to delete staff.';
        }
    } catch {
        error.value = 'Failed to delete staff.';
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
    await fetchStaff();
}

async function changePerPage(newPerPage) {
    perPage.value = newPerPage;
    currentPage.value = 1;
    await fetchStaff();
}

// Search function
async function handleSearch() {
    currentPage.value = 1;
    await fetchStaff();
}

// Watch for search query changes
watch(searchQuery, () => {
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
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);
        
        if (current > 4) {
            pages.push('...');
        }
        
        const start = Math.max(2, current - 1);
        const end = Math.min(totalPages - 1, current + 1);
        
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        
        if (current < totalPages - 3) {
            pages.push('...');
        }
        
        if (totalPages > 1) {
            pages.push(totalPages);
        }
    }
    
    return pages;
}

function getRoleBadgeVariant(role) {
    if (role === 'admin') return 'destructive';
    if (role === 'doctor') return 'default';
    if (role === 'nurse') return 'secondary';
    return 'outline';
}
</script>

<template>
    <Head title="Staff Management" />
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-4xl font-bold text-white">Staff Management</h1>
                                <p class="text-blue-100 text-lg">Admin Panel</p>
                            </div>
                        </div>
                        <p class="text-blue-100 text-lg">
                            Manage staff accounts including doctors, nurses, and administrators.
                        </p>
                    </div>
                </div>
                <!-- Search and Filters -->
                <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
                    <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search staff by name, email, or role..."
                            class="max-w-md"
                        />
                        <Button @click="openAddModal" size="lg" class="ml-auto">Add New Staff</Button>
                    </CardContent>
                </Card>
                <!-- Error Display -->
                <Card v-if="error" class="rounded-3xl border-0 shadow-xl bg-red-50 dark:bg-red-900/80 mb-6">
                    <CardContent class="p-4 text-center text-red-600">
                        {{ error }}
                    </CardContent>
                </Card>
                <!-- Staff Table -->
                <Card v-if="loading" class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
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
                <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
                    <CardHeader>
                        <CardTitle>All Staff</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Phone</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Joined</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="staffMember in filteredStaff" :key="staffMember.id" class="hover:bg-muted/50">
                                        <TableCell class="font-medium">{{ staffMember.name }}</TableCell>
                                        <TableCell>{{ staffMember.email }}</TableCell>
                                        <TableCell>{{ staffMember.phone }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="getRoleBadgeVariant(staffMember.role)" class="capitalize">
                                                {{ staffMember.role }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="staffMember.email_verified_at ? 'default' : 'secondary'">
                                                {{ staffMember.email_verified_at ? 'Verified' : 'Unverified' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{{ formatDate(staffMember.created_at) }}</TableCell>
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
                                                        <DropdownMenuItem @click="openDetailsModal(staffMember)">
                                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            View Details
                                                        </DropdownMenuItem>
                                                        
                                                        <!-- Edit Staff -->
                                                        <DropdownMenuItem @click="openEditModal(staffMember)">
                                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            Edit Staff
                                                        </DropdownMenuItem>
                                                        
                                                        <!-- Delete Staff -->
                                                        <DropdownMenuItem 
                                                            @click="openDeleteModal(staffMember)"
                                                            class="text-red-600 focus:text-red-600"
                                                        >
                                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Delete Staff
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
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Add Staff Modal -->
        <Dialog v-model:open="showAddModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Staff</DialogTitle>
                    <DialogDescription>Create a new staff account with login credentials.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="addStaff" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Name</Label>
                            <Input v-model="staffForm.name" required placeholder="Full name" />
                        </div>
                        <div class="space-y-2">
                            <Label>Email</Label>
                            <Input v-model="staffForm.email" required type="email" placeholder="email@example.com" />
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <Label>Phone number</Label>
                            <div class="flex gap-2">
                                <Select v-model="staffForm.country_code">
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
                                <Input v-model="staffForm.phone" required type="tel" placeholder="712345678" autocomplete="tel" class="flex-1" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Role</Label>
                            <Select v-model="staffForm.role" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="doctor">Doctor</SelectItem>
                                    <SelectItem value="nurse">Nurse</SelectItem>
                                    <SelectItem value="admin">Administrator</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Password</Label>
                            <Input v-model="staffForm.password" required type="password" placeholder="Password" autocomplete="new-password" />
                        </div>
                        <div class="space-y-2">
                            <Label>Confirm Password</Label>
                            <Input v-model="staffForm.password_confirmation" required type="password" placeholder="Confirm password" autocomplete="new-password" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddModal = false">Cancel</Button>
                        <Button type="submit">Add Staff</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Staff Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Staff</DialogTitle>
                    <DialogDescription>Update staff information.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="updateStaff" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <div class="space-y-2">
                            <Label>Role</Label>
                            <Select v-model="editForm.role" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="doctor">Doctor</SelectItem>
                                    <SelectItem value="nurse">Nurse</SelectItem>
                                    <SelectItem value="admin">Administrator</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditModal = false">Cancel</Button>
                        <Button type="submit">Update Staff</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Staff</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{{ selectedStaff?.name }}</strong>? 
                        This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteModal = false">Cancel</Button>
                    <Button type="button" variant="destructive" @click="deleteStaff">Delete Staff</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Staff Details Modal -->
        <Dialog v-model:open="showDetailsModal" class="max-w-4xl">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Staff Details - {{ selectedStaff?.name }}</DialogTitle>
                    <DialogDescription>View staff information and work records.</DialogDescription>
                </DialogHeader>
                <div v-if="selectedStaff" class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm text-muted-foreground">Name</Label>
                                <p class="font-medium">{{ selectedStaff.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Email</Label>
                                <p class="font-medium">{{ selectedStaff.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Phone</Label>
                                <p class="font-medium">{{ selectedStaff.phone }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Role</Label>
                                <Badge :variant="getRoleBadgeVariant(selectedStaff.role)" class="capitalize">
                                    {{ selectedStaff.role }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Status</Label>
                                <Badge :variant="selectedStaff.email_verified_at ? 'default' : 'secondary'">
                                    {{ selectedStaff.email_verified_at ? 'Verified' : 'Unverified' }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm text-muted-foreground">Joined</Label>
                                <p class="font-medium">{{ formatDate(selectedStaff.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Work Records Summary -->
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Work Records Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ selectedStaff.appointments?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Appointments</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ selectedStaff.patients?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Patients</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ selectedStaff.prescriptions?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Prescriptions</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="p-4 text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ selectedStaff.visits?.length || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">Visits</div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div v-if="selectedStaff.appointments && selectedStaff.appointments.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Recent Appointments</h3>
                        <div class="space-y-2">
                            <div v-for="appointment in selectedStaff.appointments.slice(0, 5)" :key="appointment.id" 
                                 class="flex justify-between items-center p-3 bg-muted rounded-lg">
                                <div>
                                    <div class="font-medium">{{ formatDate(appointment.date) }} at {{ appointment.time }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ appointment.patient?.name || 'Unknown Patient' }}
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