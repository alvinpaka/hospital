<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const page = usePage();
const billings = computed(() => page.props.billings.data || []);
const patients = computed(() => page.props.billings.data.map(b => b.patient).filter(Boolean));

const statusFilter = ref('');
const search = ref('');

// Pagination
const currentPage = ref(1);
const pageSize = 10;

const filteredBillings = computed(() => {
  let result = billings.value;
  if (statusFilter.value) {
    result = result.filter(b => b.status === statusFilter.value);
  }
  if (search.value) {
    result = result.filter(b => {
      return (
        (b.patient && b.patient.name.toLowerCase().includes(search.value.toLowerCase())) ||
        (b.description && b.description.toLowerCase().includes(search.value.toLowerCase()))
      );
    });
  }
  return result;
});

const paginatedBillings = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredBillings.value.slice(start, start + pageSize);
});

const totalPages = computed(() => Math.ceil(filteredBillings.value.length / pageSize));

function goToPage(page: number) {
  currentPage.value = page;
}

function statusBadge(status: string) {
  switch (status) {
    case 'paid': return 'default';
    case 'pending': return 'secondary';
    case 'overdue': return 'destructive';
    default: return 'secondary';
  }
}

function markAsPaid(billingId: number) {
  router.post(`/admin/billings/${billingId}/mark-paid`);
}

function deleteBilling(billingId: number) {
  if (confirm('Are you sure you want to delete this bill?')) {
    router.delete(`/admin/billings/${billingId}`);
  }
}

// Reset to first page when filters change
function resetPagination() {
  currentPage.value = 1;
}

// Watch for filter changes to reset pagination
import { watch } from 'vue';
watch([search, statusFilter], resetPagination);
</script>

<template>
  <Head title="Billing & Invoicing" />
  <AppLayout>
    <div class="max-w-7xl mx-auto p-4 flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Billing & Invoicing</h1>
          <p class="text-muted-foreground">Manage payments and billing for patients.</p>
        </div>
        <Button asChild>
          <Link href="/admin/billings/create">New Bill</Link>
        </Button>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>All Bills</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-wrap gap-4 mb-4 items-center">
            <Input v-model="search" placeholder="Search by patient name or description..." class="w-64" />
            <select v-model="statusFilter" class="border rounded-md px-2 py-1 text-sm">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
            </select>
          </div>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>#</TableHead>
                  <TableHead>Patient</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Due Date</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="billing in paginatedBillings" :key="billing.id" class="hover:bg-muted/50">
                  <TableCell class="font-mono text-sm">#{{ billing.id }}</TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">
                        {{ billing.patient?.name || 'Unknown Patient' }}
                      </span>
                      <span v-if="billing.patient" class="text-xs text-muted-foreground">{{ billing.patient.email || 'No email' }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-sm max-w-xs truncate">{{ billing.description || '-' }}</TableCell>
                  <TableCell class="font-medium">UGX {{ billing.amount.toLocaleString() }}</TableCell>
                  <TableCell>
                    <Badge :variant="statusBadge(billing.status)">
                      {{ billing.status.charAt(0).toUpperCase() + billing.status.slice(1) }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-sm">{{ billing.due_date || '-' }}</TableCell>
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
                        
                        <!-- View Details -->
                        <DropdownMenuItem asChild>
                          <Link :href="`/admin/billings/${billing.id}`">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Details
                          </Link>
                        </DropdownMenuItem>
                        
                        <!-- Edit Bill -->
                        <DropdownMenuItem asChild>
                          <Link :href="`/admin/billings/${billing.id}/edit`">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Bill
                          </Link>
                        </DropdownMenuItem>
                        
                        <!-- Mark as Paid -->
                        <DropdownMenuItem 
                          v-if="billing.status !== 'paid'"
                          @click="markAsPaid(billing.id)"
                          class="text-green-600 focus:text-green-600"
                        >
                          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          Mark as Paid
                        </DropdownMenuItem>
                        
                        <!-- Download PDF -->
                        <DropdownMenuItem asChild>
                          <Link :href="`/admin/billings/${billing.id}/pdf`">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                          </Link>
                        </DropdownMenuItem>
                        
                        <!-- Delete Bill -->
                        <DropdownMenuItem 
                          @click="deleteBilling(billing.id)"
                          class="text-red-600 focus:text-red-600"
                        >
                          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Delete Bill
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
                <TableRow v-if="paginatedBillings.length === 0">
                  <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm">No bills found.</p>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex items-center justify-between p-4 border-t">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <span>Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredBillings.length) }} of {{ filteredBillings.length }} results</span>
            </div>
            
            <div class="flex items-center gap-2">
              <Button 
                variant="outline" 
                size="sm" 
                @click="goToPage(currentPage - 1)" 
                :disabled="currentPage === 1"
              >
                Previous
              </Button>
              
              <div class="flex items-center gap-1">
                <Button 
                  v-for="pageNum in Math.min(5, totalPages)" 
                  :key="pageNum"
                  variant="outline" 
                  size="sm" 
                  :class="pageNum === currentPage ? 'bg-primary text-primary-foreground' : ''"
                  @click="goToPage(pageNum)"
                >
                  {{ pageNum }}
                </Button>
                
                <span v-if="totalPages > 5" class="px-2 text-sm text-muted-foreground">...</span>
                
                <Button 
                  v-if="totalPages > 5"
                  variant="outline" 
                  size="sm" 
                  :class="totalPages === currentPage ? 'bg-primary text-primary-foreground' : ''"
                  @click="goToPage(totalPages)"
                >
                  {{ totalPages }}
                </Button>
              </div>
              
              <Button 
                variant="outline" 
                size="sm" 
                @click="goToPage(currentPage + 1)" 
                :disabled="currentPage === totalPages"
              >
                Next
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 