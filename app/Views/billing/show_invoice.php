<?php
// app/Views/billing/show_invoice.php - Invoice Detail & Payment Form
?>
<div class="flex flex-wrap -mx-3">
  
  <!-- Left Side: Invoice Document Preview -->
  <div class="w-full max-w-full px-3 mb-6 lg:w-8/12 lg:mb-0">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-8 border border-slate-100" id="printableInvoice">
      
      <!-- Invoice Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-6 gap-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xl font-bold shadow-soft-md">
            <i class="fa-solid fa-tower-broadcast"></i>
          </div>
          <div>
            <h4 class="font-black text-slate-800 text-lg tracking-tight"><?php echo Helper::e($company['company_name'] ?? 'PT Nusantara Net Mandiri'); ?></h4>
            <p class="text-2xs text-slate-400"><?php echo Helper::e($company['address'] ?? ''); ?> &bull; WA: <?php echo Helper::e($company['whatsapp'] ?? ''); ?></p>
          </div>
        </div>

        <div class="text-left sm:text-right">
          <span class="text-xs font-bold uppercase tracking-wider text-purple-700 block">INVOICE RESMI</span>
          <h5 class="text-lg font-extrabold text-slate-800 font-mono"><?php echo Helper::e($invoice['invoice_no']); ?></h5>
          <div class="mt-1">
            <?php echo Helper::statusBadge($invoice['payment_status']); ?>
          </div>
        </div>
      </div>

      <!-- Bill To & Meta Info -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-100 text-xs">
        <div>
          <span class="text-2xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Tagihan Ditujukan Kepada:</span>
          <h6 class="font-bold text-slate-800 text-sm mb-0.5"><?php echo Helper::e($invoice['customer_name']); ?></h6>
          <p class="text-slate-500 text-2xs mb-0.5 font-mono">ID: <?php echo Helper::e($invoice['customer_no']); ?> &bull; Telp: <?php echo Helper::e($invoice['phone']); ?></p>
          <p class="text-slate-500 text-2xs"><?php echo Helper::e($invoice['full_address']); ?></p>
          <?php if (!empty($invoice['odp_point'])): ?>
            <span class="text-2xs text-purple-700 font-medium mt-1 block">Titik Distribusi: <?php echo Helper::e($invoice['odp_point']); ?></span>
          <?php endif; ?>
        </div>

        <div class="sm:text-right space-y-1">
          <div><span class="text-slate-400">Periode Tagihan:</span> <span class="font-bold text-slate-700"><?php echo Helper::e($invoice['billing_period']); ?></span></div>
          <div><span class="text-slate-400">Tanggal Terbit:</span> <span class="font-bold text-slate-700"><?php echo Helper::formatDate($invoice['issue_date']); ?></span></div>
          <div><span class="text-slate-400">Jatuh Tempo:</span> <span class="font-bold text-red-600"><?php echo Helper::formatDate($invoice['due_date']); ?></span></div>
          <?php if ($invoice['payment_date']): ?>
            <div><span class="text-slate-400">Tanggal Lunas:</span> <span class="font-bold text-green-600"><?php echo Helper::formatDate($invoice['payment_date'], 'd/m/Y H:i'); ?></span></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Items Table -->
      <div class="py-6 overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead>
            <tr class="border-b border-slate-200 text-slate-400 uppercase text-2xs">
              <th class="py-2 font-bold">Deskripsi Layanan / Item</th>
              <th class="py-2 font-bold text-center">Qty</th>
              <th class="py-2 font-bold text-right">Tarif (Rp)</th>
              <th class="py-2 font-bold text-right">Subtotal (Rp)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($items as $item): ?>
            <tr>
              <td class="py-3">
                <span class="font-bold text-slate-800 block"><?php echo Helper::e($item['item_name']); ?></span>
                <span class="text-2xs text-slate-400"><?php echo Helper::e($item['notes'] ?: '-'); ?></span>
              </td>
              <td class="py-3 text-center"><?php echo $item['quantity']; ?></td>
              <td class="py-3 text-right font-mono"><?php echo Helper::formatRupiah($item['unit_price'], false); ?></td>
              <td class="py-3 text-right font-bold text-slate-800 font-mono"><?php echo Helper::formatRupiah($item['subtotal'], false); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Summary & Bank Info -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-200 text-xs">
        <div>
          <span class="text-2xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Instruksi Pembayaran:</span>
          <p class="text-2xs text-slate-600 leading-relaxed">
            <?php echo nl2br(Helper::e($company['bank_account_info'] ?? 'BCA: 1234567890 a/n PT Nusantara Net Mandiri')); ?>
          </p>
          <p class="text-2xs text-slate-400 mt-2">
            <?php echo Helper::e($company['invoice_footer'] ?? ''); ?>
          </p>
        </div>

        <div class="space-y-1.5 text-right font-mono">
          <div class="flex justify-between"><span class="text-slate-400">Subtotal:</span> <span class="font-semibold text-slate-700"><?php echo Helper::formatRupiah($invoice['subtotal']); ?></span></div>
          <?php if ($invoice['discount'] > 0): ?>
            <div class="flex justify-between"><span class="text-slate-400">Diskon:</span> <span class="font-semibold text-red-500">-<?php echo Helper::formatRupiah($invoice['discount']); ?></span></div>
          <?php endif; ?>
          <div class="flex justify-between"><span class="text-slate-400">PPN:</span> <span class="font-semibold text-slate-700"><?php echo Helper::formatRupiah($invoice['tax']); ?></span></div>
          <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200">
            <span>Grand Total:</span> <span class="text-purple-700"><?php echo Helper::formatRupiah($invoice['grand_total']); ?></span>
          </div>
          <div class="flex justify-between text-xs text-green-600 font-bold">
            <span>Sudah Dibayar:</span> <span><?php echo Helper::formatRupiah($invoice['paid_amount']); ?></span>
          </div>
          <?php if ($invoice['balance_due'] > 0): ?>
            <div class="flex justify-between text-xs text-red-600 font-bold">
              <span>Sisa Tagihan:</span> <span><?php echo Helper::formatRupiah($invoice['balance_due']); ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <!-- Right Side: Actions & Payment Entry Form -->
  <div class="w-full max-w-full px-3 lg:w-4/12">
    
    <!-- Action Shortcuts -->
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6 mb-6">
      <h6 class="font-bold text-slate-800 text-sm mb-3">Tindakan Cepat</h6>
      
      <div class="space-y-2">
        <button onclick="window.print()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition-colors">
          <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
        </button>

        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $invoice['phone']); ?>?text=Halo%20<?php echo urlencode($invoice['customer_name']); ?>,%20tagihan%20NusantaraNet%20Anda%20sebesar%20<?php echo urlencode(Helper::formatRupiah($invoice['grand_total'])); ?>%20jatuh%20tempo%20<?php echo urlencode($invoice['due_date']); ?>.%20Terima%20kasih." target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-green-50 hover:bg-green-100 text-green-700 font-bold text-xs flex items-center justify-center gap-2 transition-colors">
          <i class="fa-brands fa-whatsapp text-sm"></i> Kirim Notifikasi WhatsApp
        </a>
      </div>
    </div>

    <!-- Record Payment Box (If unpaid / partially paid) -->
    <?php if ($invoice['balance_due'] > 0): ?>
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6 border-2 border-purple-200 mb-6">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-sm">
          <i class="fa-solid fa-cash-register"></i>
        </div>
        <div>
          <h6 class="font-bold text-slate-800 text-sm leading-tight">Catat Pembayaran</h6>
          <span class="text-2xs text-slate-400">Input pelunasan tagihan pelanggan</span>
        </div>
      </div>

      <form method="POST" action="<?php echo Helper::url('record_payment'); ?>" class="space-y-3">
        <?php echo Helper::csrfField(); ?>
        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nominal Pembayaran (Rp)</label>
          <input type="number" name="amount" value="<?php echo $invoice['balance_due']; ?>" required max="<?php echo $invoice['balance_due']; ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:border-purple-500">
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Rekening Kas / Bank Penerima</label>
          <select name="account_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <?php foreach ($accounts as $acc): ?>
              <option value="<?php echo $acc['id']; ?>">
                <?php echo Helper::e($acc['account_name'] . ' (' . Helper::formatRupiah($acc['current_balance']) . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Metode Pembayaran</label>
          <select name="payment_method" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="BCA Transfer">BCA Transfer</option>
            <option value="Mandiri Transfer">Mandiri Transfer</option>
            <option value="QRIS">QRIS</option>
            <option value="Tunai / Cash">Tunai / Cash</option>
          </select>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nomor Referensi / Bukti (Opsional)</label>
          <input type="text" name="reference_no" placeholder="TRX-BCA-12345" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-2.5 bg-gradient-to-tl from-green-600 to-lime-400 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-check-circle mr-1"></i> Konfirmasi Pembayaran
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Payment History Table -->
    <?php if (!empty($payments)): ?>
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <h6 class="font-bold text-slate-800 text-sm mb-3">Riwayat Pembayaran Masuk</h6>
      <div class="space-y-3">
        <?php foreach ($payments as $p): ?>
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
          <div class="flex justify-between items-center mb-1">
            <span class="font-mono font-bold text-purple-700"><?php echo Helper::e($p['payment_no']); ?></span>
            <span class="font-bold text-green-600"><?php echo Helper::formatRupiah($p['amount']); ?></span>
          </div>
          <p class="text-2xs text-slate-500">
            <?php echo Helper::e($p['payment_method']); ?> &bull; Akun: <?php echo Helper::e($p['account_name']); ?>
          </p>
          <span class="text-2xs text-slate-400 block mt-1"><?php echo Helper::formatDate($p['payment_date']); ?> &bull; Petugas: <?php echo Helper::e($p['receiver_name'] ?? 'Admin'); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

</div>
