<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BorrowRecordController;

Route::post('register', [UserController::class, 'register'])->middleware('throttle:10,1');
Route::post('login', [UserController::class, 'login'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum'])->group(function () {
        // Member Routes: Can view books/authors, borrow and return books, and update their profile
        Route::middleware(['role:Member', 'throttle:60,1'])->group(function () {
            Route::get('books', [BookController::class, 'index']); // View books
            Route::get('books/{id}', [BookController::class, 'show']); // View specific book
            Route::get('authors', [AuthorController::class, 'index']); // View authors
            Route::get('authors/{id}', [AuthorController::class, 'show']); // View specific author
            Route::post('books/{id}/borrow', [BookController::class, 'borrow']); // Borrow a book
            Route::post('books/{id}/return', [BookController::class, 'return']); // Return a book
        });
        // Admin & Member Routes: Can View, and update profile (Members own account, Admin all account)
        Route::middleware(['role:Member,Admin', 'throttle:60,1'])->group(function () {
            Route::get('users/{id}', [UserController::class, 'show']); // View own profile
            Route::put('users/{id}', [UserController::class, 'update']); // Update own profile
        });
    // Admin Routes: Full access
    Route::middleware(['role:Admin', 'throttle:50,1'])->group(function () {
        Route::apiResource('users', UserController::class); // Manage users
    });

    // Admin and Librarian Routes: Can manage books and authors, view borrow records
    Route::middleware(['role:Admin,Librarian', 'throttle:100,1'])->group(function () {
        Route::apiResource('books', BookController::class);
        Route::apiResource('authors', AuthorController::class);
        Route::get('borrow-records', [BorrowRecordController::class, 'index']); // View borrow records
        Route::get('borrow-records/{id}', [BorrowRecordController::class, 'show']);
    });


});
