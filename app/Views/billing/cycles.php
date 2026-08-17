<?php
// app/Views/billing/cycles.php - Billing Cycles
?>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 mx-auto lg:w-10/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
      
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
        <h5 class="font-bold text-slate-800 text-lg">Siklus Penagihan (Billing Cycles)</h5>
        <p class="text-xs text-slate-400">Aturan generate otomatis invoice, tanggal jatuh tempo, dan masa tenggang</p>
      </div>

      <div class="flex-auto px-0 pt-0 pb-2">
        <div class="p-0 overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nama Siklus</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Tgl Terbit</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Jatuh Tempo</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Grace Period</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Total Pelanggan</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cycles as $c): ?>
              <tr>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="text-xs font-bold text-slate-800 block"><?php echo Helper::e($c['name']); ?></span>
                  <span class="text-2xs text-slate-400"><?php echo Helper::e($c['description']); ?></span>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-purple-700">
                  Tanggal <?php echo $c['generate_day']; ?>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono font-bold text-red-600">
                  Tanggal <?php echo $c['due_day']; ?>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <?php echo $c['grace_period_days']; ?> Hari
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-bold text-xs">
                    <?php echo $c['total_customers']; ?> Pelanggan
                  </span>
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
