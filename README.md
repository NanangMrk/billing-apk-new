<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0%20%7C%208.1%20%7C%208.2%20%7C%208.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Database-SQLite%203%20(WAL%20Mode)-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite Database">
  <img src="https://img.shields.io/badge/Frontend-Tailwind%20CSS%20%2B%20Soft%20UI-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Mobile-Android%20Native%20(SDK%2024--34)-3DDC84?style=for-the-badge&logo=android&logoColor=white" alt="Android">
  <img src="https://img.shields.io/badge/Security-HMAC--SHA256%20%2B%20CSRF-E11D48?style=for-the-badge&logo=auth0&logoColor=white" alt="Security">
  <img src="https://img.shields.io/badge/License-MIT-22C55E?style=for-the-badge" alt="License MIT">
</p>

<h1 align="center">📡 NusantaraNet Billing & ISP Management System</h1>

<p align="center">
  <b>Sistem Manajemen Operasional, Pelanggan, Billing Otomatis, Keuangan, Kasir, Inventaris, dan Pengawasan Jaringan Terintegrasi untuk ISP (Internet Service Provider) & RT/RW Net.</b>
  <br>
  <i>Dilengkapi aplikasi Android Native Wrapper dengan Login Permanen (Tanpa Logout Otomatis) & Auto-Bypass Landing Page.</i>
</p>

---

## 📑 Daftar Isi (Table of Contents)
1. [Teknologi & Versi yang Digunakan](#-teknologi--versi-yang-digunakan-tech-stack)
2. [Fitur Utama & Modul Sistem](#-fitur-utama--modul-sistem)
3. [Panduan Penggunaan Lengkap](#-panduan-penggunaan-lengkap-user-guide)
4. [Akun Pengguna Bawaan (Default Credentials)](#-akun-pengguna-bawaan-default-credentials)
5. [Panduan Instalasi Detail (Step-by-Step)](#-panduan-instalasi-detail-step-by-step)
   - [Opsi A: Windows Menggunakan Laragon](#opsi-a-instalasi-di-windows-menggunakan-laragon)
   - [Opsi B: Linux VPS (Ubuntu / Debian + Nginx)](#opsi-b-instalasi-di-linux-vps-ubuntu--debian--nginx)
   - [Opsi C: aaPanel (Linux Web Hosting Control Panel)](#opsi-c-instalasi-di-aapanel-control-panel)
6. [Panduan Build APK Android](#-panduan-build-apk-android)
7. [Struktur Direktori Proyek](#-struktur-direktori-proyek)
8. [Lisensi](#-lisensi)

---

## 💻 Teknologi & Versi yang Digunakan (Tech Stack)

Aplikasi ini dibangun menggunakan arsitektur **Lightweight Native MVC** tanpa *framework* berat (seperti Laravel/CodeIgniter) untuk menjamin **performa ultra-cepat, konsumsi RAM mendekati 0 MB, dan kemudahan deployment tanpa ribet dependency**.

| Komponen | Teknologi | Versi Minimal / Rekomendasi | Keterangan |
| :--- | :--- | :--- | :--- |
| **Backend Engine** | PHP Core Native MVC | **PHP 8.0 s/d PHP 8.4+** *(Rekomendasi PHP 8.2)* | OOP, Typed Properties, Fast Routing |
| **Database Engine** | SQLite 3 (`pdo_sqlite`) | **SQLite 3.35+** | Mode **WAL (Write-Ahead Logging)** aktif, Zero-Config, Single file `database/isp.sqlite` |
| **Styling & UI** | Tailwind CSS + Soft UI Dashboard | **Tailwind v3.x** | Glassmorphism, Micro-animations, Fully Responsive |
| **Ikon & Font** | FontAwesome 6 Free + Google Inter | **FontAwesome 6.4+** | Ikon visual modern |
| **Visualisasi Grafik**| Chart.js | **Chart.js v4.x** | Grafik Interaktif Kas, Pendapatan, dan Pelanggan |
| **Mobile App (APK)** | Android Native Java | **Android SDK 24 s/d 34 (Android 7.0 - Android 14+)** | Gradle 8.5, AndroidX, Persistent WebKit CookieManager |
| **Keamanan Sesi** | Session Engine + Token HMAC | **HMAC-SHA256 Token (5 Tahun Lifetime)** | Anti-Logout Otomatis, Auto-Restore Session |

---

## 🌟 Fitur Utama & Modul Sistem

### 1. 👥 Manajemen Pelanggan (Customer Lifecycle)
- **Data Lengkap**: Nama, Kontak/WhatsApp, Email, PIC Penanggung Jawab, Area/Coverage, Paket Langganan, Alamat Pemasangan, Titik ODP/Port, Kredensial PPPoE (Username & Password).
- **Inline Quick Status Toggle**: Ubah status pelanggan (*Aktif, Isolir/Suspended, Nonaktif*) 1-klik via AJAX tanpa reload.
- **Import & Export CSV**:
  - Template CSV 15 kolom terstandarisasi.
  - Auto-generate Paket, Lokasi, dan PIC otomatis saat impor data baru.
- **Master Data Terpadu**: Master Paket Internet, Master Wilayah/Coverage ODP, Master PIC/Koordinator Lapangan.

### 2. 🧾 Billing, Tagihan & Piutang (Invoicing & Aging)
- **Auto-Billing Massal**: Terbitkan tagihan massal untuk seluruh pelanggan aktif sesuai siklus tanggal jatuh tempo masing-masing.
- **Pembuatan Tagihan Manual**: Fleksibel untuk pemasangan baru, upgrade paket, denda, atau biaya ad-hoc.
- **Aging Analysis (Pengawasan Umur Piutang)**:
  - 🟠 *1 - 7 Hari*: Kategori pengingat ramah.
  - 🔴 *8 - 30 Hari*: Kategori isolir layanan.
  - 🚨 *> 30 Hari*: Tindakan kunjungan teknisi lapangan.
- **Pengingat WhatsApp Otomatis**: Generate link WhatsApp (`wa.me`) otomatis dengan pesan tagihan rapi, detail nomor invoice, nominal, rekening bank, dan tanggal jatuh tempo.
- **Cetak Nota/Invoice Resmi**: Format cetak profesional siap kirim/cetak termal/PDF.

### 3. 💰 Keuangan & Kas (Cashflow & Accounts)
- **Multi-Akun Kas & Bank**: Akun BCA, Mandiri, BRI, Kas Tunai Kantor, dan Kas Lapangan.
- **Pencatatan Pemasukan & Pengeluaran**: Tracking real-time arus dana masuk (tagihan, pasang baru) dan operasional keluar (bandwidth, bensin, perangkat).
- **Laporan Arus Kas (Cash Flow)** & Export/Import CSV transaksi.

### 4. 👷 Penggajian & Komisi (Payroll)
- Rekapitulasi gaji bulanan teknisi & staff.
- Kalkulasi bonus komisi pemasangan baru dan insentif penagihan lapangan.
- Cetak slip gaji karyawan.

### 5. 🏗️ Rencana Anggaran Biaya (RAB Proyek)
- Penyusunan anggaran proyek penarikan kabel FO, pemasangan OLT/ODP baru, atau pengadaan tiang.
- Pengawasan realisasi biaya riil vs estimasi dan status approval direktur.

### 6. 📦 Inventaris & Aset Jaringan
- Manajemen Stok Masuk (Goods In) & Stok Keluar (Goods Out) untuk teknisi.
- Pengawasan stok minimum (*Low Stock Alert*).
- Master Aset Kantor & Jaringan (Router, Switch, OLT, Fusion Splicer, Tangga, dsb) lengkap dengan Export PDF/CSV.

### 7. 📈 Laporan Laba Rugi (Profit & Loss)
- Rekapitulasi finansial otomatis: *Gross Revenue - Beban Pokok - Beban Operasional = Net Operating Profit*.

### 8. 🎫 Ticketing & Layanan Pelanggan
- Pencatatan keluhan/gangguan koneksi internet pelanggan, penugasan teknisi, tracking status (*Open, In Progress, Resolved*).

### 9. 🤖 AI ISP Assistant
- Asisten cerdas untuk konsultasi operasional, analisis tren keuangan, rekomendasi pencegahan pelanggan churn, dan efisiensi jaringan.

### 10. 🛡️ Keamanan & Hak Akses (Role-Based Access Control)
- **Level Role**: Super Admin, Direktur/Owner, Kasir/Finance, Teknisi Lapangan, PIC Wilayah.
- **Persistent Session & Remember Token**: Sesi login aman hingga 5 tahun, tidak pernah logout otomatis saat aplikasi ditutup atau HP direstart.
- **Audit Logs**: Rekam jejak seluruh aktivitas user (Login, Logout, Insert, Update, Delete) beserta IP dan User Agent.

---

## 📖 Panduan Penggunaan Lengkap (User Guide)

### 1. Alur Login & Hak Akses
1. Akses aplikasi melalui browser atau buka **Aplikasi APK Android**.
2. Masukkan Username / Email dan Password.
3. Centang opsi **"Ingat sesi saya secara permanen"** (aktif secara default).
4. Setelah login berhasil:
   - Sesi akan tersimpan permanen di perangkat.
   - Jika Anda membuka URL utama `https://billing.nanangmrk.id/`, sistem akan **langsung mem-bypass landing page** dan masuk ke Dashboard.

### 2. Alur Penerbitan Tagihan Bulanan (Billing Cycle)
1. Buka menu **Billing & Tagihan** > **Siklus Penagihan**.
2. Pilih siklus tanggal tagihan (misal: *Tagihan Tgl 10* atau *Tagihan Tgl 20*).
3. Klik tombol **"Generate Tagihan Massal"**. Sistem otomatis membuatkan invoice untuk semua pelanggan aktif di siklus tersebut.
4. Buka menu **Daftar Tagihan (Invoices)** untuk memantau status pembayaran (*Belum Bayar, Lunas, Isolir*).

### 3. Alur Pencatatan Pembayaran
1. Buka menu **Tagihan** atau **Pengawasan Piutang**.
2. Temukan nama pelanggan, lalu klik tombol **"Catat Pembayaran"** (Ikon Kartu Kredit/Centang).
3. Pilih Akun Kas/Bank penerima (misal: *BCA Perusahaan* atau *Kas Tunai*), masukkan nominal yang diterima, dan tanggal bayar.
4. Klik **Simpan Pembayaran**. Tagihan otomatis berstatus *Lunas* dan saldo kas bertambah.

### 4. Alur Kirim Pengingat WhatsApp
1. Buka menu **Pengawasan Piutang (Receivables)**.
2. Di baris tagihan pelanggan yang belum bayar, klik tombol **WhatsApp**.
3. WhatsApp akan otomatis terbuka dengan format pesan tagihan siap kirim ke nomor HP pelanggan.

### 5. Alur Impor Data Pelanggan Baru Massal
1. Buka menu **Data Pelanggan** > klik tombol **Import CSV**.
2. Unduh template CSV yang disediakan.
3. Isi data sesuai kolom (Nama, No HP, WhatsApp, Paket, Alamat, Siklus, ODP, PPPoE).
4. Upload file CSV dan klik **Mulai Impor Data**.

---

## 🔑 Akun Pengguna Bawaan (Default Credentials)

Aplikasi telah diinisialisasi dengan satu akun Super Administrator utama. Anda dapat menambahkan pengguna/staff lainnya dari nol melalui menu **Pengaturan** > **Manajemen Pengguna**:

| Role / Jabatan | Username / Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Super Administrator** | `admin@email.com` | `password123` | Akses penuh ke seluruh modul, pengaturan sistem, manajemen user & audit logs |

---

## 🚀 Panduan Instalasi Detail (Step-by-Step)

---

### Opsi A: Instalasi di Windows Menggunakan Laragon

**Laragon** adalah web server stack paling ringan dan direkomendasikan untuk Windows.

#### Langkah 1: Siapkan Laragon
1. Download dan instal [Laragon](https://laragon.org/download/) (pilih edisi *Laragon Full* atau *Laragon WAMP*).
2. Pastikan PHP yang aktif adalah **PHP 8.1 / 8.2 / 8.3**.

#### Langkah 2: Tempatkan Proyek
Letakkan folder proyek di dalam direktori `www` Laragon:
```text
C:\laragon\www\billing-apk-new
```

#### Langkah 3: Aktifkan Ekstensi SQLite di PHP
1. Buka Laragon > Klik Kanan > **PHP** > **Extensions**.
2. Pastikan ekstensi berikut memiliki tanda centang (aktif):
   - `pdo_sqlite`
   - `sqlite3`
   - `fileinfo`
   - `mbstring`

#### Langkah 4: Jalankan Server & Inisialisasi Database
1. Buka Laragon dan klik **Start All**.
2. Klik tombol **Terminal** di Laragon, lalu jalankan perintah seeding:
   ```bash
   cd C:\laragon\www\billing-apk-new
   php database/seed.php
   ```
3. Buka browser dan akses:
   - **`http://localhost/billing-apk-new`** atau
   - **`http://billing-apk-new.test`** (jika fitur Pretty URL Laragon aktif).

---

### Opsi B: Instalasi di Linux VPS (Ubuntu / Debian + Nginx)

Panduan deployment pada VPS Linux menggunakan Nginx dan PHP-FPM.

#### Langkah 1: Update Server & Install Paket yang Dibutuhkan
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git curl unzip sqlite3
sudo apt install -y php8.2-fpm php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip
```

#### Langkah 2: Clone Repositori
```bash
cd /var/www
sudo git clone https://github.com/USERNAME/billing-apk-new.git billing
sudo chown -R www-data:www-data /var/www/billing
sudo chmod -R 775 /var/www/billing/database /var/www/billing/storage
```

#### Langkah 3: Inisialisasi Database
```bash
cd /var/www/billing
sudo -u www-data php database/seed.php
```

#### Langkah 4: Konfigurasi VirtualHost Nginx
Buat file konfigurasi server:
```bash
sudo nano /etc/nginx/sites-available/billing.conf
```

Isikan konfigurasi berikut (sesuaikan `server_name` dengan domain Anda):
```nginx
server {
    listen 80;
    server_name billing.nanangmrk.id;
    root /var/www/billing;

    index index.php index.html;
    charset utf-8;

    # Proteksi file database SQLite dan storage sensitif
    location ~* /(database|storage|config)/ {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan konfigurasi dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/billing.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Langkah 5: Pasang SSL HTTPS (Let's Encrypt Certbot)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d billing.nanangmrk.id
```

---

### Opsi C: Instalasi di aaPanel Control Panel

Panduan instalasi pada server Linux yang menggunakan panel **aaPanel**.

#### Langkah 1: Persiapan Environment di aaPanel
1. Login ke dashboard **aaPanel** Anda.
2. Buka menu **App Store**:
   - Pastikan telah terinstal **Nginx** (v1.22+).
   - Install **PHP-8.2** (atau PHP-8.1 / PHP-8.3).
3. Buka pengaturan **PHP-8.2** > Masuk ke tab **Extensions**:
   - Pastikan ekstensi `sqlite3`, `pdo_sqlite`, `fileinfo` terpasang (*Installed*).

#### Langkah 2: Menambahkan Website Baru
1. Buka menu **Website** > Klik **Add Site**.
2. Masukkan nama domain Anda: `billing.nanangmrk.id`.
3. Pada pilihan **PHP Version**, pilih **PHP-82**.
4. Klik **Submit**.

#### Langkah 3: Upload & Ekstrak File Aplikasi
1. Buka menu **Files** di aaPanel > Buka folder website Anda:
   `/www/wwwroot/billing.nanangmrk.id`
2. Upload file proyek ini (atau gunakan tab Terminal aaPanel untuk `git clone`).
3. Pastikan seluruh struktur folder (`app`, `config`, `database`, `public`, `index.php`) berada langsung di dalam root folder website tersebut.

#### Langkah 4: Mengatur Permissions & Hak Akses
1. Di menu **Files** aaPanel, pastikan owner dari folder website adalah `www` : `www`.
2. Klik kanan pada folder `database` > **Permission** > Set ke `775`.
3. Klik kanan pada folder `storage` > **Permission** > Set ke `775`.

#### Langkah 5: Mengatur URL Rewrite & Proteksi Database
1. Buka menu **Website** > Klik nama domain Anda > Masuk ke menu **URL Rewrite**.
2. Masukkan konfigurasi Nginx berikut:
   ```nginx
   location ~* /(database|storage|config)/ {
       deny all;
       return 404;
   }

   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
3. Klik **Save**.

#### Langkah 6: Mengaktifkan SSL (HTTPS)
1. Di menu pengaturan website yang sama, buka tab **SSL**.
2. Pilih **Let's Encrypt**, centang domain Anda, lalu klik **Apply**.
3. Aktifkan saklar **Force HTTPS**.

#### Langkah 7: Inisialisasi Database
Buka tab **Terminal** di aaPanel, lalu jalankan:
```bash
cd /www/wwwroot/billing.nanangmrk.id
php database/seed.php
```

Aplikasi kini telah online dan siap digunakan di `https://billing.nanangmrk.id`!

---

## 📱 Panduan Build APK Android

Proyek ini telah dilengkapi dengan source code project Android Studio native pada folder [`android-app/`](android-app/).

### Fitur Spesial APK:
* **Persistent Session**: Menggunakan `CookieManager.flush()` sehingga sesi login tetap aktif selamanya meski aplikasi di-close atau HP direstart.
* **Auto-Bypass Landing Page**: Aplikasi otomatis melompati halaman promosi dan langsung membuka Dashboard aplikasi.
* **Camera & File Upload**: Mendukung upload bukti transfer bank, foto instalasi/KTP, dan file CSV langsung dari kamera HP atau galeri.
* **Pull-to-Refresh**: Tarik layar ke bawah untuk menyegarkan tampilan.
* **Offline Screen**: Tampilan ramah saat koneksi data/WiFi terputus dengan tombol *Coba Lagi*.

### Cara Build Menjadi File APK (.apk):
1. Unduh dan buka software **Android Studio** di komputer Anda.
2. Klik **File** > **Open** > arahkan ke folder:
   ```text
   c:\laragon\www\billing-apk-new\android-app
   ```
3. Tunggu hingga proses sync Gradle selesai secara otomatis.
4. Untuk menghasilkan file `.apk`:
   - Klik menu **Build** > **Build Bundle(s) / APK(s)** > **Build APK(s)**.
5. Setelah notifikasi *Build APK(s) successfully* muncul di pojok kanan bawah, klik **locate**.
6. File `app-debug.apk` (atau `app-release.apk`) siap dikirim dan diinstal ke HP Android!

---

## 📁 Struktur Direktori Proyek

```text
billing-apk-new/
├── android-app/           # Source code Project Android Studio (APK Native Wrapper)
│   ├── app/               # Modul aplikasi Android (Manifest, Java, Resources)
│   ├── build.gradle       # Gradle build configuration
│   └── README.md          # Panduan khusus modul Android
├── app/
│   ├── Controllers/       # Controller logic (Auth, Customer, Billing, Finance, dll)
│   ├── Helpers/           # Global Helper & CSRF Verifier (Helper.php)
│   ├── Middleware/        # Gatekeeper Auth & Role Middleware (AuthMiddleware.php)
│   ├── Services/          # AuthService (HMAC Session), AiAssistantService, Database.php
│   └── Views/             # Template View Soft UI Dashboard & Landing
│       ├── ai/            # Tampilan AI Assistant
│       ├── assets/        # Manajemen Aset & Inventaris
│       ├── auth/          # Halaman Login
│       ├── billing/       # Tagihan, Faktur, Pembayaran, Siklus, Aging
│       ├── customers/     # Data Pelanggan, Paket, Lokasi, PIC
│       ├── dashboard/     # Dashboard Eksekutif & Statistik
│       ├── finance/       # Kas & Transaksi Finansial
│       ├── inventory/     # Barang Masuk & Keluar
│       ├── landing/       # Landing page publik
│       ├── layouts/       # Main, Auth, dan Landing Layout
│       ├── partials/      # Navbar, Sidebar, Footer, Mobile Navigation
│       ├── payroll/       # Penggajian Karyawan
│       ├── profit_loss/   # Laporan Laba Rugi
│       ├── rab/           # Rencana Anggaran Biaya Proyek
│       ├── settings/      # Profil Perusahaan, Manajemen User & Audit Logs
│       └── tickets/       # Layanan Pengaduan Gangguan
├── config/                # Konfigurasi Database PDO SQLite (database.php)
├── database/              # File Database SQLite (isp.sqlite), Schema, dan Seeder
├── public/                # Asset statis publik (CSS, JS, Fonts, Gambar, Uploads)
├── storage/               # Penyimpanan logs, dokumen export, dan backup
├── data-pelanggan .csv    # Data dummy 278 pelanggan contoh
├── index.php              # Entry point utama & Route Dispatcher
├── LICENSE                # Lisensi MIT
└── README.md              # Dokumentasi lengkap sistem
```

---

## 📄 Format CSV Impor Data Pelanggan

Untuk mengimpor data pelanggan secara massal, gunakan format 15 kolom berikut:

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

## 📝 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).
Bebas digunakan, dimodifikasi, dan didistribusikan untuk kebutuhan operasional ISP / RT-RW Net mandiri maupun komersial.
