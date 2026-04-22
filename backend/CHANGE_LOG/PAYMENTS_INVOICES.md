# Payments + Invoices Module — Deployment Change Log

Greenfield billing module. Master admins can record payments against companies, auto-generate invoices (PDF), email them, and review history.

---

## Backend (`cloud-app/backend`)

### NEW files (create)

**Migrations** — `database/migrations/`
- `2026_04_21_090000_add_currency_to_companies_table.php`
- `2026_04_21_090100_create_invoice_sequences_table.php`
- `2026_04_21_090200_create_payments_table.php`
- `2026_04_21_090300_create_invoices_table.php`

**Models** — `app/Models/`
- `Payment.php`
- `Invoice.php`
- `InvoiceSequence.php`

**Services** — `app/Services/Billing/` *(new directory)*
- `InvoiceNumberService.php`
- `InvoiceService.php`
- `InvoicePdfService.php`
- `InvoiceMailer.php`

**Mail + Views**
- `app/Mail/InvoiceMail.php`
- `resources/views/pdf/invoices/default.blade.php` *(new directory)*
- `resources/views/emails/invoice_sent.blade.php`

**Requests** — `app/Http/Requests/Master/`
- `Payment/StoreRequest.php` *(new directory)*
- `Invoice/EmailRequest.php` *(new directory)*

**Controllers** — `app/Http/Controllers/Master/`
- `PaymentController.php`
- `InvoiceController.php`

**Routes**
- `routes/master-billing.php`

### MODIFIED files
- `app/Models/Company.php` — added `payments()` and `invoices()` hasMany relations
- `routes/api.php` — added `include('master-billing.php');` alongside other `include(...)` lines

### Database action
```bash
php artisan migrate
```
Runs the 4 new migrations (adds `currency` column to `companies`, seeds the invoice sequence row, creates `payments` + `invoices` tables).

### Runtime requirements
- `barryvdh/laravel-dompdf` package (should already be installed; else `composer require barryvdh/laravel-dompdf`)
- SMTP configured in `.env` (`MAIL_*`)
- Queue worker running: `php artisan queue:work` (`InvoiceMail` implements `ShouldQueue`)

---

## Frontend (`master-app`)

### NEW files (create)

**Helper**
- `src/services/downloadBlob.js`

**Invoice pages** — `src/pages/invoices/` *(new directory)*
- `InvoicesList.jsx`
- `InvoiceDetail.jsx`
- `InvoiceEmailModal.jsx`

**Payment pages** — `src/pages/payments/` *(new directory)*
- `PaymentsList.jsx`
- `PaymentCreate.jsx`
- `PaymentRow.jsx`

### MODIFIED files
- `src/components/Icon.jsx` — added 4 SVG paths: `invoice`, `payment`, `download`, `send`
- `src/components/AppSidebar.jsx` — appended 2 entries to `NAV_ITEMS` (Payments, Invoices)
- `src/App.jsx` — added 4 routes: `/payments`, `/payments/create`, `/invoices`, `/invoices/:id`
- `src/pages/companies/CompanyEdit.jsx` — added Payments tab, new imports, new toast-callback props on `TabContent`

### Build action
```bash
npm run build   # then deploy /dist as usual
```
No new npm dependencies required.

---

## Production deploy order (safe sequence)

1. **Backup database** (money data — always).
2. Copy all NEW + MODIFIED backend files.
3. `php artisan migrate` on production DB.
4. `php artisan config:clear && php artisan route:clear && php artisan view:clear`.
5. Restart the queue worker so it picks up the new `InvoiceMail` class.
6. Copy all NEW + MODIFIED frontend files, `npm run build`, deploy `dist/`.
7. Smoke test: open `/payments/create`, record a test payment, download PDF, send email.

## Rollback

- Frontend: redeploy previous `dist/`.
- Backend: restore previous files, then:
  ```bash
  php artisan migrate:rollback --step=4
  ```
  (rolls back payments + invoices tables, invoice_sequences, currency column)

---

## Verification checklist

1. `php artisan migrate` — all 4 migrations succeed.
2. `php artisan tinker` → `app(App\Services\Billing\InvoiceNumberService::class)->next()` three times → expect `INV-000001`, `INV-000002`, `INV-000003`.
3. Open `/payments/create`, pick a company, amount=100, tax=5%, description="test", method=bank, email off → submit → redirects to `/invoices/:id`.
4. On the invoice page: subtotal 100.00, VAT 5.00, total 105.00, currency AED.
5. Click **Download PDF** → PDF downloads with correct company + amounts.
6. Click **Resend Email** with a note → email arrives with PDF attached.
7. Record another payment with "email invoice now" ticked → green "Sent" badge appears.
8. Open a company → **Payments** tab shows that company's payments with "Record Payment for {company}" CTA.
9. `/payments`: search by company, filter by date, paginate.
10. Try deleting a company with payments → FK restrict error (intentional; money records are immutable).

---

## Known side effects (by design)

1. **Company delete is blocked once payments exist** — `onDelete('restrict')` on the FKs. Deliberate: money records must be preserved. A future "void" feature should add a `voided_at` column rather than allow hard delete.
2. **Emails require a running queue worker** — `InvoiceMail` is queued. Without a worker, emails sit in the queue. No other functionality is affected.

## Deferred / follow-up items

- **Auth on `routes/master-billing.php`** — flagged with `TODO(auth)` comment at the top of the file. Add `auth:sanctum` + master-admin gate once master auth is standardized across `routes/admin.php`. The frontend already sends `Bearer master_token` via axios interceptor.
- Refunds / void (add `voided_at` columns; keep audit trail)
- Partial payments (would require schema change to many-payments-per-invoice)
- Recurring invoices, multi-currency FX, stored PDF archive
