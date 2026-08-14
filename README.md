# Web-Based Sports Management Information System

Native PHP, MySQL, Tailwind CSS, JavaScript, AJAX, and a Semaphore-ready SMS helper.

## Setup with XAMPP

1. Copy this folder to `xampp/htdocs/Sport_management`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `database/sports_management.sql`.
4. Confirm database settings in `app/config/database.php`:
   - host: `localhost`
   - user: `root`
   - password: empty by default
   - database: `sport_management`
5. Open `http://localhost/Sport_management/public/login.php`.

### EnvKit (webroot = `public/`) note

If your local server (e.g. EnvKit) points the site's DocumentRoot at the `public/`
folder with a catch-all rewrite to `index.php`, the AJAX endpoints under
`app/ajax/` are unreachable and every `data-ajax-form` will silently fail
(saves appear to do nothing). Fix it once on that machine by making `app/`
reachable from the web root:

```
mklink /J "public\app" "..\app"
```

On a normal host (DocumentRoot = project root) this is not needed; the project's
`.htaccess` and direct `app/` access work as designed.

## Sample Accounts

All sample accounts use password `admin123`.

- Admin: `admin@sports.test`
- Coach: `coach@sports.test`
- Athlete: `athlete@sports.test`

## Main Features

- Role-based login for admin, sports coordinator, coach, and athlete.
- User management with activate/deactivate AJAX action.
- Athlete biodata with photo upload and printable profile.
- Sports and team management with coach assignments.
- Requirement types, document uploads, approval/rejection, and missing requirement reports.
- Downloadable form templates.
- Training schedules with filters and optional SMS notification logging.
- AJAX attendance monitoring.
- Announcements with optional SMS.
- Printable reports for athletes, schedules, missing requirements, and SMS logs.

## Semaphore SMS

SMS integration is prepared in `app/helpers/sms_helper.php` and configured in `app/config/sms.php`.

By default, `enabled` is `false`, so SMS messages are logged without contacting Semaphore. To enable real sending:

1. Copy `app/config/sms.example.php` to `app/config/sms.php`.
2. Add your Semaphore API key and sender name in `app/config/sms.php`.
3. Set `enabled` to `true`.
4. Make sure PHP cURL is enabled in XAMPP.

## Upload Folders

The app stores uploads in:

- `public/uploads/athlete_documents`
- `public/uploads/profile_photos`
- `public/uploads/templates`

Allowed athlete document files: PDF, JPG, JPEG, PNG.

## Notes

This project intentionally uses native PHP and PDO prepared statements so it is beginner-friendly and easy to modify for a school capstone project.
