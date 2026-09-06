<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table): void {
            $table->string('card_highlight_bn', 180)->nullable()->after('accreditation');
            $table->string('card_highlight_en', 180)->nullable()->after('card_highlight_bn');
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table): void {
            $table->dropColumn(['card_highlight_bn', 'card_highlight_en']);
        });
    }
};
