<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Treatment;
use Illuminate\Http\Response;
use XMLWriter;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $xml->startElement('url');
        $xml->writeElement('loc', route('doctors.index'));
        $xml->endElement();

        Doctor::query()
            ->whereNotNull('source_url')
            ->orderBy('id')
            ->chunkById(500, function ($doctors) use ($xml): void {
                foreach ($doctors as $doctor) {
                    $xml->startElement('url');
                    $xml->writeElement('loc', route('doctors.show', $doctor));

                    if ($doctor->updated_at) {
                        $xml->writeElement('lastmod', $doctor->updated_at->toDateString());
                    }

                    $xml->endElement();
                }
            });

        $xml->startElement('url');
        $xml->writeElement('loc', route('treatments.index'));
        $xml->endElement();

        Treatment::query()->orderBy('id')->chunkById(500, function ($treatments) use ($xml): void {
            foreach ($treatments as $treatment) {
                $xml->startElement('url');
                $xml->writeElement('loc', route('treatments.show', $treatment));
                if ($treatment->updated_at) {
                    $xml->writeElement('lastmod', $treatment->updated_at->toDateString());
                }
                $xml->endElement();
            }
        });

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nAllow: /\nSitemap: ".route('sitemap')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
