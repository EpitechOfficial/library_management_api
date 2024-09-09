<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors (accessible to all roles).
     */
    public function index(Request $request)
    {
        // Optional: Add search functionality by name or bio
        $query = Author::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'LIKE', "%$searchTerm%")
                  ->orWhere('bio', 'LIKE', "%$searchTerm%");
        }

        // Paginate the results
        $authors = $query->paginate(10);

        return response()->json($authors, 200);
    }

    /**
     * Store a newly created author (Admin/Librarian only).
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'birthdate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new author
        $author = Author::create($request->all());

        return response()->json(['message' => 'Author created successfully', 'author' => $author], 201);
    }

    /**
     * Display the specified author (accessible to all roles).
     */
    public function show($id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        return response()->json($author, 200);
    }

    /**
     * Update the specified author (Admin/Librarian only).
     */
    public function update(Request $request, $id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'bio' => 'nullable|string',
            'birthdate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update the author's details
        $author->update($request->all());

        return response()->json(['message' => 'Author updated successfully', 'author' => $author], 200);
    }

    /**
     * Remove the specified author from storage (Admin only).
     */
    public function destroy($id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        // Optional: Check if the author has any associated books before deletion
        if ($author->books()->exists()) {
            return response()->json(['message' => 'Cannot delete author with associated books'], 400);
        }

        $author->delete();

        return response()->json(['message' => 'Author deleted successfully'], 200);
    }
}
