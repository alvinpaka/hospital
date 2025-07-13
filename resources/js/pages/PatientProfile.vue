<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Input from '@/components/ui/input/Input.vue';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';

const page = usePage();
const patientId = computed(() => page.props.patientId || page.props.id);
const breadcrumbs = computed(() => [
  { title: 'Staff Dashboard', href: '/staff/dashboard' },
  { title: 'My Patients', href: '/staff/patients' },
  { title: 'Patient Profile', href: `/staff/patients/${patientId.value}` },
]);

const loading = ref(true);
const error = ref('');
const patient = ref(null);
const vitals = ref([]);
const prescriptions = ref([]);
const labResults = ref([]);
const timeline = ref([]);
const activeTab = ref('timeline');
const showVitalsModal = ref(false);
const showPrescriptionModal = ref(false);
const showLabModal = ref(false);

const vitalsForm = ref({
  temperature: '',
  blood_pressure: '',
  heart_rate: '',
  weight: '',
  recorded_at: new Date().toISOString().slice(0, 16),
});
const vitalsLoading = ref(false);
const vitalsError = ref('');
function resetVitalsForm() {
  vitalsForm.value = {
    temperature: '',
    blood_pressure: '',
    heart_rate: '',
    weight: '',
    recorded_at: new Date().toISOString().slice(0, 16),
  };
  vitalsError.value = '';
}
async function submitVitals() {
  vitalsLoading.value = true;
  vitalsError.value = '';
  try {
    const response = await fetch(`/api/staff/patients/${patientId.value}/vitals`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        ...vitalsForm.value,
        recorded_at: new Date(vitalsForm.value.recorded_at).toISOString(),
      }),
    });
    if (response.ok) {
      showVitalsModal.value = false;
      resetVitalsForm();
      await fetchProfile();
    } else {
      const data = await response.json();
      vitalsError.value = data.error || 'Failed to add vitals.';
    }
  } catch {
    vitalsError.value = 'Failed to add vitals.';
  } finally {
    vitalsLoading.value = false;
  }
}

const prescriptionForm = ref({
  drug_id: '',
  dosage: '',
  instructions: '',
  start_date: new Date().toISOString().slice(0, 10),
  end_date: '',
});
const prescriptionLoading = ref(false);
const prescriptionError = ref('');
const drugs = ref([]);
const drugsLoading = ref(false);
async function fetchDrugs() {
  drugsLoading.value = true;
  try {
    const response = await fetch('/api/staff/drugs', {
      credentials: 'include',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });
    if (response.ok) {
      const data = await response.json();
      drugs.value = data.drugs || [];
    }
  } finally {
    drugsLoading.value = false;
  }
}
function resetPrescriptionForm() {
  prescriptionForm.value = {
    drug_id: '',
    dosage: '',
    instructions: '',
    start_date: new Date().toISOString().slice(0, 10),
    end_date: '',
  };
  prescriptionError.value = '';
}
async function submitPrescription() {
  prescriptionLoading.value = true;
  prescriptionError.value = '';
  try {
    const response = await fetch(`/api/staff/patients/${patientId.value}/prescriptions`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        ...prescriptionForm.value,
        drug_id: Number(prescriptionForm.value.drug_id),
      }),
    });
    if (response.ok) {
      showPrescriptionModal.value = false;
      resetPrescriptionForm();
      await fetchProfile();
    } else {
      const data = await response.json();
      prescriptionError.value = data.error || 'Failed to prescribe medication.';
    }
  } catch {
    prescriptionError.value = 'Failed to prescribe medication.';
  } finally {
    prescriptionLoading.value = false;
  }
}

const labForm = ref({
  type: '',
  notes: '',
  date: new Date().toISOString().slice(0, 10),
});
const labLoading = ref(false);
const labError = ref('');
const labTypes = [
  'Complete Blood Count',
  'Urinalysis',
  'Blood Glucose',
  'Liver Function Test',
  'Kidney Function Test',
  'Malaria Test',
  'COVID-19 PCR',
  'Other',
];
function resetLabForm() {
  labForm.value = {
    type: '',
    notes: '',
    date: new Date().toISOString().slice(0, 10),
  };
  labError.value = '';
}
async function submitLab() {
  labLoading.value = true;
  labError.value = '';
  try {
    const response = await fetch(`/api/staff/patients/${patientId.value}/lab-results`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        ...labForm.value,
        date: new Date(labForm.value.date).toISOString().slice(0, 10),
      }),
    });
    if (response.ok) {
      showLabModal.value = false;
      resetLabForm();
      await fetchProfile();
    } else {
      const data = await response.json();
      labError.value = data.error || 'Failed to order lab test.';
    }
  } catch {
    labError.value = 'Failed to order lab test.';
  } finally {
    labLoading.value = false;
  }
}

async function fetchProfile() {
  loading.value = true;
  try {
    const response = await fetch(`/api/staff/patients/${patientId.value}/profile`, {
      credentials: 'include',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });
    if (response.ok) {
      const data = await response.json();
      patient.value = data.patient;
      vitals.value = data.vitals;
      prescriptions.value = data.prescriptions;
      labResults.value = data.labResults;
      timeline.value = data.timeline;
    } else {
      error.value = 'Failed to load patient profile.';
    }
  } catch (err) {
    error.value = 'Failed to load patient profile.';
    console.error('Error fetching patient profile:', err);
  } finally {
    loading.value = false;
  }
}

function formatDate(dateStr: string) {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

onMounted(() => {
  fetchProfile();
});
</script>

<template>
  <Head :title="patient?.name ? `${patient.name} - Profile` : 'Patient Profile'" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <Card class="mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-4">
            <span>Patient Profile</span>
            <Badge v-if="patient?.email_verified_at" variant="default">Verified</Badge>
            <Badge v-else variant="secondary">Unverified</Badge>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="loading" class="text-center py-8 text-muted-foreground">Loading...</div>
          <div v-else-if="error" class="text-center py-8 text-red-600">{{ error }}</div>
          <div v-else class="flex flex-col md:flex-row gap-8 items-start">
            <div class="flex-1 space-y-2">
              <div class="text-2xl font-bold">{{ patient.name }}</div>
              <div class="text-muted-foreground text-sm">{{ patient.email }}</div>
              <div class="text-muted-foreground text-sm">Phone: {{ patient.phone || 'N/A' }}</div>
              <div class="text-muted-foreground text-sm">Joined: {{ formatDate(patient.created_at) }}</div>
            </div>
            <div class="flex flex-col gap-2">
              <Button variant="outline" as-child>
                <Link :href="`/staff/patients`">Back to Patients</Link>
              </Button>
            </div>
          </div>
          <!-- Quick Actions -->
          <div class="flex flex-wrap gap-2 mt-6">
            <Button variant="default" @click="showVitalsModal = true">Add Vitals</Button>
            <Button variant="default" @click="showPrescriptionModal = true">Prescribe Medication</Button>
            <Button variant="default" @click="showLabModal = true">Order Lab Test</Button>
          </div>
        </CardContent>
      </Card>

      <!-- Add Vitals Modal -->
      <Dialog v-model:open="showVitalsModal" @after-leave="resetVitalsForm">
        <DialogContent class="max-w-lg">
          <DialogHeader>
            <DialogTitle>Add Vitals</DialogTitle>
          </DialogHeader>
          <form class="space-y-4 py-2" @submit.prevent="submitVitals">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm mb-1">Temperature (°C)</label>
                <Input v-model="vitalsForm.temperature" type="number" step="0.1" min="30" max="45" placeholder="e.g. 36.7" />
              </div>
              <div>
                <label class="block text-sm mb-1">Blood Pressure</label>
                <Input v-model="vitalsForm.blood_pressure" type="text" placeholder="e.g. 120/80" />
              </div>
              <div>
                <label class="block text-sm mb-1">Heart Rate</label>
                <Input v-model="vitalsForm.heart_rate" type="number" min="30" max="200" placeholder="e.g. 72" />
              </div>
              <div>
                <label class="block text-sm mb-1">Weight (kg)</label>
                <Input v-model="vitalsForm.weight" type="number" step="0.1" min="1" max="500" placeholder="e.g. 70" />
              </div>
              <div class="col-span-2">
                <label class="block text-sm mb-1">Recorded At</label>
                <Input v-model="vitalsForm.recorded_at" type="datetime-local" />
              </div>
            </div>
            <div v-if="vitalsError" class="text-red-600 text-sm">{{ vitalsError }}</div>
          </form>
          <DialogFooter>
            <Button variant="outline" @click="showVitalsModal = false" :disabled="vitalsLoading">Cancel</Button>
            <Button type="submit" form="" :disabled="vitalsLoading" @click="submitVitals">
              <span v-if="vitalsLoading">Adding...</span>
              <span v-else>Add</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <!-- Prescribe Medication Modal -->
      <Dialog v-model:open="showPrescriptionModal" @after-leave="resetPrescriptionForm" @open="fetchDrugs()">
        <DialogContent class="max-w-lg">
          <DialogHeader>
            <DialogTitle>Prescribe Medication</DialogTitle>
          </DialogHeader>
          <form class="space-y-4 py-2" @submit.prevent="submitPrescription">
            <div class="space-y-2">
              <label class="block text-sm mb-1">Drug</label>
              <Select v-model="prescriptionForm.drug_id" :disabled="drugsLoading">
                <SelectTrigger class="w-full">{{ drugsLoading ? 'Loading...' : (drugs.find(d => d.id == prescriptionForm.drug_id)?.name || 'Select a drug') }}</SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="drug in drugs" :key="drug.id" :value="String(drug.id)">
                    {{ drug.name }}<span v-if="drug.generic_name"> ({{ drug.generic_name }})</span>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm mb-1">Dosage</label>
                <Input v-model="prescriptionForm.dosage" type="text" placeholder="e.g. 500mg 2x daily" />
              </div>
              <div>
                <label class="block text-sm mb-1">Instructions</label>
                <Input v-model="prescriptionForm.instructions" type="text" placeholder="e.g. After meals" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm mb-1">Start Date</label>
                <Input v-model="prescriptionForm.start_date" type="date" />
              </div>
              <div>
                <label class="block text-sm mb-1">End Date</label>
                <Input v-model="prescriptionForm.end_date" type="date" />
              </div>
            </div>
            <div v-if="prescriptionError" class="text-red-600 text-sm">{{ prescriptionError }}</div>
          </form>
          <DialogFooter>
            <Button variant="outline" @click="showPrescriptionModal = false" :disabled="prescriptionLoading">Cancel</Button>
            <Button type="submit" form="" :disabled="prescriptionLoading || !prescriptionForm.drug_id || !prescriptionForm.dosage || !prescriptionForm.start_date" @click="submitPrescription">
              <span v-if="prescriptionLoading">Prescribing...</span>
              <span v-else>Prescribe</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <!-- Order Lab Test Modal -->
      <Dialog v-model:open="showLabModal" @after-leave="resetLabForm">
        <DialogContent class="max-w-lg">
          <DialogHeader>
            <DialogTitle>Order Lab Test</DialogTitle>
          </DialogHeader>
          <form class="space-y-4 py-2" @submit.prevent="submitLab">
            <div class="space-y-2">
              <label class="block text-sm mb-1">Test Type</label>
              <Select v-model="labForm.type">
                <SelectTrigger class="w-full">{{ labForm.type || 'Select test type' }}</SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="type in labTypes" :key="type" :value="type">{{ type }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <label class="block text-sm mb-1">Notes</label>
              <Input v-model="labForm.notes" type="text" placeholder="Additional notes (optional)" />
            </div>
            <div>
              <label class="block text-sm mb-1">Date</label>
              <Input v-model="labForm.date" type="date" />
            </div>
            <div v-if="labError" class="text-red-600 text-sm">{{ labError }}</div>
          </form>
          <DialogFooter>
            <Button variant="outline" @click="showLabModal = false" :disabled="labLoading">Cancel</Button>
            <Button type="submit" form="" :disabled="labLoading || !labForm.type || !labForm.date" @click="submitLab">
              <span v-if="labLoading">Ordering...</span>
              <span v-else>Order</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="mb-4">
          <TabsTrigger value="timeline">Timeline</TabsTrigger>
          <TabsTrigger value="vitals">Vitals</TabsTrigger>
          <TabsTrigger value="prescriptions">Prescriptions</TabsTrigger>
          <TabsTrigger value="labresults">Lab Results</TabsTrigger>
        </TabsList>
        <TabsContent value="timeline">
          <Card>
            <CardHeader>
              <CardTitle>Patient Timeline</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="timeline.length === 0" class="text-center text-muted-foreground py-8">No events yet.</div>
              <ol class="relative border-l border-muted-foreground/20">
                <li v-for="event in timeline" :key="event.id" class="mb-8 ml-4">
                  <div class="absolute w-3 h-3 bg-primary rounded-full mt-1.5 -left-1.5 border border-white"></div>
                  <time class="mb-1 text-xs font-normal text-muted-foreground">{{ formatDate(event.date) }}</time>
                  <div class="text-base font-semibold">{{ event.title }}</div>
                  <div class="text-sm text-muted-foreground">{{ event.description }}</div>
                </li>
              </ol>
            </CardContent>
          </Card>
        </TabsContent>
        <TabsContent value="vitals">
          <Card>
            <CardHeader>
              <CardTitle>Vitals History</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="vitals.length === 0" class="text-center text-muted-foreground py-8">No vitals recorded.</div>
              <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr>
                      <th class="px-2 py-1">Date</th>
                      <th class="px-2 py-1">Temp (°C)</th>
                      <th class="px-2 py-1">BP</th>
                      <th class="px-2 py-1">HR</th>
                      <th class="px-2 py-1">Weight (kg)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="vital in vitals" :key="vital.id">
                      <td class="px-2 py-1">{{ formatDate(vital.recorded_at) }}</td>
                      <td class="px-2 py-1">{{ vital.temperature ?? '-' }}</td>
                      <td class="px-2 py-1">{{ vital.blood_pressure ?? '-' }}</td>
                      <td class="px-2 py-1">{{ vital.heart_rate ?? '-' }}</td>
                      <td class="px-2 py-1">{{ vital.weight ?? '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
        <TabsContent value="prescriptions">
          <Card>
            <CardHeader>
              <CardTitle>Prescriptions</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="prescriptions.length === 0" class="text-center text-muted-foreground py-8">No prescriptions found.</div>
              <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr>
                      <th class="px-2 py-1">Date</th>
                      <th class="px-2 py-1">Medication</th>
                      <th class="px-2 py-1">Dosage</th>
                      <th class="px-2 py-1">Instructions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="rx in prescriptions" :key="rx.id">
                      <td class="px-2 py-1">{{ formatDate(rx.start_date) }}</td>
                      <td class="px-2 py-1">{{ rx.drug?.name || rx.drug?.generic_name || 'N/A' }}</td>
                      <td class="px-2 py-1">{{ rx.dosage }}</td>
                      <td class="px-2 py-1">{{ rx.instructions || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
        <TabsContent value="labresults">
          <Card>
            <CardHeader>
              <CardTitle>Lab Results</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="labResults.length === 0" class="text-center text-muted-foreground py-8">No lab results found.</div>
              <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr>
                      <th class="px-2 py-1">Date</th>
                      <th class="px-2 py-1">Type</th>
                      <th class="px-2 py-1">Result</th>
                      <th class="px-2 py-1">Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="lab in labResults" :key="lab.id">
                      <td class="px-2 py-1">{{ formatDate(lab.date) }}</td>
                      <td class="px-2 py-1">{{ lab.type }}</td>
                      <td class="px-2 py-1">{{ lab.result }}</td>
                      <td class="px-2 py-1">{{ lab.notes || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template> 