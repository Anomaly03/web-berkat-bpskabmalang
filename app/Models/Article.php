<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Konfigurasi relasi table
class Article extends Model
{
    protected $fillable = [
        'category_id',
        'judul',
        'link',
        'sumber',
        'tanggal_rilis',
        'ringkasan',
        'bulan_target',
        'tahun_target',
    ];

    protected $casts = [ // untuk mengubah tipe data kolom menjadi tipe data yang sesuai
        'tanggal_rilis' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}