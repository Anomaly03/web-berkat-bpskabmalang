<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Konfigurasi relasi table
class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'nama_kategori',
        'level',
    ];
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

}
