<?php
// app/Views/settings/company.php - Company Profile Form
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 mx-auto lg:w-8/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6 md:p-8">
      
      <div class="border-b border-slate-100 pb-4 mb-6">
        <h5 class="font-bold text-slate-800 text-lg">Profil Perusahaan & Template Invoice</h5>
        <p class="text-xs text-slate-400">Pengaturan identitas legal ISP, nomor kontak NOC, dan informasi rekening tagihan</p>
      </div>

      <form method="POST" action="<?php echo Helper::url('settings_company'); ?>" class="space-y-4">
        <?php echo Helper::csrfField(); ?>

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

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Instruksi Rekening Bank pada Invoice</label>
          <textarea name="bank_account_info" rows="2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"><?php echo Helper::e($company['bank_account_info'] ?? ''); ?></textarea>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Kaki (Footer Invoice)</label>
          <input type="text" name="invoice_footer" value="<?php echo Helper::e($company['invoice_footer'] ?? ''); ?>" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>

        <div class="text-right pt-4 border-t border-slate-100">
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
            <i class="fa-solid fa-save mr-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
