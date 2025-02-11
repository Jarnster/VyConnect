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
    $api = new RestAPI($REST_BASE_URL);
    $api->save_config_file();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['rejectPendingChanges'])) {
    $api = new RestAPI($REST_BASE_URL);
    $_SESSION['pendingChanges'] = false;
    $_SESSION['alert'] = 'Pending changes rejected. Changes are not saved to the config file. Changes you have commited are still active up on reboot.';
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><span class="material-icons">router</span> VyConnect</h2>
        <p style="text-align:center;font-size:12px;">Local UI for VyOS</p>
        <p style="text-align:center;font-size:14px;">Early Development</p>
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
            <p>You have committed changes, that aren't saved to the config file yet. If you are sure about your current configuration, accept the changes. <br> <p>Hint: if you want to see what's changed, use the CLI and enter: 'compare saved'</p>
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