# Checklist Deployment VPS Sitori

Panduan ini dipakai sebelum aplikasi dipindahkan ke VPS production.

## 1. Environment Production

Pastikan `.env` di VPS memakai nilai production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventori_toko
DB_USERNAME=user_production
DB_PASSWORD=password_production

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.provider-kamu.com
MAIL_PORT=587
MAIL_USERNAME=mailbox@domain-kamu.com
MAIL_PASSWORD=password_smtp
MAIL_FROM_ADDRESS=no-reply@domain-kamu.com
MAIL_FROM_NAME="${APP_NAME}"
```

Gunakan password database dan SMTP yang berbeda dari local development.

## 2. Command Setelah Upload

Jalankan dari root project di VPS:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Jika sudah punya `APP_KEY` lama dari deployment sebelumnya, jangan generate ulang karena session dan data terenkripsi bisa berubah.

## 3. Test SMTP

Setelah `.env` SMTP production diisi:

```bash
php artisan config:clear
php artisan mail:test emailtujuan@domain.com
```

Jika email tidak masuk:

- cek `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- cek apakah provider membutuhkan `MAIL_SCHEME=tls` atau `MAIL_SCHEME=ssl`
- cek firewall VPS untuk koneksi keluar port SMTP
- cek folder spam
- cek log Laravel di `storage/logs/laravel.log`

## 4. Permission

Pastikan web server bisa menulis ke:

```bash
storage
bootstrap/cache
```

Contoh umum:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

Sesuaikan user web server jika bukan `www-data`.

## 5. Queue dan Scheduler

Project memakai `QUEUE_CONNECTION=database`. Jika nanti ada job queue yang perlu diproses, jalankan worker dengan Supervisor:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Tambahkan cron scheduler:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Backup Database

Minimal siapkan backup harian:

```bash
mysqldump -u user_production -p inventori_toko > backup-$(date +%F).sql
```

Simpan backup di lokasi berbeda dari VPS jika memungkinkan.

## 7. Audit Logging

Aksi penting dicatat di tabel `activity_logs`, termasuk:

- login
- registrasi owner
- tambah user bawahan
- import produk
- pembatalan transaksi penjualan
- pembatalan pembelian
- hapus semua produk, kategori, dan supplier

Contoh cek cepat:

```bash
php artisan tinker
App\Models\ActivityLog::latest()->take(10)->get();
```

## 8. Final Smoke Test

Sebelum dianggap live:

- login owner sederhana, owner lengkap, gudang lengkap, kasir
- import produk CSV
- buat transaksi POS
- batalkan transaksi
- buat pembelian
- batalkan pembelian
- export laporan penjualan, pembelian, stok, laba rugi
- test reset sandi owner via email
