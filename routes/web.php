<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminIngramController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminScraperController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - INEXUS Chile
|--------------------------------------------------------------------------
*/

// Public Storefront Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tienda', [ShopController::class, 'index'])->name('shop.index');
Route::get('/categoria/{slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/producto/{slug}', [ProductController::class, 'show'])->name('product.show');

// Cart
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::get('/carrito/drawer-html', [CartController::class, 'drawerHtml'])->name('cart.drawer');
Route::post('/carrito/agregar', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/actualizar', [CartController::class, 'update'])->name('cart.update');
Route::post('/carrito/eliminar', [CartController::class, 'remove'])->name('cart.remove');

// Checkout & Payments
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/procesar', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/simular/{order_number}', [CheckoutController::class, 'simulateMp'])->name('checkout.simulate_mp');
Route::post('/checkout/simular/{order_number}', [CheckoutController::class, 'completeSimulatedMp'])->name('checkout.simulate_mp.complete');
Route::get('/checkout/exito/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/pendiente/{order_number}', [CheckoutController::class, 'pending'])->name('checkout.pending');
Route::get('/checkout/fallo/{order_number}', [CheckoutController::class, 'failure'])->name('checkout.failure');
Route::get('/pedido/{order_number}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

// Static & Legal Pages
Route::get('/nosotros', [PageController::class, 'about'])->name('page.about');
Route::get('/terminos-y-condiciones', [PageController::class, 'terms'])->name('page.terms');
Route::get('/politicas-de-privacidad', [PageController::class, 'privacy'])->name('page.privacy');
Route::get('/cambios-y-devoluciones', [PageController::class, 'returns'])->name('page.returns');
Route::get('/faqs', [PageController::class, 'faqs'])->name('page.faqs');
Route::get('/contacto', [PageController::class, 'contact'])->name('page.contact');
Route::post('/contacto', [PageController::class, 'contactSubmit'])->name('page.contact.submit');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('page.sitemap');

// Customer Authentication & Account
use App\Http\Controllers\CustomerAuthController;

Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');
Route::get('/registro', [CustomerAuthController::class, 'showRegister'])->name('register');
Route::post('/registro', [CustomerAuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta', [CustomerAuthController::class, 'account'])->name('customer.account');
    Route::put('/mi-cuenta', [CustomerAuthController::class, 'updateProfile'])->name('customer.account.update');
});

// Admin Authentication
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Panel
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/productos', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/productos/{id}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/productos/{id}', [AdminProductController::class, 'update'])->name('products.update');
    Route::post('/productos/{id}/scrape', [AdminProductController::class, 'triggerScrape'])->name('products.scrape');

    // Categories & Margins
    Route::get('/categorias', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::put('/categorias/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');

    // Ingram Micro Administration
    Route::get('/ingram', [AdminIngramController::class, 'index'])->name('ingram.index');
    Route::post('/ingram/settings', [AdminIngramController::class, 'updateSettings'])->name('ingram.settings');
    Route::post('/ingram/test', [AdminIngramController::class, 'testConnection'])->name('ingram.test');
    Route::post('/ingram/preview', [AdminIngramController::class, 'preview'])->name('ingram.preview');
    Route::post('/ingram/sync', [AdminIngramController::class, 'sync'])->name('ingram.sync');
    Route::post('/ingram/recalculate-all', [AdminIngramController::class, 'recalculateAllPrices'])->name('ingram.recalculate');

    // Scraper Administration
    Route::get('/scraper', [AdminScraperController::class, 'index'])->name('scraper.index');
    Route::post('/scraper/test', [AdminScraperController::class, 'testScrape'])->name('scraper.test');
    Route::post('/scraper/batch', [AdminScraperController::class, 'runBatch'])->name('scraper.batch');
    Route::post('/scraper/cross-match', [AdminScraperController::class, 'crossMatch'])->name('scraper.cross-match');
    Route::post('/scraper/apply', [AdminScraperController::class, 'applyToProduct'])->name('scraper.apply');

    // Orders
    Route::get('/pedidos', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/pedidos/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
});
