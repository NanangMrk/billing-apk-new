<?php
// app/Views/rab/index.php - RAB Project Budgeting, Itemization & Realization
$totalBudget = (int)($summary['total_budget'] ?? 0);
$totalRealized = (int)($summary['total_realized'] ?? 0);
$countSubmitted = (int)($summary['count_submitted'] ?? 0);
$countApproved = (int)($summary['count_approved'] ?? 0);

$hasActiveFilters = !empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['category_id']) || !empty($_GET['month']);
?>

<div class="space-y-6">

  <!-- Summary Metric Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Plafon Anggaran -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xl flex items-center justify-between">
      <div>
        <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Total Plafon Anggaran</span>
        <h4 class="text-lg font-black text-slate-900 font-mono"><?php echo Helper::formatRupiah($totalBudget); ?></h4>
        <span class="text-3xs text-slate-500 font-semibold"><?php echo (int)($summary['total_count'] ?? count($rabs)); ?> total proyek</span>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
        <i class="fa-solid fa-calculator"></i>
      </div>
    </div>

    <!-- Total Realisasi Terpakai -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xl flex items-center justify-between">
      <div>
        <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Total Realisasi Biaya</span>
        <h4 class="text-lg font-black text-purple-700 font-mono"><?php echo Helper::formatRupiah($totalRealized); ?></h4>
        <?php $overallPct = $totalBudget > 0 ? round(($totalRealized / $totalBudget) * 100, 1) : 0; ?>
        <span class="text-3xs text-purple-600 font-bold"><?php echo $overallPct; ?>% terserap dari plafon</span>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
        <i class="fa-solid fa-receipt"></i>
      </div>
    </div>

    <!-- Menunggu Persetujuan -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xl flex items-center justify-between">
      <div>
        <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Menunggu Approval</span>
        <h4 class="text-lg font-black text-amber-600"><?php echo $countSubmitted; ?> Proyek</h4>
        <span class="text-3xs text-slate-500 font-semibold">Perlu persetujuan manajemen</span>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-amber-500 to-orange-400 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>
    </div>

    <!-- Disetujui / Berjalan -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xl flex items-center justify-between">
      <div>
        <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Siap / Berjalan</span>
        <h4 class="text-lg font-black text-emerald-600"><?php echo $countApproved; ?> Proyek</h4>
        <span class="text-3xs text-slate-500 font-semibold">Bisa diinput realisasinya</span>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-emerald-600 to-teal-400 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
        <i class="fa-solid fa-circle-check"></i>
      </div>
    </div>
  </div>

  <!-- Main Content Card -->
  <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
    
    <!-- Header with Title & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
      <div>
        <h4 class="font-black text-slate-900 text-lg tracking-tight">RAB Proyek & Monitoring Realisasi</h4>
        <p class="text-2xs text-slate-400">Pengajuan anggaran rincian barang, approval manajemen, dan pencatatan realisasi belanja proyek</p>
      </div>

      <?php if (AuthService::hasPermission('rab.create')): ?>
      <button type="button" onclick="openRabModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Ajukan RAB Baru</span>
      </button>
      <?php endif; ?>
    </div>

    <!-- Filter & Search Toolbar -->
    <form method="GET" action="<?php echo Helper::url('rab'); ?>" class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/60 space-y-3">
      <input type="hidden" name="page" value="rab">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5">
        
        <!-- Search Keyword -->
        <div class="lg:col-span-4 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
          </div>
          <input type="text" name="search" value="<?php echo Helper::e($_GET['search'] ?? ''); ?>" placeholder="Cari nama proyek, no. RAB, lokasi, PIC..." class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
        </div>

        <!-- Filter Status -->
        <div class="lg:col-span-3">
          <select name="status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500 font-medium">
            <option value="">Semua Status Approval</option>
            <option value="submitted" <?php echo (($_GET['status'] ?? '') === 'submitted') ? 'selected' : ''; ?>>Diajukan (Menunggu)</option>
            <option value="approved" <?php echo (($_GET['status'] ?? '') === 'approved') ? 'selected' : ''; ?>>Disetujui (Approved)</option>
            <option value="in_progress" <?php echo (($_GET['status'] ?? '') === 'in_progress') ? 'selected' : ''; ?>>Proses Realisasi</option>
            <option value="completed" <?php echo (($_GET['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Selesai Penuh</option>
            <option value="rejected" <?php echo (($_GET['status'] ?? '') === 'rejected') ? 'selected' : ''; ?>>Ditolak (Rejected)</option>
          </select>
        </div>

        <!-- Filter Kategori -->
        <div class="lg:col-span-3">
          <select name="category_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500 font-medium">
            <option value="">Semua Kategori RAB</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?php echo $c['id']; ?>" <?php echo ((int)($_GET['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
                <?php echo Helper::e($c['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Bulan / Periode -->
        <div class="lg:col-span-2">
          <input type="month" name="month" value="<?php echo Helper::e($_GET['month'] ?? ''); ?>" class="w-full text-xs px-2.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500 font-medium" title="Pilih Bulan Periode Proyek">
        </div>

      </div>

      <!-- Action & Reset Buttons -->
      <div class="flex items-center justify-between pt-1 border-t border-slate-200/50">
        <!-- Active Filter Badges -->
        <div class="flex flex-wrap items-center gap-1.5">
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 mr-1">Filter:</span>
          
          <?php if (!empty($_GET['search'])): ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-purple-100 text-purple-700 text-3xs font-bold">
              <i class="fa-solid fa-magnifying-glass text-3xs"></i>
              <span>"<?php echo Helper::e($_GET['search']); ?>"</span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['status'])): 
            $statusLabels = [
              'submitted' => 'Diajukan',
              'approved' => 'Disetujui',
              'in_progress' => 'Proses',
              'completed' => 'Selesai',
              'rejected' => 'Ditolak'
            ];
          ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-indigo-100 text-indigo-700 text-3xs font-bold">
              <i class="fa-solid fa-tag text-3xs"></i>
              <span>Status: <?php echo $statusLabels[$_GET['status']] ?? Helper::e($_GET['status']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['category_id'])): 
            $catLabel = '';
            foreach ($categories as $cat) { if ((int)$cat['id'] === (int)$_GET['category_id']) { $catLabel = $cat['name']; break; } }
          ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-pink-100 text-pink-700 text-3xs font-bold">
              <i class="fa-solid fa-folder text-3xs"></i>
              <span><?php echo Helper::e($catLabel ?: 'ID ' . $_GET['category_id']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['month'])): ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-blue-100 text-blue-700 text-3xs font-bold">
              <i class="fa-solid fa-calendar-days text-3xs"></i>
              <span>Bulan: <?php echo Helper::e($_GET['month']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!$hasActiveFilters): ?>
            <span class="text-3xs text-slate-400 italic">Menampilkan semua data</span>
          <?php endif; ?>
        </div>

        <div class="flex items-center gap-2">
          <?php if ($hasActiveFilters): ?>
            <a href="<?php echo Helper::url('rab'); ?>" class="px-3 py-1.5 text-3xs font-bold text-slate-600 hover:text-rose-600 bg-white hover:bg-rose-50 border border-slate-200 rounded-xl transition-colors flex items-center gap-1">
              <i class="fa-solid fa-rotate-left text-3xs"></i>
              <span>Reset</span>
            </a>
          <?php endif; ?>

          <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-soft-xs transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-filter text-2xs"></i>
            <span>Terapkan Filter</span>
          </button>
        </div>
      </div>
    </form>

    <!-- RAB List Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead>
          <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
            <th class="py-3 px-4 font-bold">No. RAB & Nama Proyek</th>
            <th class="py-3 px-4 font-bold">Kategori & Lokasi</th>
            <th class="py-3 px-4 font-bold text-center">Item Barang</th>
            <th class="py-3 px-4 font-bold text-right">Plafon Anggaran</th>
            <th class="py-3 px-4 font-bold text-right">Realisasi Biaya</th>
            <th class="py-3 px-4 font-bold text-center">Status</th>
            <th class="py-3 px-4 font-bold text-center">Aksi & Realisasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($rabs)): ?>
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400">
              <i class="fa-solid fa-calculator text-3xl mb-2 block text-slate-300"></i>
              <?php if ($hasActiveFilters): ?>
                Tidak ada data RAB yang sesuai dengan filter pencarian
              <?php else: ?>
                Belum ada pengajuan RAB proyek yang terdaftar
              <?php endif; ?>
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($rabs as $r): 
              $itemCount = count($r['items']);
              $pct = $r['budget_total'] > 0 ? round(($r['realized_total'] / $r['budget_total']) * 100) : 0;
            ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <!-- No. RAB & Nama Proyek -->
              <td class="py-3.5 px-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                    <i class="fa-solid fa-file-contract"></i>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($r['project_name']); ?></span>
                    <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($r['rab_no']); ?></span>
                    <?php if (!empty($r['creator_name'])): ?>
                      <span class="text-3xs text-slate-400">&bull; Oleh: <?php echo Helper::e($r['creator_name']); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <!-- Kategori & Lokasi -->
              <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                <span class="px-2.5 py-0.5 rounded-xl bg-purple-50 text-purple-700 font-bold text-3xs inline-block mb-0.5">
                  <?php echo Helper::e($r['category_name'] ?: 'Umum'); ?>
                </span>
                <span class="text-slate-800 font-semibold block text-2xs"><?php echo Helper::e($r['location'] ?: '-'); ?></span>
                <span class="text-3xs text-slate-400">PIC: <?php echo Helper::e($r['pic_name'] ?: '-'); ?></span>
              </td>

              <!-- Rincian Item -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <button type="button" 
                        onclick='openRabDetailModal(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)'
                        class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-purple-100 text-slate-700 hover:text-purple-700 font-bold text-3xs transition-colors inline-flex items-center gap-1">
                  <i class="fa-solid fa-boxes-stacked text-3xs"></i>
                  <span><?php echo $itemCount; ?> Barang</span>
                </button>
              </td>

              <!-- Plafon Anggaran -->
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <span class="text-sm font-black text-slate-900 font-mono block"><?php echo Helper::formatRupiah($r['budget_total']); ?></span>
                <span class="text-3xs text-slate-400">Estimasi</span>
              </td>

              <!-- Realisasi Biaya -->
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <span class="text-sm font-black text-purple-700 font-mono block"><?php echo Helper::formatRupiah($r['realized_total']); ?></span>
                <div class="flex items-center justify-end gap-1.5 mt-0.5">
                  <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-500 h-1.5 rounded-full" style="width: <?php echo min(100, $pct); ?>%"></div>
                  </div>
                  <span class="text-3xs font-bold <?php echo $pct > 100 ? 'text-rose-600' : 'text-slate-500'; ?>"><?php echo $pct; ?>%</span>
                </div>
              </td>

              <!-- Status -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <?php echo Helper::statusBadge($r['status']); ?>
              </td>

              <!-- Aksi & Realisasi -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  
                  <!-- Detail Button -->
                  <button type="button" 
                          onclick='openRabDetailModal(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)' 
                          class="px-2.5 py-1.5 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                          title="Lihat Detail & Rincian Barang">
                    <i class="fa-solid fa-eye text-xs"></i>
                    <span>Rincian</span>
                  </button>

                  <!-- Actions for SUBMITTED: Approve or Reject -->
                  <?php if ($r['status'] === 'submitted' && AuthService::hasPermission('rab.approve')): ?>
                    <button type="button" 
                            onclick="confirmApproveRab(<?php echo $r['id']; ?>, '<?php echo Helper::e(addslashes($r['project_name'])); ?>', '<?php echo Helper::formatRupiah($r['budget_total']); ?>')" 
                            class="px-2.5 py-1.5 text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                            title="Setujui Pengajuan RAB">
                      <i class="fa-solid fa-check text-xs"></i>
                      <span>Setujui</span>
                    </button>

                    <button type="button" 
                            onclick="confirmRejectRab(<?php echo $r['id']; ?>, '<?php echo Helper::e(addslashes($r['project_name'])); ?>')" 
                            class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                            title="Tolak Pengajuan RAB">
                      <i class="fa-solid fa-xmark text-xs"></i>
                      <span>Tolak</span>
                    </button>
                  <?php endif; ?>

                  <!-- Actions for APPROVED or IN_PROGRESS: Input Realization -->
                  <?php if (in_array($r['status'], ['approved', 'in_progress']) && (AuthService::hasPermission('rab.edit') || AuthService::hasPermission('rab.approve'))): ?>
                    <button type="button" 
                            onclick='openRealizationModal(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)' 
                            class="px-2.5 py-1.5 text-purple-700 hover:text-purple-800 bg-purple-50 hover:bg-purple-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1 shadow-soft-xs" 
                            title="Input Realisasi Biaya Barang">
                      <i class="fa-solid fa-receipt text-xs"></i>
                      <span>Input Realisasi</span>
                    </button>
                  <?php endif; ?>

                  <!-- Delete Button -->
                  <?php if (AuthService::hasPermission('rab.delete')): ?>
                  <button type="button" 
                          onclick="confirmDeleteRab(<?php echo $r['id']; ?>, '<?php echo Helper::e(addslashes($r['project_name'])); ?>')" 
                          class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" 
                          title="Hapus RAB">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                  <?php endif; ?>

                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: AJUKAN RAB BARU DENGAN MULTIPLE BARANG -->
<!-- ========================================================================= -->
<div id="rabModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="rabModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-calculator"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Ajukan RAB Proyek Baru</h4>
          <span class="text-2xs text-slate-400">Rencanakan anggaran dan rincian kebutuhan item barang secara detail</span>
        </div>
      </div>
      <button type="button" onclick="closeRabModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form method="POST" action="<?php echo Helper::url('rab'); ?>" class="space-y-5" onsubmit="return validateRabForm()">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_rab">

      <!-- Section: Info Proyek -->
      <div class="space-y-3 bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
        <h6 class="text-2xs font-extrabold text-slate-500 uppercase tracking-wider">Informasi Proyek / Kegiatan</h6>
        
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nama Proyek / Kegiatan <span class="text-red-500">*</span></label>
          <input type="text" name="project_name" required placeholder="contoh: Penarikan Feeder ODP Blok D & Penggantian Tiang" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Kategori RAB <span class="text-red-500">*</span></label>
            <select name="category_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo Helper::e($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Lokasi Proyek</label>
            <input type="text" name="location" placeholder="Perum Grand Galaxy Blok C" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PIC Lapangan</label>
            <input type="text" name="pic_name" placeholder="Ahmad Fauzi (Teknisi)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Tgl Mulai Proyek</label>
            <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Target Selesai</label>
            <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Keterangan / Justifikasi</label>
          <textarea name="description" rows="2" placeholder="Uraian kebutuhan, lingkup pekerjaan, atau justifikasi urgensi..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-purple-500"></textarea>
        </div>
      </div>

      <!-- Section: Rincian Item Barang (Dynamic Repeater) -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h6 class="text-xs font-black text-slate-900 tracking-tight">Rincian Item Barang & Kebutuhan Material</h6>
            <span class="text-3xs text-slate-400">Tambahkan material, kabel, perangkat, atau jasa pekerjaan</span>
          </div>

          <!-- Quick Template Selector from Inventory -->
          <?php if (!empty($inventoryItems)): ?>
          <div class="flex items-center gap-2">
            <select id="inventoryQuickPicker" onchange="addInventoryItemToRab(this)" class="text-3xs px-2.5 py-1.5 rounded-xl border border-purple-200 bg-purple-50 text-purple-700 font-bold focus:outline-none">
              <option value="">+ Pilih Cepat dari Stok Barang</option>
              <?php foreach ($inventoryItems as $inv): ?>
                <option value="<?php echo htmlspecialchars(json_encode($inv), ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo Helper::e($inv['name']); ?> (<?php echo Helper::formatRupiah($inv['purchase_price']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </div>

        <!-- Table of Items -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
          <table class="w-full text-xs text-left" id="rabItemsTable">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-3xs uppercase font-extrabold">
              <tr>
                <th class="py-2.5 px-3">Nama Barang / Material / Jasa</th>
                <th class="py-2.5 px-2 w-28">Kategori</th>
                <th class="py-2.5 px-2 w-16 text-center">Qty</th>
                <th class="py-2.5 px-2 w-20">Satuan</th>
                <th class="py-2.5 px-3 w-32 text-right">Harga Satuan (Rp)</th>
                <th class="py-2.5 px-3 w-32 text-right">Subtotal (Rp)</th>
                <th class="py-2.5 px-2 w-10 text-center"></th>
              </tr>
            </thead>
            <tbody id="rabItemsBody" class="divide-y divide-slate-100">
              <!-- Item rows populated via JS -->
            </tbody>
          </table>
        </div>

        <!-- Add Item Button & Summary Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
          <button type="button" onclick="addRabItemRow()" class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-plus-circle text-xs"></i>
            <span>Tambah Baris Barang</span>
          </button>

          <!-- Live Budget Calculation Box -->
          <div class="flex items-center gap-3 bg-slate-900 text-white px-4 py-2.5 rounded-2xl shadow-soft-sm">
            <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400">Total Anggaran:</span>
            <span class="text-sm font-black font-mono text-emerald-400" id="liveBudgetTotalDisplay">Rp 0</span>
            <input type="hidden" name="budget_total" id="liveBudgetTotalInput" value="0">
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeRabModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-paper-plane mr-1.5"></i> Ajukan RAB Proyek
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: INPUT REALISASI BIAYA RAB (SETELAH DISETUJUI) -->
<!-- ========================================================================= -->
<div id="realizationModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="realizationModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Input Realisasi Biaya RAB</h4>
          <span class="text-2xs text-slate-400" id="realizationSubtitle">Catat pengeluaran aktual dan belanja barang riil di lapangan</span>
        </div>
      </div>
      <button type="button" onclick="closeRealizationModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form method="POST" action="<?php echo Helper::url('rab'); ?>" class="space-y-5">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_realization">
      <input type="hidden" name="id" id="realizationRabId">

      <!-- Project Snapshot Card -->
      <div class="p-4 bg-purple-50/60 border border-purple-100 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <span class="text-3xs font-extrabold uppercase text-purple-700 block">Nama Proyek:</span>
          <span class="text-xs font-bold text-slate-900" id="realizationProjectName">-</span>
        </div>
        <div>
          <span class="text-3xs font-extrabold uppercase text-purple-700 block">Plafon Anggaran:</span>
          <span class="text-xs font-black font-mono text-slate-900" id="realizationBudgetTotal">-</span>
        </div>
        <div>
          <span class="text-3xs font-extrabold uppercase text-purple-700 block">Status Proyek:</span>
          <select name="status" id="realizationStatusSelect" class="text-xs font-bold px-2 py-1 rounded-lg border border-purple-200 bg-white text-slate-800 focus:outline-none">
            <option value="in_progress">Dalam Proses Pengerjaan</option>
            <option value="completed" selected>Selesai Penuh (Completed)</option>
          </select>
        </div>
      </div>

      <!-- Item Realization Table -->
      <div>
        <h6 class="text-xs font-black text-slate-900 mb-2">Realisasi Per Item Barang / Material:</h6>
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
          <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-3xs uppercase font-extrabold">
              <tr>
                <th class="py-2.5 px-3">Item Barang</th>
                <th class="py-2.5 px-2 text-center w-20">Rencana Qty</th>
                <th class="py-2.5 px-3 text-right w-32">Estimasi Anggaran</th>
                <th class="py-2.5 px-3 text-right w-40">Realisasi Biaya (Rp)</th>
              </tr>
            </thead>
            <tbody id="realizationItemsBody" class="divide-y divide-slate-100">
              <!-- Realization item rows populated via JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Realization Total & Diff Display -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 bg-slate-900 rounded-2xl text-white">
        <div>
          <span class="text-3xs font-extrabold uppercase text-slate-400 block">Total Realisasi Belanja:</span>
          <span class="text-base font-black font-mono text-emerald-400" id="liveRealizedTotalDisplay">Rp 0</span>
        </div>
        <div class="text-right">
          <span class="text-3xs font-extrabold uppercase text-slate-400 block" id="varianceLabel">Sisa Efisiensi Anggaran:</span>
          <span class="text-sm font-bold font-mono" id="varianceDisplay">Rp 0</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeRealizationModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Realisasi
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: DETAIL RINCIAN RAB & BARANG -->
<!-- ========================================================================= -->
<div id="rabDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="rabDetailModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-file-contract"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight" id="detailRabTitle">Rincian Proyek RAB</h4>
          <span class="text-2xs text-purple-700 font-mono font-bold" id="detailRabNo">-</span>
        </div>
      </div>
      <button type="button" onclick="closeRabDetailModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <div class="space-y-4">
      <!-- Project Meta Info -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
        <div>
          <span class="text-3xs uppercase font-extrabold text-slate-400 block">Kategori</span>
          <span class="text-xs font-bold text-slate-800" id="detailCategory">-</span>
        </div>
        <div>
          <span class="text-3xs uppercase font-extrabold text-slate-400 block">Lokasi & PIC</span>
          <span class="text-xs font-bold text-slate-800" id="detailLocation">-</span>
        </div>
        <div>
          <span class="text-3xs uppercase font-extrabold text-slate-400 block">Waktu Proyek</span>
          <span class="text-xs font-bold text-slate-800" id="detailDates">-</span>
        </div>
        <div>
          <span class="text-3xs uppercase font-extrabold text-slate-400 block">Status</span>
          <div id="detailStatusBadge">-</div>
        </div>
      </div>

      <!-- Description Note -->
      <div id="detailDescContainer" class="p-3 bg-purple-50/40 rounded-2xl border border-purple-100 text-xs text-slate-700 hidden">
        <span class="font-bold text-purple-900 block mb-0.5">Keterangan / Justifikasi:</span>
        <p id="detailDescription" class="text-2xs text-slate-600"></p>
      </div>

      <!-- Items Table -->
      <div>
        <h6 class="text-xs font-black text-slate-900 mb-2">Daftar Barang & Rincian Realisasi:</h6>
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
          <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-3xs uppercase font-extrabold">
              <tr>
                <th class="py-2.5 px-3">Nama Barang</th>
                <th class="py-2.5 px-2">Kategori</th>
                <th class="py-2.5 px-2 text-center">Qty</th>
                <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                <th class="py-2.5 px-3 text-right">Estimasi (Rp)</th>
                <th class="py-2.5 px-3 text-right">Realisasi (Rp)</th>
              </tr>
            </thead>
            <tbody id="detailItemsBody" class="divide-y divide-slate-100">
              <!-- Item rows populated via JS -->
            </tbody>
            <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
              <tr>
                <td colspan="4" class="py-2.5 px-3 text-right text-xs">Total:</td>
                <td class="py-2.5 px-3 text-right font-mono text-slate-900" id="detailTotalBudget">Rp 0</td>
                <td class="py-2.5 px-3 text-right font-mono text-purple-700" id="detailTotalRealized">Rp 0</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="flex items-center justify-end pt-3">
        <button type="button" onclick="closeRabDetailModal()" class="px-5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Tutup
        </button>
      </div>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- HIDDEN FORMS FOR APPROVE, REJECT & DELETE -->
<!-- ========================================================================= -->
<form id="approveRabForm" method="POST" action="<?php echo Helper::url('rab'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="approve_rab">
  <input type="hidden" name="id" id="approveRabId">
</form>

<form id="rejectRabForm" method="POST" action="<?php echo Helper::url('rab'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="reject_rab">
  <input type="hidden" name="id" id="rejectRabId">
</form>

<form id="deleteRabForm" method="POST" action="<?php echo Helper::url('rab'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_rab">
  <input type="hidden" name="id" id="deleteRabId">
</form>

<!-- ========================================================================= -->
<!-- JAVASCRIPT CONTROLLERS -->
<!-- ========================================================================= -->
<script>
let rabItemIndex = 0;

// Format number to Rupiah string
function formatRp(num) {
  return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

// Modal open/close helpers
function openModal(modalId, contentId) {
  const modal = document.getElementById(modalId);
  const content = document.getElementById(contentId);
  if (!modal || !content) return;
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeModal(modalId, contentId) {
  const modal = document.getElementById(modalId);
  const content = document.getElementById(contentId);
  if (!modal || !content) return;
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

// 1. RAB Add Modal & Item Repeater
function openRabModal() {
  const tbody = document.getElementById("rabItemsBody");
  if (tbody.children.length === 0) {
    addRabItemRow(); // start with 1 blank row
  }
  openModal('rabModal', 'rabModalContent');
}

function closeRabModal() {
  closeModal('rabModal', 'rabModalContent');
}

function addRabItemRow(data = {}) {
  const tbody = document.getElementById("rabItemsBody");
  const idx = rabItemIndex++;

  const name = data.name || '';
  const category = data.category || 'Material';
  const qty = data.quantity || 1;
  const unit = data.unit || 'pcs';
  const price = data.purchase_price !== undefined ? data.purchase_price : (data.unit_price || 0);
  const subtotal = qty * price;

  const tr = document.createElement("tr");
  tr.id = `rab_item_row_${idx}`;
  tr.className = "hover:bg-slate-50/50 transition-colors";
  tr.innerHTML = `
    <td class="py-2 px-3">
      <input type="text" name="items[${idx}][name]" value="${name}" required placeholder="Nama barang / jasa" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-purple-500">
    </td>
    <td class="py-2 px-2">
      <select name="items[${idx}][category]" class="w-full text-3xs px-2 py-1.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:border-purple-500">
        <option value="Material" ${category === 'Material' ? 'selected' : ''}>Material</option>
        <option value="Perangkat" ${category === 'Perangkat' ? 'selected' : ''}>Perangkat</option>
        <option value="Aksesoris" ${category === 'Aksesoris' ? 'selected' : ''}>Aksesoris</option>
        <option value="Jasa" ${category === 'Jasa' ? 'selected' : ''}>Jasa</option>
        <option value="Lainnya" ${category === 'Lainnya' ? 'selected' : ''}>Lainnya</option>
      </select>
    </td>
    <td class="py-2 px-2 text-center">
      <input type="number" name="items[${idx}][quantity]" id="item_qty_${idx}" value="${qty}" min="1" oninput="calcRabRowSubtotal(${idx})" class="w-full text-xs text-center px-1.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-purple-500 font-bold">
    </td>
    <td class="py-2 px-2">
      <input type="text" name="items[${idx}][unit]" value="${unit}" placeholder="pcs/roll" class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-purple-500">
    </td>
    <td class="py-2 px-3 text-right">
      <input type="number" name="items[${idx}][unit_price]" id="item_price_${idx}" value="${price}" min="0" oninput="calcRabRowSubtotal(${idx})" placeholder="0" class="w-full text-xs text-right font-mono px-2 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-purple-500">
    </td>
    <td class="py-2 px-3 text-right">
      <span class="text-xs font-bold font-mono text-slate-900 block" id="item_subtotal_display_${idx}">${formatRp(subtotal)}</span>
    </td>
    <td class="py-2 px-2 text-center">
      <button type="button" onclick="removeRabItemRow(${idx})" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="Hapus Baris">
        <i class="fa-solid fa-trash-can text-xs"></i>
      </button>
    </td>
  `;

  tbody.appendChild(tr);
  recalculateTotalRabBudget();
}

function removeRabItemRow(idx) {
  const row = document.getElementById(`rab_item_row_${idx}`);
  if (row) {
    row.remove();
    recalculateTotalRabBudget();
  }
}

function calcRabRowSubtotal(idx) {
  const qtyInput = document.getElementById(`item_qty_${idx}`);
  const priceInput = document.getElementById(`item_price_${idx}`);
  const display = document.getElementById(`item_subtotal_display_${idx}`);

  const qty = Math.max(0, parseInt(qtyInput ? qtyInput.value : 0) || 0);
  const price = Math.max(0, parseInt(priceInput ? priceInput.value : 0) || 0);
  const subtotal = qty * price;

  if (display) {
    display.innerText = formatRp(subtotal);
  }
  recalculateTotalRabBudget();
}

function recalculateTotalRabBudget() {
  let total = 0;
  const tbody = document.getElementById("rabItemsBody");
  const rows = tbody.querySelectorAll("tr");

  rows.forEach(row => {
    const qtyEl = row.querySelector("input[name*='[quantity]']");
    const priceEl = row.querySelector("input[name*='[unit_price]']");
    const q = parseInt(qtyEl ? qtyEl.value : 0) || 0;
    const p = parseInt(priceEl ? priceEl.value : 0) || 0;
    total += (q * p);
  });

  document.getElementById("liveBudgetTotalDisplay").innerText = formatRp(total);
  document.getElementById("liveBudgetTotalInput").value = total;
}

function addInventoryItemToRab(select) {
  if (!select.value) return;
  try {
    const item = JSON.parse(select.value);
    addRabItemRow({
      name: item.name,
      category: 'Material',
      quantity: 1,
      unit: item.unit || 'pcs',
      purchase_price: item.purchase_price || 0
    });
  } catch (e) {
    console.error(e);
  }
  select.value = "";
}

function validateRabForm() {
  const total = parseInt(document.getElementById("liveBudgetTotalInput").value) || 0;
  if (total <= 0) {
    alert("Harap tambahkan setidaknya 1 item barang dengan harga lebih dari 0.");
    return false;
  }
  return true;
}

// 2. Realization Modal Logic
function openRealizationModal(rab) {
  if (!rab) return;

  document.getElementById("realizationRabId").value = rab.id;
  document.getElementById("realizationProjectName").innerText = rab.project_name + ' (' + rab.rab_no + ')';
  document.getElementById("realizationBudgetTotal").innerText = formatRp(rab.budget_total);
  document.getElementById("realizationStatusSelect").value = rab.status === 'completed' ? 'completed' : 'in_progress';

  const tbody = document.getElementById("realizationItemsBody");
  tbody.innerHTML = "";

  const items = rab.items || [];
  if (items.length === 0) {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td class="py-3 px-3 font-bold text-slate-800">Total Biaya Realisasi Proyek</td>
      <td class="py-3 px-2 text-center font-mono">1 lot</td>
      <td class="py-3 px-3 text-right font-mono font-bold text-slate-700">${formatRp(rab.budget_total)}</td>
      <td class="py-3 px-3 text-right">
        <input type="number" name="realized_total" value="${rab.realized_total || rab.budget_total}" min="0" oninput="calcRealizationTotals(${rab.budget_total})" class="w-full text-xs text-right font-mono font-bold px-3 py-1.5 rounded-lg border border-purple-300 bg-purple-50/50 focus:outline-none focus:border-purple-600 text-purple-900">
      </td>
    `;
    tbody.appendChild(tr);
  } else {
    items.forEach(it => {
      const tr = document.createElement("tr");
      const realizedVal = it.realized_subtotal > 0 ? it.realized_subtotal : (rab.realized_total > 0 ? 0 : it.subtotal);
      tr.innerHTML = `
        <td class="py-3 px-3">
          <span class="font-bold text-slate-900 block">${it.item_name}</span>
          <span class="text-3xs text-slate-400 font-mono">${it.category || 'Material'} &bull; @${formatRp(it.unit_price)}</span>
        </td>
        <td class="py-3 px-2 text-center font-mono font-bold text-slate-700">
          ${it.quantity} ${it.unit}
        </td>
        <td class="py-3 px-3 text-right font-mono font-bold text-slate-700">
          ${formatRp(it.subtotal)}
        </td>
        <td class="py-3 px-3 text-right">
          <input type="number" name="realized_items[${it.id}]" value="${realizedVal}" min="0" oninput="calcRealizationTotals(${rab.budget_total})" class="w-full text-xs text-right font-mono font-bold px-3 py-1.5 rounded-lg border border-purple-300 bg-purple-50/50 focus:outline-none focus:border-purple-600 text-purple-900">
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  calcRealizationTotals(rab.budget_total);
  openModal('realizationModal', 'realizationModalContent');
}

function closeRealizationModal() {
  closeModal('realizationModal', 'realizationModalContent');
}

function calcRealizationTotals(budgetPlafon) {
  let totalRealized = 0;
  const tbody = document.getElementById("realizationItemsBody");
  const inputs = tbody.querySelectorAll("input[type='number']");

  inputs.forEach(inp => {
    totalRealized += parseInt(inp.value || 0) || 0;
  });

  document.getElementById("liveRealizedTotalDisplay").innerText = formatRp(totalRealized);

  const diff = budgetPlafon - totalRealized;
  const varianceDisplay = document.getElementById("varianceDisplay");
  const varianceLabel = document.getElementById("varianceLabel");

  if (diff >= 0) {
    varianceLabel.innerText = "Sisa Efisiensi Anggaran (Hemat):";
    varianceDisplay.className = "text-sm font-bold font-mono text-emerald-400";
    varianceDisplay.innerText = "+ " + formatRp(diff);
  } else {
    varianceLabel.innerText = "Over Budget (Melebihi Plafon):";
    varianceDisplay.className = "text-sm font-bold font-mono text-rose-400";
    varianceDisplay.innerText = "- " + formatRp(Math.abs(diff));
  }
}

// 3. Detail RAB Modal Logic
function openRabDetailModal(rab) {
  if (!rab) return;

  document.getElementById("detailRabTitle").innerText = rab.project_name;
  document.getElementById("detailRabNo").innerText = rab.rab_no;
  document.getElementById("detailCategory").innerText = rab.category_name || 'Umum';
  document.getElementById("detailLocation").innerText = (rab.location || '-') + ' (PIC: ' + (rab.pic_name || '-') + ')';
  document.getElementById("detailDates").innerText = (rab.start_date || '-') + ' s/d ' + (rab.end_date || '-');
  document.getElementById("detailTotalBudget").innerText = formatRp(rab.budget_total);
  document.getElementById("detailTotalRealized").innerText = formatRp(rab.realized_total);

  // Status Badge
  const badgeMap = {
    'submitted': '<span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase">Diajukan</span>',
    'approved': '<span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Disetujui</span>',
    'rejected': '<span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase">Ditolak</span>',
    'in_progress': '<span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold bg-blue-50 text-blue-700 border border-blue-200 uppercase">Proses</span>',
    'completed': '<span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Selesai</span>'
  };
  document.getElementById("detailStatusBadge").innerHTML = badgeMap[rab.status] || rab.status;

  // Description
  const descContainer = document.getElementById("detailDescContainer");
  const descEl = document.getElementById("detailDescription");
  if (rab.description && rab.description.trim()) {
    descEl.innerText = rab.description;
    descContainer.classList.remove("hidden");
  } else {
    descContainer.classList.add("hidden");
  }

  // Items Body
  const tbody = document.getElementById("detailItemsBody");
  tbody.innerHTML = "";

  const items = rab.items || [];
  if (items.length === 0) {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td colspan="6" class="py-4 text-center text-slate-400">Tidak ada rincian item barang khusus</td>`;
    tbody.appendChild(tr);
  } else {
    items.forEach(it => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="py-2.5 px-3 font-bold text-slate-900">${it.item_name}</td>
        <td class="py-2.5 px-2 text-2xs text-slate-500">${it.category || '-'}</td>
        <td class="py-2.5 px-2 text-center font-mono font-bold">${it.quantity} ${it.unit}</td>
        <td class="py-2.5 px-3 text-right font-mono text-slate-600">${formatRp(it.unit_price)}</td>
        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">${formatRp(it.subtotal)}</td>
        <td class="py-2.5 px-3 text-right font-mono font-bold text-purple-700">${formatRp(it.realized_subtotal)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  openModal('rabDetailModal', 'rabDetailModalContent');
}

function closeRabDetailModal() {
  closeModal('rabDetailModal', 'rabDetailModalContent');
}

// 4. Action Confirmations
function confirmApproveRab(id, name, budget) {
  if (confirm('Setujui pengajuan RAB untuk proyek "' + name + '" sebesar ' + budget + '?\n\nSetelah disetujui, proyek dapat mulai dijalankan dan tim dapat menginput realisasi biayanya.')) {
    document.getElementById("approveRabId").value = id;
    document.getElementById("approveRabForm").submit();
  }
}

function confirmRejectRab(id, name) {
  if (confirm('Apakah Anda yakin ingin menolak pengajuan RAB untuk proyek "' + name + '"?')) {
    document.getElementById("rejectRabId").value = id;
    document.getElementById("rejectRabForm").submit();
  }
}

function confirmDeleteRab(id, name) {
  if (confirm('Yakin ingin menghapus RAB proyek "' + name + '" beserta seluruh rincian barangnya?')) {
    document.getElementById("deleteRabId").value = id;
    document.getElementById("deleteRabForm").submit();
  }
}

// Global modal background & ESC handlers
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'rabModal', content: 'rabModalContent' },
    { id: 'realizationModal', content: 'realizationModalContent' },
    { id: 'rabDetailModal', content: 'rabDetailModalContent' }
  ];

  modals.forEach(m => {
    const modalEl = document.getElementById(m.id);
    if (modalEl) {
      modalEl.addEventListener("click", function(e) {
        if (e.target === modalEl) {
          closeModal(m.id, m.content);
        }
      });
    }
  });

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
      modals.forEach(m => {
        const modalEl = document.getElementById(m.id);
        if (modalEl && !modalEl.classList.contains("hidden")) {
          closeModal(m.id, m.content);
        }
      });
    }
  });
});
</script>
