<?php
// Class to communicate with the REST API of VyOS
class RestAPI
{
    protected bool $curl_debugging = false;
    protected bool $disable_ssl_verify = false;
    protected string $restURL;
    private $ch;
    private $response;

    public function __construct(string $ip = null, bool $curl_debugging = false, bool $disable_ssl_verify = false)
    {
        require 'includes/config.php';

        if ($ip != null) {
            $this->setURL($ip);
        } else {
            $this->setURL(get_selected_router_ip());
        }

        $this->setDebugParameters($curl_debugging, $disable_ssl_verify);
    }
    private function getCurlOptParameters() // Only call this function when $this->ch is already initialized (with curl_init)
    {
        if ($this->curl_debugging) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        }

        if ($this->disable_ssl_verify) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    }

    protected function setDebugParameters(bool $curl_debugging, bool $disable_ssl_verify)
    {
        $this->curl_debugging = $curl_debugging;
        $this->disable_ssl_verify = $disable_ssl_verify;
    }

    protected function setURL(string $ip)
    {
        $this->restURL = $ip;
    }

    public function retrieve()
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/retrieve";
        $req_data = json_encode(["op" => "showConfig", "path" => []]);


        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON in multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => $API_KEY
        ]);

        $this->response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            if ($this->curl_debugging == true) {
                echo "cURL Error: " . curl_error($this->ch);
            }
        }

        curl_close($this->ch);
        return $this->response;
    }

    public function save_config_file()
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/config-file";
        $req_data = json_encode(["op" => "save", "path" => []]);


        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON in multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => $API_KEY
        ]);

        $this->response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            if ($this->curl_debugging == true) {
                echo "cURL Error: " . curl_error($this->ch);
            }
        }

        curl_close($this->ch);
        $_SESSION['pendingChanges'] = false;
        return $this->response;
    }

    public function show_running_config(array $path)
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/show";

        $commands = [];

        $commands[] = [
            "op" => "show",
            "path" => $path
        ];

        $req_data = json_encode($commands);

        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => $API_KEY
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

    public function update_interface($interface_name, $data)
    {
        require 'includes/config.php';

        // Variables bound to this request
        $endpoint = $this->restURL . "/configure";

        $commands = [];

        foreach ($data as $key => $value) {
            $commands[] = [
                "op" => "set",
                "path" => ["interfaces", "ethernet", $interface_name, $key, $value]
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
            "key" => $API_KEY
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

    public function update_service($service_name, $data)
    {
        require 'includes/config.php';

        $endpoint = $this->restURL . "/configure";
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

        $this->ch = curl_init();

        $this->getCurlOptParameters();

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => $API_KEY
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
            "key" => $API_KEY
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
