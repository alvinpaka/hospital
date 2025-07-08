<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $billing->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; background: #fff; }
        .title { font-size: 2em; font-weight: bold; margin-bottom: 10px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 0.9em; color: #fff; }
        .status-paid { background: #22c55e; }
        .status-pending { background: #f59e42; }
        .status-overdue { background: #ef4444; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th, table.items td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.items th { background: #f9fafb; }
        .total-row td { font-weight: bold; }
        .text-right { text-align: right; }
        .mb-2 { margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="title">Invoice</div>
    <table class="info-table">
        <tr>
            <td><strong>Bill #:</strong> {{ $billing->id }}</td>
            <td class="text-right">
                <span class="status-badge status-{{ $billing->status }}">
                    {{ ucfirst($billing->status) }}
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Billed To:</strong>
                @if($billing->patient)
                    Patient: {{ $billing->patient->name }}
                @elseif($billing->clinic)
                    Clinic: {{ $billing->clinic->name }}
                @else
                    N/A
                @endif
            </td>
            <td class="text-right"><strong>Due Date:</strong> {{ $billing->due_date ?? '-' }}</td>
        </tr>
        @if($billing->paid_at)
        <tr>
            <td></td>
            <td class="text-right"><strong>Paid At:</strong> {{ $billing->paid_at }}</td>
        </tr>
        @endif
    </table>
    <div class="mb-2"><strong>Description:</strong> {{ $billing->description ?? '-' }}</div>
    <table class="items">
        <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($billing->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="text-right">UGX{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">UGX{{ number_format($item->total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="total-row">
            <td colspan="4" class="text-right">Total</td>
            <td class="text-right">UGX{{ number_format($billing->amount, 2) }}</td>
        </tr>
        </tfoot>
    </table>
    <div style="text-align:center; color:#888; font-size:0.9em; margin-top:40px;">
        Thank you for your business.
    </div>
</div>
</body>
</html> 