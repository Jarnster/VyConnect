<?php
require_once 'utils.php';

$config = json_decode(file_get_contents('../data/config.json'), true);

// Path and other configurations
$ADMIN_PWD = $config['ADMIN_PWD'] ?? 'zendns';
$PWD_HASH = $config['ADMIN_PWD_HASH'] ?? null;
$ROUTERS = $config['VYOS_ROUTERS'];
$VYOS_API_TIMEOUT = $config['VYOS_API_TIMEOUT'];
$curl_debugging = $config["CURL_DEBUGGING"];
$disable_ssl_verify = $config["DISABLE_SSL_VERIFY"];
$__VERSIONING_CODE = $config["__VERSIONING_CODE"];

// If there's no existing password hash in the config, generate and save one
if ($PWD_HASH === null) {
    $PWD_HASH = password_hash($ADMIN_PWD, PASSWORD_DEFAULT);
    set_config_pwd_hash($PWD_HASH); // Store the newly generated hash in the config
}
