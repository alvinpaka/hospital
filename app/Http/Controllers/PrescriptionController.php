<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use Inertia\Inertia;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $prescriptions = Prescription::with('drug')->where('patient_id', $request->user()->id)->get();
        return Inertia::render('prescriptions', [
            'prescriptions' => $prescriptions
        ]);
    }

    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view all prescriptions.');
        }
        $prescriptions = \App\Models\Prescription::with(['patient', 'drug'])->orderByDesc('created_at')->get();
        return \Inertia\Inertia::render('AdminPrescriptions', [
            'prescriptions' => $prescriptions
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $prescription = \App\Models\Prescription::findOrFail($id);
        $data = $request->validate([
            'medication' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'instructions' => 'required|string|max:255',
            'doctor' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $prescription->update($data);
        return response()->json(['success' => true, 'prescription' => $prescription->fresh('patient')]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $prescription = \App\Models\Prescription::findOrFail($id);
        $prescription->delete();
        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $prescription = \App\Models\Prescription::findOrFail($id);
        $data = $request->validate([
            'doctor' => 'required|string|max:255',
        ]);
        $prescription->update(['doctor' => $data['doctor']]);
        return response()->json(['success' => true, 'prescription' => $prescription->fresh('patient')]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $prescriptions = \App\Models\Prescription::with('patient')->get();
        $csv = "ID,Patient,Medication,Dosage,Instructions,Doctor,Start Date,End Date\n";
        foreach ($prescriptions as $p) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"\n',
                $p->id,
                $p->patient ? $p->patient->name : '',
                $p->medication,
                $p->dosage,
                $p->instructions,
                $p->doctor,
                $p->start_date,
                $p->end_date
            );
        }
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="prescriptions.csv"');
    }
}
