# Elite Mobile

Elite Mobile is a capstone web application for a premium mobile retail and repair studio. It showcases a product catalogue with rich merchandising content, a service booking flow, and an admin dashboard for managing inventory, categories, and highlighted repair services. The project is written in plain PHP and designed to run on a traditional LAMP stack or the PHP development server.

## Features
- Responsive storefront with featured products, latest arrivals, and service highlights
- Product detail views with PayPal sandbox checkout demo integration
- Search-by-category browsing with sorting controls
- Service landing page for booking repair packages and enterprise consultations
- Admin dashboard to insert, edit, and remove categories and products
- Session-based authentication for administrators (sample credentials provided)

## Tech Stack
- PHP 8.0+ with mysqli extension
- MySQL / MariaDB (schema export in `phpproject.sql`)
- HTML5, Bootstrap 3, custom CSS, and Font Awesome assets

## Prerequisites
- PHP 8.0 or newer with mysqli enabled (tested with PHP 8.2)
- MySQL 5.7+ or MariaDB 10+
- Composer is **not** required
- Optional: Apache/Nginx with PHP-FPM if you prefer a full web server stack

## Getting Started
1. Clone or download the repository into your web root or a working directory.
2. Create a MySQL database (default name: `phpproject`).
3. Import the schema and seed data:

```bash
mysql -u root -p phpproject < phpproject.sql
```

4. Configure database credentials:
	 - By default, the app reads connection settings from the constants defined in [app/includes/config.php](app/includes/config.php).
	 - Override any value by exporting environment variables (`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`, optionally `DB_SOCKET`).

## Running the Application
- **Using the PHP development server** (recommended for quick testing):

```bash
php -S localhost:8000 -t app
```

Then open http://localhost:8000/home.php in your browser.

- **Using Apache/Nginx**: point your document root to the `app/` directory and ensure PHP can read the configuration and `img`, `styles`, `vendor`, and `bootstrap-3.3.7-dist` assets.

## Admin Access
- Navigate to http://localhost:8000/LoginForm.php (or the equivalent route in your server configuration).
- Sample credentials from the seed data:
	- Username: `taulant`
	- Password: `taulant`
	- Alternate account: `elijon` / `elijon123`

## Project Structure
- [app/](app) – public PHP pages, admin tools, and includes
- [app/includes/](app/includes) – configuration, database helpers, and shared UI components
- [img/](img) – product imagery and branding assets
- [styles/](styles) – custom CSS files for public pages and admin login
- [vendor/](vendor) and [bootstrap-3.3.7-dist/](bootstrap-3.3.7-dist) – third-party assets
- [phpproject.sql](phpproject.sql) – database schema and seed data

## Development Notes
- PHP strict types and union types are enabled, so ensure your runtime is PHP 8.0+.
- PayPal integration targets the sandbox environment; update the form values in [app/includes/functions.php](app/includes/functions.php) before going live.
- For production deployment, secure admin credentials (hash passwords, enforce HTTPS, and rotate PayPal sandbox keys).

## License
This project was created as an academic capstone. Adapt and reuse for learning purposes.
