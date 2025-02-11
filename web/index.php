<?php
require 'includes/header.php';
require 'includes/utils.php';

// Tab configuration
$tabs = include 'includes/tabs.php';

// Get the current tab
$tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ? $_GET['tab'] : 'dashboard';
?>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><span class="material-icons">router</span> VyConnect</h2>
        <p style="text-align:center;font-size:12px;">Local UI for VyOS</p>
        <p style="text-align:center;font-size:14px;">Early Development</p>
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