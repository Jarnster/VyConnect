<h2><i class="fa fa-tv"></i> Dashboard</h2>
<div class="dashboard-widgets">
    <div class="widget">
        <h2><i class="material-icons">router</i> VyConnect</h2>
        <p>Version: Early Development</p>
    </div>
    <div class="widget">
        <h2>Quick Overview</h2>
        <?php
        $Rest = new RestAPI($REST_BASE_URL);

        $runningConfiguration = $Rest->retrieve();
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
</div>