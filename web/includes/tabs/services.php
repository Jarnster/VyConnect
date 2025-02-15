<?php
if (isset($_POST['save']) && isset($_POST['service_name'])) {
    $api = new RestAPI();

    $service_name = htmlspecialchars($_POST['service_name']);

    $data = [];

    foreach ($_POST as $key => $value) {
        if ($key !== 'service_name' && $key !== 'save') {
            $keys = explode('[', str_replace(']', '', $key));
            $temp = &$data;
            foreach ($keys as $keyPart) {
                if (!isset($temp[$keyPart])) {
                    $temp[$keyPart] = [];
                }
                $temp = &$temp[$keyPart];
            }
            $temp = $value;
        }
    }

    $api->update_service($service_name, $data);

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<h2><i class="fa fa-cogs"></i> Services</h2>

<!-- Service Configuration Modal -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Service Configuration: <b id="serviceNameDisplay" name="serviceNameDisplay"></b></h2>
        <form method="post" id="serviceForm" name="serviceForm">
            <label for="service_name">Service:</label>
            <input type="text" id="service_name" name="service_name" readonly>

            <div id="configFields"></div> <!-- Dynamically populated config fields go here -->

            <button type="submit" name="save"><i class="fa fa-save"></i> Commit Changes</button>
        </form>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th><i class="fa fa-podcast"></i> Service</th>
            <th><i class="fa fa-cogs"></i> Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $Rest = new RestAPI();
        $runningConfiguration = $Rest->retrieve();
        $runningConfiguration = json_decode($runningConfiguration);
        $runningConfiguration = $runningConfiguration->data;
        $services = $runningConfiguration->service;

        foreach ($services as $service_name => $service) {
            $service_json = htmlspecialchars(json_encode([
                "name" => $service_name,
                "configuration" => $service
            ], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS));

            echo "<tr>";
            echo "<td>" . $service_name . "</td>";
            echo "<td><button class='open-modal' data-service='$service_json'>CONFIGURATION</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("serviceModal");
        const closeButton = document.querySelector(".close");
        const configFields = document.getElementById("configFields");

        function createInputField(key, value) {
            const label = document.createElement("label");
            label.setAttribute("for", key);
            label.textContent = key.charAt(0).toUpperCase() + key.slice(1) + ":";

            const input = document.createElement("input");
            input.type = "text";
            input.id = key;
            input.name = key;
            input.value = value;

            configFields.appendChild(label);
            configFields.appendChild(input);
            configFields.appendChild(document.createElement("br"));
        }

        function processConfiguration(config, prefix = '') {
            for (const [key, value] of Object.entries(config)) {
                const newKey = prefix ? `${prefix}[${key}]` : key;

                if (typeof value === 'object' && !Array.isArray(value)) {
                    processConfiguration(value, newKey);
                } else {
                    createInputField(newKey, value);
                }
            }
        }

        function openModal(serviceData) {
            document.getElementById("service_name").value = serviceData.name;
            document.getElementById("serviceNameDisplay").innerText = serviceData.name;

            configFields.innerHTML = '';

            processConfiguration(serviceData.configuration);

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
                const serviceData = JSON.parse(this.dataset.service);
                openModal(serviceData);
            });
        });
    });
</script>