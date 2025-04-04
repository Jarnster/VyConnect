<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';

$fields = [
    "description" => "Description",
    "address" => "Address",
    "speed" => "Speed",
    "duplex" => "Duplex",
    "mtu" => "MTU"
];

$readonly_fields = [
    "hw-id" => "MAC Address"
];

if (isset($_POST['save']) && isset($_POST['interface_name'])) {
    $api = new RestAPI();

    $interface_name = htmlspecialchars($_POST['interface_name']);
    $data = [];

    foreach ($fields as $key => $label) {
        if (isset($_POST[$key])) {
            $data[$key] = htmlspecialchars($_POST[$key]);
        }
    }

    $api->update_interface($interface_name, $data);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<h2><i class="fa fa-ethernet"></i> Ethernet Interfaces</h2>

<!-- Interface Configuration Modal -->
<div id="interfaceModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Interface Configuration: <b id="interfaceNameDisplay"></b></h2>
        <form method="post" id="interfaceForm" name="interfaceForm" autocomplete="off">
            <label for="interface_name">Interface (read-only):</label>
            <input type="text" id="interface_name" name="interface_name" readonly>
            <br>

            <?php foreach ($fields as $key => $label): ?>
                <label for="<?= $key ?>"><?= $label ?>:</label>
                <input type="text" id="<?= $key ?>" name="<?= $key ?>">
                <br>
            <?php endforeach; ?>

            <?php foreach ($readonly_fields as $key => $label): ?>
                <label for="<?= $key ?>"><?= $label ?> (read-only):</label>
                <input type="text" id="<?= $key ?>" name="<?= $key ?>" readonly>
                <br>
            <?php endforeach; ?>

            <br>
            <button type="submit" name="save" class="button" style="background:green"><i class="fa fa-upload"></i> Commit Changes</button>
        </form>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th><i class="fa fa-ethernet"></i> Interface</th>
            <?php foreach ($fields as $label): ?>
                <th><?= $label ?></th>
            <?php endforeach; ?>
            <?php foreach ($readonly_fields as $label): ?>
                <th><?= $label ?></th>
            <?php endforeach; ?>
            <th><i class="fa fa-power-on"></i> State</th>
            <th><i class="fa fa-cogs"></i> Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $api = new RestAPI();
        $runningConfiguration = json_decode($api->retrieve())->data;
        $ethernet_interfaces = $runningConfiguration->interfaces->ethernet;

        foreach ($ethernet_interfaces as $interface_name => $interface) {
            $interface_json = htmlspecialchars(json_encode(array_merge([
                "name" => $interface_name,
                "state" => get_interface_state($interface)
            ], array_combine(array_keys($fields), array_map(fn($key) => $interface->$key ?? '', array_keys($fields))),
               array_combine(array_keys($readonly_fields), array_map(fn($key) => $interface->$key ?? '', array_keys($readonly_fields)))
            ), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS));

            echo "<tr>";
            echo "<td>$interface_name</td>";

            foreach ($fields as $key => $_) {
                echo "<td>" . ($interface->$key ?? '') . "</td>";
            }

            foreach ($readonly_fields as $key => $_) {
                echo "<td>" . ($interface->$key ?? '') . "</td>";
            }

            echo "<td>" . create_html_interface_state($interface) . "</td>";
            echo "<td><button class='open-modal button' data-interface='$interface_json'><i class='fa fa-pencil'></i> CONFIGURATION</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("interfaceModal");
        const closeButton = document.querySelector(".close");

        function openModal(interfaceData) {
            document.getElementById("interface_name").value = interfaceData.name;
            document.getElementById("interfaceNameDisplay").innerText = interfaceData.name;

            Object.keys(interfaceData).forEach(key => {
                let input = document.getElementById(key);
                if (input) input.value = interfaceData[key];
            });

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
                openModal(JSON.parse(this.dataset.interface));
            });
        });
    });
</script>
