<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categories;
class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_menu',
        'category_id',
        'name',
        'price',
        'diskon_persen',
        'diskon_rupiah',
        'description',
        'image',
        'statusActive',
    ];

    public $timestamps=false;
}
