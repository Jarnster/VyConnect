<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<h2><i class="fa fa-tv"></i> Dashboard</h2>
<div class="dashboard-widgets">
    <div class="widget">
        <h2><i class="fa fa-eye"></i> Quick Overview</h2>
        <?php
        $api = new RestAPI();

        $runningConfiguration = $api->retrieve();
        $runningConfiguration = json_decode($runningConfiguration);
        $runningConfiguration = $runningConfiguration->data;

        $interfaces = $runningConfiguration->interfaces;
        $ethernet_interfaces = $interfaces->ethernet;

        $amount_up = 0;
        $amount_down = 0;

        foreach ($ethernet_interfaces as $interface) {
            if (get_interface_state($interface)) {
                $amount_up++;
            } else {
                $amount_down++;
            }
        }

        if ($amount_down === 0) {
            echo "<b style='color:green;'><i class='fa fa-check' style='color:green;'></i> All interfaces UP</b>";
        } elseif ($amount_up === 0) {
            echo "<b style='color:crimson;'><i class='fa fa-close' style='color:red;'></i> All interfaces DOWN</b>";
        } else {
            echo "<b style='color:orange;'><i class='fa fa-warning' style='color:orange;'></i> $amount_down interfaces DOWN </b> <br> <b style='color:green;'><i class='fa fa-check' style='color:green;'></i> $amount_up interfaces UP</b>";
        }
        ?>
    </div>
    <div class="widget">
        <h2><i class="fa fa-tachometer-alt"></i> System Performance</h2>
        <?php
        function formatData($data)
        {
            // Remove extra spaces and new lnes
            $data = preg_replace('/\s+/', ' ', trim($data));
            // Turn spaces into <br>
            return str_replace(" ", "<br>", htmlspecialchars($data));
        }

        // Uptime and load averages
        $uptime = $api->show_running_config(["system", "uptime"]);
        $uptime = json_decode($uptime)->data;

        $uptime = preg_replace('/\s+/', ' ', trim($uptime));

        if (strpos($uptime, "Load averages:") !== false) {
            list($uptime_text, $load_avg) = explode(" Load averages: ", $uptime, 2);
        } else {
            $uptime_text = $uptime;
            $load_avg = "Load averages: N/A"; // Fallback value
        }

        echo "<strong>Uptime:</strong><br>" . nl2br(htmlspecialchars($uptime_text)) . "<br><hr>";
        echo "<strong>Load Averages:</strong><br>" . nl2br(htmlspecialchars($load_avg)) . "<br><hr>";

        // Memory Info
        $memory_info = $api->show_running_config(["system", "memory"]);
        echo "<strong>Memory:</strong><br>" . formatData(json_decode($memory_info)->data) . "<br><hr>";

        // CPU Info
        $cpu_info = $api->show_running_config(["system", "cpu"]);
        echo "<strong>CPU Info:</strong><br>" . formatData(json_decode($cpu_info)->data) . "<br><hr>";

        // Storage Info
        $storage_info = $api->show_running_config(["system", "storage"]);
        echo "<strong>Storage:</strong><br>" . formatData(json_decode($storage_info)->data);
        ?>
    </div>
</div>