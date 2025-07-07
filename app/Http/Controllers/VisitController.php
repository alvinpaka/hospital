<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use Inertia\Inertia;
use App\Models\ActivityLog;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = Visit::where('patient_id', $request->user()->id)->get();
        return Inertia::render('visits', [
            'visits' => $visits
        ]);
    }

    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view all visits.');
        }
        $visits = \App\Models\Visit::with('patient')->orderByDesc('created_at')->get();
        return \Inertia\Inertia::render('AdminVisits', [
            'visits' => $visits
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $visit = \App\Models\Visit::findOrFail($id);
        $data = $request->validate([
            'date' => 'required|date',
            'reason' => 'required|string|max:255',
            'doctor' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $visit->update($data);
        return response()->json(['success' => true, 'visit' => $visit->fresh('patient')]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $visit = \App\Models\Visit::findOrFail($id);
        $visit->delete();
        return response()->json(['success' => true]);
    }

    public function assign(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $visit = \App\Models\Visit::findOrFail($id);
        $data = $request->validate([
            'doctor' => 'required|string|max:255',
        ]);
        $visit->update(['doctor' => $data['doctor']]);
        return response()->json(['success' => true, 'visit' => $visit->fresh('patient')]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $visits = \App\Models\Visit::with('patient')->get();
        $csv = "ID,Patient,Date,Reason,Doctor,Notes\n";
        foreach ($visits as $v) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s"\n',
                $v->id,
                $v->patient ? $v->patient->name : '',
                $v->date,
                $v->reason,
                $v->doctor,
                str_replace('"', '""', $v->notes)
            );
        }
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="visits.csv"');
    }
}
