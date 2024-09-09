<?php

namespace App\Http\Controllers;

use App\Models\BorrowRecord;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BorrowRecordController extends Controller
{
    /**
     * Display a listing of the borrow records (Admin/Librarian only).
     */
    public function index(Request $request)
    {
        // Optional: Add search functionality by book title or user name
        $query = BorrowRecord::with(['book', 'user']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('book', function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%$searchTerm%");
            })->orWhereHas('user', function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%$searchTerm%");
            });
        }

        // Paginate the results
        $borrowRecords = $query->paginate(10);

        return response()->json($borrowRecords, 200);
    }

    /**
     * Display the specified borrow record (Admin/Librarian only).
     */
    public function show($id)
    {
        $borrowRecord = BorrowRecord::with(['book', 'user'])->find($id);

        if (!$borrowRecord) {
            return response()->json(['message' => 'Borrow record not found'], 404);
        }

        return response()->json($borrowRecord, 200);
    }

    /**
     * Borrow a book (Member only).
     */
    public function borrow($bookId)
    {
        $user = Auth::user();
        $book = Book::find($bookId);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Check if the book is available
        if ($book->status !== 'Available') {
            return response()->json(['message' => 'Book is currently unavailable'], 400);
        }

        // Check if the user has already borrowed this book
        $existingRecord = BorrowRecord::where('book_id', $bookId)
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->first();

        if ($existingRecord) {
            return response()->json(['message' => 'You have already borrowed this book'], 400);
        }

        // Create a borrow record
        $borrowRecord = BorrowRecord::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'borrowed_at' => Carbon::now(),
            'due_at' => Carbon::now()->addDays(14), // Set a due date of 14 days
        ]);

        // Update book status to "Borrowed"
        $book->update(['status' => 'Borrowed']);

        return response()->json(['message' => 'Book borrowed successfully', 'borrowRecord' => $borrowRecord], 201);
    }

    /**
     * Return a borrowed book (Member only).
     */
    public function return($bookId)
    {
        $user = Auth::user();
        $book = Book::find($bookId);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Find the active borrow record
        $borrowRecord = BorrowRecord::where('book_id', $bookId)
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->first();

        if (!$borrowRecord) {
            return response()->json(['message' => 'No active borrow record found for this book'], 404);
        }

        // Update the borrow record to mark the return
        $borrowRecord->update([
            'returned_at' => Carbon::now(),
        ]);

        // Update book status to "Available"
        $book->update(['status' => 'Available']);

        return response()->json(['message' => 'Book returned successfully'], 200);
    }
}
