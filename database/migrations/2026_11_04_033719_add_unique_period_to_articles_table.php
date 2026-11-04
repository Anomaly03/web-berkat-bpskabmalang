<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // URL berita bisa lebih dari 255 karakter
            $table->text('link')->change();

            // Satu artikel per kategori per bulan (satu sel per bulan di Excel)
            $table->unique(['category_id', 'bulan_target', 'tahun_target'], 'articles_kategori_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique('articles_kategori_periode_unique');
            $table->string('link')->change();
        });
    }
};