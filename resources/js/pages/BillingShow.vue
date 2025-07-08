<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

const page = usePage();
const billing = computed(() => page.props.billing);
const isAdmin = computed(() => page.props.isAdmin);

function statusBadge(status: string) {
  switch (status) {
    case 'paid': return 'default';
    case 'pending': return 'secondary';
    case 'overdue': return 'destructive';
    default: return 'secondary';
  }
}

function printInvoice() {
  window.print();
}

function pdfUrl() {
  if (isAdmin.value) {
    return `/admin/billings/${billing.value.id}/pdf`;
  } else {
    return `/my-billings/${billing.value.id}/pdf`;
  }
}

function pdfViewUrl() {
  if (isAdmin.value) {
    return `/admin/billings/${billing.value.id}/pdf/view`;
  } else {
    return `/my-billings/${billing.value.id}/pdf/view`;
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search);
    if (params.get('print') === '1') {
      setTimeout(() => window.print(), 300);
    }
  }
});
</script>

<template>
  <Head :title="`Bill #${billing.id}`" />
  <AppLayout>
    <div class="max-w-2xl mx-auto p-4 flex flex-col gap-6 print:bg-white">
      <div class="flex items-center justify-between print:hidden">
        <h1 class="text-2xl font-bold tracking-tight">Bill Details</h1>
        <Button asChild variant="outline">
          <Link :href="isAdmin ? '/admin/billings' : '/my-billings'">Back</Link>
        </Button>
      </div>
      <Card class="shadow print:shadow-none print:border print:border-gray-300">
        <CardHeader class="flex flex-col items-center gap-2">
          <CardTitle class="text-xl">Invoice</CardTitle>
          <div class="flex gap-4 items-center">
            <span class="text-sm text-muted-foreground">Bill #{{ billing.id }}</span>
            <Badge :variant="statusBadge(billing.status)">{{ billing.status.charAt(0).toUpperCase() + billing.status.slice(1) }}</Badge>
          </div>
        </CardHeader>
        <CardContent>
          <div class="mb-4 flex flex-col md:flex-row md:justify-between gap-2">
            <div>
              <div class="font-semibold">Billed To:</div>
              <div v-if="billing.patient">Patient: {{ billing.patient.name }}</div>
              <div v-if="billing.clinic">Clinic: {{ billing.clinic.name }}</div>
            </div>
            <div>
              <div><span class="font-semibold">Due Date:</span> {{ billing.due_date || '-' }}</div>
              <div v-if="billing.paid_at"><span class="font-semibold">Paid At:</span> {{ billing.paid_at }}</div>
            </div>
          </div>
          <div class="mb-4">
            <div class="font-semibold">Description:</div>
            <div>{{ billing.description || '-' }}</div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200">
              <thead>
                <tr class="bg-muted">
                  <th class="px-3 py-2 text-left">#</th>
                  <th class="px-3 py-2 text-left">Item</th>
                  <th class="px-3 py-2 text-right">Qty</th>
                  <th class="px-3 py-2 text-right">Unit Price</th>
                  <th class="px-3 py-2 text-right">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in billing.items" :key="item.id">
                  <td class="px-3 py-2">{{ idx + 1 }}</td>
                  <td class="px-3 py-2">{{ item.description }}</td>
                  <td class="px-3 py-2 text-right">{{ item.quantity }}</td>
                  <td class="px-3 py-2 text-right">UGX {{ item.unit_price.toLocaleString() }}</td>
                  <td class="px-3 py-2 text-right">UGX {{ item.total.toLocaleString() }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="px-3 py-2 text-right font-bold">Total</td>
                  <td class="px-3 py-2 text-right font-bold">UGX {{ billing.amount.toLocaleString() }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="flex justify-end gap-2 mt-6 print:hidden">
            <Button variant="outline" @click="printInvoice">Print</Button>
            <Button variant="outline" asChild>
              <a :href="pdfUrl()" target="_blank">Download PDF</a>
            </Button>
            <Button variant="outline" asChild>
              <a :href="pdfViewUrl()" target="_blank">View PDF</a>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<style scoped>
@media print {
  .print\:hidden { display: none !important; }
  .print\:bg-white { background: #fff !important; }
  .print\:shadow-none { box-shadow: none !important; }
}
</style> 