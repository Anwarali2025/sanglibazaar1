<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Supplier Dashboard</h1>
        <nav class="nav nav-tabs mt-4">
            <a class="nav-link" href="{{ route('supplier.add_product') }}">Add Product</a>
            <a class="nav-link" href="{{ route('supplier.my_products') }}">My Products</a>
            <a class="nav-link" href="{{ route('supplier.payments') }}">Payments</a>
            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
        </nav>
        <div class="mt-4">
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>