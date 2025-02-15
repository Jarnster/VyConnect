<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<?php
if (isset($_POST['reboot_power_off'])) {
    $api = new RestAPI();
    $api->poweroff();
}
?>

<h2><i class="fa fa-refresh"></i> Power Off</h2>

<form method="post" autocomplete="off">
    <button type="submit" name="reboot_power_off" class="button" style="background:crimson;"><i class="fa fa-check"></i> Confirm Power Off</button>
</form>