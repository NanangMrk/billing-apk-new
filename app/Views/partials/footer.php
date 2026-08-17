<?php
// app/Views/partials/footer.php - Application Footer
?>
<footer class="pt-6 pb-4">
  <div class="w-full px-6 mx-auto">
    <div class="flex flex-wrap items-center -mx-3 lg:justify-between">
      <div class="w-full max-w-full px-3 mt-0 mb-6 shrink-0 lg:mb-0 lg:w-1/2 lg:flex-none">
        <div class="text-xs leading-normal text-center text-slate-500 lg:text-left">
          &copy; <?php echo date('Y'); ?> <span class="font-semibold text-slate-700">PT Nusantara Net Mandiri</span>. All rights reserved.
        </div>
      </div>
      <div class="w-full max-w-full px-3 mt-0 shrink-0 lg:w-1/2 lg:flex-none">
        <ul class="flex flex-wrap justify-center pl-0 mb-0 list-none lg:justify-end gap-4 text-xs text-slate-500">
          <li><a href="<?php echo Helper::url('landing'); ?>" class="hover:text-slate-800">Portal Publik</a></li>
          <li><a href="<?php echo Helper::url('ai'); ?>" class="hover:text-slate-800">AI Advisor</a></li>
          <li><a href="<?php echo Helper::url('settings_company'); ?>" class="hover:text-slate-800">Bantuan Sistem</a></li>
        </ul>
      </div>
    </div>
  </div>
</footer>
