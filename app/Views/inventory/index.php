<?php
// app/Views/inventory/index.php - Inventory Catalog
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Buttons -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Katalog Stok Persediaan Gudang</h4>
      <p class="text-2xs text-slate-400">Monitoring persediaan kabel fiber optik, perangkat modem ONT, dan aksesoris</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="<?php echo Helper::url('goods_in'); ?>" class="px-3.5 py-2.5 bg-green-50 hover:bg-green-100 text-green-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-dolly"></i>
        <span>Barang Masuk</span>
      </a>
      <a href="<?php echo Helper::url('goods_out'); ?>" class="px-3.5 py-2.5 bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-truck-ramp-box"></i>
        <span>Barang Keluar</span>
      </a>
      <button type="button" onclick="openItemModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Tambah Barang</span>
      </button>
    </div>
  </div>

  <!-- Items Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Barang & SKU</th>
          <th class="py-3 px-4 font-bold">Kategori</th>
          <th class="py-3 px-4 font-bold text-right">Harga Beli</th>
          <th class="py-3 px-4 font-bold text-center">Sisa Stok</th>
          <th class="py-3 px-4 font-bold text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($items as $item): 
          $isLow = ($item['current_stock'] <= $item['min_stock']);
        ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-box"></i>
              </div>
              <div>
                <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($item['name']); ?></span>
                <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($item['sku']); ?> &bull; <?php echo Helper::e($item['brand'] ?: 'Generic'); ?></span>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
            <?php echo Helper::e($item['category_name']); ?>
          </td>

          <td class="py-3.5 px-4 text-right whitespace-nowrap text-xs font-bold text-slate-800 font-mono">
            <?php echo Helper::formatRupiah($item['purchase_price']); ?>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono">
            <span class="font-bold <?php echo $isLow ? 'text-red-600' : 'text-slate-900'; ?>">
              <?php echo $item['current_stock']; ?> <?php echo Helper::e($item['unit']); ?>
            </span>
            <span class="text-3xs text-slate-400 block font-normal">(Min: <?php echo $item['min_stock']; ?>)</span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <?php if ($item['current_stock'] <= 0): ?>
              <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-rose-50 text-rose-700 border border-rose-200 uppercase">Habis</span>
            <?php elseif ($isLow): ?>
              <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-amber-50 text-amber-700 border border-amber-200 uppercase">Stok Menipis</span>
            <?php else: ?>
              <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Aman</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Tambah Barang -->
<div id="itemModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="itemModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Master Barang</h4>
          <span class="text-2xs text-slate-400">Input katalog material fiber optik atau perangkat</span>
        </div>
      </div>
      <button type="button" onclick="closeItemModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('inventory'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_item">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Barang <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: ONT Huawei HG8245H" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kategori</label>
          <select name="category_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo Helper::e($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Brand / Merek</label>
          <input type="text" name="brand" placeholder="Huawei / ZTE" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Satuan</label>
          <input type="text" name="unit" value="unit" placeholder="unit / pcs / roll" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli Estimasi (Rp)</label>
          <input type="number" name="purchase_price" placeholder="175000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Min. Stok Warning</label>
          <input type="number" name="min_stock" value="5" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Stok Awal</label>
          <input type="number" name="current_stock" value="0" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeItemModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Barang
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openItemModal() {
  const modal = document.getElementById("itemModal");
  const content = document.getElementById("itemModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeItemModal() {
  const modal = document.getElementById("itemModal");
  const content = document.getElementById("itemModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("itemModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closeItemModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeItemModal();
  });
});
</script>
