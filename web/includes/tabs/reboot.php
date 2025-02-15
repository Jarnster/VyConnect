<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<?php
if (isset($_POST['reboot_confirm'])) {
    $api = new RestAPI();
    $api->reboot();
}
?>

<h2><i class="fa fa-refresh"></i> Reboot</h2>

<form method="post">
    <button type="submit" name="reboot_confirm" class="button" style="background:crimson;"><i class="fa fa-check"></i> Confirm Reboot</button>
</form>