<?php

namespace Database\Seeders;

use App\Services\SiteContactService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanglaContentSeeder extends Seeder
{
    public function run(): void
    {
        $translations = require database_path('translations/bangla-content.php');

        // Only translate known source text into empty fields; retain all administrator edits.
        DB::transaction(function () use ($translations): void {
            foreach ($translations as $table => $fields) {
                foreach ($fields as $field => $pairs) {
                    foreach ($pairs as $english => $bangla) {
                        DB::table($table)->where($field, $english)
                            ->where(fn ($query) => $query->whereNull($field.'_bn')->orWhere($field.'_bn', ''))
                            ->update([$field.'_bn' => $bangla]);
                    }
                }
            }
        });

        app(SiteContactService::class)->forget();
    }
}
