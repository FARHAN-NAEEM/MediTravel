# Treatment catalogue

The September 2026 catalogue contains 56 bilingual care topics in 16 specialties. It uses common topic names from the supplied treatment reference, with newly authored coordination-focused descriptions and 18 original AI-generated category illustrations. No competitor text, images, logos, prices or outcome claims are copied.

## Installation

The deployment script clears stale configuration, migrates the schema, then runs:

```sh
php artisan db:seed --class=TreatmentCatalogSeeder --force
```

The seeder reuses matching treatment names/slugs and department aliases. Existing URLs, prices, image uploads and authored content are preserved. It fills missing translations/illustrations and records a one-time installation marker in settings. Later deploys do not overwrite admin edits or restore deleted topics. Do not delete this marker to perform routine updates.

Only the new catalogue seeder should be run on an existing live database. The general DatabaseSeeder is a development fixture and is not part of this installation.

## Administration

Medical Content > Treatments supports individual creation/editing, English and Bangla names/descriptions, specialty selection, a built-in illustration or custom JPG/PNG/WebP upload, featured state and drag reordering. A custom upload takes precedence over the built-in illustration. Department Bangla labels can be edited under Departments. The existing treatment cost resource remains the source of any published estimates.

The public catalogue supports bilingual keyword search, specialty filtering and 24-item pagination. Detail pages preserve cost and inquiry links. New topics have no invented prices; they request an individual hospital estimate. Public treatment URLs are included in the sitemap; inclusion does not guarantee search-engine indexing.

## Artwork

Production images are in `public/images/treatments/` (640x640 WebP). They are symbolic category illustrations, not diagnostic diagrams, and can be shared by related topics. The built-in OpenAI image generation tool was used. The prompt set is recorded in `docs/treatment-art-prompts.json`.

`scripts/prepare-treatment-art.php` can optimize generated PNG originals using a JSON manifest with `key` and `source` fields. It leaves originals untouched. The generated source manifest and local test database are not deployed.

## Verification

`tests/Feature/TreatmentCatalogTest.php` covers installation, duplicate reuse, preservation of existing content/costs and subsequent edits/deletions, bilingual filtering, detail inquiry context, estimates, sitemap inclusion, and admin creation/editing with an image upload.
