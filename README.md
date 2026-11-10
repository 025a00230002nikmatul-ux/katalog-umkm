
# Katalog UMKM Sekar Jagad Solo

Katalog produk dinamis berbasis PHP untuk tugas Pertemuan 7 Pemrograman Web.

## Cara Menjalankan

1. Pastikan Laragon sudah aktif.
2. Letakkan folder proyek di `C:/laragon/www/p07/katalog-umkm`.
3. Buka browser.
4. Akses `http://localhost/p07/katalog-umkm/`.

## Fitur

- Kartu produk ditampilkan menggunakan foreach.
- Label stok menggunakan match(true).
- Filter kategori melalui URL.
- Menampilkan informasi produk dan harga.

## Tantangan HO-5

- Menambahkan produk P07 kategori Kerajinan.
- Menambahkan fungsi labelPromo() untuk produk dengan harga minimal Rp100.000.
- Mengatur status stok menggunakan match(true).

## Struktur File

- `index.php` : Halaman utama katalog produk.
- `data.php` : Menyimpan data produk UMKM.
- `functions.php` : Menyimpan fungsi pengolahan data.
- `style.css` : Mengatur tampilan halaman katalog.
- `.gitignore` : Mengatur file yang tidak dimasukkan ke Git.