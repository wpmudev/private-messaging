<?php
/*
Plugin Name: User Online Widget
Description:
Author: Incsub
Version: 1.0.0
Author URI:
*/

function widget_user_online_init() {
	global $wpdb, $user_ID;
		
	// Check for the required API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_user_online_control() {
		global $wpdb, $user_ID;
		$options = $newoptions = get_option('widget_user_online');
		if ( $_POST['user-online-submit'] ) {
			$newoptions['user-online-title'] = strip_tags(stripslashes($_POST['user-online-title']));
			$newoptions['user-online-message-form'] = $_POST['user-online-message-form'];
			$newoptions['user-online-uid'] = $_POST['user-online-uid'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_user_online', $options);
		}
	?>
				<div style="text-align:right">
                <?php
				$tmp_user_online_title = wp_specialchars($options['title'], true);
				if ($tmp_user_online_title == ''){
					$tmp_user_online_title = __('Online Status');
				}
				?>
                <!---
				<label for="user-online-title" style="line-height:35px;display:block;"><?php _e('Title:', 'widgets'); ?> <input type="text" id="user-online-title" name="user-online-title" value="<?php echo $tmp_user_online_title; ?>" style="width:65%;" /></label>
                -->
                <?php
				$tmp_blog_users_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpdb->base_prefix . $wpdb->blogid . "_capabilities'");
				if ($tmp_blog_users_count > 1){
							$tmp_username = $wpdb->get_var("SELECT user_login FROM " . $wpdb->users . " WHERE ID = '" . $tmp_sent_message['sent_message_from_user_ID'] . "'");
					?>
					<label for="user-online-uid" style="line-height:35px;display:block;"><?php _e('User:', 'widgets'); ?>
					<select name="user-online-uid" id="user-online-uid" style="width:65%;">
					<?php
					$query = "SELECT user_id FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpdb->base_prefix . $wpdb->blogid . "_capabilities'";
					$tmp_users = $wpdb->get_results( $query, ARRAY_A );
					if (count($tmp_users) > 0){
						foreach ($tmp_users as $tmp_user){
							$tmp_username = $wpdb->get_var("SELECT user_login FROM " . $wpdb->users . " WHERE ID = '" . $tmp_user['user_id'] . "'");
							?>
							<option value="<?php echo $tmp_user['user_id']; ?>" <?php if ($options['user-online-uid'] == $tmp_user['user_id']){ echo 'selected="selected"'; } ?> ><?php echo $tmp_username; ?></option>
                            <?php
						}
					}
					?>
					</select>
					</label>
					<?php
				} else {
					if ($tmp_blog_users_count == 1){
						$tmp_user_online_uid = $wpdb->get_var("SELECT user_id FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpdb->base_prefix . $wpdb->blogid . "_capabilities'");
					} else {
						$tmp_user_online_uid = $user_ID;
					}
					?>
					<input type="hidden" name="user-online-uid" value="<?php echo $tmp_user_online_uid; ?>" />
					<?php
				}
				if (function_exists('messaging_make_current')){
				?>
				<label for="user-online-message-form" style="line-height:35px;display:block;"><?php _e('Message Form', 'widgets'); ?>:
                <select name="user-online-message-form" id="user-online-message-form" style="width:65%;">
                <option value="show" <?php if ($options['user-online-message-form'] == 'show'){ echo 'selected="selected"'; } ?> ><?php _e('Show'); ?></option>
                <option value="hide" <?php if ($options['user-online-message-form'] == 'hide'){ echo 'selected="selected"'; } ?> ><?php _e('Hide'); ?></option>
                </select>
                </label>
				<?php
				}
				?>
				<input type="hidden" name="user-online-submit" id="user-online-submit" value="1" />
				</div>
	<?php
	}
// This prints the widget
	function widget_user_online($args) {
		global $wpdb, $user_ID, $current_site, $messaging_current_version, $friends_current_version, $friends_enable_approval;
		extract($args);
		$defaults = array('count' => 10, 'username' => 'wordpress');
		$options = (array) get_option('widget_user_online');

		foreach ( $defaults as $key => $value )
			if ( !isset($options[$key]) )
				$options[$key] = $defaults[$key];

		?>
		<?php echo $before_widget; ?>
        	<?php
			$tmp_username = $wpdb->get_var("SELECT user_login FROM " . $wpdb->users . " WHERE ID = '" . $options['user-online-uid'] . "'");
			$tmp_display_name = $wpdb->get_var("SELECT display_name FROM " . $wpdb->users . " WHERE ID = '" . $options['user-online-uid'] . "'");
			if ($tmp_display_name == ''){
				$tmp_display_name = $tmp_username;
			}
			if ($user_ID != '') {
				$tmp_blog_id = $wpdb->get_var("SELECT meta_value FROM " . $wpdb->base_prefix . "usermeta WHERE meta_key = 'primary_blog' AND user_id = '" . $user_ID . "'");
				$tmp_blog_domain = $wpdb->get_var("SELECT domain FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = '" . $tmp_blog_id . "'");
				$tmp_blog_path = $wpdb->get_var("SELECT path FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = '" . $tmp_blog_id . "'");
				$tmp_blog_url =  'http://' . $tmp_blog_domain . $tmp_blog_path;
			}
			?>
			<?php //echo $before_title . $options['user-online-title'] . $after_title; ?>
			<?php echo $before_title . $tmp_display_name . $after_title; ?>
            <?php
				//=================================================//
				$tmp_last_active = $wpdb->get_var("SELECT last_active FROM " . $wpdb->base_prefix . "user_activity WHERE user_ID = '" . $options['user-online-uid'] . "'");
				$tmp_seconds = time() - $tmp_last_active;
				$tmp_user_online = '';
				if ($tmp_seconds < 301){
					$tmp_user_online = 'Online';
				} else {
					$tmp_user_online = 'Offline';
				}
				if (!is_site_admin($tmp_username)) {
				?>
                <p>
				<strong><?php _e('Status'); ?>: <?php echo __($tmp_user_online);  ?></strong>
				</p>
                <?php
				}
                if ($options['user-online-message-form'] == 'show' && $user_ID != '' && $messaging_current_version != '' && !is_site_admin($tmp_username)){
					?>
					<p>
					<strong><?php _e('Send Message'); ?>: </strong><br />
                    <?php
					if ( $_POST['action'] == 'process_message' && $_POST['message_content'] != '' ) {
						messaging_insert_message($options['user-online-uid'],'|' . $options['user-online-uid'] . '|',$user_ID,__('Quick Message'),$_POST['message_content'],'unread',0);
						messaging_new_message_notification($options['user-online-uid'],$user_ID,__('Quick Message'),$_POST['message_content']);
						messaging_insert_sent_message('|' . $options['user-online-uid'] . '|',$user_ID,__('Quick Message'),$_POST['message_content'],0);
						?>
						<br /><strong style="color:#666666"><?php _e('Message Sent!'); ?></strong><br />
                        <?php
					}
					?>
                    <form name="send_message" method="POST">
                    <input type="hidden" name="action" value="process_message" />
                    <input type="hidden" name="message_to" value="<?php echo $tmp_username; ?>" />
                    <input type="hidden" name="message_subject" value="<?php _e('Quick Message') ?>" />
                    <input type="text" name="message_content" value="" />
                    <input type="submit" name="Submit" value="<?php _e('Send') ?> &raquo;" />
                    </form>
                    </p>
					<?php
				}
                if ($user_ID != '' && $friends_current_version != ''){
					?>
					<p>
					<strong><?php _e('Add as friend'); ?>: </strong><br />
                    <?php
					if ( $_POST['action'] == 'process_friend' ) {
						$tmp_friend_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "friends WHERE friend_user_ID = '" . $options['user-online-uid'] . "' AND user_ID = '" . $user_ID . "'");
						if ( $tmp_friend_count > 0 ) {
							$tmp_friend_status = $wpdb->get_var("SELECT friend_approved FROM " . $wpdb->base_prefix . "friends WHERE friend_user_ID = '" . $options['user-online-uid'] . "' AND user_ID = '" . $user_ID . "'");
							if ( $tmp_friend_status == '1' ) {
								?>
								<br /><strong style="color:#666666"><?php _e('Already A Friend!'); ?></strong><br />
								<?php
							} else {
								?>
								<br /><strong style="color:#666666"><?php _e('Request Already Sent!'); ?></strong><br />
								<?php
							}
						} else {
							if ($friends_enable_approval == 1) {
								friends_add($user_ID, $options['user-online-uid'], '0');
								friends_add_notification($options['user-online-uid'],$user_ID);
								?>
								<br /><strong style="color:#666666"><?php _e('Friend Request Sent!'); ?></strong><br />
								<?php
							} else {
								friends_add($user_ID, $options['user-online-uid'], '1');
								friends_add_notification($options['user-online-uid'],$user_ID);
								?>
								<br /><strong style="color:#666666"><?php _e('Friend Added!'); ?></strong><br />
								<?php
							}
						}
					}
					?>
                    <form name="send_message" method="POST">
                    <input type="hidden" name="action" value="process_friend" />
                    <input type="submit" name="Submit" value="<?php _e('Add') ?> &raquo;" />
                    </form>
                    </p>
					<?php
				}
				//=================================================//
			?>
		<?php echo $after_widget; ?>
<?php
	}
	// Tell Dynamic Sidebar about our new widget and its control
	wp_register_sidebar_widget('Online Status', __('Online Status'), 'widget_user_online');
	wp_register_widget_control('Online Status', __('Online Status'), 'widget_user_online_control');

}

add_action('widgets_init', 'widget_user_online_init');

?>