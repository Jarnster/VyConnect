<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<?php
if (isset($_POST['save'])) {
    $api = new RestAPI();

    $performance_priority = htmlspecialchars($_POST['performance_priority']);
    $keyboard_layout = htmlspecialchars($_POST['keyboard_layout']);

    $data = [
        "performance" => $performance_priority,
        "keyboard-layout" => $keyboard_layout
    ];

    $api->update_system_config($data);

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<h2><i class="fa fa-server"></i> System Config</h2>

<?php
$api = new RestAPI();

$runningConfiguration = $api->retrieve();
$runningConfiguration = json_decode($runningConfiguration);
$runningConfiguration = $runningConfiguration->data;

$systemConfig = $runningConfiguration->system->option;
?>

<form method="post" id="systemConfigForm" name="systemConfigForm">
    <label for="performance_priority">Performance Priority:</label>
    <select id="performance_priority" name="performance_priority">
        <option value="" <?php echo (!$systemConfig->performance) ? 'selected' : ''; ?>>None</option>
        <option value="network-latency" <?php echo ($systemConfig->performance == 'network-latency') ? 'selected' : ''; ?>>Network Latency</option>
        <option value="network-throughput" <?php echo ($systemConfig->performance == 'network-throughput') ? 'selected' : ''; ?>>Network Throughput</option>
    </select>

    <br>

    <label for="keyboard_layout">Keyboard Layout:</label>
    <input type="text" id="keyboard_layout" name="keyboard_layout" value="<?php echo $systemConfig->{"keyboard-layout"} ?>">

    <br><br>

    <button type="submit" name="save" class="button" style="background:green"><i class="fa fa-upload"></i> Commit Changes</button>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("systemConfigForm");
        const closeButton = document.querySelector(".close");

        function openModal(interfaceData) {
            document.getElementById("interface_name").value = interfaceData.name;
            document.getElementById("interfaceNameDisplay").innerText = interfaceData.name;
            document.getElementById("description").value = interfaceData.description;
            document.getElementById("address").value = interfaceData.address;
            document.getElementById("macAddress").value = interfaceData.mac;
            document.getElementById("speed").value = interfaceData.speed;
            document.getElementById("duplex").value = interfaceData.duplex;

            modal.style.display = "block";
        }

        closeButton.addEventListener("click", function() {
            modal.style.display = "none";
        });

        window.addEventListener("click", function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });

        document.querySelectorAll(".open-modal").forEach(button => {
            button.addEventListener("click", function() {
                const interfaceData = JSON.parse(this.dataset.interface);
                openModal(interfaceData);
            });
        });
    });
</script>