<?php
$flashOk = flash_take('flash_success');
$flashErr = flash_take('flash_error');
?>
<?php if ($flashOk !== null): ?>
    <div class="flash flash-success" role="status"><?= htmlspecialchars($flashOk) ?></div>
<?php endif; ?>
<?php if ($flashErr !== null): ?>
    <div class="flash flash-error" role="alert"><?= htmlspecialchars($flashErr) ?></div>
<?php endif; ?>
