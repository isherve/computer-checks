# Computer Checks

Web application for UTB Rubavu Campus gate checks: register laptops, generate QR codes by owner name, and log check-in/check-out.

## Stack

- PHP (PDO) + MySQL
- HTML, Bootstrap, JavaScript
- XAMPP (Apache + MySQL)
- TCPDF QR barcodes

## Local setup (XAMPP)

1. Copy the `QR/` folder into `C:\xampp\htdocs\QR`
2. Start Apache and MySQL in XAMPP
3. Import `computer_records.sql` into MySQL (database: `computer_records`)
4. Open http://localhost/QR/

Default DB settings in `QR/connection.php`: host `localhost`, user `root`, empty password, database `computer_records`.

## Demo logins

| Role | Email | Password | User type |
|------|-------|----------|-----------|
| Admin | mfitumukizaeric3@gmail.com | admin123 | Admin |
| Gate Officer | mimi@gmail.com | 12345 | Guest |

> Change these passwords before any public deployment.

## Deploy note

This is a **PHP + MySQL** app. It does **not** run on Vercel (Vercel is for Node/static/serverless). Use a PHP host such as Railway, Render, InfinityFree, Hostinger, or a VPS with Apache/Nginx + MySQL.

