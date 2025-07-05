<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|json',
            'total' => 'required|numeric'
        ]);

        $item = new Cart();
        $item->value = $request->value;
        $item->total = $request->total;
        $item->save();

        return response()->json($item, 201);
    }

    public function index()
    {
        $item = Cart::all();
        return response()->json($item);
    }
    public function destroy($id)
    {
        $item = Cart::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item berhasil dihapus'], 200);
    }

}
