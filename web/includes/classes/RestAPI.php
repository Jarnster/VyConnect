<?php
// Class to communicate with the REST API of VyOS
class RestAPI
{
    protected bool $curl_debugging = false;
    protected bool $disable_ssl_verify = false;
    private string $restURL;
    private $ch;
    private $response;

    public function __construct(string $restURL, bool $curl_debugging = false, bool $disable_ssl_verify = false)
    {
        require 'includes/config.php';
        $this->setDebugParameters($curl_debugging, $disable_ssl_verify);
        $this->setURL($restURL);
    }

    protected function setDebugParameters(bool $curl_debugging, bool $disable_ssl_verify)
    {
        $this->curl_debugging = $curl_debugging;
        $this->disable_ssl_verify = $disable_ssl_verify;
    }

    protected function setURL($restURL)
    {
        $this->restURL = $restURL;
    }

    public function retrieve()
    {
        // Variables bound to this request
        $endpoint = $this->restURL . "/retrieve";
        $req_data = json_encode(["op" => "showConfig", "path" => []]);


        $this->ch = curl_init();

        if ($this->curl_debugging) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        }

        if ($this->disable_ssl_verify) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        // JSON in multipart/form-data
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => "plaintxt"
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

    public function update_interface($interface_name, $data)
    {
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

        if ($this->curl_debugging) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        }

        if ($this->disable_ssl_verify) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($this->ch, CURLOPT_URL, $endpoint);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            "data" => $req_data,
            "key" => "plaintxt"
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
