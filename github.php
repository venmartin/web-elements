<?php
$text_from_github = file_get_contents('php://input');
$json_from_github = json_decode($text_from_github, true);
$event_type = $_SERVER['HTTP_X_GITHUB_EVENT'];

function get_text($event_type, $json_from_github) {
    if ($event_type == 'ping') {
        return 'ping from github.com';
    } else if ($event_type == 'push') {
        $commits = $json_from_github['commits'];
        $text = '#### ' . $event_type . " from github.com\n";
        $ref = $json_from_github['ref'];
        // $ref_type = explode('/', $ref)[1];
        $ref_url = $json_from_github['repository']['html_url'] . '/tree/' . $ref;
        $text .= '[' . $ref . '](' . $ref_url . ")\n";
        foreach ($commits as $commit) {
            $text .= '[' . $commit['id'] . '](' . $commit['url'] . ') ' . $commit['message'] . "\n";
            $added = $commit['added'];
            if (count($added) > 0) {
                foreach ($added as $file) {
                    $text .= 'A ' . $file . "\n";
                }
            }
            $modified = $commit['modified'];
            if (count($modified) > 0) {
                foreach ($modified as $file) {
                    $text .= 'M ' . $file . "\n";
                }
            }
            $removed = $commit['removed'];
            if (count($removed) > 0) {
                foreach ($removed as $file) {
                    $text .= 'D ' . $file . "\n";
                }
            }
        }
        return $text;
    } else if ($event_type == 'pull_request') {
        $action = $json_from_github['action'];
        $pull_request = $json_from_github['pull_request'];
        $text = '#### ' . $action . " pull request: [" . $pull_request['title'] . "](" . $pull_request['html_url'] . ")\n";
        $text .= $pull_request['body'] . "\n";
        return $text;
    } else if ($event_type == 'pull_request_review_comment') {
        $action = $json_from_github['action'];
        $pull_request = $json_from_github['pull_request'];
        $comment = $json_from_github['comment'];
        $text = '#### ' . $action . " comment on pull request: [" . $pull_request['title'] . "](" . $comment['html_url'] . ")\n";
        $text .= "```diff\n" . $comment['diff_hunk'] . "\n```\n";
        if ($comment['in_reply_to_id'] != null) {
            $text .= "in reply to [" . $comment['in_reply_to_id'] . "](" . str_replace($comment['id'], $comment['in_reply_to_id'], $comment['html_url']) . ")\n";
        }
        $text .= $comment['body'] . "\n";
        return $text;
    } else if ($event_type == 'issues') {
        $action = $json_from_github['action'];
        $issue = $json_from_github['issue'];
        $text = '#### ' . $action . " issue: [" . $issue['title'] . "](" . $issue['html_url'] . ")\n";
        $text .= $issue['body'] . "\n";
        return $text;
    } else if ($event_type == 'issue_comment') {
        $action = $json_from_github['action'];
        $issue = $json_from_github['issue'];
        $comment = $json_from_github['comment'];
        $text = '#### ' . $action . " comment on issue: [" . $issue['title'] . "](" . $issue['html_url'] . ")\n";
        $text .= $comment['body'] . "\n";
        return $text;
    } else if ($event_type == 'release') {
        $action = $json_from_github['action'];
        $release = $json_from_github['release'];
        $text = '#### ' . $action . " release: [" . $release['name'] . "](" . $release['html_url'] . ")\n";
        $text .= $release['body'] . "\n";
        return $text;
    } else if ($event_type == 'create') {
        $ref_type = $json_from_github['ref_type'];
        $ref = $json_from_github['ref'];
        $text = '#### ' . $event_type . " " . $ref_type . ": " . $ref . "\n";
        return $text;
    } else {
        return "#### " . $event_type . " from github.com\n" .
        "```json\n" . $text_from_github . "\n```";
    }
}

// Mattermost webhook URL
$url = $_GET['mattermost'];
$postData = [
    'text' => get_text($event_type, $json_from_github),
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
