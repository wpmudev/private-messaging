<?php
/**
 * @author:Hoang Ngo
 */
$docroot = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
require_once $docroot . DIRECTORY_SEPARATOR . 'wp-load.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');


$lastEventId = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : 0);
if ($lastEventId == 0) {
    $lastEventId = floatval(isset($_GET["lastEventId"]) ? $_GET["lastEventId"] : 0);
}

echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE

function mm_server_sent_msg($id, $msg)
{
    echo "id: $id" . PHP_EOL;
    echo "data: $msg" . PHP_EOL;
    echo PHP_EOL;
    ob_flush();
    flush();
}

// event-stream
$i = 1;
$max = 20;
$nonce = fRequest::get('wpnonce');
$user_name = fRequest::get('key');
$user = get_user_by('login', $user_name);

$key = "mm_notification";
if (!wp_verify_nonce($nonce, $user->ID)) {
    exit;
}
global $wpdb;
$sql = "SELECT meta_value FROM " . $wpdb->usermeta . " WHERE user_id = %d AND meta_key=%s";
$cache = $wpdb->get_var($wpdb->prepare($sql, $user->ID, $key));
$cache = unserialize($cache);

if (is_array($cache) && $cache['status'] == true) {
    //display notice
    $json = array(
        'count' => $cache['count'],
        'messages' => $cache['messages']
    );

    mm_server_sent_msg($i, fJSON::encode($cache));
    delete_user_meta($user->ID, $key);
    exit;
} else {
    echo "retry: 10000" . PHP_EOL;
}