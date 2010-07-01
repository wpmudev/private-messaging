<?php
require_once('admin.php');
$tmp_unread_message_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = '" . $user_ID . "' AND message_status = 'unread'");
if ($tmp_unread_message_count > 0){
	$title = __('Inbox') . ' (' . $tmp_unread_message_count . ')';
} else {
	$title = __('Inbox');
}
$parent_file = 'inbox.php';
require_once('admin-header.php');

messaging_inbox_page_output();

include('admin-footer.php');
?>