<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const page = usePage();
const billings = computed(() => page.props.billings.data || []);
const statusFilter = ref('');

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
  <AppLayout>
    <div class="max-w-5xl mx-auto p-4 flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">My Billings</h1>
          <p class="text-muted-foreground">View and manage your bills and invoices.</p>
        </div>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>All My Bills</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-wrap gap-4 mb-4 items-center">
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
  </AppLayout>
</template> 