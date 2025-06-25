@extends('supplier.dashboard')
@section('content')
<div class="card">
    <div class="card-body">
        <h2>Add New Product</h2>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="{{ route('supplier.add_product') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="wholesale_rate" class="form-label">Wholesale Rate (₹)</label>
                <input type="number" class="form-control" id="wholesale_rate" name="wholesale_rate" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="retail_rate" class="form-label">Suggested Retail Rate (₹)</label>
                <input type="number" class="form-control" id="retail_rate" name="retail_rate" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" required>
            </div>
            <div class="mb-3">
                <label for="images" class="form-label">Images (up to 3)</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</div>
@endsection