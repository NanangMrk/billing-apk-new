<?php
// app/Views/payroll/index.php - Payroll Overview & Employee Salaries
?>
<!-- KPI Cards -->
<div class="flex flex-wrap -mx-3 mb-6">
  <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 xl:w-1/3 xl:mb-0">
    <div class="p-4 bg-white rounded-2xl shadow-soft-xl border border-slate-100">
      <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider block">Total Karyawan Aktif</span>
      <h5 class="text-xl font-bold text-slate-800 mb-0"><?php echo count($employees); ?> Orang</h5>
      <span class="text-2xs text-slate-400">Tim teknis, NOC, dan staf kantor</span>
    </div>
  </div>

  <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 xl:w-1/3 xl:mb-0">
    <div class="p-4 bg-white rounded-2xl shadow-soft-xl border border-slate-100">
      <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider block">Estimasi Beban Gaji Pokok</span>
      <h5 class="text-xl font-bold text-purple-700 mb-0"><?php echo Helper::formatRupiah($totalPayrollCost); ?></h5>
      <span class="text-2xs text-slate-400">Alokasi bulanan operasional tim</span>
    </div>
  </div>

  <div class="w-full max-w-full px-3 sm:w-1/2 xl:w-1/3">
    <div class="p-4 bg-white rounded-2xl shadow-soft-xl border border-slate-100">
      <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider block">Status Penggajian Bulan Ini</span>
      <h5 class="text-xl font-bold text-green-600 mb-0">Tersedia / Siap Run</h5>
      <span class="text-2xs text-slate-400">Periode <?php echo date('F Y'); ?></span>
    </div>
  </div>
</div>

<!-- Employee Salary Table -->
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
      
      <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
        <h5 class="font-bold text-slate-800 text-lg">Daftar Karyawan & Komponen Gaji</h5>
        <p class="text-xs text-slate-400">Master data karyawan, divisi, dan rekening penerimaan gaji</p>
      </div>

      <div class="flex-auto px-0 pt-4 pb-2">
        <div class="p-0 overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Nama Karyawan</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Divisi & Posisi</th>
                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Rekening Payroll</th>
                <th class="px-6 py-3 font-bold text-right uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Gaji Pokok (Rp)</th>
                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 text-2xs tracking-tight text-slate-400 opacity-70">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($employees as $emp): ?>
              <tr>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="text-xs font-bold text-slate-800 block"><?php echo Helper::e($emp['name']); ?></span>
                  <span class="text-2xs text-purple-700 font-mono"><?php echo Helper::e($emp['employee_no']); ?> &bull; <?php echo Helper::e($emp['phone']); ?></span>
                </td>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs">
                  <span class="font-bold text-slate-700 block"><?php echo Helper::e($emp['position']); ?></span>
                  <span class="text-2xs text-slate-400"><?php echo Helper::e($emp['department_name']); ?></span>
                </td>
                <td class="p-3 px-6 align-middle bg-transparent border-b whitespace-nowrap text-xs font-mono">
                  <span class="font-semibold text-slate-800 block"><?php echo Helper::e($emp['bank_name']); ?>: <?php echo Helper::e($emp['bank_account']); ?></span>
                  <span class="text-2xs text-slate-400 uppercase"><?php echo Helper::e($emp['employment_status']); ?></span>
                </td>
                <td class="p-3 px-6 text-right align-middle bg-transparent border-b whitespace-nowrap text-xs font-bold text-slate-900 font-mono">
                  <?php echo Helper::formatRupiah($emp['basic_salary']); ?>
                </td>
                <td class="p-3 px-6 text-center align-middle bg-transparent border-b whitespace-nowrap">
                  <span class="px-2.5 py-1 text-2xs font-bold rounded-full bg-green-100 text-green-700 uppercase">Aktif</span>
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
