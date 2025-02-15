<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<h2><i class="fa fa-wrench"></i> Setup Wizard</h2>

<form method="post">
    <input type="text" name="interface" placeholder="Interface Name" required>
</form>
