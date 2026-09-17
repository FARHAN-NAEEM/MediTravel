<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_images', function (Blueprint $table): void {
            $table->string('image_fit')->default('contain')->change();
        });

        DB::table('hero_images')->where('image_fit', 'cover')->update(['image_fit' => 'contain']);
    }

    public function down(): void
    {
        Schema::table('hero_images', function (Blueprint $table): void {
            $table->string('image_fit')->default('cover')->change();
        });
    }
};
