# Doctor roster import

Use a roster supplied or licensed by Apollo Hospitals, Fortis Healthcare, or Max Healthcare. The importer does not scrape their websites or call an undocumented API.

Save a UTF-8 CSV with these columns:

```csv
country,city,hospital,department,doctor_name,source_url,designation,qualifications,experience_years
```

The first six columns are required. The remaining three are optional. Each `source_url` must be an HTTPS link on an official `apollohospitals.com`, `fortishealthcare.com`, or `maxhealthcare.in` domain. Use the hospital's exact branch name and one website category in `department`, such as `Cardiac Care` or `Cancer Care`. Do not copy biographies or photos without explicit reuse rights.

Order rows by the preferred hospital. The first occurrence of a normalized doctor name wins; later occurrences, including those already in the database, are skipped and reported by line number. This is a conservative name-based match, not proof that two people with the same name are the same doctor. Review the collision report before committing.

Preview without changing the database:

```bash
php artisan doctors:import /path/to/authorized-doctors.csv
```

After checking the row counts and duplicate report, import:

```bash
php artisan doctors:import /path/to/authorized-doctors.csv --commit
```

Invalid rows or non-official source links reject the entire CSV before any writes. The import runs in one database transaction, creates missing countries, cities, hospitals and departments, never reassigns existing doctors, and is safe to rerun. New doctors are not featured automatically. Keep the licensed roster and permission record outside the public web directory.

An official-domain link validates the source address format, not the truth of a profile. Check names, specialities, and hospital branches against the partner roster before committing, especially for same-name collisions. The initial database seeder contains two example doctors; review those records before treating the directory as a verified roster.

## Apollo roster staged on 2026-09-17

The local collection from nine Apollo specialty pages was converted with `php scripts/build-apollo-roster.php`. The versioned CSV is at `database/rosters/apollo-doctors-2026-09-17.csv`. The raw collection and the review CSV in `storage/app/imports/` stay local and are excluded from Git. The source pages were cardiologist, orthopedician, neurologist, gastroenterologist, oncologist, nephrologist, urologist, gynecologist, and ent-specialist. The vascular-surgeon page is **not** included; its collection was interrupted by the browser permission/usage limit.

The generated CSV has 1,867 unique doctor names from those nine lists. It keeps the first listed hospital and first encountered specialty for each normalized name. Thirty repeated profiles and 11 same-name/different-profile collisions were skipped; one listing without a hospital was also omitted. Check `apollo-doctors-review-2026-09-17.csv` before using the roster as a definitive directory. A same-name collision can represent two different people.

The 1,867 records were imported into the local `meditravel` database, after applying the `source_url` migration. The cPanel deployment script now runs the same idempotent import after migrations, so a successful deployment populates production too. The `/sitemap.xml` endpoint lists the imported doctor profiles and `/robots.txt` points to it. Search engines may take time to crawl and index them; a successful deployment does not guarantee immediate search results.
