# Resolve — Campus Helpdesk

Program 29, Major Project I. Approved theme: Online Complaint Registration.

Laravel 12, Blade layouts, locally bundled Bootstrap 5.3.8, SQLite (PDO), responsive interface.

## Local use

Double-click `Launch.cmd`, then use http://127.0.0.1:8029. The PHP executable must be on PATH.
Local demo credentials are in `LOCAL LOGIN DETAILS.txt`, which is intentionally excluded from Git.
You can also register a new student account.

## Modules and permissions

- Complaints: complete CRUD for the owner or staff; students cannot change assignment or staff status.
- Categories: complete CRUD for staff; categories in use cannot be deleted.
- Follow-up timeline, search/status/category filters, dashboard, and filtered printable reports.
- Related application tables: users, categories, complaints, comments. All foreign keys are defined in migrations.
- Authentication uses Laravel sessions, CSRF protection, hashed passwords, session regeneration, logout invalidation, and throttled login/registration.

Run `Test.cmd` for seven feature tests including access isolation and validation.

## Deployment status

Local application is implemented and tested. A live URL and a real SSH deployment/update demonstration are still pending server access. Do not present the local address as a live deployment.

Deployment preparation is in `DEPLOYMENT.md`. Never commit `.env`, local passwords, database files, or SSH keys.

AI assistance and commands are documented in `AI_ASSISTANCE.md` and `DEPLOYMENT.md`. Screenshots will be taken by the student.
