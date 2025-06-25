@extends('supplier.dashboard')
@section('content')
<div class="card">
    <div class="card-body">
        <h2>Payments</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Amount Paid</th>
                    <th>Amount Pending</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ $payment->product ? $payment->product->name : 'N/A' }}</td>
                        <td>₹{{ $payment->amount_paid }}</td>
                        <td>₹{{ $payment->amount_pending }}</td>
                        <td>{{ $payment->created_at->format('d-m-Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection