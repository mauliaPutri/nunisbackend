<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detail_penjualan extends Model
{
    protected $table = 'detail_penjualan';
    public $timestamps=false;
    protected $fillable =[
        'faktur','kode_menu','name','jumlah','total','sub_total','diskon_persen','diskon_persen',
    ];
    use HasFactory;
    public function menu()
    {
        return $this->belongsTo(MenuItem::class, 'kode_menu', 'kode_menu');
    }
}
