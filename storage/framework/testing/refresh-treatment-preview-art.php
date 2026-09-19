<?php
require __DIR__.'/../../../vendor/autoload.php';
$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['hip-replacement' => 'hip', 'bone-cancer' => 'cancer'] as $slug => $image) {
    App\Models\Treatment::where('slug', $slug)->where('illustration', 'joints')->whereNull('image_path')->update(['illustration' => $image]);
}
echo App\Models\Treatment::count()." treatments\n";
