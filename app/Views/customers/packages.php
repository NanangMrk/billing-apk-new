<?php
// app/Views/customers/packages.php - Internet Package Master Data
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Katalog Paket Internet</h4>
      <p class="text-2xs text-slate-400">Daftar paket internet aktif, kecepatan bandwidth, dan tarif bulanan</p>
    </div>

    <button type="button" onclick="openPackageModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Tambah Paket Baru</span>
    </button>
  </div>

  <!-- Package List Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Paket & Deskripsi</th>
          <th class="py-3 px-4 font-bold text-center">Kecepatan (DL / UL)</th>
          <th class="py-3 px-4 font-bold text-right">Tarif Bulanan</th>
          <th class="py-3 px-4 font-bold text-center">PPN / Biaya Pasang</th>
          <th class="py-3 px-4 font-bold text-center">Pelanggan Aktif</th>
          <th class="py-3 px-4 font-bold text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($packages as $pkg): ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-wifi"></i>
              </div>
              <div>
                <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($pkg['name']); ?></span>
                <span class="text-2xs text-slate-400 max-w-xs truncate block"><?php echo Helper::e($pkg['description'] ?: 'Layanan broadband fiber optic'); ?></span>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-700 font-mono font-bold text-xs">
              <?php echo Helper::e($pkg['download_speed']); ?> &bull; <?php echo Helper::e($pkg['upload_speed']); ?>
            </span>
          </td>

          <td class="py-3.5 px-4 text-right whitespace-nowrap">
            <span class="text-sm font-black text-slate-900 font-mono block"><?php echo Helper::formatRupiah($pkg['price']); ?></span>
            <span class="text-3xs text-slate-400">/ bulan</span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap text-2xs text-slate-600">
            <span class="font-bold text-slate-800">PPN <?php echo $pkg['tax_percent']; ?>%</span>
            <span class="text-slate-400 block">Pasang: <?php echo Helper::formatRupiah($pkg['installation_fee']); ?></span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-2xs">
              <?php echo $pkg['total_users']; ?> User
            </span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <?php echo Helper::statusBadge($pkg['status']); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Tambah Paket -->
<div id="packageModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="packageModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-wifi"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Paket Internet</h4>
          <span class="text-2xs text-slate-400">Buat tarif layanan baru untuk ditawarkan ke pelanggan</span>
        </div>
      </div>
      <button type="button" onclick="closePackageModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form method="POST" action="<?php echo Helper::url('packages'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_package">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Paket <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: Home Gamer 75 Mbps" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Download Speed <span class="text-red-500">*</span></label>
          <input type="text" name="download_speed" required placeholder="contoh: 75 Mbps" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Upload Speed <span class="text-red-500">*</span></label>
          <input type="text" name="upload_speed" required placeholder="contoh: 35 Mbps" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Tarif Bulanan (Rupiah) <span class="text-red-500">*</span></label>
        <input type="number" name="price" required placeholder="350000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold text-slate-900 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">PPN (%)</label>
          <input type="number" name="tax_percent" value="11" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Biaya Pasang Baru (Rp)</label>
          <input type="number" name="installation_fee" value="150000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Deskripsi Layanan (Opsional)</label>
        <textarea name="description" rows="2" placeholder="contoh: Paket internet ultra stabil untuk gaming & streaming 4K tanpa FUP" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closePackageModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Paket
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Control Script -->
<script>
function openPackageModal() {
  const modal = document.getElementById("packageModal");
  const content = document.getElementById("packageModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closePackageModal() {
  const modal = document.getElementById("packageModal");
  const content = document.getElementById("packageModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("packageModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) {
      closePackageModal();
    }
  });

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) {
      closePackageModal();
    }
  });
});
</script>
