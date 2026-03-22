<?php
$currentYear  = date('Y');
$softwareName = 'SmartSplit';
?>
<footer class="mt-auto px-7 py-5 border-t border-surface-100">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
        <p class="text-xs text-surface-400">
            &copy; <?= $currentYear ?> <?= $softwareName ?>. All rights reserved.
        </p>
        <p class="text-xs text-surface-300 font-mono">
            v1.0.0
        </p>
    </div>
</footer>