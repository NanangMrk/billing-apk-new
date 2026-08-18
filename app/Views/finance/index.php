<?php
// app/Views/finance/index.php - Kas & Bank Accounts and Record Transaction
?>
<div class="flex flex-wrap -mx-3">
  
  <!-- Accounts List Cards -->
  <div class="w-full max-w-full px-3 mb-6 lg:w-7/12 lg:mb-0">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h5 class="font-bold text-slate-800 text-lg">Rekening Kas & Bank</h5>
          <p class="text-xs text-slate-400">Daftar saldo rekening penampung operasional ISP</p>
        </div>
        <a href="<?php echo Helper::url('transactions'); ?>" class="text-xs font-bold text-purple-700 hover:text-purple-900">
          Lihat Mutasi Jurnal <i class="fa-solid fa-arrow-right ml-1"></i>
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($accounts as $acc): 
          $icon = ($acc['account_type'] === 'cash') ? 'fa-wallet text-green-600 bg-green-50' : (($acc['account_type'] === 'qris') ? 'fa-qrcode text-pink-600 bg-pink-50' : 'fa-building-columns text-purple-600 bg-purple-50');
        ?>
        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 shadow-soft-xs space-y-2">
          <div class="flex items-center justify-between">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base <?php echo $icon; ?>">
              <i class="fa-solid <?php echo explode(' ', $icon)[0]; ?>"></i>
            </div>
            <span class="text-2xs font-bold px-2 py-0.5 rounded-full bg-white text-slate-600 border border-slate-200 uppercase font-mono">
              <?php echo Helper::e($acc['account_type']); ?>
            </span>
          </div>

          <div>
            <h6 class="text-xs font-bold text-slate-800"><?php echo Helper::e($acc['account_name']); ?></h6>
            <p class="text-2xs text-slate-400 font-mono"><?php echo Helper::e($acc['bank_name'] . ' &bull; ' . $acc['account_number']); ?></p>
          </div>

          <div class="pt-2 border-t border-slate-200">
            <span class="text-2xs text-slate-400 block font-medium">Saldo Saat Ini</span>
            <span class="text-lg font-black text-slate-900"><?php echo Helper::formatRupiah($acc['current_balance']); ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Record Transaction Quick Form -->
  <div class="w-full max-w-full px-3 lg:w-5/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
          <i class="fa-solid fa-money-bill-transfer"></i>
        </div>
        <div>
          <h5 class="font-bold text-slate-800 text-base leading-tight">Catat Pemasukan / Pengeluaran</h5>
          <span class="text-2xs text-slate-400">Input transaksi kas operasional harian</span>
        </div>
      </div>

      <form method="POST" action="<?php echo Helper::url('finance'); ?>" class="space-y-3">
        <?php echo Helper::csrfField(); ?>
        <input type="hidden" name="action" value="save_transaction">

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Jenis Transaksi</label>
            <?php $defaultType = $_GET['type'] ?? 'expense'; ?>
            <select name="type" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold" id="typeSelect">
              <option value="expense" <?php echo $defaultType === 'expense' ? 'selected' : ''; ?>>Pengeluaran (Expense)</option>
              <option value="income" <?php echo $defaultType === 'income' ? 'selected' : ''; ?>>Pemasukan (Income)</option>
              <option value="debt" <?php echo $defaultType === 'debt' ? 'selected' : ''; ?>>Catat Hutang</option>
              <option value="receivable" <?php echo $defaultType === 'receivable' ? 'selected' : ''; ?>>Catat Piutang</option>
            </select>
          </div>

          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Tanggal</label>
            <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Rekening Kas / Bank</label>
          <select name="account_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <?php foreach ($accounts as $acc): ?>
              <option value="<?php echo $acc['id']; ?>">
                <?php echo Helper::e($acc['account_name'] . ' (' . Helper::formatRupiah($acc['current_balance']) . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kategori Transaksi</label>
          <select name="category_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>">
                [<?php echo strtoupper($cat['type']); ?>] <?php echo Helper::e($cat['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nominal (Rupiah)</label>
          <input type="number" name="amount" required placeholder="0" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:border-purple-500">
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Deskripsi / Keterangan</label>
          <textarea name="description" rows="2" required placeholder="contoh: Pembayaran sewa tiang FO atau beli solar genset..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-save mr-1"></i> Simpan Transaksi
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
