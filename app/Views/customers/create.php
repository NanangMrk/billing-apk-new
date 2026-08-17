<?php
// app/Views/customers/create.php - Create New Customer Form
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 mx-auto lg:w-9/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
      
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex items-center justify-between">
        <div>
          <h5 class="mb-0 font-bold text-slate-800 text-lg">Form Registrasi Pelanggan Baru</h5>
          <p class="text-xs text-slate-400">Lengkapi data pelanggan untuk aktivasi layanan internet</p>
        </div>
        <a href="<?php echo Helper::url('customers'); ?>" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
          <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>

      <div class="p-6">
        <form method="POST" action="<?php echo Helper::url('customers_create'); ?>" class="space-y-6">
          <?php echo Helper::csrfField(); ?>

          <!-- Section 1: Personal Information -->
          <div class="border-b border-slate-100 pb-4">
            <h6 class="text-xs font-bold uppercase tracking-wider text-purple-700 mb-3">1. Data Pribadi & Kontak</h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap / Perusahaan <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="contoh: Hendra Setiawan / CV Media Solusi" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone / WhatsApp <span class="text-red-500">*</span></label>
                <input type="text" name="phone" required placeholder="contoh: 081234567890" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Email</label>
                <input type="email" name="email" placeholder="contoh: pelanggan@email.com" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Koordinator / PIC Wilayah (Opsional)</label>
                <select name="pic_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
                  <option value="">-- Tanpa PIC / Mandiri --</option>
                  <?php foreach ($pics as $pic): ?>
                    <option value="<?php echo $pic['id']; ?>"><?php echo Helper::e($pic['name'] . ' (' . $pic['position'] . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Section 2: Service & Package -->
          <div class="border-b border-slate-100 pb-4">
            <h6 class="text-xs font-bold uppercase tracking-wider text-purple-700 mb-3">2. Paket Layanan & Siklus Billing</h6>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              
              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Pilihan Paket Internet <span class="text-red-500">*</span></label>
                <select name="package_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
                  <option value="">-- Pilih Paket --</option>
                  <?php foreach ($packages as $pkg): ?>
                    <option value="<?php echo $pkg['id']; ?>">
                      <?php echo Helper::e($pkg['name'] . ' - ' . Helper::formatRupiah($pkg['price'])); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Siklus Billing / Penagihan <span class="text-red-500">*</span></label>
                <select name="billing_cycle_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
                  <?php foreach ($cycles as $cyc): ?>
                    <option value="<?php echo $cyc['id']; ?>"><?php echo Helper::e($cyc['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Status Awal</label>
                <select name="status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
                  <option value="active">Aktif</option>
                  <option value="prospect">Prospek / Survei</option>
                  <option value="installation">Jadwal Pasang</option>
                </select>
              </div>

            </div>
          </div>

          <!-- Section 3: Technical & Address -->
          <div>
            <h6 class="text-xs font-bold uppercase tracking-wider text-purple-700 mb-3">3. Lokasi Pemasangan & Konfigurasi Jaringan</h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Area Jangkauan Coverage</label>
                <select name="location_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
                  <option value="">-- Pilih Wilayah --</option>
                  <?php foreach ($locations as $loc): ?>
                    <option value="<?php echo $loc['id']; ?>">
                      <?php echo Helper::e($loc['area_name'] . ' (' . $loc['city'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Titik ODP / Tiang Distribusi</label>
                <input type="text" name="odp_point" placeholder="contoh: ODP-GLX-001 Port 4" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>
            </div>

            <div class="mb-4">
              <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Lengkap Pemasangan <span class="text-red-500">*</span></label>
              <textarea name="full_address" required rows="2" placeholder="contoh: Jl. Mawar No. 12 RT 03 RW 05 Perum Grand Galaxy" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Username PPPoE (Opsional)</label>
                <input type="text" name="pppoe_username" placeholder="contoh: user_glx_01" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>
              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Password PPPoE (Opsional)</label>
                <input type="text" name="pppoe_password" placeholder="contoh: pass123" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>
            </div>
          </div>

          <div class="text-right pt-4 border-t border-slate-100">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
              <i class="fa-solid fa-save mr-1"></i> Simpan Data Pelanggan
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
