<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_images', function (Blueprint $table): void {
            $table->string('heading_bn', 160)->nullable();
            $table->string('heading_en', 160)->nullable();
            $table->text('body_bn')->nullable();
            $table->text('body_en')->nullable();
            $table->string('image_fit')->default('cover');
        });
    }

    public function down(): void
    {
        Schema::table('hero_images', function (Blueprint $table): void {
            $table->dropColumn(['heading_bn', 'heading_en', 'body_bn', 'body_en', 'image_fit']);
        });
    }
};
