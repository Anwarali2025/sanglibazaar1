@extends('supplier.dashboard')
@section('content')
<div class="card">
    <div class="card-body">
        <h2>My Products</h2>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Wholesale Rate</th>
                    <th>Retail Rate</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>₹{{ $product->wholesale_rate }}</td>
                        <td>₹{{ $product->retail_rate }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ ucfirst($product->status) }}</td>
                        <td>
                            @if ($product->status === 'pending')
                                <a href="{{ route('supplier.edit_product', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection