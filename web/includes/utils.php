<?php
// Ensure these functions are only defined once (by using 'if !function_exists')

if (!function_exists('set_config_pwd_hash')) {
    function set_config_pwd_hash($hash)
    {
        $config = json_decode(file_get_contents(filename: '../data/config.json'), true);
        $config["ADMIN_PWD_HASH"] = $hash;
        $json = json_encode($config, JSON_PRETTY_PRINT);
        file_put_contents("../data/config.json", $json);
    }
}

// Function to sanitize input
if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_interface_state')) {
    function get_interface_state($data) // Returns false when down, returns true when up
    {
        if (!$data->{"speed"} && !$data->{"duplex"}) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('create_html_interface_state')) {
    function create_html_interface_state($data)
    {
        if (get_interface_state($data)) {
            return "<b style='color:green;'><i class='fa fa-check' style='color:green;'></i> UP</b>";
        } else {
            return "<b style='color:crimson;'><i class='fa fa-stop' style='color:red;'></i> DOWN</b>";
        }
    }
}

if (!function_exists('get_selected_router_ip')) {
    function get_selected_router_ip()
    {
        $config = json_decode(file_get_contents(filename: '../data/config.json'), true);
        $ROUTERS = $config["VYOS_ROUTERS"];
        $routerIndex = intval($_SESSION['routerIndex']) ?? 0;
        return $ROUTERS[$routerIndex or 0]['ip'];
    }
}

if (!function_exists('get_selected_router_api_key')) {
    function get_selected_router_api_key()
    {
        $config = json_decode(file_get_contents(filename: '../data/config.json'), true);
        $ROUTERS = $config["VYOS_ROUTERS"];
        $routerIndex = intval($_SESSION['routerIndex']) ?? 0;
        return $ROUTERS[$routerIndex or 0]['api_key'];
    }
}
