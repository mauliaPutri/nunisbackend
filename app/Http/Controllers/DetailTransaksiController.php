<?php

namespace App\Http\Controllers;

use App\Models\detail_penjualan;
use Illuminate\Http\Request;

class DetailTransaksiController extends Controller
{
    public function index()
    {
        $detail = detail_penjualan::all();
        return response()->json($detail);
    }
    public function showByFaktur($faktur)
    {
        $detail = detail_penjualan::where('faktur', $faktur)->get();
        return response()->json($detail);
    }
}
