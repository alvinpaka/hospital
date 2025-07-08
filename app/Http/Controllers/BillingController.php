<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    // Admin: List all bills
    public function index(Request $request)
    {
        $billings = Billing::with(['patient', 'clinic', 'items'])
            ->orderByDesc('created_at')
            ->paginate(20);
        return Inertia::render('AdminBillings', [
            'billings' => $billings,
        ]);
    }

    // Show a single bill
    public function show(Billing $billing)
    {
        $billing->load(['patient', 'clinic', 'items']);
        $isAdmin = request()->routeIs('admin.billings.show');
        return Inertia::render('BillingShow', [
            'billing' => $billing,
            'isAdmin' => $isAdmin,
        ]);
    }

    // Admin: Create bill form
    public function create()
    {
        $patients = User::where('role', 'patient')->get(['id', 'name']);
        return Inertia::render('BillingCreate', [
            'patients' => $patients,
        ]);
    }

    // Admin: Store new bill
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'items' => 'array',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
        
        // Set clinic_id from authenticated admin user
        $data['clinic_id'] = $request->user()->clinic_id;
        
        $billing = Billing::create($data);
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $billing->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }
        return redirect()->route('admin.billings')->with('success', 'Billing created successfully.');
    }

    // Admin: Edit bill form
    public function edit(Billing $billing)
    {
        $billing->load('items');
        $patients = User::where('role', 'patient')->get(['id', 'name']);
        return Inertia::render('BillingEdit', [
            'billing' => $billing,
            'patients' => $patients,
        ]);
    }

    // Admin: Update bill
    public function update(Request $request, Billing $billing)
    {
        $data = $request->validate([
            'patient_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'items' => 'array',
            'items.*.id' => 'nullable|exists:billing_items,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
        
        // Ensure clinic_id is set from authenticated admin user
        $data['clinic_id'] = $request->user()->clinic_id;
        
        $billing->update($data);
        // Sync items
        $existingIds = $billing->items()->pluck('id')->toArray();
        $newIds = collect($data['items'])->pluck('id')->filter()->toArray();
        $toDelete = array_diff($existingIds, $newIds);
        if ($toDelete) {
            $billing->items()->whereIn('id', $toDelete)->delete();
        }
        foreach ($data['items'] as $item) {
            if (!empty($item['id'])) {
                $billing->items()->where('id', $item['id'])->update([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            } else {
                $billing->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }
        return redirect()->route('admin.billings')->with('success', 'Billing updated successfully.');
    }

    // Admin: Delete bill
    public function destroy(Billing $billing)
    {
        $billing->delete();
        return redirect()->route('admin.billings')->with('success', 'Billing deleted.');
    }

    // Mark as paid
    public function markAsPaid(Billing $billing)
    {
        $billing->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Billing marked as paid.');
    }

    // Patient/Clinic: View own bills
    public function myBills(Request $request)
    {
        $user = $request->user();
        $query = Billing::with(['items']);
        if ($user->role === 'patient') {
            $query->where('patient_id', $user->id);
        } elseif ($user->role === 'clinic') {
            $query->where('clinic_id', $user->id);
        }
        $billings = $query->orderByDesc('created_at')->paginate(20);
        return Inertia::render('MyBillings', [
            'billings' => $billings,
        ]);
    }

    // Export PDF (Download)
    public function pdf(Billing $billing)
    {
        $billing->load(['patient', 'clinic', 'items']);
        $pdf = Pdf::loadView('pdf.invoice', ['billing' => $billing]);
        return $pdf->download('invoice_'.$billing->id.'.pdf');
    }

    // Export PDF (View in browser)
    public function pdfView(Billing $billing)
    {
        $billing->load(['patient', 'clinic', 'items']);
        $pdf = Pdf::loadView('pdf.invoice', ['billing' => $billing]);
        return $pdf->stream('invoice_'.$billing->id.'.pdf');
    }
} 