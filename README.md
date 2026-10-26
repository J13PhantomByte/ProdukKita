# ProdukKita — CRUD Produk dengan Accessibility & Inclusive Design

Aplikasi CRUD produk berbasis PHP Native, PDO, MySQL, Bootstrap CSS, CSS, dan JavaScript.

## Fitur utama
- Tambah, lihat, edit, dan hapus produk.
- Cari nama/kategori dan filter kategori.
- Responsif untuk ponsel, tablet, dan desktop.
- Prepared statements PDO, validasi server-side, output escaping, token CSRF, cookie sesi HttpOnly/SameSite, dan security headers.
- URL gambar dibatasi ke HTTPS `images.unsplash.com`.

## Penambahan tugas accessibility dan inclusive design
### Layar 1 — Form Tambah/Edit Produk
- Label input jelas dan terhubung dengan kontrol melalui `for`/`id`.
- Kolom wajib ditandai dengan teks dan atribut `required`.
- Petunjuk input menjelaskan format data.
- Indikator fokus keyboard dibuat jelas.
- Pesan kesalahan menjelaskan bahwa data perlu diperbaiki.
- Tombol dan area interaksi dibuat lebih mudah digunakan.

### Layar 2 — Katalog Produk
- Label pencarian dan filter terlihat.
- Teks harga, stok, kategori, dan nama produk memiliki hierarki yang jelas.
- Tombol Edit/Hapus mempunyai nama aksesibel spesifik per produk.
- Layout kartu responsif untuk ukuran layar berbeda.
- Tautan “Lewati ke konten utama” membantu pengguna keyboard.
- Preferensi `prefers-reduced-motion` dihormati.

## Checklist pemeriksaan sederhana
- [ ] Navigasi dengan keyboard menggunakan Tab dan Shift+Tab.
- [ ] Pastikan fokus terlihat di setiap tautan, tombol, input, dan select.
- [ ] Coba mengirim form kosong dan periksa petunjuk validasi.
- [ ] Periksa apakah label tetap jelas tanpa mengandalkan placeholder.
- [ ] Perbesar halaman sampai 200% dan pastikan konten masih terbaca.
- [ ] Coba lebar layar ponsel dan desktop.
- [ ] Periksa kontras warna dengan alat pemeriksa kontras.
- [ ] Jika memungkinkan, coba pembaca layar seperti NVDA.

Catatan: ini merupakan pemeriksaan aksesibilitas sederhana, bukan sertifikasi kepatuhan WCAG. Pengujian manual dan alat bantu masih diperlukan.

## Menjalankan di XAMPP
1. Salin folder `produk-kita-accessible` ke `C:\xampp\htdocs\`.
2. Buka phpMyAdmin dan impor `database.sql`.
3. Pastikan `config.php` sesuai konfigurasi MySQL lokal. Default XAMPP biasanya user `root` tanpa password.
4. Jalankan Apache dan MySQL.
5. Buka `http://localhost/produk-kita-accessible/`.

## Menjalankan dengan PHP built-in server
1. Impor `database.sql` ke MySQL/MariaDB.
2. Atur environment `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` bila diperlukan.
3. Dari folder project jalankan `php -S 127.0.0.1:8080`.
4. Buka `http://127.0.0.1:8080`.

## Panduan membuat PDF tugas
Saran susunan:
1. Sampul: judul tugas, nama, NIM, kelas, mata kuliah.
2. Tujuan tugas.
3. Desain awal dan masalah yang ditemukan.
4. Perbaikan layar 1: screenshot form dan penjelasan.
5. Perbaikan layar 2: screenshot katalog dan penjelasan.
6. Checklist accessibility/inclusive yang dilakukan.
7. Potongan kode yang relevan.
8. Kesimpulan dan keterbatasan pengujian.


## Catatan keamanan
- Jangan gunakan kredensial database default di server produksi.
- Gunakan HTTPS di produksi.
- Aplikasi ini belum menyediakan login/otorisasi admin; jangan mengekspos endpoint CRUD ke internet sebelum menambahkan autentikasi dan pembatasan akses.
- Batasi akses database dan lakukan backup rutin.
