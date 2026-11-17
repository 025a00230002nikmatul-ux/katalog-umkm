<?php
declare(strict_types=1);

// functions.php - logika bisnis (tanpa HTML)

function formatRupiah(int $angka): string
{
    return "Rp" . number_format($angka, 0, ',', '.');
}

// match(true): lengan PERTAMA yang bernilai true dipilih
function statusStok(int $stok): string
{
    return match (true) {
        $stok === 0 => "Habis",
        $stok <= 10 => "Menipis",
        default => "Tersedia",
    };
}

// Nama kelas CSS untuk badge, diturunkan dari status
function kelasBadge(string $status): string
{
    return match ($status) {
        "Habis" => "badge badge--habis",
        "Menipis" => "badge badge--menipis",
        default => "badge badge--aman",
    };
}

function hitungNilaiStok(array $produk): int
{
    $total = 0;

    foreach ($produk as $item) {
        $total += $item["harga"] * $item["stok"];
    }

    return $total;
}

function filterKategori(array $produk, string $kategori): array
{
    if ($kategori === "Semua") {
        return $produk;
    }

    return array_filter(
        $produk,
        fn(array $p): bool => $p["kategori"] === $kategori
    );
}

// Wajib dipakai setiap kali mencetak data ke HTML (cegah XSS)
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

function labelPromo(int $harga): string
{
    return $harga >= 100000 ? "Gratis Ongkir" : "";
}

// HO-3: pencarian nama produk, tidak membedakan huruf besar/kecil
function cariProduk(array $produk, string $kataKunci): array
{
    if ($kataKunci === "") {
        return $produk;
    }

    return array_filter(
        $produk,
        fn(array $p): bool => stripos($p["nama"], $kataKunci) !== false
    );
}

function cariProdukByKode(array $produk, string $kode): ?array
{
    foreach ($produk as $item) {
        if ($item["kode"] === $kode) {
            return $item;
        }
    }

    return null;
}


// HO-4: validasi form pesanan
// Mengembalikan array kesalahan: ["nama_kolom" => "pesan"]. Kosong = valid.
function validasiPesanan(array $input, array $daftarProduk): array
{
    $kesalahan = [];

    // Nama: wajib, 3-50 karakter
    $panjangNama = mb_strlen($input["nama"]);
    if ($panjangNama === 0) {
        $kesalahan["nama"] = "Nama wajib diisi.";
    } elseif ($panjangNama < 3 || $panjangNama > 50) {
        $kesalahan["nama"] = "Nama harus 3-50 karakter.";
    }

    // WhatsApp: diawali 08 atau 628, total 10-15 digit
    $waValid = filter_var($input["whatsapp"], FILTER_VALIDATE_REGEXP, [
        "options" => ["regexp" => "/^(08|628)[0-9]{8,12}$/"],
    ]);
    if ($input["whatsapp"] === "") {
        $kesalahan["whatsapp"] = "Nomor WhatsApp wajib diisi.";
    } elseif ($waValid === false) {
        $kesalahan["whatsapp"] = "Gunakan format 08xxxxxxxxxx (angka saja).";
    }

    // Alamat pengiriman: wajib, 10-150 karakter
    $panjangAlamat = mb_strlen($input["alamat"]);
    if ($panjangAlamat === 0) {
        $kesalahan["alamat"] = "Alamat pengiriman wajib diisi.";
    } elseif ($panjangAlamat < 10 || $panjangAlamat > 150) {
        $kesalahan["alamat"] = "Alamat pengiriman harus 10-150 karakter.";
    }

    // Metode pengambilan: harus sesuai pilihan
    $metodeValid = ["Ambil di toko", "Kirim kurir"];
    if (!in_array($input["metode_pengambilan"], $metodeValid, true)) {
        $kesalahan["metode_pengambilan"] = "Pilih metode pengambilan.";
    }

    // Produk: harus ada di katalog dan stoknya tidak habis
    $produk = cariProdukByKode($daftarProduk, $input["kode_produk"]);
    if ($produk === null) {
        $kesalahan["kode_produk"] = "Pilih produk dari daftar.";
    } elseif ($produk["stok"] === 0) {
        $kesalahan["kode_produk"] = "Maaf, stok produk ini habis.";
    }

    // Jumlah: bilangan bulat 1-20 dan tidak melebihi stok
    $jumlah = filter_var($input["jumlah"], FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1, "max_range" => 20],
    ]);
    if ($jumlah === false) {
        $kesalahan["jumlah"] = "Jumlah harus angka 1-20.";
    } elseif ($produk !== null && $jumlah > $produk["stok"]) {
        $kesalahan["jumlah"] = "Jumlah melebihi stok (tersisa {$produk['stok']}).";
    }

    // Catatan: opsional, maksimal 200 karakter
    if (mb_strlen($input["catatan"]) > 200) {
        $kesalahan["catatan"] = "Catatan maksimal 200 karakter.";
    }

    return $kesalahan;
}


// HO-5: penyimpanan sementara ke file JSON (diganti database di P10)
function bacaPesanan(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $isi = json_decode((string) file_get_contents($file), true);
    return is_array($isi) ? $isi : [];
}

function simpanPesanan(string $file, array $pesananBaru): void
{
    $semua = bacaPesanan($file);
    $semua[] = $pesananBaru;
    $json = json_encode($semua, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json, LOCK_EX);
}

function buatKodePesanan(int $nomorUrut): string
{
    return sprintf("PSN-%04d", $nomorUrut);
}
