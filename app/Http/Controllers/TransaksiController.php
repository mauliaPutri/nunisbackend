<?php

namespace App\Http\Controllers;

use App\Models\detail_penjualan;
use App\Models\total_penjualan;
use Illuminate\Http\Request;
use App\Models\transaksi;
use Carbon\Carbon;

class TransaksiController extends Controller
{ 
    function generatefaktur() {
    $faktur = 'INV'.mt_rand(1000000000, 2000000000);
    while (total_penjualan::where('faktur', $faktur)->exists()) {
        $faktur ='INV'.mt_rand(1000000000, 2000000000);
    }

    return $faktur;
}
public function store(Request $request)
{
    $request->validate([
        'id_user' => 'required|integer',
        'no_telepon' => 'required|string|max:20',
        'alamat' => 'required|string|max:100',
        'sub_total' => 'required|numeric',
        'total' => 'required|numeric',
        'items' => 'required|array',
        'notes' => 'nullable|string|max:1000'
    ]);

    // Generate random faktur
    $faktur = $this -> generatefaktur();

    // Create total_penjualan record
    $transaksi = new total_penjualan();
    $transaksi->faktur = $faktur;
    $transaksi->id_user = $request->id_user;
    $transaksi->no_telepon = $request->no_telepon;
    $transaksi->alamat = $request->alamat;
    $transaksi->sub_total = $request->sub_total;
    $transaksi->diskon_persen = $request->diskon_persen;
    $transaksi->diskon_rupiah = $request->diskon_rupiah;
    $transaksi->total = $request->total;
    $transaksi->notes = $request->notes;
    $transaksi->status = 0; // Set default status to 0 for new transactions
    $transaksi->save();

    // Create detail_penjualan records
    foreach ($request->items as $item) {
        $detail = new detail_penjualan();
        $detail->faktur = $faktur;
        $detail ->name = $item['name'];
        $detail->kode_menu = $item['kode_menu'];
        $detail->jumlah = $item['count'];
        $detail->subtotal = $item['sub_total_item'];
        $detail->total = $item['total_item'];
        $detail->diskon_rupiah = $item['diskon_rupiah_item'];
        $detail->diskon_persen = $item['diskon_persen_item'];
        $detail->save();
    }
    return response()->json(['message' => 'Transaksi berhasil disimpan', 'faktur' => $faktur], 201);
}

public function updateStatusByFaktur(Request $request)
{
    $request->validate([
        'faktur' => 'required|string',
        'status' => 'required|integer'
    ]);

    $transaksi = total_penjualan::where('faktur', $request->faktur)->first();
    $transaksi->status = $request->status;
    $transaksi->save();

    return response()->json(['message' => 'Status transaksi berhasil diubah'], 200);
}

public function index()
{
    $transaksi = total_penjualan::with(['user', 'detailPenjualan'])
                    ->get();
    
    return response()->json($transaksi);
}
public function destroy($id)
{
    $transaksi = total_penjualan::findOrFail($id);
    $transaksi->delete();

    return response()->json(['message' => 'Transaksi berhasil dihapus'], 200);
}

public function statistics()
{

    $totalPenjualan = total_penjualan::sum('total');
    $jumlahOrder = total_penjualan::count();
    $jumlahClient = total_penjualan::distinct('id_user')->count('id_user');

    return response()->json([
        'total_penjualan' => $totalPenjualan,
        'jumlah_order' => $jumlahOrder,
        'jumlah_client' => $jumlahClient
    ]);
}
public function getTransactionsByDateRange(Request $request)
{
    $request->validate([
        'from' => 'required|date',
        'to' => 'required|date',
    ]);

    $transactions = total_penjualan::whereBetween('tanggal', [$request->from, $request->to])
        ->orderBy('tanggal', 'asc')
        ->get();

    return response()->json($transactions);
}
public function getTransactionByUser($id){
    $transaction = total_penjualan::where('id_user',$id)->get();
    return response()->json($transaction);
}
public function getTransactionByUserWithDetails($id, Request $request)
{
    $query = total_penjualan::where('id_user', $id);

    if ($request->has('start_date')) {
        $start_date = Carbon::parse($request->start_date)
            ->setTimezone('Asia/Jakarta')
            ->startOfDay();
        $query->where('tanggal', '>=', $start_date);
    }

    if ($request->has('end_date')) {
        $end_date = Carbon::parse($request->end_date)
            ->setTimezone('Asia/Jakarta')
            ->endOfDay();
        $query->where('tanggal', '<=', $end_date);
    }

    $transactions = $query->get();

    $transactionsWithDetails = $transactions->map(function ($transaction) {
        $details = detail_penjualan::where('faktur', $transaction->faktur)
            ->join('menu_items', 'detail_penjualan.kode_menu', '=', 'menu_items.kode_menu')
            ->select('detail_penjualan.*', 'menu_items.name as menu_name', 'menu_items.image')
            ->get();

        // Format tanggal ke timezone Asia/Jakarta
        $transaction->tanggal = Carbon::parse($transaction->tanggal)
            ->setTimezone('Asia/Jakarta')
            ->format('Y-m-d H:i:s');

        $transaction->details = $details;
        $transaction->main_item = $details->first();
        $transaction->other_items_count = $details->count() - 1;

        return $transaction;
    });

    return response()->json($transactionsWithDetails);
}
public function getTransaksiByFaktur($faktur)
{
    $transaksi = total_penjualan::with('detailPenjualan')
                    ->where('faktur', $faktur)
                    ->first();

    if (!$transaksi) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    return response()->json($transaksi);
}
}

