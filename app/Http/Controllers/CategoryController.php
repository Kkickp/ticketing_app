<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = Kategori::all();

        return view('pages.admin.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama',
        ]);

        Kategori::create([
            'nama' => $request->nama,
        ]);

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama,' . $id,
        ]);

        $category = Kategori::findOrFail($id);
        $category->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = Kategori::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}
