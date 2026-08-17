<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Database-SQLite%203-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite Database">
  <img src="https://img.shields.io/badge/Styling-Tailwind%20CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Icons-FontAwesome%206-538DD5?style=for-the-badge&logo=font-awesome&logoColor=white" alt="FontAwesome">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT">
</p>

<h1 align="center">📡 NusantaraNet - ISP & RT/RW Net Billing & Management System</h1>

<p align="center">
  Aplikasi web manajemen pelanggan, billing otomatis, pencatatan pembayaran, pengawasan umur piutang (<i>aging analysis</i>), integrasi reminder WhatsApp, dan pelaporan keuangan untuk Internet Service Provider (ISP) & RT/RW Net.
</p>

---

## 🌟 Fitur Utama (Key Features)

### 1. 👥 Manajemen Pelanggan (Customer Management)
* **Pendaftaran Pelanggan Lengkap**: Data kontak, PIC wilayah, alamat pemasangan, titik port ODP/tiang, username & password PPPoE.
* **Filter Multi-Kriteria & Pagination**: Filter berdasarkan status langganan, paket layanan, area cakupan, dan PIC penanggung jawab. Pengaturan baris data (10, 25, 50, 100, Semua).
* **Perubahan Status Cepat (Inline Status Update)**: Mengubah status pelanggan (*Aktif, Isolir/Suspended, Nonaktif*) secara langsung dari tabel via AJAX.
* **Import & Export CSV**:
  * Download template CSV berisikan **15 kolom lengkap** sesuai formulir pendaftaran pelanggan baru dengan **3 data contoh (dummy)**.
  * Auto-register Paket Layanan, Area Coverage, dan PIC baru secara otomatis saat mengimpor CSV.

### 2. 🧾 Billing & Invoicing
* **Penerbitan Tagihan Otomatis (Auto-Billing)**: Generate tagihan bulanan massal untuk seluruh pelanggan aktif sesuai siklus jatuh tempo masing-masing.
* **Penerbitan Tagihan Manual**: Fleksibilitas pembuatan invoice ad-hoc dengan custom item rincian, diskon, dan PPN.
* **Aksi Massal Tagihan**: Tandai lunas massal, kirim pengingat WhatsApp massal, cetak batch, atau batalkan tagihan terpilih.
* **Cetak Invoice PDF / Slip Cetak**: Rincian tagihan resmi lengkap dengan instruksi transfer rekening bank dan logo perusahaan.

### 3. ⏳ Pengawasan Piutang & Aging Analysis (Receivables)
* **Kategori Umur Piutang (Aging Buckets)**:
  * 🟠 *1 - 7 Hari*: Peringatan ramah WhatsApp.
  * 🔴 *8 - 30 Hari*: Prioritas tindakan isolir layanan.
  * 🚨 *> 30 Hari*: Tindakan kunjungan lapangan / teknisi penagihan.
* **Kirim Reminder WhatsApp 1-Klik**: Otomatis membuat template pesan penagihan sopan berisi nomor tagihan, nominal sisa tagihan, dan tanggal jatuh tempo.

### 4. 💳 Pembayaran & Kas Keuangan (Payments & Finance)
* Pencatatan riwayat pembayaran per akun bank / kas tunai.
* Mutasi saldo otomatis dan rekonsiliasi pembayaran.
* Filter riwayat pembayaran berdasarkan metode bayar, akun penerima kas, rentang tanggal, area, dan PIC.

### 5. 🔒 Keamanan & Audit Log
* **Autentikasi Berbasis Role**: Administrator, Operator Billing, Kasir, dan Teknisi Lapangan.
* **CSRF Token Protection** pada setiap formulir dan request mutasi data.
* **Activity & Audit Trail**: Pencatatan lengkap aktivitas user (login, import data, update status, pembayaran, dll).

---

## 🛠️ Kebutuhan Sistem (Prerequisites)

* **PHP**: Versi **8.2** atau lebih baru (Didukung penuh hingga **PHP 8.4+**).
* **Ekstensi PHP**: `pdo`, `pdo_sqlite`, `mbstring`, `json`, `fileinfo`.
* **Web Server**: Apache / Nginx / PHP Built-in Web Server (MAMP, XAMPP, Laragon, dll).

---

## 🚀 Panduan Instalasi Cepat (Quick Start)

### 1. Clone Repositori
```bash
git clone https://github.com/USERNAME/billing-apk.git
cd billing-apk
```

### 2. Konfigurasi Izin Folder (Permissions)
Pastikan folder `database/` dan `storage/` dapat ditulisi (*writeable*):
```bash
chmod -R 775 database storage
```

### 3. Inisialisasi & Seeding Database (Opsional)
Aplikasi menggunakan database SQLite bawaan `database/isp.sqlite`. Anda dapat menginisialisasi ulang database dan data awal menggunakan:
```bash
php database/seed.php
```

### 4. Jalankan Aplikasi
Anda dapat menggunakan web server lokal pilihan Anda:

* **Opsi 1: PHP Built-in Server (Sangat Mudah)**
  ```bash
  php -S localhost:8080
  ```
  Lalu buka di browser: **[http://localhost:8080](http://localhost:8080)**

* **Opsi 2: MAMP / XAMPP / Laragon**
  Letakkan folder proyek di dalam direktori root server (misal: `/Applications/MAMP/htdocs/billing-apk` atau `C:/xampp/htdocs/billing-apk`) dan akses via URL browser.

---

## 🔑 Akun Demo Bawaan (Default Accounts)

| Role | Email Login | Password | Akses Fitur |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@nusantara.net` | `admin123` | Akses Penuh ke Semua Modul & Pengaturan |
| **Billing Manager** | `billing@nusantara.net` | `billing123` | Pelanggan, Tagihan, Piutang & Pembayaran |
| **Kasir / Finance** | `kasir@nusantara.net` | `kasir123` | Catat Bayar & Laporan Kas |

---

## 📁 Struktur Direktori (Project Structure)

```text
billing-apk/
├── app/
│   ├── Controllers/       # Controller logic (AuthController, CustomerController, BillingController, dll)
│   ├── Helpers/           # Helper functions & Utilities (Helper.php, Formatters, CSRF, dll)
│   ├── Middlewares/       # AuthMiddleware & Role Handlers
│   ├── Services/          # AuthService, BillingService, dll
│   └── Views/             # Blade/PHP View templates
│       ├── auth/          # Login & session views
│       ├── billing/       # Invoices, Payments, Receivables, Invoice detail
│       ├── customers/     # Customer list, registration, edit, CSV import
│       ├── dashboard/     # Dashboard & analytics overview
│       └── layouts/       # Main layout, Sidebar, Navbar & Footer
├── config/                # Konfigurasi database & global app
├── database/              # Schema SQL, Seeders, & SQLite database file
├── public/                # Assets publik (CSS, JS, Fonts, Images)
├── storage/               # Logs, backup database, attachments, dan file generated
├── index.php              # Application Router & Entry Point
├── data-pelanggan .csv    # Contoh data 278 pelanggan bawaan
├── .gitignore             # Git ignore rules
├── LICENSE                # MIT License
└── README.md              # Dokumentasi proyek
```

---

## 📄 Format CSV Impor Data Pelanggan

Untuk mengimpor data pelanggan secara massal, gunakan format kolom berikut:
```csv
ID Pelanggan,Nama Lengkap,No Handphone,WhatsApp,Email,PIC Wilayah,Alamat Pemasangan,Area Coverage,Paket Internet,Tarif Bulanan,Siklus Penagihan,Kode Port ODP,PPPoE Username,PPPoE Password,Status Langganan
```

*Contoh 3 Baris Data:*
```csv
CUST-001,"VERY PRASETYO",081234567891,081234567891,very.prasetyo@gmail.com,"Ahmad Fauzi","Jl. Sekembang Raya No. 12 RT 01/RW 02",SEKEMBANG,"PAKET HEMAT",100000,"Tagihan Tgl 10","ODP-SKB-001 Port 2",very_net,pass1234,Aktif
CUST-002,"NURUL HIDAYAH",085712345678,085712345678,nurul.hidayah@gmail.com,"Rudi Hartono","Dusun Prangkokan RT 03/RW 01",PRANGKOKAN,"PAKET HEMAT",100000,"Tagihan Tgl 10","ODP-PRK-002 Port 4",nurul_net,pass5678,Aktif
CUST-003,SRIMULYANI,087811223344,087811223344,srimulyani@gmail.com,"Ahmad Fauzi","Perum Grand Galaxy Blok A5 No. 8","GRAND GALAXY","HOME FIBER 20M",150000,"Tagihan Tgl 15","ODP-GLX-001 Port 1",sri_galaxy,pass9012,Isolir
```

---

## 📝 Lisensi (License)

Proyek ini dilisensikan di bawah [MIT License](LICENSE).
Bebas digunakan dan dikembangkan untuk kebutuhan operasional ISP / RT-RW Net mandiri maupun komersial.
