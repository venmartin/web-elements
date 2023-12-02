<?php
$text_from_github = file_get_contents('php://input');
$json_from_github = json_decode($text_from_github, true);
$event_type = $_SERVER['HTTP_X_GITHUB_EVENT'];

// Mattermost webhook URL
$url = $_GET['mattermost'];
$postData = [
    'text' => $event_type == 'ping' ? 'ping from github.com' :
    "#### " . $event_type . " from github.com\n" .
    "```json\n" . $text_from_github . "\n```",
    'username' => $json_from_github['sender']['login'],
    'icon_url' => $json_from_github['sender']['avatar_url'],
];

// Setup cURL
$ch = curl_init($url);
curl_setopt_array($ch, array(
    CURLOPT_POST => TRUE,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    CURLOPT_POSTFIELDS => json_encode($postData)
));

// Send the request
$response = curl_exec($ch);
echo "response: $response\n";


// Check for errors
if($response === FALSE){
    echo "curl_getinfo: \n";
    echo print_r(curl_getinfo($ch), true);
    echo "\n";
    die(curl_error($ch));
}

// Close the cURL handler
curl_close($ch);
?>
