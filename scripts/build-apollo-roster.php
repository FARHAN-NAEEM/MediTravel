<?php

declare(strict_types=1);

use Illuminate\Support\Str;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$input = $root.'/storage/app/apollo-roster-raw.jsonl';
$directory = $root.'/database/rosters';
$output = $directory.'/apollo-doctors-2026-09-17.csv';
$reviewDirectory = $root.'/storage/app/imports';
$review = $reviewDirectory.'/apollo-doctors-review-2026-09-17.csv';

$departments = [
    'cardiologist' => 'Cardiac Care',
    'orthopedician' => 'Orthopedic',
    'neurologist' => 'Neurology',
    'gastroenterologist' => 'Gastroenterology',
    'oncologist' => 'Cancer Care',
    'nephrologist' => 'Nephrology',
    'urologist' => 'Urology',
    'gynecologist' => 'Obstetrics & Gynecology',
    'ent-specialist' => 'ENT',
    'vascular-surgeon' => 'Vascular Surgery',
];

$cityNames = [
    'bhubaneshwar' => 'Bhubaneswar',
    'chennai-proton-centre' => 'Chennai',
    'karim-nagar' => 'Karim Nagar',
    'visakhapatnam' => 'Visakhapatnam',
];

$hospitalNames = [
    "Apollo Children's Hospitals, Chennai" => "Apollo Children's Hospital, Chennai",
    'Apollo Health City, Jubilee Hills' => 'Apollo Health City, Jubilee Hills, Hyderabad',
    'Apollo Hospitals, Bannerghatta Road' => 'Apollo Hospitals, Bannerghatta Road, Bangalore',
    'Apollo Hospitals, Seshadripuram' => 'Apollo Hospitals, Seshadripuram, Bangalore',
    'Apollo Hospitals Ramnagar Vizag' => 'Apollo Hospitals Ramnagar, Vizag',
    'Apollo Speciality Hospitals, Vanagaram' => 'Apollo Speciality Hospitals, Vanagaram, Chennai',
    'Best Women & Children Hospital in Madurai | Apollo Women & Child Care Hospital' => 'Apollo Women & Child Care Hospital, Madurai',
];

if (! is_file($input)) {
    fwrite(STDERR, "Missing raw roster: {$input}\n");
    exit(1);
}

if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
    fwrite(STDERR, "Cannot create output directory: {$directory}\n");
    exit(1);
}

if (! is_dir($reviewDirectory) && ! mkdir($reviewDirectory, 0775, true) && ! is_dir($reviewDirectory)) {
    fwrite(STDERR, "Cannot create review directory: {$reviewDirectory}\n");
    exit(1);
}

$source = fopen($input, 'rb');
$csv = fopen($output, 'wb');
$reviewCsv = fopen($review, 'wb');

if (! $source || ! $csv || ! $reviewCsv) {
    fwrite(STDERR, "Cannot open roster files.\n");
    exit(1);
}

fputcsv($csv, ['country', 'city', 'hospital', 'department', 'doctor_name', 'source_url']);
fputcsv($reviewCsv, ['reason', 'doctor_name', 'source_url', 'first_source_url']);

$seen = [];
$counts = ['raw' => 0, 'ready' => 0, 'repeat' => 0, 'collision' => 0, 'invalid' => 0];
$page = 0;

while (($line = fgets($source)) !== false) {
    $page++;
    $rows = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

    if (! is_array($rows)) {
        throw new RuntimeException("Invalid batch on line {$page}.");
    }

    foreach ($rows as $item) {
        $counts['raw']++;
        $name = trim((string) ($item['name'] ?? ''));
        $url = (string) ($item['profileUrl'] ?? '');
        $sourcePage = (string) ($item['sourcePage'] ?? '');
        $hospital = trim((string) ($item['hospitals'][0] ?? ''));
        $specialty = basename((string) parse_url($sourcePage, PHP_URL_PATH));
        $path = (string) parse_url($url, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));
        $citySlug = Str::lower($parts[2] ?? '');
        $key = Str::lower(Str::squish($name));
        $key = preg_replace('/^(?:(?:dr|doctor)\.?\s+)+/iu', '', $key);
        $key = Str::squish((string) preg_replace('/[^\pL\pN]+/u', ' ', $key));

        if ($name === '' || $key === '' || $hospital === '' || ! isset($departments[$specialty])
            || parse_url($url, PHP_URL_HOST) !== 'www.apollohospitals.com'
            || ($parts[0] ?? '') !== 'doctors' || count($parts) !== 4
            || ! preg_match('/^[a-z-]+$/', $citySlug)
            || parse_url($sourcePage, PHP_URL_HOST) !== 'www.apollohospitals.com') {
            $counts['invalid']++;
            fputcsv($reviewCsv, ['missing or invalid source, city, hospital, or specialty', $name, $url, '']);

            continue;
        }

        if (isset($seen[$key])) {
            $reason = $seen[$key] === $url ? 'repeat profile' : 'same normalized name; verify identity';
            $counts[$seen[$key] === $url ? 'repeat' : 'collision']++;
            fputcsv($reviewCsv, [$reason, $name, $url, $seen[$key]]);

            continue;
        }

        $seen[$key] = $url;
        $city = $cityNames[$citySlug] ?? Str::title(str_replace('-', ' ', $citySlug));
        $hospital = $hospitalNames[$hospital] ?? $hospital;
        fputcsv($csv, ['India', $city, $hospital, $departments[$specialty], $name, $url]);
        $counts['ready']++;
    }
}

fclose($source);
fclose($csv);
fclose($reviewCsv);

echo json_encode($counts, JSON_PRETTY_PRINT)."\n";
echo "CSV: {$output}\nReview: {$review}\n";
