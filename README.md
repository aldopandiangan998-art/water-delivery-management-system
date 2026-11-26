# AquaFlow - Water Delivery Management System 💧

Sistem Manajemen Pemesanan, Distribusi, dan Pengantaran Air Galon berbasis web yang dirancang khusus untuk operasional depot air minum **Toko Nova Jaya** (Bratang Gede, Wonokromo, Kota Surabaya).

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Leaflet](https://img.shields.io/badge/Leaflet-OpenStreetMap%20%26%20Google-199900?style=for-the-badge&logo=leaflet&logoColor=white)](https://leafletjs.com)

---

## 📌 Fitur Utama

### 1. Role Access Control
- **Admin**: Manajemen operasional penuh, katalog produk air, pemrosesan pesanan, manajemen deposit galon, dan laporan keuangan/penjualan.
- **Pelanggan (Customer)**: Katalog produk air galon, checkout pemesanan cepat, pemantauan status pesanan, tracking kurir realtime, riwayat saldo galon, dan profil toko.

### 2. Integrasi Peta & Geolocation Surabaya
- **Peta Interaktif Toko Nova Jaya**: Menampilkan lokasi fisik depot di Bratang Gede, Wonokromo, Surabaya dengan koordinat presisi (`-7.303488, 112.753373`).
- **Hybrid Tile Switcher**: Pilihan layer peta standar (OpenStreetMap) dan Citra Satelit Google Maps.
- **Live GPS Driver Tracking**: Pelanggan dapat memantau pergerakan kurir secara realtime saat pesanan sedang dikirim ke alamat tujuan.
- **Radius Wilayah Pengantaran**: Batasan otomatis wilayah operasional khusus area Kota Surabaya.

### 3. Manajemen Deposit Galon & Keuangan
- Pencatatan saldo deposit galon milik pelanggan (galon kosong yang dititipkan/dipinjam).
- Laporan arus kas, total penjualan, dan filter periode waktu yang rapi dan komprehensif.
- Integrasi tombol pesanan dan konfirmasi WhatsApp langsung ke kontak pelanggan atau depot.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x, Eloquent ORM, PHP 8.2+
- **Frontend**: Blade Templating, Tailwind CSS, Alpine.js, FontAwesome 6
- **Maps**: Leaflet.js (OpenStreetMap Tiles + Google Satellite XYZ)
- **Database**: MySQL / SQLite (Development)
- **Testing**: PHPUnit / Pest

---

## 🚀 Panduan Instalasi Lokal

### 1. Clone Repositori
```bash
git clone https://github.com/aldopandiangan998-art/water-delivery-management-system.git
cd water-delivery-management-system
```

### 2. Instal Dependensi
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan (.env)
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan konfigurasi database Anda pada file `.env`.

### 4. Migrasi & Seeder Database
```bash
php artisan migrate --seed
```

### 5. Jalankan Aplikasi
```bash
# Terminal 1: Jalankan server Laravel
php artisan serve

# Terminal 2: Jalankan Vite asset bundler
npm run dev
```
Buka browser di `http://localhost:8000`.

---

## 🧪 Pengujian (Automated Tests)

Aplikasi dilengkapi dengan rangkaian pengujian otomatis (unit & feature tests):

```bash
php artisan test
```

---

## 📍 Informasi Depot
- **Nama Toko**: Toko Nova Jaya
- **Alamat**: Jl. Bratang Gede, Kel. Ngagelrejo, Kec. Wonokromo, Kota Surabaya, Jawa Timur
- **Titik Koordinat**: `-7.303488, 112.753373`
- **Tautan Google Maps**: [Buka di Google Maps](https://maps.app.goo.gl/fozU4QCYdbE6unCX7)

---

## 📄 Lisensi
Proyek ini dilisensikan di bawah lisensi [MIT](LICENSE).
