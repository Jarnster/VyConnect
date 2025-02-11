<?php
$url = "https://10.10.1.66/retrieve";
$data = json_encode(["op" => "showConfig", "path" => []]);

$ch = curl_init();

// DEBUG + SSL DISABLE
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    "data" => $data,
    "key" => "plaintxt"
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
}
curl_close($ch);

echo $response;
