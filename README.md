# Shopify Import Studio

CSV to Shopify product import app built in Laravel.

## Setup

1. Install dependencies:

```bash
composer install
```

2. Copy and configure `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

3. Set your database and Shopify credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopify_import
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

SHOPIFY_STORE_URL=your-store.myshopify.com
SHOPIFY_ACCESS_TOKEN=shpat_your_token
SHOPIFY_API_VERSION=2024-01
SHOPIFY_COLLECTION_ID=your_collection_id
```

4. Run migrations:

```bash
php artisan migrate --force
```

## Run

Start the app with:

```bash
php artisan serve
```

If you want background imports to process immediately, keep a queue worker running in another terminal:

```bash
php artisan queue:work
```

## What It Does

- Uploads CSV files through a drag-and-drop import workspace
- Processes imports in the background
- Creates products in Shopify and adds them to the configured collection
- Skips rows only when the product is already present in that Shopify collection
- Exports the current Shopify collection to CSV for verification
- Removes products from the configured collection through the UI or API
- Tracks upload status on import activity tab, and import logs

## API

- `GET /collection-products/export` downloads a CSV of products in the configured collection.
- `POST /collection-products/remove` removes a single product from the configured collection.
- `POST /upload/{upload}/remove-from-collection` removes imported products for a specific upload from the configured collection.

## Notes

- The app keeps local `products`, `uploads`, and `import_logs` records for tracking and visibility.
- Duplicate skipping is based on Shopify collection membership, not on local database rows.
- If Shopify rejects a custom handle as already in use, the importer retries without the handle and lets Shopify generate one automatically.

## Import File for testing

Use product_csv_to_impory.csv file present in root that contain 156 products