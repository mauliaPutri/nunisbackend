<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DetailTransaksiController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[UserController::class,'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/getUser', [UserController::class, 'index']);
Route::delete('/deleteuser/{id}', [UserController::class, 'deleteuser']);
Route::get('/user/{email}', [UserController::class, 'getoneuser']);
Route::get('/picture/{email}',[UserController::class, 'getpicturebyemail']);
Route::post('/update-user-status/{email}', [UserController::class, 'updateUserStatus']);
Route::post('/forgot-password/{email}', [UserController::class, 'forgotPassword']);
Route::post('/verify-otp', [UserController::class, 'verifyOTP']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categoriesAdd', [CategoryController::class, 'store']);
// Route::post('categories', [CategoryController::class, 'store']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
Route::post('/editcategories', [CategoryController::class, 'update']);

// Endpoint baru untuk mendapatkan kategori beserta menu items
Route::get('/categories/{id}/menu-items', [CategoryController::class, 'getMenuItemsByCategory']);

// Route::get('getCategory', [UserController::class, 'getCategory']);
Route::post('/contact',[ContactController::class, 'contact']);
Route::get('/contact', [ContactController::class, 'index']);
// Route::get('contactcol', [ContactController::class, 'getColumns']);
Route::delete('/contact/{id}', [ContactController::class, 'destroy']);

Route::post('/addmenuitems', [MenuController::class, 'store']);
Route::get('/allmenu', [MenuController::class, 'indexAll']);
Route::get('/menu-items', [MenuController::class, 'index']);
Route::delete('/menu-items/{kode_menu}', [MenuController::class, 'destroy']);
Route::post('/editmenu', [MenuController::class, 'update']);

Route::post('/upload-profile-picture', [UserController::class, 'uploadProfilePicture']);

Route::post('/cart', [CartController::class, 'store']);
Route::get('/allcart', [CartController::class, 'index']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);

Route::post('/transaksi', [TransaksiController::class, 'store']);
Route::get('/transaksi/{id}', [TransaksiController::class, 'getTransactionByUser']);
Route::get('/alltransaksi', [TransaksiController::class, 'index']);
Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy']);
Route::get('/statistics', [TransaksiController::class, 'statistics']); 
Route::post('/transaksi-date-range', [TransaksiController::class, 'getTransactionsByDateRange']);
Route::get('/transaksi/{id}/with-details', [TransaksiController::class, 'getTransactionByUserWithDetails']);
Route::get('transaksiget/{faktur}', [TransaksiController::class, 'getTransaksiByFaktur']);
Route::get('/detail-transaksi', [DetailTransaksiController::class, 'index']);
Route::get('/detail-transaksi/{faktur}', [DetailTransaksiController::class, 'showByFaktur']);
Route::post('/update-status-by-faktur', [TransaksiController::class, 'updateStatusByFaktur']);
Route::post('/update-user-profile', [UserController::class, 'updateUserProfile']);

// Tambahkan route baru
Route::post('/statistics-by-date', [DashboardController::class, 'statisticsByDateRange']);
Route::post('/menu-terlaris', [DashboardController::class, 'menuTerlarisByDateRange']);



