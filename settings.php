<?php
ini_set('display_errors', 1);

session_save_path("data/.sessions");

// Your application configuration in Fénix should contain the following URLs:
// - Site: https://fenix.tecnico.ulisboa.pt
// - Redirect Url: <Your application URL>
function getFenixEduSettings() {
    $access_key = "";
    $secret_key = "";
    $callback_url = "";
    $api_base_url = "";
    
    return array(
        "access_key" => $access_key,
        "secret_key" => $secret_key,
        "callback_url" => $callback_url,
        "api_base_url" => $api_base_url
    );
}
?>
