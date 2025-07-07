<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const page = usePage();
const billing = computed(() => page.props.billing);
const patients = computed(() => page.props.patients || []);
const clinics = computed(() => page.props.clinics || []);

const form = ref({
  patient_id: billing.value.patient_id || '',
  clinic_id: billing.value.clinic_id || '',
  due_date: billing.value.due_date || '',
  status: billing.value.status || 'pending',
  description: billing.value.description || '',
  items: billing.value.items.map((item: any) => ({
    id: item.id,
    description: item.description,
    quantity: item.quantity,
    unit_price: item.unit_price,
  })) || [
    { description: '', quantity: 1, unit_price: 0 }
  ],
});

const errors = computed(() => page.props.errors || {});

function addItem() {
  form.value.items.push({ description: '', quantity: 1, unit_price: 0 });
}
function removeItem(idx: number) {
  if (form.value.items.length > 1) form.value.items.splice(idx, 1);
}

const total = computed(() =>
  form.value.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
);

function submit() {
  router.put(`/admin/billings/${billing.value.id}`, {
    ...form.value,
    amount: total.value,
  });
}
</script>

<template>
  <Head title="Edit Bill" />
  <AppLayout>
    <div class="max-w-2xl mx-auto p-4 flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Edit Bill</h1>
        <Button asChild variant="outline">
          <Link href="/admin/billings">Back</Link>
        </Button>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Edit Bill Details</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Patient</label>
                <select v-model="form.patient_id" class="w-full border rounded-md px-2 py-2">
                  <option value="">-- Select Patient --</option>
                  <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Clinic</label>
                <select v-model="form.clinic_id" class="w-full border rounded-md px-2 py-2">
                  <option value="">-- Select Clinic --</option>
                  <option v-for="c in clinics" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Due Date</label>
              <Input v-model="form.due_date" type="date" class="w-full" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Status</label>
              <select v-model="form.status" class="w-full border rounded-md px-2 py-2">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <Input v-model="form.description" placeholder="Bill description (optional)" class="w-full" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Line Items</label>
              <div class="space-y-2">
                <div v-for="(item, idx) in form.items" :key="item.id || idx" class="flex gap-2 items-center">
                  <Input v-model="item.description" placeholder="Description" class="flex-1" />
                  <Input v-model.number="item.quantity" type="number" min="1" class="w-20" />
                  <Input v-model.number="item.unit_price" type="number" min="0" class="w-28" />
                  <span class="w-24 text-right">₦{{ (item.quantity * item.unit_price).toLocaleString() }}</span>
                  <Button type="button" variant="destructive" size="sm" @click="removeItem(idx)" v-if="form.items.length > 1">Remove</Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addItem">Add Item</Button>
              </div>
            </div>
            <div class="flex justify-end items-center gap-4">
              <span class="font-bold text-lg">Total: ₦{{ total.toLocaleString() }}</span>
              <Button type="submit">Update Bill</Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 