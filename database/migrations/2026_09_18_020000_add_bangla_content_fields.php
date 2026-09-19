<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
        'services' => ['name', 'short_desc', 'body'],
        'reviews' => ['patient_name', 'body', 'treatment'],
        'blog_categories' => ['name'],
        'blog_posts' => ['title', 'body', 'meta_title', 'meta_description'],
        'faqs' => ['question', 'answer'],
        'contact_channels' => ['label'],
        'office_locations' => ['name', 'district', 'address'],
        'hospitals' => ['name'],
    ];

    public function up(): void
    {
        foreach ($this->fields as $name => $fields) {
            Schema::table($name, function (Blueprint $table) use ($fields): void {
                foreach ($fields as $field) {
                    $table->longText($field.'_bn')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->fields as $name => $fields) {
            Schema::table($name, function (Blueprint $table) use ($fields): void {
                $table->dropColumn(array_map(fn ($field) => $field.'_bn', $fields));
            });
        }
    }
};
