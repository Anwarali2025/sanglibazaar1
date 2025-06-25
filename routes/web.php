<?php
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
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
