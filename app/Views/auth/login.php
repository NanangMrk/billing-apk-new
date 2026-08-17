<?php
// app/Views/auth/login.php - Login Form
?>
<div class="p-6">
  <div class="mb-4 text-center">
    <h3 class="font-bold text-slate-800 text-xl">Masuk ke Sistem</h3>
    <p class="mb-0 text-xs text-slate-400">Masukkan username atau email dan kata sandi Anda</p>
  </div>

  <form role="form" method="POST" action="<?php echo Helper::url('login'); ?>" class="space-y-4">
    <?php echo Helper::csrfField(); ?>

    <div>
      <label class="mb-2 ml-1 font-bold text-xs text-slate-700 block">Username / Email</label>
      <div class="relative flex items-center">
        <input type="text" name="username" value="<?php echo Helper::e($_POST['username'] ?? 'admin'); ?>" required autofocus placeholder="contoh: admin" class="focus:shadow-soft-primary-outline text-xs leading-5.6 ease-soft block w-full appearance-none rounded-xl border border-solid border-gray-300 bg-white bg-clip-padding py-2.5 px-3 font-normal text-slate-700 transition-all focus:border-purple-500 focus:outline-none" />
      </div>
    </div>

    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="ml-1 font-bold text-xs text-slate-700 block">Kata Sandi</label>
      </div>
      <div class="relative flex items-center">
        <input type="password" name="password" value="admin123" required placeholder="Kata sandi" class="focus:shadow-soft-primary-outline text-xs leading-5.6 ease-soft block w-full appearance-none rounded-xl border border-solid border-gray-300 bg-white bg-clip-padding py-2.5 px-3 font-normal text-slate-700 transition-all focus:border-purple-500 focus:outline-none" />
      </div>
    </div>

    <div class="flex items-center pl-1">
      <input id="remember" type="checkbox" class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500" />
      <label for="remember" class="ml-2 text-xs font-normal text-slate-600">Ingat sesi saya</label>
    </div>

    <div class="text-center pt-2">
      <button type="submit" class="inline-block w-full px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all bg-gradient-to-tl from-purple-700 to-pink-500 rounded-xl cursor-pointer text-xs shadow-soft-md hover:shadow-soft-lg hover:scale-[1.02] tracking-wider">
        <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk Sekarang
      </button>
    </div>
  </form>

  <!-- Demo credentials box for easy access -->
  <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
    <span class="text-2xs font-bold text-slate-500 uppercase tracking-wider block mb-2">Akun Demo Tersedia:</span>
    <div class="grid grid-cols-2 gap-2 text-2xs">
      <div class="p-1.5 bg-white rounded border border-slate-100 cursor-pointer hover:bg-purple-50 transition-colors" onclick="document.querySelector('input[name=username]').value='admin'; document.querySelector('input[name=password]').value='admin123';">
        <span class="font-bold text-slate-800 block">Super Admin</span>
        <span class="text-slate-400">admin / admin123</span>
      </div>
      <div class="p-1.5 bg-white rounded border border-slate-100 cursor-pointer hover:bg-purple-50 transition-colors" onclick="document.querySelector('input[name=username]').value='owner'; document.querySelector('input[name=password]').value='admin123';">
        <span class="font-bold text-slate-800 block">Direktur/Owner</span>
        <span class="text-slate-400">owner / admin123</span>
      </div>
      <div class="p-1.5 bg-white rounded border border-slate-100 cursor-pointer hover:bg-purple-50 transition-colors" onclick="document.querySelector('input[name=username]').value='finance'; document.querySelector('input[name=password]').value='admin123';">
        <span class="font-bold text-slate-800 block">Finance</span>
        <span class="text-slate-400">finance / admin123</span>
      </div>
      <div class="p-1.5 bg-white rounded border border-slate-100 cursor-pointer hover:bg-purple-50 transition-colors" onclick="document.querySelector('input[name=username]').value='teknisi'; document.querySelector('input[name=password]').value='admin123';">
        <span class="font-bold text-slate-800 block">Teknisi</span>
        <span class="text-slate-400">teknisi / admin123</span>
      </div>
    </div>
  </div>

</div>
