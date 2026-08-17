<?php
// app/Views/settings/audit_logs.php - Activity Audit Logs
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
      
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
        <h5 class="font-bold text-slate-800 text-lg">Audit & Activity Log Keamanan</h5>
        <p class="text-xs text-slate-400">Jejak rekaman aktivitas pengguna, penagihan, transaksi keuangan, dan perubahan sistem</p>
      </div>

      <div class="flex-auto px-0 pt-4 pb-2">
        <div class="p-0 overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Waktu</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Pengguna</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Modul & Aksi</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Deskripsi / Record ID</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">IP Address</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
              <tr>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                  <?php echo Helper::formatDate($l['created_at'], 'd/m/Y H:i'); ?>
                </td>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <span class="font-bold text-slate-800 block"><?php echo Helper::e($l['user_name'] ?? 'System'); ?></span>
                  <?php if (!empty($l['username'])): ?>
                    <span class="text-2xs text-purple-700 font-mono">@<?php echo Helper::e($l['username']); ?></span>
                  <?php endif; ?>
                </td>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-bold text-2xs uppercase"><?php echo Helper::e($l['module']); ?></span>
                  <span class="text-2xs text-slate-600 font-mono block mt-1"><?php echo Helper::e($l['action']); ?></span>
                </td>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <span class="font-semibold text-slate-800 block"><?php echo Helper::e($l['new_value'] ?? $l['action']); ?></span>
                  <?php if (!empty($l['record_id'])): ?>
                    <span class="text-2xs text-slate-400 font-mono">ID: <?php echo Helper::e($l['record_id']); ?></span>
                  <?php endif; ?>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                  <?php echo Helper::e($l['ip_address']); ?>
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
