<?php
// app/Views/finance/transactions.php - Journal of Transactions
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
      
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h5 class="font-bold text-slate-800 text-lg">Jurnal Transaksi Kas & Bank</h5>
          <p class="text-xs text-slate-400">Mutasi keuangan lengkap pemasukan, beban operasional, dan transfer</p>
        </div>

        <a href="<?php echo Helper::url('finance'); ?>" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-plus mr-1"></i> Catat Transaksi
        </a>
      </div>

      <div class="flex-auto px-0 pt-4 pb-2">
        <div class="p-0 overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">No. Transaksi</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Keterangan</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Kategori & Akun</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Jenis</th>
                <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nominal (Rp)</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $t): 
                $isIncome = ($t['type'] === 'income');
              ?>
              <tr>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-purple-700">
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
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="px-2.5 py-1 text-2xs font-bold rounded-full <?php echo $isIncome ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> uppercase">
                    <?php echo $isIncome ? 'Pemasukan' : 'Pengeluaran'; ?>
                  </span>
                </td>
                <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold font-mono <?php echo $isIncome ? 'text-green-600' : 'text-red-600'; ?>">
                  <?php echo ($isIncome ? '+' : '-') . Helper::formatRupiah($t['amount'], false); ?>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                  <?php echo Helper::formatDate($t['transaction_date']); ?>
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
