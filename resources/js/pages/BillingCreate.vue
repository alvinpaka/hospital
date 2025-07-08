<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarIcon } from 'lucide-vue-next';
import { DateFormatter, getLocalTimeZone } from '@internationalized/date';
import { cn } from '@/lib/utils';

const page = usePage();
const patients = computed(() => page.props.patients || []);

const form = ref({
  patient_id: '',
  due_date: null as Date | null,
  status: 'pending',
  description: '',
  items: [
    { description: '', quantity: 1, unit_price: 0 }
  ],
});

const patientSearch = ref('');
const selectedPatient = computed(() => patients.value.find(p => p.id === form.value.patient_id));
const filteredPatients = computed(() => {
  if (!patientSearch.value) return patients.value;
  const search = patientSearch.value.toLowerCase();
  return patients.value.filter(p => p.name.toLowerCase().includes(search) || (p.email && p.email.toLowerCase().includes(search)));
});
watch(() => form.value.patient_id, (val) => {
  if (val) {
    const patient = patients.value.find(p => p.id === val);
    if (patient) patientSearch.value = patient.name;
  }
});

function addItem() {
  form.value.items.push({ description: '', quantity: 1, unit_price: 0 });
}
function removeItem(idx: number) {
  if (form.value.items.length > 1) form.value.items.splice(idx, 1);
}

const total = computed(() =>
  form.value.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
);

const df = new DateFormatter('en-US', { dateStyle: 'long' });
const dueDate = ref(form.value.due_date);
watch(dueDate, (val) => { form.value.due_date = val; });
watch(() => form.value.due_date, (val) => { dueDate.value = val; });

function submit() {
  const formData = {
    ...form.value,
    due_date: form.value.due_date
      ? (typeof form.value.due_date.toString === 'function' && form.value.due_date.toString().match(/^\d{4}-\d{2}-\d{2}$/)
          ? form.value.due_date.toString()
          : form.value.due_date instanceof Date
            ? form.value.due_date.toISOString().split('T')[0]
            : form.value.due_date.toString())
      : null,
    amount: total.value,
  };
  router.post('/admin/billings', formData);
}
</script>

<template>
  <Head title="Create Bill" />
  <AppLayout>
    <div class="max-w-2xl mx-auto p-4 flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Create Bill</h1>
        <Button asChild variant="outline">
          <Link href="/admin/billings">Back</Link>
        </Button>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Bill Details</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium mb-1">Patient</label>
              <div class="relative">
                <Input
                  v-model="patientSearch"
                  :readonly="!!form.patient_id"
                  placeholder="Search patient by name..."
                  class="w-full"
                  @focus="patientSearch = ''"
                />
                <button v-if="form.patient_id" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" @click="form.patient_id = ''; patientSearch = ''">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div v-if="!form.patient_id && patientSearch && filteredPatients.length > 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-neutral-900 border rounded shadow max-h-48 overflow-y-auto">
                  <ul>
                    <li v-for="p in filteredPatients" :key="p.id">
                      <button type="button" class="w-full text-left px-4 py-2 hover:bg-muted/50 focus:bg-muted/80 rounded flex justify-between items-center" @click="form.patient_id = p.id; patientSearch = p.name">
                        <span>{{ p.name }} <span class="text-xs text-muted-foreground">{{ p.email }}</span></span>
                        <span v-if="form.patient_id === p.id" class="text-blue-600 font-bold">Selected</span>
                      </button>
                    </li>
                  </ul>
                </div>
                <div v-if="!form.patient_id && patientSearch && filteredPatients.length === 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-neutral-900 border rounded shadow p-2 text-muted-foreground text-sm">No patients found.</div>
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
                <div v-for="(item, idx) in form.items" :key="idx" class="flex gap-2 items-center">
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
              <Button type="submit">Create Bill</Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 