<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    function generateKodeBarang() {
        $prefix = 'BRG';  // You can change this prefix as needed
        $lastMenuItem = MenuItem::orderBy('kode_menu', 'desc')->first();
    
        if (!$lastMenuItem) {
            // If no menu items exist, start with MENU0000000001
            $nextNumber = 1;
        } else {
            $lastNumber = substr($lastMenuItem->kode_menu, 3); // Remove the prefix
            $nextNumber = intval($lastNumber) + 1;
        }
    
        // Keep incrementing until we find an unused kode_menu
        do {
            $kodeMenu = $prefix . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (MenuItem::where('kode_menu', $kodeMenu)->exists());
    
        return $kodeMenu;
    }
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'diskon_persen' => 'nullable|numeric',
            'diskon_rupiah' => 'nullable|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'statusActive' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $kodeBarang = $this->generateKodeBarang();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $menuItem = MenuItem::create([
            'id'=>0,
            'kode_menu' => $kodeBarang,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'diskon_persen' => $request->diskon_persen,
            'diskon_rupiah' => $request->diskon_rupiah,
            'image' => $request->image,
            'description' => $request->description,
            'statusActive' => 1,
        ]);

        return response()->json(['menuItem' => $menuItem], 201);
    }

    public function index()
    {
        $menuItems = MenuItem::where('statusActive', 1)->get();
        return response()->json($menuItems);
    }
    public function indexAll()
    {
        $menuItems = MenuItem::all();
        return response()->json($menuItems);
    }

    public function destroy($kode_menu)
    {
        try {
            // Cari menu berdasarkan kode_menu
            $menuItem = MenuItem::where('kode_menu', $kode_menu)->first();

            // Periksa apakah menu ada
            if (!$menuItem) {
                return response()->json(["Error" => "Menu tidak ditemukan"], 404);
            }

            // Hapus menu
            $menuItem->delete();

            // Kembalikan response sukses
            return response()->json(["Message" => "Menu berhasil dihapus"], 200);
        } catch (\Exception $e) {
            // Cek apakah error disebabkan oleh foreign key constraint
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return response()->json([
                    "Error" => "Tidak dapat menghapus menu",
                    "reason" => "Menu memiliki riwayat transaksi"
                ], 422);
            }
            
            return response()->json([
                "Error" => "Terjadi kesalahan saat menghapus menu",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'kode_menu'=>'required|string',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'diskon_persen' => 'nullable|numeric',
            'diskon_rupiah' => 'nullable|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'statusActive' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Find the menu item
        $menuItem = MenuItem::where('kode_menu', $request->kode_menu)->first();
        if (!$menuItem) {
            return response()->json(['message' => $request->kode_menu], 404);
        }

        // Update the menu item
        if ($request->has('category_id')) {
            $menuItem->category_id = $request->category_id;
        }
        if ($request->has('name')) {
            $menuItem->name = $request->name;
        }
        if ($request->has('price')) {
            $menuItem->price = $request->price;
        }
        if ($request->has('image')) {
            $menuItem->image = $request->image;
        }
        if ($request->has('description')) {
            $menuItem->description = $request->description;
        }
        if ($request->has('diskon_persen')) {
            $menuItem->diskon_persen = $request->diskon_persen;
        }
        if ($request->has('diskon_rupiah')) {
            $menuItem->diskon_rupiah = $request->diskon_rupiah;
        }
        if ($request->has('statusActive')) {
            $menuItem->statusActive = $request->statusActive;
        }
        $menuItem->save();
        return response()->json(['message' => 'Menu item berhasil diperbarui', 'menuItem' => $menuItem], 200);
    }
}

