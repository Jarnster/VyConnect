<?php
require 'includes/header.php';
require 'includes/utils.php';

// Tab configuration
$tabs = include 'includes/tabs.php';

// Get the current tab
$tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ? $_GET['tab'] : 'dashboard';
?>

<?php
if (isset($_POST['acceptPendingChanges'])) {
    $api = new RestAPI();
    $api->save_config_file();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['rejectPendingChanges'])) {
    $api = new RestAPI();
    $_SESSION['pendingChanges'] = false;
    $_SESSION['alert'] += '[Pending changes rejected] Committed changes are not saved to the config file. The committed changes are still active until next reboot.';
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['selectRouterIndex'])) {
    $_SESSION['routerIndex'] = intval($_POST['routerIndex']);
}
?>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><span class="material-icons">router</span> VyConnect</h2>
        <p style="text-align:center;font-size:14px;">Controller UI</p>
        <p style="text-align:center;font-size:14px;">Early Development</p>

        <form id="routerForm" method="post">
            <select name="routerIndex" id="routerSelect" onchange="document.getElementById('routerForm').submit();">
                <?php
                // Zorg ervoor dat de $ROUTERS array beschikbaar is
                if (isset($ROUTERS) && is_array($ROUTERS)) {
                    $selectedRouter = $_SESSION['routerIndex'] ?? 0;

                    function pingRouter($ip)
                    {
                        $api = new RestAPI($ip);
                        if ($api->retrieve() != null) {
                            return true;
                        }
                        return false;
                    }

                    foreach ($ROUTERS as $index => $router) {
                        $routerName = htmlspecialchars($router['name']);
                        $routerIp = $router['ip'];
                        $isActive = ($index == $selectedRouter);
                        $pingStatus = pingRouter($routerIp) ? 'UP' : 'DOWN';
                        $pingColor = $pingStatus == 'UP' ? 'green' : 'red';
                        $statusEmoji = $pingStatus == 'UP' ? '✅' : '❌';

                        if ($isActive) {
                            $routerName .= " (🦺 ACTIVE)";
                        }

                        echo "<option value='$index' style='color:$pingColor;'>$routerName ($statusEmoji $pingStatus)</option>";
                    }
                } else {
                    echo "<option disabled>No routers available</option>";
                }
                ?>
            </select>
            <input type="hidden" name="selectRouterIndex" value="1">
        </form>


        <?php
        if (isset($_SESSION['alert'])) {
            echo "<div class='alert'>
    <h1><i class='fa fa-bell'></i> ALERT</h1>
    <b>" . $_SESSION['alert'] . "</b>
    </div>
    <br>";
            $_SESSION['alert'] = null;
        }
        ?>

        <?php
        if (isset($_SESSION['pendingChanges']) && $_SESSION['pendingChanges'] == true) {
            echo "<div class='pendingChanges'>
            <h2><i class='fa fa-warning'></i> Unsaved Changes</h2>
            <p>You have committed changes, that aren't saved to the config file yet. If you are sure about your current configuration, accept the changes. <br> <p>Hint: if you want to see what's changed, use the CLI in Configuration mode ('configure') and enter: 'compare saved'</p>
            <form method='post' style='text-align:center'>
                <button type='submit' name='acceptPendingChanges' style='font-size:16px;background:green;padding:5px;'><i class='fa fa-check' style='color:white;'></i> Accept pending changes</button>
                <button type='submit' name='rejectPendingChanges' style='font-size:16px;background:red;padding:5px;'><i class='fa fa-close' style='color:white;'></i> Reject pending changes</button>
            </form>
            </div>
            <hr>";
        }
        ?>
        <ul>
            <?php
            foreach ($tabs as $tab_name => $data) {
                $active_class = ($tab === $tab_name) ? 'active' : '';
                echo "<li class='$active_class'><a href='?tab=$tab_name'><i class='fa {$data['icon']}'></i> {$data['title']}</a></li>";
            }
            echo "<li><a href='logout.php'><i class='fa fa-sign-out'></i> Logout</a></li>";
            ?>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php
        // Load the content for the current tab
        $tab_file = __DIR__ . "/includes/tabs/{$tab}.php";
        if (file_exists($tab_file)) {
            include $tab_file;
        } else {
            echo "<h2>Tab not found!</h2>";
        }
        ?>
    </div>
</div>

</body>

</html>