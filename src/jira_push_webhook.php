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
    "Authorization" => "Basic " . base64_encode("$jira_user:$jira_token") ,    
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
//    $data = array(
//        'body' => 'test123456'
//    );
//    print_r($data);
    //die();
    foreach ($headers as $key => $val){
        $curl_headers[] = $key . ": " . $val; 
    }
    $data_string = $data;
    $curl_headers[] = 'Content-Length: ' . strlen($data_string);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    $result = curl_exec($ch);           
    
    //print_r($curl_headers);
    print_r($result);
    return $result;
}

function post_jira_comment($ticket_id, $comment) {
    /*
     * post comment to assembla by ticketid
     */
    global $headers;
    $url = "https://leadspace.atlassian.net/rest/api/2/issue/$ticket_id/comment";        
    return request($url, 'POST', $headers, $comment);
}

/*
 * Handling gitlab webhook
 */



function get_jira_issue($commits_array){
    /*
     * Get ticket id from commit messages
     * get all commit from gitlab webhook
     * search for ticket syntax on all commit messages
     * return uniqe tikets numbers
     */
    $messages = '';
    $matches = NULL;
    foreach ($commits_array as $commit) {
        $messages .= $commit->message;
    }
    preg_match_all('/^[A-Z]+\-[0-9]+/', $messages, $matches);
    return array_unique($matches[0]);
    
}


function get_commits_info($data) {
    /*
     * format Gitlab webhook data into assembla comment format
     * get gitlab data
     * return Assembla comment format 
     */
    $commits_str = '*Project*: [' . $data->project->name . '|'. $data->project->homepage . "]\n";
    foreach ($data->commits as $commit) {
        $commits_str .= "*Commit*: ";
        $commits_str .= '[' . end(explode('/', $commit->url)) . '|' . $commit->url . "]\n";
        $commits_str .= "| *Author* |" . $commit->author->name . "|\n";
        $commits_str .= "| *Message* |" . $commit->message . "|\n";
        $commits_str .= "| *Files* |" . implode("\n", $commit->modified) . "|\n";        
    }
    return $commits_str;
}


file_put_contents('/var/tmp/jira_webhook.json', $data);
$data = json_decode($data);
//print_r($data);
if (!empty($data->commits)) {
    $tickets = get_jira_issue($data->commits);    
    //print_r($tickets);
    $comment = get_commits_info($data);
    
    $comment = json_encode( array('body' => $comment)); 
    //print_r($comment);
    //$comment = "test12345";
    foreach ($tickets as $ticket) {        
        post_jira_comment($ticket, $comment);
    }
}