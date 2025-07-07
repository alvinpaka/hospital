<?php

namespace App\Http\Controllers;

use App\Models\Vital;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VitalController extends Controller
{
    public function index()
    {
        $vitals = Vital::where('patient_id', auth()->id())->get();
        return Inertia::render('vitals', [
            'vitals' => $vitals,
        ]);
    }

    public function store(Request $request)
    {
        // Store a new vital
    }

    public function show(Vital $vital)
    {
        // Show a single vital
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $vital = \App\Models\Vital::findOrFail($id);
        $old = $vital->toArray();
        $data = $request->validate([
            'date' => 'required|date',
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $vital->update($data);
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'subject_type' => 'Vital',
            'subject_id' => $vital->id,
            'details' => [
                'old' => $old,
                'new' => $vital->toArray(),
            ],
        ]);
        return response()->json(['success' => true, 'vital' => $vital->fresh('patient')]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $vital = \App\Models\Vital::findOrFail($id);
        $old = $vital->toArray();
        $vital->delete();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'delete',
            'subject_type' => 'Vital',
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
        $vital = \App\Models\Vital::findOrFail($id);
        $oldNurse = $vital->nurse;
        $data = $request->validate([
            'nurse' => 'required|string|max:255',
        ]);
        $vital->update(['nurse' => $data['nurse']]);
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'assign',
            'subject_type' => 'Vital',
            'subject_id' => $vital->id,
            'details' => [
                'old_nurse' => $oldNurse,
                'new_nurse' => $data['nurse'],
            ],
        ]);
        return response()->json(['success' => true, 'vital' => $vital->fresh('patient')]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'export',
            'subject_type' => 'Vital',
            'subject_id' => 0,
            'details' => [
                'exported_at' => now(),
            ],
        ]);
        $vitals = \App\Models\Vital::with('patient')->get();
        $csv = "ID,Patient,Date,Type,Value,Nurse,Notes\n";
        foreach ($vitals as $v) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"\n',
                $v->id,
                $v->patient ? $v->patient->name : '',
                $v->date,
                $v->type,
                $v->value,
                $v->nurse ?? '',
                str_replace('"', '""', $v->notes)
            );
        }
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="vitals.csv"');
    }

    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view all vitals.');
        }
        $vitals = \App\Models\Vital::with('patient')->orderByDesc('created_at')->get();
        return \Inertia\Inertia::render('AdminVitals', [
            'vitals' => $vitals
        ]);
    }
}
