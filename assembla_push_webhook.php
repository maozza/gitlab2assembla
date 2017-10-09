<?php
//$data = json_decode(file_get_contents('php://input'));
$data = file_get_contents('php://input');
include_once './config.php';



/*
 * Handling requesrt to assembla 
 * 
 * 
 */
$headers = array(
    "X-Api-key" => $key,
    "X-Api-secret" => $secret,
    "Content-type" => "application/json"
);

function create_headers($array) {
    /*
     * create headers from array
     */
    $headers = '';
    foreach ($array as $key => $value) {
        $headers = $headers . $key . ":" . $value . "\r\n";
    }
    return $headers;
}

function request($url, $method, $headers, $data = 'none') {
    /*
     * send http request
     */
    $options = array(
        'http' => array(
            'header' => create_headers($headers),
            'method' => $method,
            'content' => $data
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        echo 'Error on request to $url';
    }
    return $result;
}

function post_comment($ticket_id, $comment) {
    /*
     * post comment to assembla by ticketid
     */
    global $headers;
    global $space;
    $url = "https://api.assembla.com/v1/spaces/$space/tickets/$ticket_id/ticket_comments.json";
    $data = array(
        "ticket_comment" => array(
            "comment" => $comment
        )
    );
    return request($url, 'POST', $headers, json_encode($data));
}

/*
 * Handling gitlab webhook
 */

function get_assembla_ticket_number($commits_array) {
    /*
     * Get ticket id from commit messages
     * get all commit from gitlab webhook
     * search for ticket syntax on all commit messages
     * return uniqe tikets numbers
     */
    $messages = '';
    foreach ($commits_array as $commit) {
        $messages .= $commit->message;
    }
    preg_match_all('/\#[0-9]+/', $messages, $matches);
    return str_replace('#', '', array_unique($matches[0]));
}

function get_commits_info($data) {
    /*
     * format Gitlab webhook data into assembla comment format
     * get gitlab data
     * return Assembla comment format 
     */
    $commits_str = '[[url:' . $data->project->homepage . '|' . $data->project->name . ']]' . "\n";
    foreach ($data->commits as $commit) {
        $commits_str .= "h3. Commit \n";
        $commits_str .= '[[url:' . $commit->url . '|' . end(explode('/', $commit->url)) . ']]' . "\n";
        $commits_str .= "Author:" . $commit->author->name . "\n";
        $commits_str .= "Message: " . $commit->message . "\n";
        $commits_str .= "<pre><code>\n" . implode("\n", $commit->modified) . "\n</code></pre>\n";
        $commits_str .= "\n";
    }
    return $commits_str;
}



$data = json_decode($data);
if (!empty($data->commits)) {
    $tickets = get_assembla_ticket_number($data->commits);
    $comment = get_commits_info($data);
    foreach ($tickets as $ticket) {        
        post_comment($ticket, $comment);
    }
}