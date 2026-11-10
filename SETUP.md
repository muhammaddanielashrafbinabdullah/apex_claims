# Apex Claims – Setup (Laravel 13 version)

These files are an overlay for a fresh Laravel project. Copy them over the project
(choose "Replace" when asked), then run the commands below.

## 1. Copy the files
Copy everything inside this `apex-claims` folder into `C:\laragon\www\APEX_Claims`
and choose **Replace the files in the destination**.

## 2. .env

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=APEX_Claims
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=Asia/Kuala_Lumpur
```

Remove the `#` in front of the DB lines. Add the `APP_TIMEZONE` line (or change it if it exists).

## 3. Commands (one at a time)

```
php artisan optimize:clear
php artisan migrate:fresh --seed
npm run dev
```

`migrate:fresh` wipes the database and recreates it (fine now, no real data yet).
Keep `npm run dev` running. In a second terminal: `php artisan serve`, then open http://127.0.0.1:8000/login

## 4. Allow many / bigger uploads (php.ini)

Open the php.ini that PHP really uses (run `php --ini` to see its path), then set:

```
upload_max_filesize = 10M
post_max_size = 500M
max_file_uploads = 100
```

Save, then stop and start `php artisan serve` (and Laragon, if you use apex_claims.test).

## Logins (change in any real deployment)

| Role  | Email            | Password |
|-------|------------------|----------|
| Admin | admin@apex.test  | password |
| Staff | ayuni@apex.test  | password |

## Notes
- Rate matrix and row layout: ONE place, `config/claim_rates.php`. The Alpine calculator,
  `App\Services\ClaimCalculator` and the admin pop-up all read it.
- Totals sent by the browser are ignored; the server recomputes everything.
- Month/year in headings and saved with each claim use Malaysia time (`claim_rates.timezone`).
- Attachments are stored privately and served through an authorised route.
