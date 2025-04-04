<?php
// Class to communicate with the REST API of VyOS

require 'includes/classes/Caching.php';

class RestAPI
{
    protected bool $curl_debugging = false;
    protected bool $disable_ssl_verify = false;
    protected string $restURL;
    private $ch;
    private $response;
    protected Caching $caching;

    // Constructor: Initializes with router IP and caching setup
    public function __construct(string $ip = null, bool $curl_debugging = false, bool $disable_ssl_verify = false)
    {
        require 'includes/config.php';

        // Set router IP address
        if ($ip != null) {
            $this->setURL($ip);
        } else {
            $this->setURL(get_selected_router_ip());
        }

        // Set debug parameters
        $this->setDebugParameters($curl_debugging, $disable_ssl_verify);

        // Initialize caching module
        $this->caching = new Caching("../data/cache", $CACHE_LIFETIME);
    }

    // Returns curl options for the current request
    private function getCurlOptParameters() // Only call this function when $this->ch is already initialized (with curl_init)
    {
        require 'includes/config.php';

        // Set curl options for debugging and SSL verification
        if ($this->curl_debugging) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        }

        if ($this->disable_ssl_verify) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        // Set timeout
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $VYOS_API_TIMEOUT);
    }

    // Set debugging parameters (whether to enable curl debugging and disable SSL verification)
    protected function setDebugParameters(bool $curl_debugging, bool $disable_ssl_verify)
    {
        $this->curl_debugging = $curl_debugging;
        $this->disable_ssl_verify = $disable_ssl_verify;
    }

    // Set the router's IP address for API communication
    protected function setURL(string $ip)
    {
        $this->restURL = $ip;
    }

    protected function getRestURL()
    {
        $this->setURL(get_selected_router_ip());
        return $this->restURL;
    }

    // Retrieve data from the REST API, with caching
    public function retrieve()
    {
        require 'includes/config.php';

        // Check if cached data exists
        $cacheKey = 'retrieve';
        $cachedResponse = $this->caching->getCachedResponse($cacheKey);
        if ($cachedResponse !== null) {
            return $cachedResponse; // Return cached response
        }

        // Variables bound to this request
        $endpoint = $this->getRestURL() . "/retrieve";
        $req_data = json_encode(["op" => "showConfig", "path" => []]);

        // Initialize cURL session
        $this->ch = curl_init();
        $this->getCurlOptParameters();

        // Set cURL options for the request
        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON data for multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        // Execute the cURL request
        $this->response = curl_exec($this->ch);

        // Handle any cURL errors
        if (curl_errno($this->ch)) {
            if ($this->curl_debugging == true) {
                echo "cURL Error: " . curl_error($this->ch);
            }
        }

        // Close cURL session
        curl_close($this->ch);

        // Cache the response for future use
        $this->caching->cacheResponse($cacheKey, $this->response);

        return $this->response;
    }

    // Save the current config file via the API
    public function save_config_file()
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->getRestURL() . "/config-file";
        $req_data = json_encode(["op" => "save", "path" => []]);

        // Initialize cURL session
        $this->ch = curl_init();
        $this->getCurlOptParameters();

        // Set cURL options
        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON data for multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        // Execute cURL request
        $this->response = curl_exec($this->ch);

        // Handle any cURL errors
        if (curl_errno($this->ch)) {
            if ($this->curl_debugging == true) {
                echo "cURL Error: " . curl_error($this->ch);
            }
        }

        // Close cURL session
        curl_close($this->ch);

        // Set session state after saving
        $_SESSION['pendingChanges'] = false;

        $this->caching->invalidateRouterCache(get_selected_router_index());

        return $this->response;
    }

    // Show the running configuration for the router via the API with caching
    public function show_running_config(array $path)
    {
        require 'includes/config.php';

        // Create a unique cache key based on the path to the running config
        $cacheKey = 'show_running_config_' . md5(json_encode($path)); // Create a unique key using the path

        // Check if cached data exists
        $cachedResponse = $this->caching->getCachedResponse($cacheKey);
        if ($cachedResponse !== null) {
            return $cachedResponse; // Return cached response if available
        }

        // Variables bound to this request
        $endpoint = $this->getRestURL() . "/show";

        // Command data for the request
        $single_command = [
            "op" => "show",
            "path" => $path
        ];

        $req_data = json_encode($single_command);

        // Initialize cURL session
        $this->ch = curl_init();
        $this->getCurlOptParameters();

        // Set cURL options
        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON data for multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        // Execute cURL request
        $this->response = curl_exec($this->ch);

        // Handle any cURL errors
        if (curl_errno($this->ch)) {
            echo "cURL Error: " . curl_error($this->ch);
        } else {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                echo "API Error: HTTP " . $http_code . " - Response: " . $this->response;
            }
        }

        // Close cURL session
        curl_close($this->ch);

        // Cache the response for future use
        $this->caching->cacheResponse($cacheKey, $this->response);

        return $this->response;
    }

    // Update interface settings via the API
    public function update_interface($interface_name, $data)
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->getRestURL() . "/configure";

        // Command data for the request
        $commands = [];

        foreach ($data as $key => $value) {
            $commands[] = [
                "op" => "set",
                "path" => ["interfaces", "ethernet", $interface_name, $key, $value]
            ];
        }

        $req_data = json_encode($commands);

        // Initialize cURL session
        $this->ch = curl_init();
        $this->getCurlOptParameters();

        // Set cURL options
        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON data for multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        // Execute cURL request
        $this->response = curl_exec($this->ch);

        // Handle any cURL errors
        if (curl_errno($this->ch)) {
            echo "cURL Error: " . curl_error($this->ch);
        } else {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                echo "API Error: HTTP " . $http_code . " - Response: " . $this->response;
            }
        }

        // Close cURL session
        curl_close($this->ch);

        // Set session state after update
        $_SESSION['pendingChanges'] = true;

        $this->caching->invalidateRouterCache(get_selected_router_index());

        return $this->response;
    }

    // Update service settings via the API
    public function update_service($service_name, $data)
    {
        require 'includes/config.php';

        $endpoint = $this->getRestURL() . "/configure";
        $commands = [];

        // Recursive function to build commands from nested data
        $buildCommands = function ($data, $currentPath = []) use (&$buildCommands, &$commands, $service_name) {
            foreach ($data as $key => $value) {
                $newPath = array_merge($currentPath, [$key]);
                if (is_array($value) && !isset($value['op'])) {
                    $buildCommands($value, $newPath);
                } else {
                    $path = array_merge(["service", $service_name], $newPath, [$value]);
                    $commands[] = [
                        "op" => "set",
                        "path" => $path,
                    ];
                }
            }
        };

        $buildCommands($data);

        $req_data = json_encode($commands);

        // Initialize cURL session
        $this->ch = curl_init();
        $this->getCurlOptParameters();

        // Set cURL options
        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON data for multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        // Execute cURL request
        $this->response = curl_exec($this->ch);

        // Handle any cURL errors
        if (curl_errno($this->ch)) {
            echo "cURL Error: " . curl_error($this->ch);
        } else {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                echo "API Error: HTTP " . $http_code . " - Response: " . $this->response;
            }
        }

        // Close cURL session
        curl_close($this->ch);

        // Set session state after update
        $_SESSION['pendingChanges'] = true;

        $this->caching->invalidateRouterCache(get_selected_router_index());

        return $this->response;
    }

    public function update_system_config($data)
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/configure";

        $commands = [];

        foreach ($data as $key => $value) {
            $commands[] = [
                "op" => "set",
                "path" => ["system", "option", $key, $value]
            ];
        }

        $req_data = json_encode($commands);

        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        $this->response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            echo "cURL Error: " . curl_error($this->ch);
        } else {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                echo "API Error: HTTP " . $http_code . " - Response: " . $this->response;
            }
        }

        curl_close($this->ch);
        $_SESSION['pendingChanges'] = true;
        return $this->response;
    }

    public function reboot()
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/reboot";

        $single_command = [
            "op" => "reboot",
            "path" => ["now"]
        ];

        $req_data = json_encode($single_command);

        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        $this->response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            echo "cURL Error: " . curl_error($this->ch);
        } else {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                echo "API Error: HTTP " . $http_code . " - Response: " . $this->response;
            }
        }

        curl_close($this->ch);
        return $this->response;
    }

    public function poweroff()
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/poweroff";

        $single_command = [
            "op" => "poweroff",
            "path" => ["now"]
        ];

        $req_data = json_encode($single_command);

        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => get_selected_router_api_key()
        ]);

        $this->response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            echo "cURL Error: " . curl_error($this->ch);
        } else {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                echo "API Error: HTTP " . $http_code . " - Response: " . $this->response;
            }
        }

        curl_close($this->ch);
        return $this->response;
    }
}
