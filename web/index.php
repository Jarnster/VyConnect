<?php
require 'includes/header.php';
require 'includes/utils.php';

// Tab configuration
$tabs = include 'includes/tabs.php';

// Get the current tab
$tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ? $_GET['tab'] : 'dashboard';

// Alert system
if (!isset($_SESSION['alerts'])) {
    $_SESSION['alerts'] = [];
}

function addAlert($message, $type = 'info')
{
    foreach ($_SESSION['alerts'] as $alert) {
        if ($alert['message'] === $message && $alert['type'] === $type) {
            return; // Cancel if another alert with the same content already exists
        }
    }
    $_SESSION['alerts'][] = [
        'message' => $message,
        'type' => $type,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function check_version()
{
    require 'includes/config.php';

    $url = "https://vyconnect.jarne.synology.me/version_checker.php?current_ver=" . $__VERSIONING_CODE;

    if (isset($_SESSION['version_check_response']) && time() - $_SESSION['version_check_time'] < (3600 * 24 * 1)) {
        $response_data = $_SESSION['version_check_response'];
    } else {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($curl_debugging) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        if ($disable_ssl_verify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response !== false) {
            $response_data = json_decode($response, true);

            $_SESSION['version_check_response'] = $response_data;
            $_SESSION['version_check_time'] = time();
        }
    }

    if ($response_data) {
        if ($response_data["status"] != "success") {
            addAlert($response_data["message"], $response_data["status"]);
        }
    }
}

check_version();

// POST Handlers
if (isset($_POST['dismissAlert'])) {
    $index = intval($_POST['dismissAlert']);
    unset($_SESSION['alerts'][$index]);
    $_SESSION['alerts'] = array_values($_SESSION['alerts']); // Reset array keys
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['acceptPendingChanges'])) {
    $api = new RestAPI();
    $api->save_config_file();
    addAlert("Pending changes have been saved to the config file.", "success");
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['rejectPendingChanges'])) {
    $api = new RestAPI();
    $_SESSION['pendingChanges'] = false;
    addAlert("Pending changes rejected. The committed changes remain active until reboot.", "warning");
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['selectRouterIndex'])) {
    $_SESSION['routerIndex'] = intval($_POST['routerIndex']);
    $selectedRouterName = htmlspecialchars($ROUTERS[$_SESSION['routerIndex']]['name']) ?? "N/A";
    addAlert("Managing router changed to {$selectedRouterName}", "info");
}
?>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><span class="material-icons">router</span> VyConnect</h2>
        <p style="text-align:center;font-size:14px;">Controller UI</p>
        <p style="text-align:center;font-size:14px;">Early Development</p>

        <!-- Alert system -->
        <?php if (!empty($_SESSION['alerts'])): ?>
            <div class="alerts">
                <?php foreach ($_SESSION['alerts'] as $index => $alert): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?>">
                        <form method="post" style="display:inline;" autocomplete="off">
                            <button type="submit" name="dismissAlert" value="<?php echo $index; ?>" class="fa fa-check-circle" style="width:auto;padding:0;border:none;background:none;color:yellowgreen;font-size:20px;font-weight:bold;cursor:pointer;"></button>
                        </form>
                        <strong><?php echo strtoupper($alert['type']); ?>:</strong>
                        <?php echo htmlspecialchars($alert['message']); ?>
                        <br>
                        <small style="color: whitesmoke;">(<?php echo $alert['timestamp']; ?>)</small>
                    </div>
                    <br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Pending Changes -->
        <?php if (!empty($_SESSION['pendingChanges'])): ?>
            <div class="pendingChanges">
                <h2><i class='fa fa-warning'></i> Unsaved Changes</h2>
                <p>You have committed changes that aren't saved to the config file yet. If you are sure about your current configuration, accept the changes.</p>
                <p><strong>Hint:</strong> To see what's changed, use the CLI in Configuration mode ('configure') and enter: 'compare saved'</p>
                <form method='post' style='text-align:center' autocomplete="off">
                    <button type='submit' name='acceptPendingChanges' class='button' style='background:green'>
                        <i class='fa fa-check' style='color:white;'></i> Accept pending changes
                    </button>
                    <br><br>
                    <button type='submit' name='rejectPendingChanges' class='button' style='background:crimson'>
                        <i class='fa fa-close' style='color:white;'></i> Reject pending changes
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <hr>

        <!-- Router selection -->
        <form id="routerForm" method="post" autocomplete="off">
            <select name="routerIndex" id="routerSelect" onchange="updateRouterIndex();">
                <?php
                if (isset($ROUTERS) && is_array($ROUTERS)) {
                    $selectedRouter = $_SESSION['routerIndex'] ?? 0;

                    function isRouterUp($ip)
                    {
                        $api = new RestAPI($ip);

                        $retrieve_req = $api->retrieve();

                        if (!$retrieve_req) {
                            return false;
                        }

                        return true;
                    }

                    foreach ($ROUTERS as $index => $router) {
                        $routerName = htmlspecialchars($router['name']);
                        $routerIp = $router['ip'];
                        $isActive = ($index == $selectedRouter);
                        $pingStatus = isRouterUp($routerIp) ? 'UP' : 'DOWN';
                        $pingColor = $pingStatus == 'UP' ? 'green' : 'red';
                        $statusEmoji = $pingStatus == 'UP' ? '✅' : '❌';

                        if ($isActive) {
                            $routerName .= " (🦺 MANAGING)";
                        }

                        $selectedAttr = ($index == $selectedRouter) ? 'selected' : '';

                        echo "<option value='$index' style='color:$pingColor;' $selectedAttr>($statusEmoji $pingStatus) $routerName</option>";
                    }
                } else {
                    echo "<option disabled>No routers available</option>";
                }
                ?>
            </select>
            <input type="hidden" name="selectRouterIndex" value="1">
        </form>

        <!-- Navigation -->
        <ul>
            <?php foreach ($tabs as $tab_name => $data): ?>
                <li class="<?php echo ($tab === $tab_name) ? 'active' : ''; ?>">
                    <a href="?tab=<?php echo $tab_name; ?>">
                        <i class="fa <?php echo $data['icon']; ?>"></i> <?php echo $data['title']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li><a href='logout.php'><i class='fa fa-sign-out'></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php
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

<script>
    function updateRouterIndex() {
        var routerIndex = document.getElementById('routerSelect').value;
        document.getElementById('routerForm').querySelector('input[name="selectRouterIndex"]').value = routerIndex;
        document.getElementById('routerForm').submit();
    }
</script>

</html>