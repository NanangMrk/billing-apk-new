<?php
// app/Views/billing/create_invoice.php - Create / Generate Invoice Form
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 mx-auto lg:w-8/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
      
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl flex items-center justify-between">
        <div>
          <h5 class="mb-0 font-bold text-slate-800 text-lg">Penerbitan Invoice Tagihan</h5>
          <p class="text-xs text-slate-400">Generate invoice manual atau berkala untuk pelanggan</p>
        </div>
        <a href="<?php echo Helper::url('invoices'); ?>" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
          <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>

      <div class="p-6">
        <form method="POST" action="<?php echo Helper::url('create_invoice'); ?>" class="space-y-4" id="invoiceForm">
          <?php echo Helper::csrfField(); ?>

          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Pelanggan <span class="text-red-500">*</span></label>
            <select name="customer_id" required class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500" id="customerSelect">
              <option value="">-- Pilih Pelanggan --</option>
              <?php foreach ($customers as $c): ?>
                <option value="<?php echo $c['id']; ?>" data-price="<?php echo $c['pkg_price']; ?>" data-tax="<?php echo $c['tax_percent']; ?>" <?php echo ($preCustomerId === $c['id']) ? 'selected' : ''; ?>>
                  <?php echo Helper::e($c['customer_no'] . ' - ' . $c['name'] . ' (' . $c['pkg_name'] . ' - ' . Helper::formatRupiah($c['pkg_price']) . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Periode Tagihan</label>
              <input type="month" name="billing_period" value="<?php echo date('Y-m'); ?>" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Tanggal Terbit</label>
              <input type="date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Jatuh Tempo</label>
              <input type="date" name="due_date" value="<?php echo date('Y-m-10'); ?>" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
          </div>

          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
            <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Perhitungan Nominal</h6>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="font-bold text-xs text-slate-600 block mb-1">Subtotal Paket (Rp)</label>
                <input type="number" name="subtotal" id="subtotal" required placeholder="0" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 font-bold text-slate-800 bg-white">
              </div>

              <div>
                <label class="font-bold text-xs text-slate-600 block mb-1">Diskon / Potongan (Rp)</label>
                <input type="number" name="discount" id="discount" value="0" placeholder="0" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 bg-white">
              </div>

              <div>
                <label class="font-bold text-xs text-slate-600 block mb-1">PPN (Rp)</label>
                <input type="number" name="tax" id="tax" value="0" placeholder="0" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 bg-white">
              </div>
            </div>

            <div class="pt-2 flex justify-between items-center border-t border-slate-200">
              <span class="text-xs font-bold text-slate-700">Total Tagihan Final:</span>
              <span class="text-lg font-black text-purple-700" id="grandTotalLabel">Rp 0</span>
            </div>
          </div>

          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Tambahan (Opsional)</label>
            <textarea name="notes" rows="2" placeholder="Keterangan tagihan..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
          </div>

          <div class="text-right pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
              <i class="fa-solid fa-file-circle-check mr-1"></i> Terbitkan Tagihan
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const custSelect = document.getElementById("customerSelect");
  const subtotalInput = document.getElementById("subtotal");
  const discountInput = document.getElementById("discount");
  const taxInput = document.getElementById("tax");
  const grandLabel = document.getElementById("grandTotalLabel");

  function recalculate() {
    const sub = parseFloat(subtotalInput.value) || 0;
    const disc = parseFloat(discountInput.value) || 0;
    const tax = parseFloat(taxInput.value) || 0;
    const total = Math.max(0, (sub - disc) + tax);
    grandLabel.innerText = "Rp " + total.toLocaleString("id-ID");
  }

  custSelect.addEventListener("change", function() {
    const opt = custSelect.options[custSelect.selectedIndex];
    const price = parseFloat(opt.getAttribute("data-price")) || 0;
    const taxPct = parseFloat(opt.getAttribute("data-tax")) || 0;
    
    subtotalInput.value = price;
    taxInput.value = Math.round(price * (taxPct / 100));
    recalculate();
  });

  subtotalInput.addEventListener("input", recalculate);
  discountInput.addEventListener("input", recalculate);
  taxInput.addEventListener("input", recalculate);

  if (custSelect.value) {
    custSelect.dispatchEvent(new Event("change"));
  }
});
</script>
