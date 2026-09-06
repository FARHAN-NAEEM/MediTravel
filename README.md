# Asian Health Connect

Bangla-first medical travel platform for doctor appointments, hospitals, treatment cost ranges, visa support, WhatsApp inquiries, and admin CRM tracking.

## Tech Stack

- Laravel 11
- MySQL / MariaDB
- Blade, Tailwind CSS, Alpine.js
- Filament admin panel
- Spatie permissions
- DomPDF-ready PDF support

## Local Setup

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Admin panel:

```text
http://127.0.0.1:8000/admin
```

Before running the production seeder, configure the owner account in `.env`:

```text
OWNER_ADMIN_NAME="Primary Owner"
OWNER_ADMIN_EMAIL=owner@example.com
OWNER_ADMIN_PASSWORD=<a strong temporary password>
```

Never commit the production `.env` file or a real owner password. Change the temporary password after the first login.

## Admin Features

- Countries and cities
- Hospitals and hero images
- Doctors and departments
- Treatments and treatment cost ranges
- Visa document checklist
- Inquiries with editable reference numbers and status tracking

## Notes

The `.env`, `vendor`, `node_modules`, runtime cache, sessions, and logs are intentionally ignored by Git.
