# GrahaBag Inventory

GrahaBag Inventory adalah sistem informasi manajemen berbasis web yang digunakan untuk mengelola inventaris bahan baku untuk pembuatan tas di toko konveksi tas GrahaBag. Sistem ini dirancang untuk memudahkan toko dalam mencatat stok bahan baku, manajemen data supplier, mencatat barang masuk dan barang keluar, serta pengajuan pembelian bahan baku. 

## Fitur Utama

- **Kelola Bahan Baku:** Mendata bahan-bahan baku (seperti kain, benang, resleting) beserta stok minimum dan harganya.
- **Kelola Supplier:** Mencatat data supplier bahan baku (hanya dapat diakses oleh Owner).
- **Barang Masuk & Keluar:** Mencatat riwayat masuknya stok dari supplier dan keluarnya stok untuk proses produksi.
- **Pengajuan Pembelian Stok:** Mencatat pengajuan pembelian bahan baku ke supplier.
- **Laporan:** Mencetak laporan mingguan atau bulanan terkait pengajuan pembelian dan riwayat stok bahan baku.

## Persyaratan Sistem

Untuk menjalankan aplikasi ini di komputer lokal Anda, pastikan Anda telah menginstal perangkat lunak berikut:
- XAMPP (dengan PHP 7.x atau 8.x dan MySQL/MariaDB)
- Web Browser (Chrome, Firefox, Edge, atau Safari)

## Cara Menginstal dan Menjalankan Proyek

Berikut adalah langkah-langkah untuk mencoba dan menjalankan website ini di komputer Anda menggunakan XAMPP:

### 1. Persiapan Folder Proyek
1. Pastikan Anda telah menginstal XAMPP.
2. Clone repository dengan: git clone https://github.com/fanidwrn/grahabag-inventory.git
3. Salin atau pindahkan folder proyek grahabag-inventory ke dalam folder htdocs milik XAMPP.
   - Pada Windows, biasanya berada di C:\xampp\htdocs\grahabag-inventory
   - Pada Mac/Linux, biasanya di /Applications/XAMPP/xamppfiles/htdocs/ atau /opt/lampp/htdocs/

### 2. Konfigurasi Database
1. Buka aplikasi XAMPP Control Panel.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL**.
3. Buka web browser Anda dan akses halaman phpMyAdmin dengan mengetikkan URL: http://localhost/phpmyadmin
4. Buat database baru bernama `db_grahabag`
5. Buat tabel dan data awal:
   - Pilih database `db_grahabag` yang baru saja dibuat.
   - Klik tab **SQL**.
   - Buka file database.sql yang berada di dalam folder proyek dan salin seluruh isinya.
   - Klik tombol **Go** atau **Kirim** di bagian bawah untuk mengeksekusi struktur tabel.
   - Ulangi langkah di atas untuk file data.sql agar database terisi dengan data.

### 3. Mengakses Aplikasi
1. Buka tab baru pada web browser Anda.
2. Ketikkan URL berikut di address bar: 
   `http://localhost/grahabag-inventory/`
3. Anda akan diarahkan ke halaman login.


# Tugas Metode Penelitian
Oleh
- Fachria Zulfa / 2410512047
- Prasasti Nurul Septiana / 2410512048
- Fani Dwi Ariyanti / 2410512053
- Indah Farida Kumala / 2410512054
- Salma Hani Nazhifah / 2410512058
- Fia Dwi Agustina / 2410512060
