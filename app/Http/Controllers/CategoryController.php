<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categories::all();
        return response()->json($categories);
    }

    public function getMenuItemsByCategory($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $menuItems = MenuItem::where('category_id', $id)->get();

        return response()->json(['menuItems' => $menuItems]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'icon' => 'required|nullable|string', // ukuran maksimum 4MB
            'description' => 'nullable|string',
        ]);


        $category = new Categories();
        $category->id = $request->id;
        $category->name = $request->name;
        $category->icon =  $request->icon;
        $category->description = $request->description;
        $category->save();

        return response()->json(['message' => 'Kategori berhasil dibuat'], 201);
    }

    public function destroy($id)
    {
        $category = Categories::find($id);
    
        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }
    
        // Check if the category is used in any menu
        $menuCount = \App\Models\MenuItem::where('category_id', $id)->count();
    
        if ($menuCount > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus kategori ini',
                'reason' => "Kategori ini memiliki $menuCount menu",
            ], 400);
        }
    
        $category->delete();
    
        return response()->json(['message' => 'Kategori berhasil dihapus'], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string',
        ]);

        $category = Categories::find($request->id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        if ($request->has('name')) {
            $category->name = $request->name;
        }

        if ($request->has('description')) {
            $category->description = $request->description;
        }

        if ($request->has('icon')) {
            $category->icon = $request->icon;
        }

        $category->save();

        return response()->json(['message' => 'Kategori berhasil diperbarui'], 200);
    }
}
