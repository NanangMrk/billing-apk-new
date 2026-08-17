<?php
// app/Views/inventory/goods_out.php - Goods Out Recording
?>
<div class="flex flex-wrap -mx-3">
  
  <!-- Goods Out Form -->
  <div class="w-full max-w-full px-3 mb-6 lg:w-4/12 lg:mb-0">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <h5 class="font-bold text-slate-800 text-base mb-1">Catat Barang Keluar</h5>
      <p class="text-xs text-slate-400 mb-4">Pengurangan stok untuk instalasi pelanggan atau proyek</p>

      <form method="POST" action="<?php echo Helper::url('goods_out'); ?>" class="space-y-3">
        <?php echo Helper::csrfField(); ?>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Item Barang</label>
          <select name="item_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="">-- Pilih Barang --</option>
            <?php foreach ($items as $it): ?>
              <option value="<?php echo $it['id']; ?>">
                <?php echo Helper::e($it['name'] . ' (Tersedia: ' . $it['current_stock'] . ' ' . $it['unit'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Jumlah Keluar</label>
            <input type="number" name="quantity" min="1" value="1" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Tujuan Distribusi</label>
            <select name="destination_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <option value="customer">Instalasi Pelanggan</option>
              <option value="technician">Operasional Teknisi</option>
              <option value="project">Proyek RAB / Backbone</option>
              <option value="damaged">Rusak / Rusak Fisik</option>
            </select>
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Penggunaan</label>
          <textarea name="notes" rows="2" placeholder="contoh: Dipasang di rumah pelanggan Anugrah / penarikan ODP..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-2.5 bg-gradient-to-tl from-orange-500 to-yellow-400 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-minus-circle mr-1"></i> Simpan Barang Keluar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Goods Out History -->
  <div class="w-full max-w-full px-3 lg:w-8/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
        <h5 class="font-bold text-slate-800 text-lg">Riwayat Pengeluaran Barang</h5>
        <p class="text-xs text-slate-400">Log pengurangan stok material dan perangkat</p>
      </div>

      <div class="flex-auto px-0 pt-0 pb-2">
        <div class="p-0 overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. Bukti & Item</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Jumlah Keluar</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tujuan / Catatan</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($history as $h): ?>
              <tr>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="text-xs font-bold text-purple-700 font-mono block"><?php echo Helper::e($h['transaction_no']); ?></span>
                  <span class="text-xs font-bold text-slate-800"><?php echo Helper::e($h['item_name']); ?></span>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap font-mono text-xs font-bold text-orange-600">
                  -<?php echo $h['quantity']; ?> <?php echo Helper::e($h['unit']); ?>
                </td>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <span class="font-semibold text-slate-700 uppercase text-2xs block"><?php echo Helper::e($h['destination_type']); ?></span>
                  <span class="text-2xs text-slate-400"><?php echo Helper::e($h['notes'] ?: '-'); ?></span>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                  <?php echo Helper::formatDate($h['transaction_date']); ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

</div>
