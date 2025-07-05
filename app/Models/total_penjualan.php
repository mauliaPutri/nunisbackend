<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class total_penjualan extends Model
{
    protected $table = 'total_penjualan';
    protected $fillable = [
        'faktur', 'id_user', 'no_telepon', 'alamat', 'item', 'sub_total', 'total', 'diskon_persen', 'diskon_rupiah', 'notes', 
        'status'
    ];
    public $timestamps = false;
    use HasFactory;

    public function detailPenjualan()
    {
        return $this->hasMany(detail_penjualan::class, 'faktur', 'faktur');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}