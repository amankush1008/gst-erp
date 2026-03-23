# ⚡ GST Billing & Business ERP

A full-featured GST-compliant billing and business management system built with **Laravel 10**, designed as a Vyapar/Tally alternative.

---

## 🚀 Features

### Billing & Invoicing
- Tax Invoice, Retail Invoice, Proforma, Credit Note, Debit Note, Delivery Challan
- Real-time GST calculation (CGST/SGST for intra-state, IGST for inter-state)
- Professional PDF templates (Default, Classic, Modern, Minimal)
- E-Way Bill generation via GST API
- Duplicate, cancel, and payment recording

### Inventory Management
- Multi-warehouse stock tracking
- Stock movements (in/out/adjustment) with audit trail
- Low stock alerts, barcode generation
- HSN code management, custom units

### Party Management
- Customer & Supplier profiles
- GSTIN verification (live API)
- Party ledger with running balance
- Credit limits and payment terms

### Financial Reports
- Sales & Purchase reports
- Profit & Loss statement
- Stock report with value
- Party Ledger
- GSTR-1 (B2B/B2C/B2CL breakdown)
- GSTR-3B (tax liability vs ITC)
- Excel export for all reports

### Settings
- Multi-business support (switch between businesses)
- Custom fields (Products, Invoices, Parties)
- Invoice number sequences with custom prefix/suffix
- Warehouse management
- Activity log

### API
- RESTful JSON API with Sanctum token authentication
- Endpoints for products, parties, invoices, dashboard stats, GST operations

---

## 📋 Requirements

- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Composer 2.x
- Node.js 18+ (for assets, optional)
- PHP Extensions: `ext-gd`, `ext-zip`, `ext-pdo`, `ext-mbstring`, `ext-xml`

---

## 🛠 Installation

```bash
# 1. Clone / extract project
cd /var/www
git clone <repo> gst-erp
cd gst-erp

# 2. Install PHP dependencies
composer install --optimize-autoloader --no-dev

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
# DB_HOST=127.0.0.1
# DB_DATABASE=gst_erp
# DB_USERNAME=root
# DB_PASSWORD=yourpassword

# 5. Run migrations
php artisan migrate

# 6. Seed demo data (optional)
php artisan db:seed

# 7. Storage symlink
php artisan storage:link

# 8. Set permissions
chmod -R 775 storage bootstrap/cache

# 9. Start development server
php artisan serve
```

**Demo login after seeding:**
- Email: `demo@gsterp.com`
- Password: `password`

---

## ⚙️ Configuration

### GST API (GSTIN Verification)
```env
GST_API_BASE_URL=https://api.gst.gov.in/commonapi/v1.1
GST_API_USERNAME=your_username
GST_API_PASSWORD=your_password
GST_CLIENT_ID=your_client_id
GST_CLIENT_SECRET=your_client_secret
```

### E-Way Bill
```env
EWAY_API_BASE_URL=https://einvapi.trail.einvoice1.gst.gov.in
EWAY_USERNAME=your_gstin
EWAY_PASSWORD=your_password
EWAY_GSTIN=22AAAAA0000A1Z5
```

### Razorpay (Online Payments)
```env
RAZORPAY_KEY=rzp_live_xxx
RAZORPAY_SECRET=your_secret
```

### Multi-Tenant Mode
```env
MULTI_TENANT=true
```

---

## 📁 Project Structure

```
gst-erp/
├── app/
│   ├── Exports/            Excel export classes
│   ├── Helpers/            Global helper functions, NumberToWords
│   ├── Http/
│   │   ├── Controllers/    Main controllers (Invoice, Product, Party, etc.)
│   │   │   ├── Api/        REST API controllers
│   │   │   └── Auth/       Authentication controller
│   │   └── Middleware/     BusinessSelected middleware
│   ├── Models/             Eloquent models
│   ├── Providers/          AppServiceProvider
│   └── Services/           Business logic (InvoiceService, GstService, etc.)
│
├── bootstrap/app.php       Laravel 11 application bootstrap
├── config/                 app.php, services.php
│
├── database/
│   ├── migrations/         6 migration files covering all tables
│   └── seeders/            DatabaseSeeder with demo data
│
├── resources/views/
│   ├── auth/               login, register
│   ├── dashboard/          main dashboard
│   ├── invoices/           index, create, edit, show, templates/
│   ├── purchases/          index, create, show
│   ├── parties/            index, create, ledger
│   ├── products/           index, create
│   ├── payments/           index, create
│   ├── expenses/           index, create
│   ├── reports/            sales, purchases, gstr1, gstr3b, stock, profit-loss
│   ├── settings/           business, custom-fields, warehouses, units,
│   │                       number-formats, activity-log
│   └── layouts/app.blade.php
│
└── routes/
    ├── web.php             All web routes
    └── api.php             Sanctum-protected API routes
```

---

## 🔗 Key Routes

| Route | Description |
|-------|-------------|
| `/` | Dashboard |
| `/invoices` | Invoice list |
| `/invoices/create` | New invoice |
| `/products` | Product list |
| `/parties` | Customer/Supplier list |
| `/purchases` | Purchase bills |
| `/payments` | Payment records |
| `/expenses` | Expense tracker |
| `/reports/gstr1` | GSTR-1 report |
| `/reports/gstr3b` | GSTR-3B summary |
| `/reports/profit-loss` | P&L statement |
| `/settings/business` | Business settings |

---

## 📡 API Endpoints

```
POST   /api/auth/token              Get API token
GET    /api/products?search=        Product search
GET    /api/parties?type=customer   Party list
GET    /api/invoices                Invoice list
GET    /api/dashboard/stats         Dashboard stats
GET    /api/dashboard/chart         12-month chart data
GET    /api/gst/verify/{gstin}      GSTIN verification
POST   /api/gst/eway-bill           Generate E-Way Bill
GET    /api/reports/gstr1           GSTR-1 JSON
GET    /api/reports/gstr3b          GSTR-3B JSON
```

---

## 🏗 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 10, PHP 8.2 |
| Database | MySQL 8 / MariaDB |
| Auth | Laravel Sanctum |
| PDF | DomPDF (barryvdh/laravel-dompdf) |
| Excel | Maatwebsite Excel 3.1 |
| Frontend | Bootstrap 5.3, Chart.js, Select2, Flatpickr |
| Icons | Font Awesome 6 |
| Barcode | milon/barcode |

---

## 🔒 Security

- CSRF protection on all forms
- Sanctum token auth for API
- `BusinessSelected` middleware ensures business context
- All queries scoped to `business_id`
- Soft deletes to prevent accidental data loss

---

## 📝 License

MIT License. Free for commercial use.

---

## 💡 Extending

- **New invoice templates**: Add `resources/views/invoices/templates/{name}.blade.php`
- **New report**: Add method to `ReportController`, add route in `web.php`, add view
- **New custom field type**: Update `custom_fields.field_type` enum and the create form
- **Webhook / notifications**: Use Laravel's notification system with the `activity_logs` table
