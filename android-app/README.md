# Project Android APK - Billing ISP (billing.nanangmrk.id)

Aplikasi Android WebView native yang dikonfigurasi khusus untuk portal ISP Billing dengan dukungan **Login Permanen (tanpa logout otomatis)**, **Auto-Bypass Landing Page**, upload file/kamera, dan penanganan offline.

---

## 📱 Fitur Utama Aplikasi Android
1. **Persistent Session / Permanent Login**:
   - `CookieManager` diset permanen (`setAcceptCookie` & `flush()`).
   - Sesi dan token autentikasi disimpan di storage lokal HP sehingga tidak akan logout meskipun aplikasi di-close total atau HP direstart.
2. **Auto-Bypass Landing Page**:
   - Membuka langsung ke `https://billing.nanangmrk.id/` dan server otomatis me-redirect ke Dashboard utama.
3. **File Upload & Camera Picker**:
   - Mendukung upload bukti transfer bank, foto instalasi/KTP, dan file CSV langsung melalui kamera atau galeri file.
4. **Pull-to-Refresh & Back Navigation**:
   - Tarik layar ke bawah untuk reload halaman secara halus.
   - Tombol back di HP akan kembali ke halaman web sebelumnya (bukan langsung keluar aplikasi).
5. **Offline Fallback Screen**:
   - Tampilan ramah pengguna saat koneksi internet terputus dengan tombol coba lagi.

---

## 🛠️ Cara Build Menjadi File APK (.apk)

### Cara 1: Menggunakan Android Studio (Sangat Mudah & Direkomendasikan)
1. Buka software **Android Studio**.
2. Pilih menu **File** > **Open**, lalu arahkan ke folder:
   `c:\laragon\www\billing-apk-new\android-app`
3. Tunggu hingga Gradle selesai melakukan sync otomatis.
4. Untuk membuat file APK:
   - Klik menu **Build** > **Build Bundle(s) / APK(s)** > **Build APK(s)**.
   - Setelah selesai, klik tulisan **locate** pada notifikasi di pojok kanan bawah.
   - File APK siap di-install di HP Android Anda! (Lokasi file: `android-app/app/build/outputs/apk/debug/app-debug.apk`).

---

### Cara 2: Menggunakan Command Line (Gradle)
Jika Anda memiliki JDK/Java terinstal di komputer:
```bash
cd c:\laragon\www\billing-apk-new\android-app
gradlew assembleDebug
```
File APK akan otomatis ter-generate di:
`app/build/outputs/apk/debug/app-debug.apk`

---

## ⚙️ Mengubah URL Server (Jika Diperlukan)
Jika ingin mengubah domain atau URL tujuan aplikasi, cukup edit file:
`app/src/main/res/values/strings.xml`
```xml
<string name="web_url">https://billing.nanangmrk.id/</string>
```
