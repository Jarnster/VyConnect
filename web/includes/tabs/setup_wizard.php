<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<h2><i class="fa fa-wrench"></i> Setup Wizard</h2>
<p>Used to setup features</p>
<!-- <form method="post" autocomplete="off">
    <input type="text" name="interface" placeholder="Interface Name" required>
</form> -->
