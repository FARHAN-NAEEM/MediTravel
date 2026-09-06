<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('address');
            $table->string('area')->nullable();
            $table->text('description');
            $table->json('images')->nullable();
            $table->decimal('cost_min', 12, 2)->nullable();
            $table->decimal('cost_max', 12, 2)->nullable();
            $table->string('currency', 8)->default('USD');
            $table->string('pricing_unit', 20)->default('night');
            $table->string('hospital_distance_note')->nullable();
            $table->text('special_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['country_id', 'city_id', 'name']);
            $table->index(['country_id', 'city_id', 'is_active']);
        });

        Schema::create('hotel_hospital', function (Blueprint $table): void {
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->primary(['hotel_id', 'hospital_id']);
        });

        Schema::create('hotel_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('bed_count')->default(1);
            $table->string('room_size')->nullable();
            $table->string('bathroom')->nullable();
            $table->boolean('has_ac')->default(false);
            $table->boolean('has_wifi')->default(false);
            $table->json('facilities')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'name']);
        });

        Schema::create('hotel_booking_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('ref_number')->unique();
            $table->foreignId('hotel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hotel_room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->unsignedSmallInteger('room_count')->default(1);
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('guest_count')->default(1);
            $table->text('additional_note')->nullable();
            $table->string('status')->default('new');
            $table->string('source')->default('website');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_requests');
        Schema::dropIfExists('hotel_rooms');
        Schema::dropIfExists('hotel_hospital');
        Schema::dropIfExists('hotels');
    }
};
