<?php
// Auth Check
$rootPath = __DIR__;
while (!file_exists($rootPath . '/includes')) $rootPath = dirname($rootPath);

require $rootPath . '/includes/auth.php';
?>

<?php
if (isset($_POST['save']) && isset($_POST['service_name'])) {
    $api = new RestAPI();

    $service_name = htmlspecialchars($_POST['service_name']);

    // Retrieve the running configuration from the API
    $runningConfiguration = $api->retrieve();
    $runningConfiguration = json_decode($runningConfiguration);
    $runningConfiguration = $runningConfiguration->data;

    // Get the current configuration for the service
    $runningServiceConfig = $runningConfiguration->service->{$service_name};

    // Process the POST data into a nested array
    $data = [];
    foreach ($_POST as $key => $value) {
        if ($key !== 'service_name' && $key !== 'save') {
            // Split the key into parts based on array notation (key[subkey][subsubkey])
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

    // Recursive function to compare arrays for differences
    function recursiveArrayCompare($array1, $array2)
    {
        // If one of the values is not an array, do a simple comparison
        if (!is_array($array1) || !is_array($array2)) {
            return $array1 !== $array2;
        }

        // Compare arrays element by element
        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                return true; // Key is missing, thus changed
            }
            if (recursiveArrayCompare($value, $array2[$key])) {
                return true; // Values are different
            }
        }

        // Ensure all keys in array2 exist in array1
        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1)) {
                return true; // Key is missing in array1, hence changed
            }
        }

        return false; // No changes detected
    }

    // Recursive function to filter out unchanged values
    function filterChangedValues($newData, $oldData)
    {
        $changedData = [];
        foreach ($newData as $key => $value) {
            if (is_array($value) && isset($oldData->$key) && is_object($oldData->$key)) {
                // Recursively filter nested arrays/objects
                $filtered = filterChangedValues($value, $oldData->$key);
                if (!empty($filtered)) {
                    $changedData[$key] = $filtered;
                }
            } elseif (!isset($oldData->$key) || recursiveArrayCompare($value, $oldData->$key)) {
                // If the key doesn't exist in the old data or the values are different, keep it
                $changedData[$key] = $value;
            }
        }
        return $changedData;
    }

    // Filter out unchanged values
    $changedData = filterChangedValues($data, $runningServiceConfig);

    // If there are changes, update the service configuration via the API
    if (!empty($changedData)) {
        $api->update_service($service_name, $changedData);
    }

    // Redirect back to the same page after the update
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
            <label for="service_name">Service (read-oly):</label>
            <input type="text" id="service_name" name="service_name" readonly disabled>

            <div id="configFields"></div> <!-- Dynamically populated config fields go here -->

            <hr>

            <button type="submit" name="save" class="button" style="background:green"><i class="fa fa-upload"></i> Commit Changes</button>
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
        $api = new RestAPI();
        $runningConfiguration = $api->retrieve();
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
            echo "<td><button class='open-modal button' data-service='$service_json' class='button'><i class='fa fa-pencil'></i> CONFIGURATION</button></td>";
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

        // Function to create an input field for each key-value pair in the configuration
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

        // Recursive function to process the nested configuration data
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

        // Function to open the modal and display the service configuration
        function openModal(serviceData) {
            document.getElementById("service_name").value = serviceData.name;
            document.getElementById("serviceNameDisplay").innerText = serviceData.name;

            configFields.innerHTML = ''; // Clear existing fields

            processConfiguration(serviceData.configuration); // Generate input fields

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

        // Add event listeners to open the modal with the service configuration
        document.querySelectorAll(".open-modal").forEach(button => {
            button.addEventListener("click", function() {
                const serviceData = JSON.parse(this.dataset.service);
                openModal(serviceData);
            });
        });
    });
</script>