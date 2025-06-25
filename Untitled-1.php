{
  "description": "Complete code for Supplier functionality in SangliBazaar1, including database migrations, routes, controllers, views, and middleware for Admin dashboard Supplier button.",
  "files": [
    {
      "path": "database/migrations/2025_06_25_create_users_table.php",
      "content": "<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'supplier', 'agent', 'customer', 'sub_admin']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}"
    },
    {
      "path": "database/migrations/2025_06_25_create_products_table.php",
      "content": "<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('name');
            $table->decimal('wholesale_rate', 8, 2);
            $table->decimal('retail_rate', 8, 2);
            $table->integer('stock');
            $table->json('images');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}"
    },
    {
      "path": "database/migrations/2025_06_25_create_payments_table.php",
      "content": "<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('amount_paid', 8, 2);
            $table->decimal('amount_pending', 8, 2);
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}"
    },
    {
      "path": "routes/web.php",
      "content": "<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupplierController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:supplier'])->group(function () {
    Route::get('/supplier/dashboard', [SupplierController::class, 'dashboard'])->name('supplier.dashboard');
    Route::get('/supplier/add-product', [SupplierController::class, 'showAddProductForm'])->name('supplier.add_product');
    Route::post('/supplier/add-product', [SupplierController::class, 'addProduct']);
    Route::get('/supplier/my-products', [SupplierController::class, 'myProducts'])->name('supplier.my_products');
    Route::get('/supplier/edit-product/{id}', [SupplierController::class, 'showEditProductForm'])->name('supplier.edit_product');
    Route::post('/supplier/edit-product/{id}', [SupplierController::class, 'editProduct']);
    Route::get('/supplier/payments', [SupplierController::class, 'payments'])->name('supplier.payments');
});

Route::get('/', function () {
    return view('welcome');
});
"
    },
    {
      "path": "app/Http/Controllers/AdminController.php",
      "content": "<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }
}
"
    },
    {
      "path": "app/Http/Controllers/SupplierController.php",
      "content": "<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function dashboard()
    {
        return view('supplier.dashboard');
    }

    public function showAddProductForm()
    {
        return view('supplier.add_product');
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'wholesale_rate' => 'required|numeric|min:0',
            'retail_rate' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        Product::create([
            'supplier_id' => Auth::id(),
            'name' => $request->name,
            'wholesale_rate' => $request->wholesale_rate,
            'retail_rate' => $request->retail_rate,
            'stock' => $request->stock,
            'images' => json_encode($images),
            'status' => 'pending',
        ]);

        return redirect()->route('supplier.my_products')->with('success', 'Product added successfully.');
    }

    public function myProducts()
    {
        $products = Product::where('supplier_id', Auth::id())->get();
        return view('supplier.my_products', compact('products'));
    }

    public function showEditProductForm($id)
    {
        $product = Product::where('id', $id)->where('supplier_id', Auth::id())->firstOrFail();
        if ($product->status !== 'pending') {
            return redirect()->route('supplier.my_products')->with('error', 'Cannot edit approved/rejected product.');
        }
        return view('supplier.edit_product', compact('product'));
    }

    public function editProduct(Request $request, $id)
    {
        $product = Product::where('id', $id)->where('supplier_id', Auth::id())->firstOrFail();
        if ($product->status !== 'pending') {
            return redirect()->route('supplier.my_products')->with('error', 'Cannot edit approved/rejected product.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'wholesale_rate' => 'required|numeric|min:0',
            'retail_rate' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $images = json_decode($product->images, true) ?? [];
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        $product->update([
            'name' => $request->name,
            'wholesale_rate' => $request->wholesale_rate,
            'retail_rate' => $request->retail_rate,
            'stock' => $request->stock,
            'images' => json_encode($images),
        ]);

        return redirect()->route('supplier.my_products')->with('success', 'Product updated successfully.');
    }

    public function payments()
    {
        $payments = Payment::where('supplier_id', Auth::id())->get();
        return view('supplier.payments', compact('payments'));
    }
}
"
    },
    {
      "path": "app/Http/Middleware/RoleMiddleware.php",
      "content": "<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403, 'Unauthorized access.');
        }
        return $next($request);
    }
}
"
    },
    {
      "path": "app/Models/Product.php",
      "content": "<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'supplier_id', 'name', 'wholesale_rate', 'retail_rate', 'stock', 'images', 'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}
"
    },
    {
      "path": "app/Models/Payment.php",
      "content": "<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'supplier_id', 'product_id', 'amount_paid', 'amount_pending',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
"
    },
    {
      "path": "resources/views/admin/dashboard.blade.php",
      "content": "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Admin Dashboard</title>
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
</head>
<body>
    <div class=\"container mt-5\">
        <h1>Admin Dashboard</h1>
        <div class=\"mt-4\">
            <a href=\"{{ route('supplier.dashboard') }}\" class=\"btn btn-primary\">Supplier Dashboard</a>
            <!-- Add more buttons for Agent, Customer, Sub-Admin, Purchase Index later -->
        </div>
        <form action=\"{{ route('logout') }}\" method=\"POST\" class=\"mt-4\">
            @csrf
            <button type=\"submit\" class=\"btn btn-danger\">Logout</button>
        </form>
    </div>
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
</body>
</html>
"
    },
    {
      "path": "resources/views/supplier/dashboard.blade.php",
      "content": "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Supplier Dashboard</title>
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
</head>
<body>
    <div class=\"container mt-5\">
        <h1>Supplier Dashboard</h1>
        <nav class=\"nav nav-tabs mt-4\">
            <a class=\"nav-link\" href=\"{{ route('supplier.add_product') }}\">Add Product</a>
            <a class=\"nav-link\" href=\"{{ route('supplier.my_products') }}\">My Products</a>
            <a class=\"nav-link\" href=\"{{ route('supplier.payments') }}\">Payments</a>
            <a class=\"nav-link\" href=\"{{ route('logout') }}\">Logout</a>
        </nav>
        <div class=\"mt-4\">
            @yield('content')
        </div>
    </div>
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
</body>
</html>
"
    },
    {
      "path": "resources/views/supplier/add_product.blade.php",
      "content": "@extends('supplier.dashboard')
@section('content')
<div class=\"card\">
    <div class=\"card-body\">
        <h2>Add New Product</h2>
        @if (session('success'))
            <div class=\"alert alert-success\">{{ session('success') }}</div>
        @endif
        <form action=\"{{ route('supplier.add_product') }}\" method=\"POST\" enctype=\"multipart/form-data\">
            @csrf
            <div class=\"mb-3\">
                <label for=\"name\" class=\"form-label\">Product Name</label>
                <input type=\"text\" class=\"form-control\" id=\"name\" name=\"name\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"wholesale_rate\" class=\"form-label\">Wholesale Rate (₹)</label>
                <input type=\"number\" class=\"form-control\" id=\"wholesale_rate\" name=\"wholesale_rate\" step=\"0.01\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"retail_rate\" class=\"form-label\">Suggested Retail Rate (₹)</label>
                <input type=\"number\" class=\"form-control\" id=\"retail_rate\" name=\"retail_rate\" step=\"0.01\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"stock\" class=\"form-label\">Stock</label>
                <input type=\"number\" class=\"form-control\" id=\"stock\" name=\"stock\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"images\" class=\"form-label\">Images (up to 3)</label>
                <input type=\"file\" class=\"form-control\" id=\"images\" name=\"images[]\" multiple accept=\"image/*\" required>
            </div>
            <button type=\"submit\" class=\"btn btn-primary\">Add Product</button>
        </form>
    </div>
</div>
@endsection
"
    },
    {
      "path": "resources/views/supplier/my_products.blade.php",
      "content": "@extends('supplier.dashboard')
@section('content')
<div class=\"card\">
    <div class=\"card-body\">
        <h2>My Products</h2>
        @if (session('success'))
            <div class=\"alert alert-success\">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class=\"alert alert-danger\">{{ session('error') }}</div>
        @endif
        <table class=\"table\">
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
                                <a href=\"{{ route('supplier.edit_product', $product->id) }}\" class=\"btn btn-sm btn-warning\">Edit</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
"
    },
    {
      "path": "resources/views/supplier/edit_product.blade.php",
      "content": "@extends('supplier.dashboard')
@section('content')
<div class=\"card\">
    <div class=\"card-body\">
        <h2>Edit Product</h2>
        @if (session('success'))
            <div class=\"alert alert-success\">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class=\"alert alert-danger\">{{ session('error') }}</div>
        @endif
        <form action=\"{{ route('supplier.edit_product', $product->id) }}\" method=\"POST\" enctype=\"multipart/form-data\">
            @csrf
            <div class=\"mb-3\">
                <label for=\"name\" class=\"form-label\">Product Name</label>
                <input type=\"text\" class=\"form-control\" id=\"name\" name=\"name\" value=\"{{ $product->name }}\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"wholesale_rate\" class=\"form-label\">Wholesale Rate (₹)</label>
                <input type=\"number\" class=\"form-control\" id=\"wholesale_rate\" name=\"wholesale_rate\" step=\"0.01\" value=\"{{ $product->wholesale_rate }}\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"retail_rate\" class=\"form-label\">Suggested Retail Rate (₹)</label>
                <input type=\"number\" class=\"form-control\" id=\"retail_rate\" name=\"retail_rate\" step=\"0.01\" value=\"{{ $product->retail_rate }}\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"stock\" class=\"form-label\">Stock</label>
                <input type=\"number\" class=\"form-control\" id=\"stock\" name=\"stock\" value=\"{{ $product->stock }}\" required>
            </div>
            <div class=\"mb-3\">
                <label for=\"images\" class=\"form-label\">Images (up to 3)</label>
                <input type=\"file\" class=\"form-control\" id=\"images\" name=\"images[]\" multiple accept=\"image/*\">
            </div>
            <button type=\"submit\" class=\"btn btn-primary\">Update Product</button>
        </form>
    </div>
</div>
@endsection
"
    },
    {
      "path": "resources/views/supplier/payments.blade.php",
      "content": "@extends('supplier.dashboard')
@section('content')
<div class=\"card\">
    <div class=\"card-body\">
        <h2>Payments</h2>
        <table class=\"table\">
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
"
    },
    {
      "path": "config/app.php",
      "content": "<?php
return [
    'name' => env('APP_NAME', 'SangliBazaar1'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => 'Asia/Kolkata',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => [
        // Default Laravel providers
        Illuminate\Auth\AuthServiceProvider::class,
        // ... other providers
    ],
    'aliases' => [
        // Default Laravel aliases
    ],
];
"
    },
    {
      "path": ".env",
      "content": "APP_NAME=SangliBazaar1
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sanglibazaar1
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
"
    }
  ],
  "instructions": [
    "1. Ensure XAMPP 8.0.30 is installed and Apache/MySQL is running.",
    "2. Create `sanglibazaar1` database in phpMyAdmin.",
    "3. Place all files in `C:\\xampp\\htdocs\\sanglibazaar1\\` as per the specified paths.",
    "4. Update `.env` with your `APP_KEY` (generate using `php artisan key:generate`).",
    "5. Run migrations: `php artisan migrate`.",
    "6. Install dependencies: `composer require laravel/ui`, `php artisan ui bootstrap --auth`.",
    "7. Start server: `php artisan serve`.",
    "8. Access Admin dashboard at `http://localhost:8000/admin/dashboard`.",
    "9. Create a Supplier user via `/register` (set `role` to `supplier` in database or modify registration form).",
    "10. Test Supplier dashboard at `http://localhost:8000/supplier/dashboard`.",
    "11. For image uploads, ensure `storage/app/public/products` is writable and run `php artisan storage:link`."
  ]
}