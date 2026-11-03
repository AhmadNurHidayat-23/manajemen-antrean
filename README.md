# Manajemen Antrean

Manajemen Antrean adalah aplikasi untuk mengambil nomor, mengatur panggilan, dan memantau pelayanan. Antarmuka berbahasa Indonesia memakai desain Swiss Editorial Minimalist dengan tepat dua warna utama: ivory `#F5F3EB` dan navy `#24476B`.

## Fitur utama

- Pengambilan nomor otomatis A001, A002, dan seterusnya.
- Nomor dimulai kembali setiap hari berdasarkan zona waktu Asia/Jakarta.
- Informasi nomor yang sedang dilayani dan jumlah antrean menunggu.
- Halaman operator dengan daftar hari ini serta jumlah berdasarkan status.
- Panggil berikutnya, tandai selesai, dan lewati antrean.
- Tampilan monitor dengan pembaruan otomatis setiap lima detik.
- Validasi transisi status dan perlindungan CSRF pada perubahan data.

Penomoran memakai penghitung harian dalam transaksi SQLite. Kunci tulis diperoleh sebelum pembacaan, dengan batas unik untuk tanggal dan nomor. Database juga membatasi satu antrean berstatus dilayani per hari.

## Cara menggunakan

Buka beranda, tekan **Ambil nomor antrean**, lalu simpan nomor yang ditampilkan. Operator memanggil nomor menunggu paling awal. Selesaikan atau lewati panggilan aktif sebelum memanggil berikutnya. Antrean menunggu juga dapat dilewati dari daftar. Nomor yang dilewati tidak dipanggil ulang pada versi ini.

Buka **Monitor** pada layar pelayanan. Pembaruan berjalan otomatis ketika halaman terlihat; gangguan koneksi ditandai dan dicoba ulang. Daftar operator diperbarui otomatis saat data berubah. Catatan hari sebelumnya tetap tersimpan, tetapi tidak masuk pelayanan hari ini.

**Operator tanpa login hanya untuk demo lokal.** Middleware membatasi alamat loopback, host localhost, dan lingkungan lokal. Akses operator pada lingkungan produksi ditolak. Tambahkan autentikasi dan otorisasi sebelum memublikasikannya.

## Teknologi dan instalasi

Laravel 13, Blade, SQLite, HTML5, CSS3, Vanilla JavaScript, dan Vite. Siapkan PHP 8.3.30+ dengan SQLite, Composer, serta Node.js 22.12+ yang kompatibel dengan Vite 8.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm ci
npm run build
php artisan serve --host=127.0.0.1
```

Pada MAMP, gunakan localhost dan document root folder `public`. Pastikan database, `storage`, dan `bootstrap/cache` dapat ditulis. Opsional, `php artisan db:seed` membuat enam nomor demonstrasi hanya jika hari ini masih kosong.

Jalankan `php artisan test` dan `npm run build`. Pengujian mencakup proses PHP bersamaan, pergantian hari, alur status, dan akses operator. `.env` serta database tidak disertakan dalam Git.
