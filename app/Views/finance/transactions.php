<?php
// app/Views/finance/transactions.php - Journal of Transactions with CSV Export & Import
$incomes = array_filter($transactions, fn($t) => $t['type'] === 'income');
$expenses = array_filter($transactions, fn($t) => $t['type'] === 'expense');
$debts = array_filter($transactions, fn($t) => $t['type'] === 'debt');
$receivables = array_filter($transactions, fn($t) => $t['type'] === 'receivable');

$totalIncome = array_reduce($incomes, fn($sum, $t) => $sum + $t['amount'], 0);
$totalExpense = array_reduce($expenses, fn($sum, $t) => $sum + $t['amount'], 0);
$totalDebt = array_reduce($debts, fn($sum, $t) => $sum + $t['amount'], 0);
$totalReceivables = array_reduce($receivables, fn($sum, $t) => $sum + $t['amount'], 0);
?>
<div class="w-full space-y-4">
  
  <!-- Page Header & Action Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-100 shadow-soft-sm">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Jurnal Mutasi Transaksi Kas & Bank</h4>
      <p class="text-2xs text-slate-400">Pencatatan arus kas masuk, pengeluaran, hutang piutang, serta import & export data</p>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
      <!-- Import CSV Button -->
      <button type="button" onclick="openImportModal()" class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-soft-xs transition-all flex items-center gap-1.5 hover:scale-102">
        <i class="fa-solid fa-file-import text-purple-600"></i>
        <span>Import CSV</span>
      </button>

      <!-- Export CSV Link (includes active filters) -->
      <?php 
        $exportParams = [];
        if (!empty($_GET['month'])) $exportParams['month'] = $_GET['month'];
        if (!empty($_GET['account_id'])) $exportParams['account_id'] = $_GET['account_id'];
        if (!empty($_GET['search'])) $exportParams['search'] = $_GET['search'];
        $exportUrl = Helper::url('transactions_export_csv') . (!empty($exportParams) ? '&' . http_build_query($exportParams) : '');
      ?>
      <a href="<?php echo $exportUrl; ?>" class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-soft-xs transition-all flex items-center gap-1.5 hover:scale-102">
        <i class="fa-solid fa-file-csv text-emerald-600"></i>
        <span>Export CSV</span>
      </a>

      <!-- Quick Record Buttons -->
      <button type="button" onclick="openAddTrxModal('income')" class="px-3.5 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-emerald-600 to-teal-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Pemasukan</span>
      </button>
      <button type="button" onclick="openAddTrxModal('expense')" class="px-3.5 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-rose-600 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-minus text-xs"></i>
        <span>Pengeluaran</span>
      </button>
    </div>
  </div>

  <!-- Filter Menu -->
  <form method="GET" action="" class="flex flex-col sm:flex-row gap-3 bg-white p-4 rounded-2xl border border-slate-100 shadow-soft-sm">
    <input type="hidden" name="page" value="transactions">
    <div class="flex-[2]">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Pencarian</label>
      <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Cari keterangan, no trx, kategori..." class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
    </div>
    <div class="flex-1">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Bulan Transaksi</label>
      <input type="month" name="month" value="<?php echo htmlspecialchars($_GET['month'] ?? date('Y-m')); ?>" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
    </div>
    <div class="flex-[2]">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Rekening Kas / Bank</label>
      <select name="account_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
        <option value="">Semua Rekening</option>
        <?php foreach ($accounts as $acc): ?>
          <option value="<?php echo $acc['id']; ?>" <?php echo (isset($_GET['account_id']) && $_GET['account_id'] == $acc['id']) ? 'selected' : ''; ?>>
            <?php echo Helper::e($acc['account_name'] . (!empty($acc['bank_name']) ? ' (' . $acc['bank_name'] . ')' : '')); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-slate-800 rounded-xl shadow-soft-sm hover:bg-slate-900 transition-colors">
        Filter
      </button>
      <a href="?page=transactions" class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl shadow-soft-sm hover:bg-slate-50 transition-colors">
        Reset
      </a>
    </div>
  </form>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-soft-sm flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-arrow-down-short-wide"></i>
      </div>
      <div>
        <p class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
        <h4 class="text-lg font-black text-slate-800">Rp <?php echo Helper::formatRupiah($totalIncome, false); ?></h4>
      </div>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-soft-sm flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-arrow-up-short-wide"></i>
      </div>
      <div>
        <p class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
        <h4 class="text-lg font-black text-slate-800">Rp <?php echo Helper::formatRupiah($totalExpense, false); ?></h4>
      </div>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-soft-sm flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-hand-holding-dollar"></i>
      </div>
      <div>
        <p class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Total Hutang</p>
        <h4 class="text-lg font-black text-slate-800">Rp <?php echo Helper::formatRupiah($totalDebt, false); ?></h4>
      </div>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-soft-sm flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-file-invoice-dollar"></i>
      </div>
      <div>
        <p class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Total Piutang</p>
        <h4 class="text-lg font-black text-slate-800">Rp <?php echo Helper::formatRupiah($totalReceivables, false); ?></h4>
      </div>
    </div>
  </div>

  <!-- Tabs Navigation -->
  <div class="flex items-center gap-4 border-b border-slate-200 px-4 bg-white pt-2 rounded-t-2xl shadow-soft-sm overflow-x-auto hide-scrollbar">
    <button onclick="switchTab('income')" id="tabBtn-income" class="px-6 py-4 text-sm transition-all border-b-[3px] border-emerald-500 text-emerald-700 font-black whitespace-nowrap">
      <i class="fa-solid fa-arrow-down-short-wide mr-2"></i>Pemasukan
    </button>
    <button onclick="switchTab('expense')" id="tabBtn-expense" class="px-6 py-4 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap">
      <i class="fa-solid fa-arrow-up-short-wide mr-2"></i>Pengeluaran
    </button>
    <button onclick="switchTab('debt')" id="tabBtn-debt" class="px-6 py-4 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap">
      <i class="fa-solid fa-hand-holding-dollar mr-2"></i>Catatan Hutang
    </button>
    <button onclick="switchTab('receivable')" id="tabBtn-receivable" class="px-6 py-4 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap">
      <i class="fa-solid fa-file-invoice-dollar mr-2"></i>Catatan Piutang
    </button>
  </div>

  <!-- Income Tab -->
  <div id="tab-income" class="flex flex-wrap -mx-3 transition-opacity duration-300">
    <div class="w-full max-w-full px-3">
      <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h5 class="font-bold text-slate-800 text-lg">Mutasi Pemasukan Kas</h5>
            <p class="text-xs text-slate-400">Penerimaan dana dari pembayaran pelanggan, modal, atau lainnya</p>
          </div>
          <button type="button" onclick="openAddTrxModal('income')" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-tl from-emerald-600 to-teal-500 rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Pemasukan
          </button>
        </div>
        <div class="flex-auto px-0 pt-4 pb-2">
          <div class="p-0 overflow-x-auto">
            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
              <thead class="align-bottom">
                <tr>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. Transaksi</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Keterangan</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Kategori & Akun</th>
                  <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nominal (Rp)</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tanggal</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($incomes)): ?>
                  <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada transaksi pemasukan.</td></tr>
                <?php else: foreach ($incomes as $t): ?>
                <tr class="hover:bg-slate-50/50">
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-emerald-700">
                    <?php echo Helper::e($t['transaction_no']); ?>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                    <span class="text-xs font-bold text-slate-800 block max-w-sm truncate"><?php echo Helper::e($t['description']); ?></span>
                    <span class="text-2xs text-slate-400">Petugas: <?php echo Helper::e($t['creator_name'] ?? 'System'); ?></span>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                    <span class="font-semibold text-slate-700 block"><?php echo Helper::e($t['category_name']); ?></span>
                    <span class="text-2xs text-slate-400"><?php echo Helper::e($t['account_name']); ?></span>
                  </td>
                  <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold font-mono text-emerald-600">
                    +<?php echo Helper::formatRupiah($t['amount'], false); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                    <?php echo Helper::formatDate($t['transaction_date']); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <?php if (!empty($t['attachment'])): ?>
                      <a href="/public/<?php echo Helper::e($t['attachment']); ?>" target="_blank" class="px-2 py-1 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Lihat Lampiran"><i class="fa-solid fa-image"></i></a>
                      <?php endif; ?>
                      <button type="button" onclick='openEditTrxModal(<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES, "UTF-8"); ?>)' class="px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                      <button type="button" onclick="confirmDeleteTrx(<?php echo $t['id']; ?>, '<?php echo $t['transaction_no']; ?>')" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Expense Tab -->
  <div id="tab-expense" class="hidden flex flex-wrap -mx-3 transition-opacity duration-300">
    <div class="w-full max-w-full px-3">
      <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h5 class="font-bold text-slate-800 text-lg">Mutasi Pengeluaran Kas</h5>
            <p class="text-xs text-slate-400">Pencatatan beban operasional, gaji, pembelian aset, dan lainnya</p>
          </div>
          <button type="button" onclick="openAddTrxModal('expense')" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-tl from-rose-600 to-pink-500 rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Pengeluaran
          </button>
        </div>
        <div class="flex-auto px-0 pt-4 pb-2">
          <div class="p-0 overflow-x-auto">
            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
              <thead class="align-bottom">
                <tr>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. Transaksi</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Keterangan</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Kategori & Akun</th>
                  <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nominal (Rp)</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tanggal</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($expenses)): ?>
                  <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada transaksi pengeluaran.</td></tr>
                <?php else: foreach ($expenses as $t): ?>
                <tr class="hover:bg-slate-50/50">
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-rose-700">
                    <?php echo Helper::e($t['transaction_no']); ?>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                    <span class="text-xs font-bold text-slate-800 block max-w-sm truncate"><?php echo Helper::e($t['description']); ?></span>
                    <span class="text-2xs text-slate-400">Petugas: <?php echo Helper::e($t['creator_name'] ?? 'System'); ?></span>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                    <span class="font-semibold text-slate-700 block"><?php echo Helper::e($t['category_name']); ?></span>
                    <span class="text-2xs text-slate-400"><?php echo Helper::e($t['account_name']); ?></span>
                  </td>
                  <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold font-mono text-rose-600">
                    -<?php echo Helper::formatRupiah($t['amount'], false); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                    <?php echo Helper::formatDate($t['transaction_date']); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <?php if (!empty($t['attachment'])): ?>
                      <a href="/public/<?php echo Helper::e($t['attachment']); ?>" target="_blank" class="px-2 py-1 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Lihat Lampiran"><i class="fa-solid fa-image"></i></a>
                      <?php endif; ?>
                      <button type="button" onclick='openEditTrxModal(<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES, "UTF-8"); ?>)' class="px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                      <button type="button" onclick="confirmDeleteTrx(<?php echo $t['id']; ?>, '<?php echo $t['transaction_no']; ?>')" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Debt Tab -->
  <div id="tab-debt" class="hidden flex flex-wrap -mx-3 transition-opacity duration-300">
    <div class="w-full max-w-full px-3">
      <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h5 class="font-bold text-slate-800 text-lg">Catatan Hutang</h5>
            <p class="text-xs text-slate-400">Pencatatan hutang pinjaman atau tagihan yang belum dibayar</p>
          </div>
          <button type="button" onclick="openAddTrxModal('debt')" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-tl from-amber-500 to-orange-400 rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Hutang
          </button>
        </div>
        <div class="flex-auto px-0 pt-4 pb-2">
          <div class="p-0 overflow-x-auto">
            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
              <thead class="align-bottom">
                <tr>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. Transaksi</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Keterangan</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Kategori & Akun</th>
                  <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nominal (Rp)</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tanggal</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($debts)): ?>
                  <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada catatan hutang.</td></tr>
                <?php else: foreach ($debts as $t): ?>
                <tr class="hover:bg-slate-50/50">
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-amber-700">
                    <?php echo Helper::e($t['transaction_no']); ?>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                    <span class="text-xs font-bold text-slate-800 block max-w-sm truncate"><?php echo Helper::e($t['description']); ?></span>
                    <span class="text-2xs text-slate-400">Petugas: <?php echo Helper::e($t['creator_name'] ?? 'System'); ?></span>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                    <span class="font-semibold text-slate-700 block"><?php echo Helper::e($t['category_name']); ?></span>
                    <span class="text-2xs text-slate-400"><?php echo Helper::e($t['account_name']); ?></span>
                  </td>
                  <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold font-mono text-amber-600">
                    <?php echo Helper::formatRupiah($t['amount'], false); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                    <?php echo Helper::formatDate($t['transaction_date']); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <?php if (!empty($t['attachment'])): ?>
                      <a href="/public/<?php echo Helper::e($t['attachment']); ?>" target="_blank" class="px-2 py-1 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Lihat Lampiran"><i class="fa-solid fa-image"></i></a>
                      <?php endif; ?>
                      <button type="button" onclick='openEditTrxModal(<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES, "UTF-8"); ?>)' class="px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                      <button type="button" onclick="confirmDeleteTrx(<?php echo $t['id']; ?>, '<?php echo $t['transaction_no']; ?>')" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Receivable Tab -->
  <div id="tab-receivable" class="hidden flex flex-wrap -mx-3 transition-opacity duration-300">
    <div class="w-full max-w-full px-3">
      <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h5 class="font-bold text-slate-800 text-lg">Catatan Piutang (Tagihan)</h5>
            <p class="text-xs text-slate-400">Tagihan pelanggan yang belum dilunasi seluruhnya</p>
          </div>
          <button type="button" onclick="openAddTrxModal('receivable')" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-tl from-blue-600 to-indigo-500 rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Piutang
          </button>
        </div>
        <div class="flex-auto px-0 pt-4 pb-2">
          <div class="p-0 overflow-x-auto">
            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
              <thead class="align-bottom">
                <tr>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. Transaksi</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Keterangan</th>
                  <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Kategori & Akun</th>
                  <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nominal (Rp)</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tanggal</th>
                  <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($receivables)): ?>
                  <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada transaksi piutang.</td></tr>
                <?php else: foreach ($receivables as $t): ?>
                <tr class="hover:bg-slate-50/50">
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-blue-700">
                    <?php echo Helper::e($t['transaction_no']); ?>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                    <span class="text-xs font-bold text-slate-800 block max-w-sm truncate"><?php echo Helper::e($t['description']); ?></span>
                    <span class="text-2xs text-slate-400">Petugas: <?php echo Helper::e($t['creator_name'] ?? 'System'); ?></span>
                  </td>
                  <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                    <span class="font-semibold text-slate-700 block"><?php echo Helper::e($t['category_name']); ?></span>
                    <span class="text-2xs text-slate-400"><?php echo Helper::e($t['account_name']); ?></span>
                  </td>
                  <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold font-mono text-blue-600">
                    <?php echo Helper::formatRupiah($t['amount'], false); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                    <?php echo Helper::formatDate($t['transaction_date']); ?>
                  </td>
                  <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <?php if (!empty($t['attachment'])): ?>
                      <a href="/public/<?php echo Helper::e($t['attachment']); ?>" target="_blank" class="px-2 py-1 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Lihat Lampiran"><i class="fa-solid fa-image"></i></a>
                      <?php endif; ?>
                      <button type="button" onclick='openEditTrxModal(<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES, "UTF-8"); ?>)' class="px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                      <button type="button" onclick="confirmDeleteTrx(<?php echo $t['id']; ?>, '<?php echo $t['transaction_no']; ?>')" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Modal Pop-Up: Tambah Transaksi -->
<div id="addTrxModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="addTrxModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-600 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-plus"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight" id="addTrxTitle">Tambah Transaksi Kas</h4>
          <span class="text-2xs text-slate-400">Catat mutasi keuangan baru</span>
        </div>
      </div>
      <button type="button" onclick="closeAddTrxModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('finance'); ?>" class="space-y-4" enctype="multipart/form-data">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_transaction">
      <input type="hidden" name="type" id="add_trx_type" value="expense">

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Tanggal Transaksi</label>
          <input type="date" name="transaction_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
          <input type="number" name="amount" required placeholder="0" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Rekening Kas / Bank <span class="text-red-500">*</span></label>
        <select name="account_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <option value="">-- Pilih Rekening --</option>
          <?php foreach ($accounts as $acc): ?>
            <option value="<?php echo $acc['id']; ?>"><?php echo Helper::e($acc['account_name'] . ' (' . Helper::formatRupiah($acc['current_balance']) . ')'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Kategori Transaksi</label>
        <input type="text" name="category_id" id="add_trx_category_id" placeholder="Contoh: Beban Listrik, Pendapatan Langganan..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Keterangan / Deskripsi</label>
        <input type="text" name="description" placeholder="(Opsional) Contoh: Bayar listrik kantor" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <!-- Image Upload -->
      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Lampiran Foto / Bukti</label>
        <div id="addTrxDropzone" onclick="document.getElementById('add_trx_image').click()" class="relative border-2 border-dashed border-slate-200 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-all group min-h-[80px]">
          <div id="addTrxPlaceholder" class="flex flex-col items-center gap-1.5 text-slate-400 group-hover:text-purple-500 transition-colors">
            <i class="fa-solid fa-image text-xl"></i>
            <span class="text-2xs font-semibold">Klik atau seret foto ke sini</span>
            <span class="text-3xs">JPG, PNG, WEBP — maks. 5MB</span>
          </div>
          <img id="addTrxPreview" src="" alt="Preview" class="hidden max-h-48 rounded-xl object-contain w-full">
          <button type="button" id="addTrxRemoveBtn" onclick="event.stopPropagation(); clearAddTrxImage()" class="hidden absolute top-2 right-2 w-6 h-6 bg-rose-500 text-white rounded-full text-xs flex items-center justify-center shadow hover:bg-rose-600 transition-colors">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <input type="file" id="add_trx_image" name="attachment" accept="image/*" class="hidden" onchange="previewAddTrxImage(this)">
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeAddTrxModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-600 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Transaksi
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Pop-Up: Edit Transaksi -->
<div id="editTrxModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editTrxModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Transaksi Kas</h4>
          <span class="text-2xs text-slate-400">Koreksi nilai, akun, atau kategori</span>
        </div>
      </div>
      <button type="button" onclick="closeEditTrxModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('transactions'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_transaction">
      <input type="hidden" name="id" id="edit_trx_id">

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Tanggal Transaksi</label>
          <input type="date" name="transaction_date" id="edit_trx_date" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
          <input type="number" name="amount" id="edit_trx_amount" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Rekening Kas / Bank <span class="text-red-500">*</span></label>
        <select name="account_id" id="edit_trx_account_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <?php foreach ($accounts as $acc): ?>
            <option value="<?php echo $acc['id']; ?>"><?php echo Helper::e($acc['account_name'] . (!empty($acc['bank_name']) ? ' (' . $acc['bank_name'] . ')' : '')); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Kategori Transaksi</label>
        <input type="text" name="category_id" id="edit_trx_category_id" placeholder="Contoh: Beban Listrik, Pendapatan Langganan..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Keterangan / Deskripsi</label>
        <input type="text" name="description" id="edit_trx_description" placeholder="(Opsional)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditTrxModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-blue-600 to-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Pop-Up: Import Transaksi Kas CSV -->
<div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[92vh] overflow-y-auto" id="importModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-file-import"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Import Data Transaksi Kas (CSV)</h4>
          <span class="text-2xs text-slate-400">Unduh template CSV, isi mutasi transaksi di Excel/Spreadsheet, lalu unggah kembali</span>
        </div>
      </div>
      <button type="button" onclick="closeImportModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Step 1: Download Template Box with Live 5 Sample Records Preview -->
    <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-50/80 to-pink-50/50 border border-purple-100/80 space-y-3 mb-5">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
        <div>
          <span class="text-3xs font-extrabold text-purple-900 uppercase tracking-wider block">Langkah 1: Unduh Template CSV & Pelajari Format Data</span>
          <p class="text-2xs text-slate-600">Template memuat <strong>6 kolom standar</strong> pencatatan mutasi kas beserta <strong>5 baris data contoh (sampel)</strong>.</p>
        </div>
        <a href="<?php echo Helper::url('transactions_download_template'); ?>" class="px-4 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 hover:scale-105 text-white font-bold text-xs rounded-xl shadow-soft-xs transition-all flex items-center justify-center gap-2 shrink-0">
          <i class="fa-solid fa-cloud-arrow-down text-sm"></i>
          <span>Unduh Template CSV (5 Sampel Data)</span>
        </a>
      </div>

      <!-- Live Preview of 5 Sample Data Records -->
      <div class="space-y-1.5 pt-1">
        <div class="flex items-center justify-between text-3xs font-extrabold text-slate-500 uppercase tracking-wider">
          <span>Pratinjau 5 Baris Data Sampel &amp; Struktur Kolom Template:</span>
          <span class="text-purple-700 font-mono">* Format Tanggal: YYYY-MM-DD</span>
        </div>
        <div class="overflow-x-auto border border-purple-100 rounded-2xl bg-white shadow-soft-xs">
          <table class="w-full text-3xs text-left whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-purple-100 text-slate-600 font-bold uppercase">
              <tr>
                <th class="py-2.5 px-3">Tanggal</th>
                <th class="py-2.5 px-3">Tipe</th>
                <th class="py-2.5 px-3">Rekening Kas / Bank</th>
                <th class="py-2.5 px-3">Kategori Transaksi</th>
                <th class="py-2.5 px-3 text-right">Nominal (Rp)</th>
                <th class="py-2.5 px-3">Keterangan / Deskripsi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
              <!-- Sample 1 -->
              <tr class="hover:bg-slate-50/60 font-mono">
                <td class="py-2 px-3 text-slate-800 font-bold">2026-08-10</td>
                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold font-sans text-3xs uppercase">Pemasukan</span></td>
                <td class="py-2 px-3 font-sans font-semibold text-slate-800">Bank BCA Operasional</td>
                <td class="py-2 px-3 font-sans text-slate-600">Pendapatan Langganan Internet</td>
                <td class="py-2 px-3 text-right font-bold text-emerald-600 font-mono">2.500.000</td>
                <td class="py-2 px-3 font-sans text-slate-600 max-w-xs truncate">Pembayaran langganan internet dedicated 5 pelanggan corporate</td>
              </tr>
              <!-- Sample 2 -->
              <tr class="hover:bg-slate-50/60 font-mono">
                <td class="py-2 px-3 text-slate-800 font-bold">2026-08-11</td>
                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold font-sans text-3xs uppercase">Pengeluaran</span></td>
                <td class="py-2 px-3 font-sans font-semibold text-slate-800">Kas Tunai Kantor</td>
                <td class="py-2 px-3 font-sans text-slate-600">Beban Operasional &amp; BBM Teknisi</td>
                <td class="py-2 px-3 text-right font-bold text-rose-600 font-mono">350.000</td>
                <td class="py-2 px-3 font-sans text-slate-600 max-w-xs truncate">BBM operasional teknisi penarikan kabel FO wilayah Depok</td>
              </tr>
              <!-- Sample 3 -->
              <tr class="hover:bg-slate-50/60 font-mono">
                <td class="py-2 px-3 text-slate-800 font-bold">2026-08-12</td>
                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold font-sans text-3xs uppercase">Pengeluaran</span></td>
                <td class="py-2 px-3 font-sans font-semibold text-slate-800">Bank Mandiri Penerimaan</td>
                <td class="py-2 px-3 font-sans text-slate-600">Beban Bandwidth / Upstream ISP</td>
                <td class="py-2 px-3 text-right font-bold text-rose-600 font-mono">12.500.000</td>
                <td class="py-2 px-3 font-sans text-slate-600 max-w-xs truncate">Pembayaran tagihan upstream bandwidth IP Transit 1 Gbps</td>
              </tr>
              <!-- Sample 4 -->
              <tr class="hover:bg-slate-50/60 font-mono">
                <td class="py-2 px-3 text-slate-800 font-bold">2026-08-13</td>
                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold font-sans text-3xs uppercase">Catatan Hutang</span></td>
                <td class="py-2 px-3 font-sans font-semibold text-slate-800">Bank Mandiri Penerimaan</td>
                <td class="py-2 px-3 font-sans text-slate-600">Hutang Supplier</td>
                <td class="py-2 px-3 text-right font-bold text-amber-600 font-mono">8.500.000</td>
                <td class="py-2 px-3 font-sans text-slate-600 max-w-xs truncate">Hutang pengadaan 50 unit modem ONT GPON ke Supplier Multi Data</td>
              </tr>
              <!-- Sample 5 -->
              <tr class="hover:bg-slate-50/60 font-mono">
                <td class="py-2 px-3 text-slate-800 font-bold">2026-08-14</td>
                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold font-sans text-3xs uppercase">Catatan Piutang</span></td>
                <td class="py-2 px-3 font-sans font-semibold text-slate-800">Bank BCA Operasional</td>
                <td class="py-2 px-3 font-sans text-slate-600">Catatan Piutang Pelanggan</td>
                <td class="py-2 px-3 text-right font-bold text-blue-600 font-mono">4.500.000</td>
                <td class="py-2 px-3 font-sans text-slate-600 max-w-xs truncate">Tagihan instalasi fiber optic PT Maju Berkarya (termin 2)</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex flex-wrap items-center justify-between text-3xs text-slate-500 pt-1 gap-1">
          <span>&bull; Data duplikat (tanggal, akun, tipe, nominal &amp; keterangan sama) akan <strong>otomatis dilewati (skip)</strong>.</span>
          <span>&bull; Data transaksi yang sudah ada <strong>tidak akan dihapus</strong>, hanya menambahkan data baru.</span>
          <span>&bull; Saldo kas/bank akan otomatis disesuaikan hanya untuk data transaksi baru yang masuk.</span>
        </div>
      </div>
    </div>

    <!-- Step 2: Upload CSV Form -->
    <div>
      <span class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider block mb-2">Langkah 2: Pilih & Unggah File CSV Transaksi</span>
      
      <form method="POST" action="<?php echo Helper::url('transactions_import_csv'); ?>" enctype="multipart/form-data" class="space-y-4">
        <?php echo Helper::csrfField(); ?>

        <!-- Drag & Drop Zone -->
        <div id="importTrxDropzone" onclick="document.getElementById('import_trx_csv').click()" class="border-2 border-dashed border-slate-200 hover:border-purple-400 rounded-3xl p-6 flex flex-col items-center justify-center gap-2.5 cursor-pointer bg-slate-50/50 hover:bg-purple-50/40 transition-all text-center group">
          <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-soft-xs flex items-center justify-center text-purple-600 text-xl group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-file-arrow-up"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-700" id="importTrxFileNameDisplay">Klik untuk memilih file CSV atau seret file ke sini</p>
            <p class="text-3xs text-slate-400">Mendukung format file .CSV (Maksimal 5MB)</p>
          </div>
          <input type="file" id="import_trx_csv" name="csv_file" accept=".csv,text/csv,application/vnd.ms-excel" class="hidden" onchange="handleImportTrxFile(this)">
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <button type="button" onclick="closeImportModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            Batal
          </button>
          <button type="submit" id="btnSubmitImportTrx" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
            <i class="fa-solid fa-upload text-xs"></i>
            <span>Mulai Import Transaksi</span>
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteTrxForm" method="POST" action="<?php echo Helper::url('transactions'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_transaction">
  <input type="hidden" name="id" id="deleteTrxId">
</form>

<script>
function switchTab(tab) {
    const tabs = ['income', 'expense', 'debt', 'receivable'];
    tabs.forEach(t => {
      document.getElementById('tab-' + t).classList.add('hidden');
      document.getElementById('tabBtn-' + t).className = "px-6 py-4 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap";
    });

    document.getElementById('tab-' + tab).classList.remove('hidden');
    
    if (tab === 'income') {
        document.getElementById('tabBtn-income').className = "px-6 py-4 text-sm transition-all border-b-[3px] border-emerald-500 text-emerald-700 font-black whitespace-nowrap";
    } else if (tab === 'expense') {
        document.getElementById('tabBtn-expense').className = "px-6 py-4 text-sm transition-all border-b-[3px] border-rose-500 text-rose-700 font-black whitespace-nowrap";
    } else if (tab === 'debt') {
        document.getElementById('tabBtn-debt').className = "px-6 py-4 text-sm transition-all border-b-[3px] border-amber-500 text-amber-700 font-black whitespace-nowrap";
    } else if (tab === 'receivable') {
        document.getElementById('tabBtn-receivable').className = "px-6 py-4 text-sm transition-all border-b-[3px] border-blue-500 text-blue-700 font-black whitespace-nowrap";
    }
}

function openAddTrxModal(type) {
  document.getElementById('add_trx_type').value = type;
  const title = document.getElementById('addTrxTitle');
  if (type === 'income') title.innerText = 'Tambah Pemasukan Kas';
  else if (type === 'expense') title.innerText = 'Tambah Pengeluaran Kas';
  else if (type === 'debt') title.innerText = 'Catat Hutang Baru';
  else if (type === 'receivable') title.innerText = 'Catat Piutang Baru';

  // Filter categories to only show matching type
  const catSelect = document.getElementById('add_trx_category_id');
  if (catSelect && catSelect.tagName === 'SELECT') {
    const options = catSelect.querySelectorAll('option');
    options.forEach(opt => {
      if (opt.value === '') return;
      if (opt.getAttribute('data-type') === type) {
        opt.style.display = 'block';
        opt.disabled = false;
      } else {
        opt.style.display = 'none';
        opt.disabled = true;
      }
    });
    catSelect.value = '';
  }

  const modal = document.getElementById('addTrxModal');
  const content = document.getElementById('addTrxModalContent');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeAddTrxModal() {
  const modal = document.getElementById('addTrxModal');
  const content = document.getElementById('addTrxModalContent');
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function openEditTrxModal(trx) {
  if (!trx) return;
  document.getElementById('edit_trx_id').value = trx.id || '';
  document.getElementById('edit_trx_date').value = trx.transaction_date || '';
  document.getElementById('edit_trx_amount').value = trx.amount || 0;
  document.getElementById('edit_trx_account_id').value = trx.account_id || '';
  document.getElementById('edit_trx_category_id').value = trx.category_name || '';
  document.getElementById('edit_trx_description').value = trx.description || '';

  const modal = document.getElementById('editTrxModal');
  const content = document.getElementById('editTrxModalContent');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeEditTrxModal() {
  const modal = document.getElementById('editTrxModal');
  const content = document.getElementById('editTrxModalContent');
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function openImportModal() {
  const modal = document.getElementById('importModal');
  const content = document.getElementById('importModalContent');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeImportModal() {
  const modal = document.getElementById('importModal');
  const content = document.getElementById('importModalContent');
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function handleImportTrxFile(input) {
  const file = input.files[0];
  const display = document.getElementById('importTrxFileNameDisplay');
  if (file) {
    display.innerHTML = `<span class="text-purple-700 font-bold"><i class="fa-solid fa-file-csv mr-1"></i> ${file.name}</span> <span class="text-slate-400">(${(file.size / 1024).toFixed(1)} KB)</span>`;
  } else {
    display.textContent = 'Klik untuk memilih file CSV atau seret file ke sini';
  }
}

function confirmDeleteTrx(id, trxNo) {
  if (confirm('Apakah Anda yakin ingin menghapus transaksi ' + trxNo + '? Saldo rekening kas/bank terkait akan dikembalikan secara otomatis.')) {
    document.getElementById('deleteTrxId').value = id;
    document.getElementById('deleteTrxForm').submit();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.querySelector('input[name="search"]');
  if(searchInput) {
    searchInput.addEventListener('input', function() {
      const term = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        // Skip empty state rows
        if (row.querySelector('td[colspan]')) return;
        
        const text = row.textContent.toLowerCase();
        if(text.includes(term)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Drag & drop for import modal
  const importDropzone = document.getElementById('importTrxDropzone');
  if (importDropzone) {
    ['dragenter', 'dragover'].forEach(evt => {
      importDropzone.addEventListener(evt, e => {
        e.preventDefault();
        importDropzone.classList.add('border-purple-400', 'bg-purple-50');
      });
    });
    ['dragleave', 'drop'].forEach(evt => {
      importDropzone.addEventListener(evt, e => {
        e.preventDefault();
        importDropzone.classList.remove('border-purple-400', 'bg-purple-50');
      });
    });
    importDropzone.addEventListener('drop', e => {
      const file = e.dataTransfer.files[0];
      if (file && (file.name.endsWith('.csv') || file.type.includes('csv') || file.type.includes('excel'))) {
        const dt = new DataTransfer();
        dt.items.add(file);
        const input = document.getElementById('import_trx_csv');
        input.files = dt.files;
        handleImportTrxFile(input);
      }
    });
  }

  // Image Upload Preview Drag & Drop
  const dropzone = document.getElementById('addTrxDropzone');
  if (dropzone) {
    ['dragenter', 'dragover'].forEach(evt => {
      dropzone.addEventListener(evt, e => {
        e.preventDefault();
        dropzone.classList.add('border-purple-400', 'bg-purple-50');
      });
    });
    ['dragleave', 'drop'].forEach(evt => {
      dropzone.addEventListener(evt, e => {
        e.preventDefault();
        dropzone.classList.remove('border-purple-400', 'bg-purple-50');
      });
    });
    dropzone.addEventListener('drop', e => {
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        const input = document.getElementById('add_trx_image');
        input.files = dt.files;
        previewAddTrxImage(input);
      }
    });
  }
});

// --- Image Upload Preview ---
function previewAddTrxImage(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) {
    alert('Ukuran file terlalu besar. Maksimum 5MB.');
    input.value = '';
    return;
  }
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('addTrxPreview').src = e.target.result;
    document.getElementById('addTrxPreview').classList.remove('hidden');
    document.getElementById('addTrxPlaceholder').classList.add('hidden');
    document.getElementById('addTrxRemoveBtn').classList.remove('hidden');
  };
  reader.readAsDataURL(file);
}

function clearAddTrxImage() {
  document.getElementById('add_trx_image').value = '';
  document.getElementById('addTrxPreview').src = '';
  document.getElementById('addTrxPreview').classList.add('hidden');
  document.getElementById('addTrxPlaceholder').classList.remove('hidden');
  document.getElementById('addTrxRemoveBtn').classList.add('hidden');
}
</script>
