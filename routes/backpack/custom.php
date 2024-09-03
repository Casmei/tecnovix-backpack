<?php

use App\Http\Controllers\Admin\AddressCrudController;
use App\Http\Controllers\Admin\BookCrudController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('book', 'BookCrudController');
    Route::crud('user', 'UserCrudController');
    Route::crud('address', 'AddressCrudController');

    Route::get('book/search-isbn', [BookCrudController::class, 'searchByISBN'])->name('admin.book.search-isbn');
    Route::get('address/search-zipcode', [AddressCrudController::class, 'searchByZipCode'])
    ->name('admin.address.search-by-zipcode');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
