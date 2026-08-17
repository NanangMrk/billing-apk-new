<?php
// app/Views/ai/index.php - AI Advisor Interactive Interface
?>
<div class="flex flex-wrap -mx-3">
  
  <div class="w-full max-w-full px-3 mx-auto lg:w-9/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6 md:p-8 space-y-6">
      
      <!-- AI Header -->
      <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xl shadow-soft-md">
          <i class="fa-solid fa-robot"></i>
        </div>
        <div>
          <h4 class="font-extrabold text-slate-800 text-lg">Asisten & Penasihat Bisnis ISP</h4>
          <p class="text-xs text-slate-400">Analisis cerdas berbasis data riil tagihan, kas, stok, dan biaya operasional</p>
        </div>
      </div>

      <!-- Quick Prompt Suggestion Pills -->
      <div class="space-y-2">
        <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider block">Pertanyaan Bisnis Cepat:</span>
        <div class="flex flex-wrap gap-2">
          <button type="button" onclick="askQuick('Bisakah kita membeli 1 unit OLT GPON seharga Rp 35.000.000 bulan ini?')" class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold transition-colors text-left flex items-center gap-1.5">
            <i class="fa-solid fa-calculator text-2xs"></i> Kelayakan Beli OLT Bulan Ini?
          </button>
          <button type="button" onclick="askQuick('Siapa saja pelanggan yang belum membayar tagihan bulan ini dan berapa total piutangnya?')" class="px-3 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold transition-colors text-left flex items-center gap-1.5">
            <i class="fa-solid fa-file-invoice-dollar text-2xs"></i> Tagihan Belum Terbayar & Overdue
          </button>
          <button type="button" onclick="askQuick('Barang apa saja di gudang yang stoknya sudah menipis?')" class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold transition-colors text-left flex items-center gap-1.5">
            <i class="fa-solid fa-boxes-stacked text-2xs"></i> Peringatan Stok Menipis
          </button>
          <button type="button" onclick="askQuick('Bagaimana ringkasan keuangan dan laba rugi bulan ini?')" class="px-3 py-1.5 rounded-xl bg-green-50 hover:bg-green-100 text-green-700 text-xs font-semibold transition-colors text-left flex items-center gap-1.5">
            <i class="fa-solid fa-chart-pie text-2xs"></i> Ringkasan Finansial & Laba Bersih
          </button>
        </div>
      </div>

      <!-- Interactive Output Area -->
      <?php if (!empty($response)): ?>
      <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-soft-xs space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-200">
          <div class="w-7 h-7 rounded-lg bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs">
            <i class="fa-solid fa-brain"></i>
          </div>
          <div>
            <span class="text-xs font-bold text-slate-800">Tanggapan AI Advisor</span>
            <span class="text-2xs text-slate-400 block">Pertanyaan: "<?php echo Helper::e($userPrompt); ?>"</span>
          </div>
        </div>

        <div class="ai-response-body">
          <?php echo $response['content']; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Query Form -->
      <form method="POST" action="<?php echo Helper::url('ai'); ?>" id="aiForm" class="space-y-3">
        <?php echo Helper::csrfField(); ?>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Ketikkan Pertanyaan Operasional / Keuangan Anda</label>
          <div class="relative">
            <textarea name="prompt" id="promptTextarea" rows="3" required placeholder="contoh: Berapa total piutang yang jatuh tempo di atas 10 hari dan bagaimana rekomendasi penanganannya?" class="w-full text-xs p-3 pr-12 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white leading-relaxed"></textarea>
          </div>
        </div>

        <div class="text-right">
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-paper-plane mr-1"></i> Tanyakan ke AI
          </button>
        </div>
      </form>

    </div>
  </div>

</div>

<script>
function askQuick(text) {
  const textarea = document.getElementById("promptTextarea");
  textarea.value = text;
  document.getElementById("aiForm").submit();
}
</script>
