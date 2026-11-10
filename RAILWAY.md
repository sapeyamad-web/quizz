# Menyiapkan Deployment ke Railway

Proyek ini menggunakan builder Railpack milik Railway. Railpack mendeteksi
Laravel melalui file `artisan`, mendeteksi proses build frontend melalui
`package.json`, lalu menjalankan Laravel dengan FrankenPHP. Dockerfile atau
perintah start khusus tidak diperlukan.

## Membuat layanan Railway

1. Buat proyek Railway dan deploy repositori ini sebagai sebuah layanan.
2. Tambahkan layanan PostgreSQL di Railway. Pada layanan aplikasi, gunakan URL
   koneksi database dengan `DB_URL=${{Postgres.DATABASE_URL}}`. Jika nama
   layanan database Anda berbeda, ganti `Postgres` dengan nama yang sesuai.
3. Tambahkan variabel aplikasi seperti di bawah. Jaga kerahasiaan `APP_KEY`
   dan gunakan nilai yang sama pada setiap deployment aplikasi ini.
4. Buat domain publik untuk layanan aplikasi, lalu isi `APP_URL` dengan URL
   HTTPS tersebut.
5. Pada pengaturan layanan aplikasi, atur path healthcheck ke `/up`.
6. Agar gambar soal yang diunggah tetap tersimpan setelah deployment, pasang
   volume Railway ke layanan aplikasi pada `/app/storage/app/public`. Aplikasi
   ini menyimpan gambar soal di disk lokal `public`. Volume Railway tidak dapat
   dibagikan antar-replika, jadi gunakan satu replika untuk layanan aplikasi
   ini. Deployment yang menggunakan volume mungkin mengalami downtime singkat
   saat proses deployment ulang.
7. Setelah deployment pertama, buka shell layanan aplikasi dan jalankan
   `php artisan tinker`. Buat akun admin pertama dengan email yang unik:

   ```php
   $password = bin2hex(random_bytes(32));
   App\Models\User::create([
       'name' => 'Administrator',
       'email' => 'your-admin-email@example.com',
       'password' => $password,
   ]);
   echo $password;
   ```

   Simpan kata sandi yang dihasilkan di pengelola kata sandi. Pendaftaran
   publik dinonaktifkan; hanya akun yang dibuat oleh operator yang dapat login.

## Variabel layanan aplikasi

Atur variabel berikut di tab **Variables** pada Railway. Jangan simpan rahasia
asli di repositori:

```text
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:<generate-a-key>
APP_URL=https://<your-app-domain>
LOG_CHANNEL=stderr

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}
RAILPACK_PHP_EXTENSIONS=pdo_pgsql,gd

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

Buat key secara lokal dengan `php artisan key:generate --show`, lalu salin
hasilnya ke variabel `APP_KEY` di Railway. Jangan membuat key baru setiap
deployment karena perubahan key akan membatalkan data terenkripsi dan sesi yang
sedang aktif.

## Build, migrasi, dan penyimpanan

Railpack memasang dependensi Composer dan npm, lalu menjalankan skrip build
frontend (`npm run build`). Railpack juga menyiapkan cache Laravel untuk
produksi. Saat aplikasi dimulai, Railpack menjalankan migrasi database dan
membuat tautan storage publik Laravel. Biarkan migrasi otomatis tetap aktif;
jika pengaturannya pernah diubah, setel `RAILPACK_SKIP_MIGRATIONS=false`.

Migrasi database proyek ini sudah mencakup tabel sesi, cache, dan antrean
Laravel. Karena itu, aplikasi saat ini tidak memerlukan layanan Redis atau
worker terpisah. Jika nanti ditambahkan pekerjaan latar belakang, buat layanan
worker Railway tersendiri.

## Deploy dan verifikasi

Push proyek ke repositori Git yang terhubung, lalu mulai deployment dari
Railway. Periksa log deployment untuk memastikan pemasangan dependensi, build
frontend, migrasi database, dan startup berhasil. Buka domain yang sudah dibuat
dan periksa halaman utama, login admin, pengiriman kuis, ekspor CSV, serta
gambar soal yang diunggah. Buat cadangan database PostgreSQL dan volume gambar
secara terpisah.

Referensi Railway:

- [Deploy aplikasi Laravel](https://railway.com/guides/laravel)
- [Penyedia PHP Railpack](https://railpack.com/languages/php)
- [Volume Railway](https://docs.railway.com/volumes)
- [Healthcheck Railway](https://docs.railway.com/deployments/healthchecks)
