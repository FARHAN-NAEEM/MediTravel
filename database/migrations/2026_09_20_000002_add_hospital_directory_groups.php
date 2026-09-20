<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('catalog_logo')->nullable();
            $table->string('website', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('hospitals', function (Blueprint $table): void {
            $table->foreignId('hospital_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('care_type')->default('multi-specialty')->index();
            $table->string('source_url', 500)->nullable();
            $table->date('directory_reviewed_at')->nullable();
            $table->text('description_bn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('hospital_group_id');
            $table->dropColumn(['care_type', 'source_url', 'directory_reviewed_at', 'description_bn']);
        });
        Schema::dropIfExists('hospital_groups');
    }
};
