<?php
// app/Views/assets/index.php - Company Fixed Assets & CPE Tracking
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Aset & Peralatan Kerja</h4>
      <p class="text-2xs text-slate-400">Pengawasan serial number, kondisi perangkat, dan penanggung jawab alat</p>
    </div>

    <button type="button" onclick="openAssetModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Registrasi Aset Baru</span>
    </button>
  </div>

  <!-- Assets Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Aset & SN</th>
          <th class="py-3 px-4 font-bold">Lokasi & PIC Penanggung Jawab</th>
          <th class="py-3 px-4 font-bold text-right">Nilai Buku / Estimasi</th>
          <th class="py-3 px-4 font-bold text-center">Status Aset</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($assets as $ast): ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-laptop-code"></i>
              </div>
              <div>
                <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($ast['name']); ?></span>
                <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($ast['asset_no']); ?></span>
                <?php if (!empty($ast['serial_number'])): ?>
                  <span class="text-3xs text-slate-400 font-mono">&bull; SN: <?php echo Helper::e($ast['serial_number']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs">
            <span class="text-slate-900 font-bold block"><?php echo Helper::e($ast['location']); ?></span>
            <span class="text-3xs text-slate-400">PIC: <?php echo Helper::e($ast['pic_name'] ?: '-'); ?></span>
          </td>

          <td class="py-3.5 px-4 text-right whitespace-nowrap text-xs font-bold text-slate-900 font-mono">
            <?php echo Helper::formatRupiah($ast['current_value']); ?>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <?php echo Helper::statusBadge($ast['status']); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Tambah Aset -->
<div id="assetModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="assetModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-laptop-code"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Registrasi Aset Perusahaan</h4>
          <span class="text-2xs text-slate-400">Catat inventaris alat kerja (Splicer, OTDR) atau modem CPE</span>
        </div>
      </div>
      <button type="button" onclick="closeAssetModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('assets'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_asset">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Aset / Alat <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: Splicer Fujikura 88S / Mikrotik CCR1036" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Serial Number (SN)</label>
          <input type="text" name="serial_number" placeholder="SN-99881100" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">MAC Address</label>
          <input type="text" name="mac_address" placeholder="48:8F:5A:11:22:33" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli (Rp)</label>
          <input type="number" name="purchase_price" placeholder="45000000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Status Aset</label>
          <select name="status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="available">Tersedia di Gudang</option>
            <option value="in_use">Sedang Digunakan</option>
            <option value="assigned_customer">Dipinjamkan Pelanggan</option>
            <option value="maintenance">Perbaikan / Servis</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Lokasi Aset</label>
          <input type="text" name="location" placeholder="Gudang HQ / POP-01" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">PIC Penanggung Jawab</label>
          <input type="text" name="pic_name" placeholder="Ahmad Fauzi (Teknisi)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeAssetModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Aset
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openAssetModal() {
  const modal = document.getElementById("assetModal");
  const content = document.getElementById("assetModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeAssetModal() {
  const modal = document.getElementById("assetModal");
  const content = document.getElementById("assetModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("assetModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closeAssetModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeAssetModal();
  });
});
</script>
