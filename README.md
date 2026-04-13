# Repository Tugas Software Development (SITORI)

## Informasi Kelompok

**Nama Kelompok:** [Hikmah Tech]

**Anggota Kelompok:**

1. **Alhabib Husein Alaydrus** [2313020031]
2. **Iklima Fajri** [2313020049]
3. **Alvania Aisyah Nur Fadhlilah** [2313020072]

---

## Mata Kuliah

**Program Studi:** Teknik Informatika
**Mata Kuliah:** Software Development

---

## Deskripsi Repository

Repository ini merupakan ruang kolaborasi untuk pengerjaan proyek mata kuliah **Software Development**.
Proyek yang dikembangkan berfokus pada solusi digital berbasis deksop/web menggunakan teknologi modern untuk menyelesaikan permasalahan spesifik yang relevan dengan kebutuhan pengguna saat ini.

---

## Stack Project

- Laravel 12
- MySQL
- Tailwind CSS
- Vite
- Docker Compose

---

## Peralatan dan Kebutuhan

Berikut peralatan yang diperlukan untuk menjalankan dan mengembangkan project ini.

### Perangkat Keras Minimum

- Laptop atau PC
- Prosesor minimal dual-core
- RAM minimal 4 GB
- Ruang penyimpanan kosong minimal 5 GB
- Koneksi internet untuk clone repository, build image Docker, dan install dependency

### Perangkat Lunak Utama

- Sistem operasi Windows 10/11, Linux, atau macOS
- Docker Desktop
- Git
- Node.js
- npm
- Browser modern seperti Google Chrome atau Microsoft Edge

### Versi yang Disarankan

- PHP `8.2+`
- Laravel `12`
- Node.js `18+`
- npm `9+`
- MySQL `8`
- Docker Compose `v2`

### Fungsi Masing-Masing Tools

- `Docker Desktop`: menjalankan seluruh service aplikasi dalam container
- `Docker Compose`: mengatur container `app`, `webserver`, `mysql`, dan `phpmyadmin`
- `Git`: mengelola version control dan kolaborasi repository
- `Node.js` dan `npm`: menjalankan asset frontend dengan Vite
- `Browser`: mengakses aplikasi Laravel dan phpMyAdmin

### Opsional untuk Development

- Visual Studio Code atau editor sejenis
- Extension Laravel / PHP untuk editor
- Postman atau Thunder Client untuk pengujian request HTTP

---

## Menjalankan Dengan Docker Compose

Project ini sudah disiapkan agar bisa dijalankan penuh dengan Docker Compose, tanpa bergantung pada Laragon.

Service yang tersedia:

- `app`: container PHP-FPM untuk Laravel
- `webserver`: container Nginx untuk mengakses aplikasi
- `mysql`: container database MySQL
- `phpmyadmin`: container phpMyAdmin untuk manajemen database lewat browser

### Persiapan Awal

Sebelum menjalankan project, pastikan:

- Docker Desktop sudah aktif
- Port `8000`, `8080`, dan `3306` tidak sedang dipakai aplikasi lain
- File `.env` tersedia di root project
- Koneksi internet aktif saat build pertama kali jika image Docker belum tersedia di komputer

### Akses Lokal

- Aplikasi Laravel: `http://localhost:8000`
- phpMyAdmin: `http://localhost:8080`

### Perintah Dasar

Jalankan semua service:

```bash
docker compose up -d --build
```

Install dependency PHP di dalam container jika diperlukan:

```bash
docker compose exec app composer install
```

Generate application key:

```bash
docker compose exec app php artisan key:generate
```

Jalankan migration:

```bash
docker compose exec app php artisan migrate --force
```

Jalankan Vite di lokal jika ingin mode development frontend:

```bash
npm run dev
```

Install dependency frontend jika belum tersedia:

```bash
npm install
```

Lihat status service:

```bash
docker compose ps
```

Lihat log web server:

```bash
docker compose logs webserver --tail=50
```

Matikan semua service:

```bash
docker compose down
```

Hapus service beserta volume database:

```bash
docker compose down -v
```

### Konfigurasi Database Laravel

File `.env` sudah diarahkan ke service MySQL Docker:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=inventori_toko
DB_USERNAME=inventori_user
DB_PASSWORD=root
```

### Login phpMyAdmin

Gunakan salah satu akun berikut:

- Server: `mysql`
- Username: `root`
- Password: `root`

atau

- Server: `mysql`
- Username: `inventori_user`
- Password: `root`

## Alur Menjalankan Project

Urutan yang disarankan saat pertama kali setup:

1. Clone repository
2. Jalankan `docker compose up -d --build`
3. Jalankan `docker compose exec app composer install`
4. Jalankan `docker compose exec app php artisan key:generate`
5. Jalankan `docker compose exec app php artisan migrate --force`
6. Buka `http://localhost:8000`

## Akses Project

- Halaman utama aplikasi: `http://localhost:8000`
- Halaman login: `http://localhost:8000/login`
- phpMyAdmin: `http://localhost:8080`

## Akun Demo

Gunakan akun berikut untuk login awal:

- Email: `admin@inventori.test`
- Password: `admin12345`
- Role: `owner`

## Troubleshooting Singkat

### Docker tidak bisa jalan

Pastikan Docker Desktop sudah aktif sebelum menjalankan `docker compose`.

### Port bentrok

Jika port `8000`, `8080`, atau `3306` bentrok, cek container aktif:

```bash
docker ps -a
```

### Database belum terbentuk

Jalankan:

```bash
docker compose exec app php artisan migrate --force
```

### Asset frontend tidak ter-update

Jalankan:

```bash
npm install
npm run dev
```
