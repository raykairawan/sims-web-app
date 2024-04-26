<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('category.index', compact('categories'));
    }

    public function create()
    {
        return view('category.create');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|unique:categories',
            ]);

            // Use prepared statement query
            DB::insert('INSERT INTO categories (name) VALUES (?)', [$validatedData['name']]);

            return redirect()->route('categories.index')->with('success', 'Category added successfully.');
        } catch (QueryException $e) {
            return redirect()->route('categories.index')->with('error', 'Failed to add category.');
        }
    }

    public function edit($id)
    {
        try {
            $category = DB::selectOne('SELECT * FROM categories WHERE id = ?', [$id]);
            return view('category.edit', compact('category'));
        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', 'Failed to fetch category.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|unique:categories,name,' . $id,
            ]);

            DB::update('UPDATE categories SET name = ? WHERE id = ?', [$validatedData['name'], $id]);

            return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
        } catch (QueryException $e) {
            return redirect()->route('categories.index')->with('error', 'Failed to update category.');
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::delete('DELETE FROM categories WHERE id = ?', [$id]);
            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', 'Failed to delete category.');
        }
    }
}
