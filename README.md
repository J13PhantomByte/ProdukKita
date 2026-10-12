# ProdukKita — CRUD Produk (PHP Native + MySQL)

Aplikasi CRUD produk responsif dengan PHP native, PDO, MySQL, CSS, JavaScript, dan Bootstrap CSS.

## Fitur
- Create, Read, Update, Delete produk.
- Cari nama/kategori dan filter kategori.
- Tampilan responsif untuk ponsel, tablet, dan desktop.
- Prepared statements PDO, validasi server-side, output escaping, token CSRF, cookie sesi HttpOnly/SameSite, dan security headers.
- URL gambar dibatasi ke HTTPS `images.unsplash.com`.

## Menjalankan di XAMPP
1. Salin folder `produk-crud` ke `C:\xampp\htdocs\`.
2. Buka phpMyAdmin dan impor `database.sql`.
3. Pastikan konfigurasi MySQL di `config.php` sesuai. Default XAMPP biasanya user `root` dengan password kosong.
4. Jalankan Apache dan MySQL di XAMPP.
5. Buka `http://localhost/produk-crud/`.

## Menjalankan dengan PHP built-in server
1. Impor `database.sql` ke MySQL/MariaDB.
2. Atur environment `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` jika diperlukan.
3. Dari folder project, jalankan `php -S 127.0.0.1:8080`.
4. Buka `http://127.0.0.1:8080`.

## Catatan keamanan
- Jangan gunakan kredensial database default pada server produksi. Gunakan environment variables dan akun DB dengan hak minimum.
- Gunakan HTTPS di produksi. Header cookie `Secure` otomatis aktif saat HTTPS terdeteksi.
- Aplikasi ini belum menyediakan login/otorisasi admin. Jangan mengekspos endpoint CRUD ke internet sebelum menambahkan autentikasi dan pembatasan akses.
- Batasi akses database dan lakukan backup rutin.
- URL gambar dibatasi ke host Unsplash; gambar tetap dimuat dari server pihak ketiga.
- Error koneksi database tidak ditampilkan secara rinci kepada pengguna; detail dicatat ke error log server.
