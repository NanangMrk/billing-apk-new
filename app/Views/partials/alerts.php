<?php
// app/Views/partials/alerts.php - Flash Notification Banner
$flash = Helper::getFlash();
if ($flash):
    $type = $flash['type'];
    $message = $flash['message'];
    $colors = [
        'success' => 'bg-gradient-to-tl from-green-600 to-lime-400 text-white',
        'error' => 'bg-gradient-to-tl from-red-600 to-rose-400 text-white',
        'warning' => 'bg-gradient-to-tl from-orange-500 to-yellow-400 text-white',
        'info' => 'bg-gradient-to-tl from-blue-600 to-cyan-400 text-white',
    ];
    $icon = [
        'success' => 'fa-circle-check',
        'error' => 'fa-triangle-exclamation',
        'warning' => 'fa-circle-exclamation',
        'info' => 'fa-circle-info',
    ];
    $colorClass = $colors[$type] ?? $colors['info'];
    $iconClass = $icon[$type] ?? $icon['info'];
?>
<div class="relative p-4 mb-4 text-sm rounded-xl <?php echo $colorClass; ?> shadow-soft-md flex items-center justify-between transition-all duration-300">
    <div class="flex items-center">
        <i class="fa-solid <?php echo $iconClass; ?> mr-3 text-lg"></i>
        <span class="font-semibold"><?php echo Helper::e($message); ?></span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-white opacity-80 hover:opacity-100 transition-opacity">
        <i class="fa-solid fa-xmark text-base"></i>
    </button>
</div>
<?php endif; ?>
