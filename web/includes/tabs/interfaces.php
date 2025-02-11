<?php
if (isset($_POST['save']) && isset($_POST['interface_name'])) {
    require 'includes/classes/RestAPI.php';

    $Rest = new RestAPI($REST_BASE_URL);

    $interface_name = htmlspecialchars($_POST['interface_name']);
    $description = htmlspecialchars($_POST['description']);
    $address = htmlspecialchars($_POST['address']);
    $speed = htmlspecialchars($_POST['speed']);
    $duplex = htmlspecialchars($_POST['duplex']);

    $data = [
        "description" => $description,
        "address" => $address,
        "speed" => $speed,
        "duplex" => $duplex
    ];

    $Rest->update_interface($interface_name, $data);

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<h2><i class="fa fa-ethernet"></i> Ethernet Interfaces</h2>

<!-- Interface Configuration Modal -->
<div id="interfaceModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Interface Configuration: <b id="interfaceNameDisplay" name="interfaceNameDisplay"></b></h2>
        <form method="post" id="interfaceForm" name="interfaceForm">
            <label for="description">Interface:</label>
            <input type="text" id="interface_name" name="interface_name" readonly>

            <br>

            <label for="description">Description:</label>
            <input type="text" id="description" name="description">

            <br>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address">

            <br>

            <label for="speed">Speed:</label>
            <input type="text" id="speed" name="speed">

            <br>

            <label for="duplex">Duplex:</label>
            <input type="text" id="duplex" name="duplex">

            <br>

            <label for="macAddress">MAC Address (read-only):</label>
            <input type="text" id="macAddress" name="macAddress" readonly>

            <hr>

            <button type="submit" name="save"><i class="fa fa-save"></i> Commit Changes</button>
        </form>
    </div>
</div>


<table class="interfaces-table">
    <thead>
        <tr>
            <th><i class="fa fa-ethernet"></i> Interface</th>
            <th><i class="fa fa-message"></i> Description</th>
            <th><i class="fa fa-exchange"></i> Address</th>
            <th><i class="fa fa-plug"></i> MAC Address</th>
            <th><i class="fa fa-podcast"></i> Speed</th>
            <th><i class="fa fa-signal"></i> Duplex</th>
            <th><i class="fa fa-power-on"></i> State</th>
            <th><i class="fa fa-cogs"></i> Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        require 'includes/classes/RestAPI.php';

        $Rest = new RestAPI($REST_BASE_URL);

        $runningConfiguration = $Rest->retrieve();
        $runningConfiguration = json_decode($runningConfiguration);
        $runningConfiguration = $runningConfiguration->data;

        $interfaces = $runningConfiguration->interfaces;
        $ethernet_interfaces = $interfaces->ethernet;


        foreach ($ethernet_interfaces as $interface_name => $interface) {
            // Maak een JSON-encoded string van het object en escape het correct
            $interface_json = htmlspecialchars(json_encode([
                "name" => $interface_name,
                "description" => $interface->description,
                "address" => $interface->address,
                "mac" => $interface->{"hw-id"},
                "speed" => $interface->speed,
                "duplex" => $interface->duplex,
                "state" => get_interface_state($interface)
            ], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS));

            echo "<tr>";
            echo "<td><b>" . $interface_name . "</b></td>";
            echo "<td>" . $interface->description . "</td>";
            echo "<td>" . $interface->address . "</td>";
            echo "<td>" . $interface->{"hw-id"} . "</td>";
            echo "<td>" . $interface->speed . "</td>";
            echo "<td>" . $interface->duplex . "</td>";
            echo "<td>" . create_html_interface_state($interface) . "</td>";
            echo "<td><button class='open-modal' data-interface='$interface_json'>EDIT</button></td>";
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