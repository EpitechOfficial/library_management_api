<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookController extends Controller
{
    /**
     * Display a listing of books (accessible to all roles).
     */
    public function index(Request $request)
    {
        // Optional: Add search functionality by title, author, or ISBN
        $query = Book::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'LIKE', "%$searchTerm%")
                ->orWhere('isbn', 'LIKE', "%$searchTerm%");
        }

        // Paginate the results
        $books = $query->paginate(10);

        return response()->json($books, 200);
    }

    /**
     * Store a newly created book (Admin/Librarian only).
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'published_date' => 'nullable|date',
            'author_id' => 'required|exists:authors,id',
            'status' => 'required|in:Available,Borrowed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new book
        $book = Book::create($request->all());

        return response()->json(['message' => 'Book created successfully', 'book' => $book], 201);
    }

    /**
     * Display the specified book (accessible to all roles).
     */
    public function show($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json($book, 200);
    }

    /**
     * Update the specified book (Admin/Librarian only).
     */
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'isbn' => 'sometimes|required|string|unique:books,isbn,' . $book->id,
            'published_date' => 'nullable|date',
            'author_id' => 'sometimes|required|exists:authors,id',
            'status' => 'required|in:Available,Borrowed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update book details
        $book->update($request->all());

        return response()->json(['message' => 'Book updated successfully', 'book' => $book], 200);
    }

    /**
     * Remove the specified book from storage (Admin only).
     */
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Only allow deletion if the book is available
        if ($book->status == 'Borrowed') {
            return response()->json(['message' => 'Cannot delete a borrowed book'], 400);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }

    /**
     * Borrow a book (Member only).
     */
    public function borrow($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Check if the book is available
        if ($book->status != 'Available') {
            return response()->json(['message' => 'Book is currently unavailable'], 400);
        }

        // Create a borrow record
        $borrowRecord = BorrowRecord::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'borrowed_at' => Carbon::now(),
            'due_at' => Carbon::now()->addDays(14), // Example: due in 14 days
        ]);

        // Update book status
        $book->update(['status' => 'Borrowed']);

        return response()->json(['message' => 'Book borrowed successfully', 'borrow_record' => $borrowRecord], 200);
    }

    /**
     * Return a book (Member only).
     */
    public function return($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Check if the book is currently borrowed by the user
        $borrowRecord = BorrowRecord::where('book_id', $book->id)
            ->where('user_id', Auth::id())
            ->whereNull('returned_at')
            ->first();

        if (!$borrowRecord) {
            return response()->json(['message' => 'No active borrow record found for this book'], 400);
        }

        // Mark the book as returned
        $borrowRecord->update(['returned_at' => Carbon::now()]);

        // Update book status
        $book->update(['status' => 'Available']);

        return response()->json(['message' => 'Book returned successfully'], 200);
    }
}
