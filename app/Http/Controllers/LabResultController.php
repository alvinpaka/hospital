<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabResult;
use Inertia\Inertia;
use App\Models\ActivityLog;

class LabResultController extends Controller
{
    public function index(Request $request)
    {
        $labResults = LabResult::where('patient_id', $request->user()->id)->get();
        return Inertia::render('lab-results', [
            'labResults' => $labResults
        ]);
    }

    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view all lab results.');
        }
        $labResults = \App\Models\LabResult::with('patient')->orderByDesc('created_at')->get();
        return \Inertia\Inertia::render('AdminLabResults', [
            'labResults' => $labResults
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $labResult = \App\Models\LabResult::findOrFail($id);
        $old = $labResult->toArray();
        $data = $request->validate([
            'date' => 'required|date',
            'type' => 'required|string|max:255',
            'result' => 'required|string|max:255',
            'doctor' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $labResult->update($data);
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'subject_type' => 'LabResult',
            'subject_id' => $labResult->id,
            'details' => [
                'old' => $old,
                'new' => $labResult->toArray(),
            ],
        ]);
        return response()->json(['success' => true, 'labResult' => $labResult->fresh('patient')]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $labResult = \App\Models\LabResult::findOrFail($id);
        $old = $labResult->toArray();
        $labResult->delete();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'delete',
            'subject_type' => 'LabResult',
            'subject_id' => $id,
            'details' => [
                'old' => $old,
            ],
        ]);
        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $labResult = \App\Models\LabResult::findOrFail($id);
        $oldDoctor = $labResult->doctor;
        $data = $request->validate([
            'doctor' => 'required|string|max:255',
        ]);
        $labResult->update(['doctor' => $data['doctor']]);
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'assign',
            'subject_type' => 'LabResult',
            'subject_id' => $labResult->id,
            'details' => [
                'old_doctor' => $oldDoctor,
                'new_doctor' => $data['doctor'],
            ],
        ]);
        return response()->json(['success' => true, 'labResult' => $labResult->fresh('patient')]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'export',
            'subject_type' => 'LabResult',
            'subject_id' => 0,
            'details' => [
                'exported_at' => now(),
            ],
        ]);
        $labResults = \App\Models\LabResult::with('patient')->get();
        $csv = "ID,Patient,Date,Type,Result,Doctor,Notes\n";
        foreach ($labResults as $l) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"\n',
                $l->id,
                $l->patient ? $l->patient->name : '',
                $l->date,
                $l->type,
                $l->result,
                $l->doctor,
                str_replace('"', '""', $l->notes)
            );
        }
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="lab_results.csv"');
    }
}
