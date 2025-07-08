<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarIcon } from 'lucide-vue-next';
import { DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { cn } from '@/lib/utils';
import { Calendar } from '@/components/ui/calendar';

const page = usePage();
const billing = computed(() => page.props.billing);
const patients = computed(() => page.props.patients || []);

const form = ref({
  patient_id: billing.value.patient_id || '',
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

// Initialize dueDate as a CalendarDate if it's a string
function toCalendarDate(val) {
  if (!val) return null;
  if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
    return parseDate(val);
  }
  return val;
}

const df = new DateFormatter('en-US', { dateStyle: 'long' });
const dueDate = ref(toCalendarDate(billing.value.due_date));
watch(dueDate, (val) => { form.value.due_date = val; });
watch(() => form.value.due_date, (val) => { dueDate.value = toCalendarDate(val); });

function addItem() {
  form.value.items.push({ description: '', quantity: 1, unit_price: 0 });
}
function removeItem(idx: number) {
  if (form.value.items.length > 1) form.value.items.splice(idx, 1);
}

const total = computed(() =>
  form.value.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
);

const selectedPatient = computed(() => patients.value.find(p => p.id === form.value.patient_id));

function submit() {
  router.put(`/admin/billings/${billing.value.id}`, {
    ...form.value,
    due_date: form.value.due_date
      ? (typeof form.value.due_date.toString === 'function' && form.value.due_date.toString().match(/^\d{4}-\d{2}-\d{2}$/)
          ? form.value.due_date.toString()
          : form.value.due_date instanceof Date
            ? form.value.due_date.toISOString().split('T')[0]
            : form.value.due_date.toString())
      : null,
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
                <div class="w-full rounded-md border bg-background text-foreground px-3 py-2">{{ selectedPatient ? selectedPatient.name : '' }}</div>
              </div>
            </div>
            <div>
              <Label>Due Date</Label>
              <Popover>
                <PopoverTrigger as-child>
                  <Button
                    variant="outline"
                    :class="cn('w-full justify-start text-left font-normal', !dueDate && 'text-muted-foreground')"
                  >
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    {{ dueDate ? df.format(dueDate.toDate ? dueDate.toDate(getLocalTimeZone()) : dueDate) : 'Pick a date' }}
                  </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                  <Calendar v-model="dueDate" initial-focus />
                </PopoverContent>
              </Popover>
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
                  <span class="w-24 text-right">UGX {{ (item.quantity * item.unit_price).toLocaleString() }}</span>
                  <Button type="button" variant="destructive" size="sm" @click="removeItem(idx)" v-if="form.items.length > 1">Remove</Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addItem">Add Item</Button>
              </div>
            </div>
            <div class="flex justify-end items-center gap-4">
              <span class="font-bold text-lg">Total: UGX {{ total.toLocaleString() }}</span>
              <Button type="submit">Update Bill</Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 