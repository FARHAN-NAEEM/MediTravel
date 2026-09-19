<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->string('name_bn')->nullable();
        });

        Schema::table('treatments', function (Blueprint $table): void {
            $table->string('name_bn')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('image_path')->nullable();
            $table->string('illustration', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table): void {
            $table->dropIndex(['sort_order']);
            $table->dropColumn(['name_bn', 'description_bn', 'image_path', 'illustration', 'sort_order']);
        });
        Schema::table('departments', fn (Blueprint $table) => $table->dropColumn('name_bn'));
    }
};
