<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Skeleton } from '@/components/ui/skeleton';

const page = usePage();
const billings = computed(() => page.props.billings.data || []);
const statusFilter = ref('');
const loading = ref(false);
const error = ref(null);

const breadcrumbs = [
  { title: 'My Billings', href: '/my-billings' },
];

const filteredBillings = computed(() => {
  let result = billings.value;
  if (statusFilter.value) {
    result = result.filter(b => b.status === statusFilter.value);
  }
  return result;
});

function statusBadge(status: string) {
  switch (status) {
    case 'paid': return 'default';
    case 'pending': return 'secondary';
    case 'overdue': return 'destructive';
    default: return 'secondary';
  }
}
</script>

<template>
  <Head title="My Billings" />
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 0V4m0 12v4m8-8h-4m-8 0H4" />
                </svg>
              </div>
              <div>
                <h1 class="text-4xl font-bold text-white">My Billings</h1>
                <p class="text-blue-100 text-lg">Patient Panel</p>
              </div>
            </div>
            <p class="text-blue-100 text-lg">
              View and manage your billing history and invoices.
            </p>
          </div>
        </div>
        <!-- Search/Filters (if any) and Table in Card -->
        <Card class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm mb-8">
          <CardContent class="p-6 flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex flex-wrap gap-4 mb-4 items-center">
              <select v-model="statusFilter" class="border rounded-md px-2 py-1 text-sm">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
              </select>
            </div>
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
        <!-- Billings Table -->
        <Card v-else class="rounded-3xl border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 mb-6">
          <CardHeader>
            <CardTitle>Billing History</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Due Date</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="billing in filteredBillings" :key="billing.id" class="hover:bg-muted/50">
                    <TableCell class="font-mono text-sm">#{{ billing.id }}</TableCell>
                    <TableCell class="text-sm max-w-xs truncate">{{ billing.description || '-' }}</TableCell>
                    <TableCell class="font-medium">UGX {{ billing.amount.toLocaleString() }}</TableCell>
                    <TableCell>
                      <Badge :variant="statusBadge(billing.status)">
                        {{ billing.status.charAt(0).toUpperCase() + billing.status.slice(1) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-sm">{{ billing.due_date || '-' }}</TableCell>
                    <TableCell>
                      <Button size="sm" variant="outline" asChild>
                        <Link :href="`/my-billings/${billing.id}`">View Details</Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                  <TableRow v-if="filteredBillings.length === 0">
                    <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                      <svg class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <p class="text-sm">No bills found.</p>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template> 