<?php
// app/Views/rab/index.php - RAB Project Budgeting
?>
<div class="flex flex-wrap -mx-3">
  
  <!-- Add RAB Form -->
  <div class="w-full max-w-full px-3 mb-6 lg:w-4/12 lg:mb-0">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <h5 class="font-bold text-slate-800 text-base mb-1">Ajukan RAB Proyek Baru</h5>
      <p class="text-xs text-slate-400 mb-4">Perencanaan anggaran ekspansi jaringan, ODP, atau pengadaan</p>

      <form method="POST" action="<?php echo Helper::url('rab'); ?>" class="space-y-3">
        <?php echo Helper::csrfField(); ?>
        <input type="hidden" name="action" value="save_rab">

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nama Proyek / Kegiatan</label>
          <input type="text" name="project_name" required placeholder="contoh: Penarikan Feeder ODP Blok D" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kategori RAB</label>
          <select name="category_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo Helper::e($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Lokasi Proyek</label>
            <input type="text" name="location" placeholder="Perum Grand Galaxy" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PIC Lapangan</label>
            <input type="text" name="pic_name" placeholder="Ahmad Fauzi" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Total Estimasi Anggaran (Rp)</label>
          <input type="number" name="budget_total" required placeholder="8500000" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:border-purple-500">
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Tgl Mulai</label>
            <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Tgl Target Selesai</label>
            <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Rincian & Justifikasi</label>
          <textarea name="description" rows="2" placeholder="Uraian kebutuhan material, tiang, dropcore..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-paper-plane mr-1"></i> Ajukan RAB
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- RAB List Table -->
  <div class="w-full max-w-full px-3 lg:w-8/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
        <h5 class="font-bold text-slate-800 text-lg">Daftar RAB & Monitoring Realisasi</h5>
        <p class="text-xs text-slate-400">Pengawasan anggaran proyek dan perbandingan realisasi biaya</p>
      </div>

      <div class="flex-auto px-0 pt-0 pb-2">
        <div class="p-0 overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. RAB & Proyek</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Lokasi & PIC</th>
                <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Anggaran (Rp)</th>
                <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Realisasi (Rp)</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rabs as $r): ?>
              <tr>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="text-xs font-bold text-slate-800 block"><?php echo Helper::e($r['project_name']); ?></span>
                  <span class="text-2xs text-purple-700 font-mono font-bold"><?php echo Helper::e($r['rab_no']); ?></span>
                  <span class="text-2xs text-slate-400">&bull; <?php echo Helper::e($r['category_name']); ?></span>
                </td>

                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <span class="text-slate-700 font-semibold block"><?php echo Helper::e($r['location']); ?></span>
                  <span class="text-2xs text-slate-400">PIC: <?php echo Helper::e($r['pic_name'] ?: '-'); ?></span>
                </td>

                <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold text-slate-900 font-mono">
                  <?php echo Helper::formatRupiah($r['budget_total']); ?>
                </td>

                <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold text-purple-700 font-mono">
                  <?php echo Helper::formatRupiah($r['realized_total']); ?>
                </td>

                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                  <?php echo Helper::statusBadge($r['status']); ?>
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
