<?php
// app/Views/inventory/suppliers.php - Suppliers Master Data
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Supplier & Vendor Logistik</h4>
      <p class="text-2xs text-slate-400">Kontak vendor pengadaan hardware, kabel fiber optik, dan perangkat jaringan</p>
    </div>

    <button type="button" onclick="openSupplierModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Tambah Supplier Baru</span>
    </button>
  </div>

  <!-- Suppliers Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Supplier</th>
          <th class="py-3 px-4 font-bold">Kontak Sales / PIC</th>
          <th class="py-3 px-4 font-bold">Alamat Kantor / Gudang</th>
          <th class="py-3 px-4 font-bold text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($suppliers as $s): ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-truck-field"></i>
              </div>
              <div>
                <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($s['name']); ?></span>
                <span class="text-3xs text-purple-700 font-semibold"><?php echo Helper::e($s['company'] ?: 'Vendor Hardware'); ?></span>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs">
            <span class="font-bold text-slate-800 block"><?php echo Helper::e($s['contact_person'] ?: '-'); ?></span>
            <span class="text-2xs text-slate-500 font-mono"><?php echo Helper::e($s['phone'] ?: '-'); ?></span>
          </td>

          <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs truncate">
            <?php echo Helper::e($s['address'] ?: '-'); ?>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Aktif</span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Tambah Supplier -->
<div id="supplierModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="supplierModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-truck-field"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Supplier Vendor</h4>
          <span class="text-2xs text-slate-400">Pemasok material fiber optik, OLT, dan kabel</span>
        </div>
      </div>
      <button type="button" onclick="closeSupplierModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('suppliers'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_supplier">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Supplier / PT <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: PT Optik Solusi Indonesia" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Contact Person (PIC)</label>
          <input type="text" name="contact_person" placeholder="Bapak Surya (Sales)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">No. Telepon / WA</label>
          <input type="text" name="phone" placeholder="081299881100" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Kantor / Gudang</label>
        <textarea name="address" rows="2" placeholder="Alamat supplier..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeSupplierModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Supplier
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openSupplierModal() {
  const modal = document.getElementById("supplierModal");
  const content = document.getElementById("supplierModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeSupplierModal() {
  const modal = document.getElementById("supplierModal");
  const content = document.getElementById("supplierModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("supplierModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closeSupplierModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeSupplierModal();
  });
});
</script>
