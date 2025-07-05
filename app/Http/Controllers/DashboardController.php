<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\detail_penjualan;
use App\Models\total_penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Statistik berdasarkan tanggal
    public function statisticsByDateRange(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        // Jika from dan to sama, set to ke akhir hari
        if ($from == $to) {
            $from = $from . ' 00:00:00';
            $to = $to . ' 23:59:59';
        }

        // Total penjualan berdasarkan tanggal
        $total_penjualan = total_penjualan::whereBetween('tanggal', [$from, $to])
            ->sum('total');

        // Jumlah order berdasarkan tanggal
        $jumlah_order = total_penjualan::whereBetween('tanggal', [$from, $to])
            ->count();

        // Jumlah client (total user)
        $jumlah_client = DB::table('users')->count();

        return response()->json([
            'total_penjualan' => $total_penjualan,
            'jumlah_order' => $jumlah_order,
            'jumlah_client' => $jumlah_client,
        ]);
    }

    // Menu terlaris berdasarkan tanggal
    public function menuTerlarisByDateRange(Request $request)
    {
        // Ambil menu terlaris dari semua data (tanpa filter tanggal)
        $menu = detail_penjualan::select('name', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('name')
            ->orderByDesc('total_terjual')
            ->first();

        return response()->json($menu);
    }
}
