<?php
// app/Views/settings/company.php - Company Profile Form
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 mx-auto lg:w-11/12 xl:w-11/12 transition-all duration-300" id="mainSettingsContainer">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6 md:p-8">
      
      <div class="border-b border-slate-100 pb-4 mb-4">
        <h5 class="font-bold text-slate-800 text-lg">Profil Perusahaan & Template Invoice</h5>
        <p class="text-xs text-slate-400">Pengaturan identitas legal ISP, nomor kontak NOC, dan informasi rekening tagihan</p>
      </div>

      <!-- Tabs Navigation -->
      <div class="flex items-center gap-4 border-b border-slate-200 mb-6 px-2 overflow-x-auto hide-scrollbar">
        <button type="button" onclick="switchCompanyTab('profile')" id="tabBtn-profile" class="px-4 py-3 text-sm transition-all border-b-[3px] border-purple-500 text-purple-700 font-black whitespace-nowrap">
          <i class="fa-solid fa-building mr-2"></i>Profil Dasar
        </button>
        <button type="button" onclick="switchCompanyTab('bank')" id="tabBtn-bank" class="px-4 py-3 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap">
          <i class="fa-solid fa-wallet mr-2"></i>Rekening Operasional
        </button>
        <button type="button" onclick="switchCompanyTab('invoice')" id="tabBtn-invoice" class="px-4 py-3 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap">
          <i class="fa-solid fa-file-invoice mr-2"></i>Template Invoice
        </button>
      </div>

      <form method="POST" action="<?php echo Helper::url('settings_company'); ?>">
        <?php echo Helper::csrfField(); ?>

        <!-- Tab: Profil Dasar -->
        <div id="tab-profile" class="space-y-4 transition-opacity duration-300">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Nama Legal Perusahaan</label>
              <input type="text" name="company_name" value="<?php echo Helper::e($company['company_name'] ?? ''); ?>" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Nama Brand / Komersial</label>
              <input type="text" name="brand_name" value="<?php echo Helper::e($company['brand_name'] ?? ''); ?>" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
          </div>

          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Kantor Pusat / Server HQ</label>
            <textarea name="address" rows="2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"><?php echo Helper::e($company['address'] ?? ''); ?></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Telepon Kantor</label>
              <input type="text" name="phone" value="<?php echo Helper::e($company['phone'] ?? ''); ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">WhatsApp CS / NOC</label>
              <input type="text" name="whatsapp" value="<?php echo Helper::e($company['whatsapp'] ?? ''); ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
            <div>
              <label class="font-bold text-xs text-slate-700 block mb-1">Email Resmi</label>
              <input type="email" name="email" value="<?php echo Helper::e($company['email'] ?? ''); ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            </div>
          </div>
        </div>

        <!-- Tab: Rekening Operasional -->
        <div id="tab-bank" class="hidden space-y-4 transition-opacity duration-300">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h6 class="font-bold text-slate-800"><i class="fa-solid fa-wallet mr-2"></i>Rekening Operasional & Kas</h6>
              <p class="text-xs text-slate-400">Daftar rekening yang digunakan untuk pencatatan transaksi keuangan (Pemasukan, Pengeluaran, dll)</p>
            </div>
            <button type="button" onclick="openAddAccModal()" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-tl from-emerald-600 to-teal-500 rounded-xl shadow-soft-md hover:scale-105 transition-all">
              <i class="fa-solid fa-plus mr-1"></i> Tambah Rekening
            </button>
          </div>

          <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-600">
              <thead class="bg-slate-50 text-xs text-slate-500 font-bold border-b border-slate-200">
                <tr>
                  <th class="p-3">Nama Rekening</th>
                  <th class="p-3">Bank / Tipe</th>
                  <th class="p-3">No. Rekening</th>
                  <th class="p-3 text-right">Saldo Saat Ini</th>
                  <th class="p-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($accounts)): ?>
                  <tr><td colspan="5" class="p-4 text-center text-slate-400">Belum ada data rekening operasional.</td></tr>
                <?php else: foreach ($accounts as $acc): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                    <td class="p-3 font-semibold text-slate-800"><?php echo Helper::e($acc['account_name']); ?></td>
                    <td class="p-3">
                      <span class="inline-block px-2 py-0.5 rounded text-2xs font-bold bg-slate-100 text-slate-600 uppercase tracking-wider mb-1">
                        <?php echo Helper::e($acc['account_type']); ?>
                      </span><br>
                      <span class="text-xs"><?php echo Helper::e($acc['bank_name'] ?: '-'); ?></span>
                    </td>
                    <td class="p-3 font-mono text-xs text-slate-500"><?php echo Helper::e($acc['account_number'] ?: '-'); ?></td>
                    <td class="p-3 text-right font-mono font-bold text-emerald-600">
                      <?php echo Helper::formatRupiah($acc['current_balance']); ?>
                    </td>
                    <td class="p-3 text-center">
                      <div class="flex items-center justify-center gap-2">
                        <button type="button" onclick='openEditAccModal(<?php echo htmlspecialchars(json_encode($acc), ENT_QUOTES, "UTF-8"); ?>)' class="px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button type="button" onclick="confirmDeleteAcc(<?php echo $acc['id']; ?>, '<?php echo $acc['account_name']; ?>')" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab: Template Invoice (Interactive Editor) -->
        <div id="tab-invoice" class="hidden space-y-4 transition-opacity duration-300">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Panel: Editor -->
            <div class="lg:col-span-5 space-y-5">
              <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                <h6 class="text-sm font-bold text-purple-800 mb-1"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Live Editor</h6>
                <p class="text-xs text-purple-600">Edit teks instruksi di bawah ini dan perhatikan perubahannya secara langsung pada pratinjau invoice di sebelah kanan.</p>
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Instruksi Pembayaran & Rekening</label>
                <textarea name="bank_account_info" id="live_bank_info" rows="6" placeholder="Contoh:&#10;Transfer ke Rekening Berikut:&#10;BCA: 123456789 a.n PT Nusantara&#10;Mandiri: 987654321 a.n PT Nusantara" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono"><?php echo Helper::e($company['bank_account_info'] ?? ''); ?></textarea>
              </div>

              <div>
                <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Kaki (Footer Invoice)</label>
                <input type="text" name="invoice_footer" id="live_footer" value="<?php echo Helper::e($company['invoice_footer'] ?? ''); ?>" placeholder="Terima kasih telah menggunakan layanan kami..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              </div>

            </div>

            <!-- Right Panel: Live Preview Mockup -->
            <div class="lg:col-span-7">
              <div class="bg-slate-50 p-4 rounded-3xl border border-slate-200">
                <h6 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 text-center">Live Preview</h6>
                
                <!-- Mockup Invoice Container -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 pointer-events-none transform scale-[0.85] origin-top md:scale-100">
                  
                  <!-- Mock Header -->
                  <div class="flex justify-between border-b border-slate-100 pb-4 mb-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-tower-broadcast"></i>
                      </div>
                      <div>
                        <h4 class="font-black text-slate-800 text-sm" id="mockup_company_name"><?php echo Helper::e($company['company_name'] ?? 'PT Nusantara'); ?></h4>
                        <p class="text-[9px] text-slate-400" id="mockup_company_contact">WA: <?php echo Helper::e($company['whatsapp'] ?? '08123456789'); ?></p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="text-[9px] font-bold uppercase tracking-wider text-purple-700 block">INVOICE RESMI</span>
                      <h5 class="text-sm font-extrabold text-slate-800 font-mono">INV-2608-0001</h5>
                    </div>
                  </div>

                  <!-- Mock Items -->
                  <div class="border-b border-slate-100 pb-4 mb-4">
                    <table class="w-full text-left text-[10px]">
                      <thead class="text-slate-400 uppercase border-b border-slate-100">
                        <tr><th class="py-1">Deskripsi Layanan</th><th class="py-1 text-right">Subtotal</th></tr>
                      </thead>
                      <tbody>
                        <tr><td class="py-2 font-bold text-slate-800">Paket Internet 50 Mbps</td><td class="py-2 text-right font-mono font-bold">Rp 350.000</td></tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Mock Footer & Bank Info -->
                  <div class="grid grid-cols-2 gap-4 text-[10px]">
                    <div>
                      <span class="font-bold uppercase tracking-wider text-slate-400 block mb-1">Instruksi Pembayaran:</span>
                      <div id="mockup_bank_info" class="text-slate-600 leading-relaxed whitespace-pre-wrap font-mono bg-slate-50 p-2 rounded-lg border border-slate-100 min-h-[60px]"><?php echo Helper::e($company['bank_account_info'] ?? ''); ?></div>
                      <p id="mockup_footer" class="text-slate-400 mt-2 italic"><?php echo Helper::e($company['invoice_footer'] ?? ''); ?></p>
                    </div>
                    <div class="text-right space-y-1 font-mono">
                      <div class="flex justify-between"><span class="text-slate-400">Subtotal:</span> <span class="font-semibold text-slate-700">Rp 350.000</span></div>
                      <div class="flex justify-between text-[11px] font-bold text-slate-900 pt-1 border-t border-slate-100">
                        <span>Grand Total:</span> <span class="text-purple-700">Rp 350.000</span>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="text-right pt-4 mt-6 border-t border-slate-100">
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-save mr-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<form id="deleteAccForm" method="POST" action="<?php echo Helper::url('settings_company'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_account">
  <input type="hidden" name="id" id="delete_acc_id">
</form>

<!-- Modal Add Account -->
<div id="addAccModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 transform transition-all duration-300 scale-95 opacity-0" id="addAccModalContent">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <h4 class="font-black text-slate-800 text-lg">Tambah Rekening Baru</h4>
      <button type="button" onclick="closeAddAccModal()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark text-lg"></i></button>
    </div>
    <form method="POST" action="<?php echo Helper::url('settings_company'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_account">
      
      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Rekening / Kas <span class="text-red-500">*</span></label>
        <input type="text" name="account_name" required placeholder="Contoh: Kas Utama, BCA Operasional..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Tipe Rekening</label>
          <select name="account_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="cash">Uang Tunai / Kas</option>
            <option value="bank" selected>Rekening Bank</option>
            <option value="qris">QRIS / E-Wallet</option>
            <option value="gateway">Payment Gateway</option>
          </select>
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nama Bank (Opsional)</label>
          <input type="text" name="bank_name" placeholder="BCA, Mandiri, dll" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">No. Rekening (Opsional)</label>
          <input type="text" name="account_number" placeholder="Nomor rekening" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Saldo Awal (Rp)</label>
          <input type="number" name="opening_balance" value="0" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold text-emerald-600">
        </div>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeAddAccModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-emerald-600 to-teal-500 text-white font-bold text-xs rounded-xl shadow-soft-md hover:scale-105 transition-all">Simpan Rekening</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Account -->
<div id="editAccModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 transform transition-all duration-300 scale-95 opacity-0" id="editAccModalContent">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <h4 class="font-black text-slate-800 text-lg">Edit Rekening</h4>
      <button type="button" onclick="closeEditAccModal()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark text-lg"></i></button>
    </div>
    <form method="POST" action="<?php echo Helper::url('settings_company'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_account">
      <input type="hidden" name="id" id="edit_acc_id">
      
      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Rekening / Kas <span class="text-red-500">*</span></label>
        <input type="text" name="account_name" id="edit_acc_name" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Tipe Rekening</label>
          <select name="account_type" id="edit_acc_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="cash">Uang Tunai / Kas</option>
            <option value="bank">Rekening Bank</option>
            <option value="qris">QRIS / E-Wallet</option>
            <option value="gateway">Payment Gateway</option>
          </select>
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nama Bank (Opsional)</label>
          <input type="text" name="bank_name" id="edit_acc_bank" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>
      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">No. Rekening (Opsional)</label>
        <input type="text" name="account_number" id="edit_acc_number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditAccModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-blue-600 to-indigo-500 text-white font-bold text-xs rounded-xl shadow-soft-md hover:scale-105 transition-all">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function switchCompanyTab(tab) {
  document.getElementById('tab-profile').classList.add('hidden');
  document.getElementById('tab-bank').classList.add('hidden');
  document.getElementById('tab-invoice').classList.add('hidden');
  
  document.getElementById('tabBtn-profile').className = "px-4 py-3 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap";
  document.getElementById('tabBtn-bank').className = "px-4 py-3 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap";
  document.getElementById('tabBtn-invoice').className = "px-4 py-3 text-sm transition-all text-slate-500 hover:text-slate-700 border-b-[3px] border-transparent font-semibold whitespace-nowrap";

  document.getElementById('tab-' + tab).classList.remove('hidden');
  if (tab === 'profile') {
    document.getElementById('tabBtn-profile').className = "px-4 py-3 text-sm transition-all border-b-[3px] border-purple-500 text-purple-700 font-black whitespace-nowrap";
  } else if (tab === 'bank') {
    document.getElementById('tabBtn-bank').className = "px-4 py-3 text-sm transition-all border-b-[3px] border-purple-500 text-purple-700 font-black whitespace-nowrap";
  } else if (tab === 'invoice') {
    document.getElementById('tabBtn-invoice').className = "px-4 py-3 text-sm transition-all border-b-[3px] border-purple-500 text-purple-700 font-black whitespace-nowrap";
  }
}

// Live Preview Logic
document.addEventListener('DOMContentLoaded', () => {
  const bankInput = document.getElementById('live_bank_info');
  const footerInput = document.getElementById('live_footer');
  
  const mockBank = document.getElementById('mockup_bank_info');
  const mockFooter = document.getElementById('mockup_footer');

  function updatePreview() {
    mockBank.textContent = bankInput.value || '-';
    mockFooter.textContent = footerInput.value || '';
  }

  bankInput.addEventListener('input', updatePreview);
  footerInput.addEventListener('input', updatePreview);
  updatePreview(); // Initial load
});

function openAddAccModal() {
  const modal = document.getElementById('addAccModal');
  const content = document.getElementById('addAccModalContent');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeAddAccModal() {
  const modal = document.getElementById('addAccModal');
  const content = document.getElementById('addAccModalContent');
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function openEditAccModal(acc) {
  document.getElementById('edit_acc_id').value = acc.id;
  document.getElementById('edit_acc_name').value = acc.account_name;
  document.getElementById('edit_acc_type').value = acc.account_type;
  document.getElementById('edit_acc_bank').value = acc.bank_name || '';
  document.getElementById('edit_acc_number').value = acc.account_number || '';
  
  const modal = document.getElementById('editAccModal');
  const content = document.getElementById('editAccModalContent');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeEditAccModal() {
  const modal = document.getElementById('editAccModal');
  const content = document.getElementById('editAccModalContent');
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function confirmDeleteAcc(id, name) {
  if (confirm(`Apakah Anda yakin ingin menghapus rekening "${name}"?\n(Peringatan: Rekening yang sudah memiliki riwayat transaksi mungkin tidak dapat dihapus)`)) {
    document.getElementById('delete_acc_id').value = id;
    document.getElementById('deleteAccForm').submit();
  }
}
</script>
