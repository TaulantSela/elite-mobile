<p align="center">
  <img src="img/elite-mobile_logo.png" alt="Elite Mobile logo" width="140" />
</p>

# 📱🔧 Elite Mobile

A demo storefront and admin panel for a mobile phone shop and repair studio, written in plain PHP
and MySQL. It started as my university capstone project and has since been redesigned and
deployed.

**▶ [Browse the live demo →](https://elite-mobile.vercel.app)** — running on Vercel with the
community `vercel-php` runtime.

There is no framework and no Composer: just PHP 8, `mysqli` and shared includes, so you can read
the whole app end to end in an afternoon.

## Features

**Storefront**

- **Home**: shop-by-brand tiles, fresh arrivals, "Pro picks" (the priciest phones), featured
  repair services, and a bulk-order call to action.
- **Catalogue** (`products.php`): brand filter chips with product counts, search, and sorting by
  newest, price (high to low, low to high) or name.
- **Product page** (`description.php`): specs, price in denars, a PayPal **sandbox** checkout
  button, and links to preorder or book a repair. A paid product shows as *Reserved*.
- **Services** (`service.php`): repair services from the database with price and duration.
  "Book a repair" leads to the contact form.
- **Contact** and **About** pages. The contact form sends through PHP's `mail()`.

**Admin** (sign in at `LoginForm.php`)

- **Dashboard**: totals for products, categories and services, the average price, a quick-add
  product form, and the latest products.
- **Products**: add (with image upload), edit and delete.
- **Categories** (brands): add, edit and delete.
- **Account**: change your password, set a recovery email, and create a recovery code. Each
  change asks for your current password.
- **Forgot password?** on the sign-in page emails a reset link to the recovery email (single
  use, expires in 30 minutes, at most three an hour), or resets with the recovery code, which
  is then replaced. Neither path reveals whether an account exists.
- **Passwords** are stored as bcrypt hashes, recovery codes too, and reset tokens as SHA-256.
  Any plaintext password left in the database is accepted once and re-hashed on that login, and
  the session id is regenerated on sign-in.

## How it works

1. **Routing.** On Vercel, `vercel.json` sends asset folders (`img/`, `styles/`, `css/`, `js/`,
   `vendor/`, `bootstrap-3.3.7-dist/`) straight to static files and every other path to
   `api/index.php`. That front controller maps `/products.php` to `app/products.php` (`/` goes to
   `home.php`), refuses anything outside `app/` or anything that isn't a `.php` file, and then
   runs the page.
2. **Pages** in `app/` are server-rendered PHP that pull in shared partials from `app/includes/`
   (header, footer, head) and query helpers from `app/includes/functions.php`. The storefront
   uses Bootstrap 3 and jQuery plus the custom stylesheet `styles/store.css`; the admin panel has
   its own layout and `styles/admin.css`.
3. **Database.** `app/includes/database.php` opens one shared `mysqli` connection from the `DB_*`
   environment variables, with optional TLS for managed MySQL hosts. The schema has five tables:
   `category`, `product`, `services`, `users` and `password_resets`. Changes to an existing
   database live in `migrations/`; `phpproject.sql` already includes them.
4. **Images.** Seed images are filenames served from `img/`. When `BLOB_READ_WRITE_TOKEN` is set,
   uploaded product images go to **Vercel Blob** through its HTTP API (`app/includes/blob.php`)
   and the public URL is saved on the product, because Vercel's filesystem is read-only. Without
   a token, uploads are written to `img/products/` on disk.

## Run locally

You need PHP 8.0+ with the `mysqli` extension (`curl` too if you want Blob uploads) and MySQL 5.7+
or MariaDB 10+.

1. Create the database and load the schema and seed data:

   ```bash
   mysql -u root -p -e "CREATE DATABASE phpproject"
   mysql -u root -p phpproject < phpproject.sql
   ```

2. Point the app at it. Defaults live in `app/includes/config.php` (`localhost`, `root`, empty
   password, `phpproject`, port `3306`); override any of them with environment variables:

   | Variable | Purpose |
   |----------|---------|
   | `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` | Connection settings |
   | `DB_SOCKET` | Optional Unix socket |
   | `DB_SSL` | `1` to connect over TLS (managed databases) |
   | `DB_SSL_CA`, `DB_SSL_CERT`, `DB_SSL_KEY` | Optional TLS certificate paths |
   | `DB_CONNECT_TIMEOUT`, `DB_READ_TIMEOUT` | Seconds (defaults: 10 and 30) |
   | `BLOB_READ_WRITE_TOKEN` | Optional: store uploaded images in Vercel Blob |
   | `PAYPAL_PDT_TOKEN` | Optional: PayPal sandbox PDT identity token, needed to mark products as paid |
   | `RESEND_API_KEY` | Optional: sends reset emails through [Resend](https://resend.com). Without it, locally the reset link is written to the PHP error log |
   | `MAIL_FROM` | Optional: sender address, default `Elite Mobile <onboarding@resend.dev>` |
   | `APP_URL` | Optional: base URL for links in emails; on Vercel it defaults to the production domain |

3. Start PHP's built-in server **from the repo root** and open
   <http://localhost:8000/app/home.php>:

   ```bash
   php -S localhost:8000
   ```

   PHP doesn't read `.env.local` by itself. To send real reset emails locally, load just the
   mail settings from it, not the whole file (`vercel env pull` can put production database
   settings there):

   ```bash
   set -a; eval "$(grep -E '^(RESEND_API_KEY|MAIL_FROM)=' .env.local)"; set +a
   php -S localhost:8000
   ```

4. The seed data ships no usable admin password. Set one (12+ characters; it uses the same
   `DB_*` variables, so check which database they point at):

   ```bash
   php scripts/set-admin-password.php taulant
   ```

   Then sign in at <http://localhost:8000/app/LoginForm.php>. After that you can change it, and
   set up a recovery email and code, under **Account**.

> [!NOTE]
> The pages are written for the Vercel route table: PHP pages at the site root, assets from the
> repo root. PHP's built-in server can't do both, so locally the header logo, the home-page hero
> image and the admin sidebar links (root paths like `/admin/admin_products.php`) don't resolve.
> Open admin pages directly under `/app/`, e.g. `/app/admin/admin_products.php`.

## Deploy

The repo deploys to Vercel as-is: `vercel.json` pins the `vercel-php@0.9.0` runtime for
`api/**/*.php` and defines the routes. Set the `DB_*` variables (and `DB_SSL=1` for a managed
MySQL) plus `BLOB_READ_WRITE_TOKEN` (and optionally `PAYPAL_PDT_TOKEN`, `RESEND_API_KEY`,
`MAIL_FROM`) in the Vercel project. Apply any new file in `migrations/` to the production database
before deploying the code that needs it, and set the first admin password with
`scripts/set-admin-password.php`.

## Known limitations

- **PayPal is a sandbox demo.** Checkout goes to PayPal's sandbox. A product is marked as
  paid (*Reserved*) only after PayPal confirms the transaction through PDT, which needs the
  sandbox merchant's `PAYPAL_PDT_TOKEN`; without it, nothing is ever marked as paid.
- **The contact form** needs a working mail transport for PHP's `mail()`.
- **Editing a product** changes its details, not its image.
- **Repair services** are seeded in `phpproject.sql`. The admin panel shows their count but has
  no page to edit them.
