# Hospital directory catalogue

The September 2026 catalogue contains 44 directory entries across 35 groups in India, Thailand and China. It is not a partnership register, accreditation register, doctor roster, or clinical recommendation.

## Sources and review

- `database/catalogs/hospitals-2026-09.json` records each official hospital source, review date, legacy URL aliases and pending entries.
- `database/catalogs/hospital-logo-sources.json` records asset provenance. Logos identify their respective institutions and remain their owners' marks. Most come directly from hospital/group sites; Fortis, Max, AIG, MIOT and Samitivej use published third-party listings or event/Commons sources.
- KKR ENT's official logo downloads were denied by its server. Its listing uses the shared hospital illustration until an administrator supplies the approved logo. No replacement logo was invented.
- Manvi Hospitals is in Vijayawada, not Chennai. Max Bengaluru is excluded at the owner's request.
- HCG Delhi NCR, HCG Hyderabad and the ambiguous Kolkata Fertility Centre are pending exact branch confirmation. They are not published by this catalogue.
- Sri Ramachandra Medical Centre and its parent institute are represented once. City-level Nova and Dr Agarwal entries require branch confirmation before an appointment.
- No hospital descriptions, patient numbers, outcome claims, doctor availability or accreditation claims were copied from competing websites.

## Installation

Run migrations, then `php artisan db:seed --class=HospitalDirectorySeeder --force`. The cPanel deployment script does this after the existing doctor import.

The one-time `hospital_directory_20260920_installed` setting protects subsequent admin edits and deletions. The installer matches known legacy slugs, preserves existing IDs/URLs/doctor links/content/uploads, reuses Bangalore/Bengaluru and Delhi/New Delhi city records, and fills missing group/source metadata. Do not delete the installation marker to synchronize changes; use the admin panel or a separately reviewed follow-up migration.

All downloaded logos ship as local assets. Production deployments never fetch external images. `scripts/download-hospital-logos.php` is a development-only preparation utility and reports failed downloads for manual review.

## Administration

- **Hospital Groups:** edit the display name, official website, order and uploaded group logo. Uploaded raster logos override the bundled mark.
- **Hospitals:** choose group, country, city and care category; edit Bangla/English copy; upload a branch logo and up to five photos; feature/order records.
- Hospital cards use a branch photo first, then a branch/group logo, then the shared illustration. The detail page preserves the hospital ID in the inquiry form and includes hospital/city context in WhatsApp links.
- Existing contact settings remain authoritative. Check the primary WhatsApp contact before a campaign; local demo settings may still contain a placeholder number.

Hospital directory/detail URLs are included in the sitemap. Search indexing and rankings are not guaranteed or immediate.

## Verification

`php artisan test --filter=HospitalDirectoryTest` checks catalogue counts, exclusions, aliases, preservation, filters, pagination, uploads, inquiry context and sitemap links. Run the full test suite and `npm run build` before release. Visual QA should include desktop and mobile directory, group filtering, detail, homepage cards, and logo loading/contrast.
