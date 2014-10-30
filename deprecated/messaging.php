<?php
/*
Plugin Name: Messaging
Plugin URI: http://premium.wpmudev.org/project/messaging
Description: An internal email / messaging / inbox solution
Author: WPMU DEV
Version: 1.1.6.6
Author URI: http://premium.wpmudev.org
WDP ID: 68
Text Domain: messaging
*/

/*
Copyright 2007-2010 Incsub (http://incsub.com)
Author - S H Mohanjith (Incsub)
Contributors - Andrew Billits (Incsub)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$messaging_current_version = '1.1.6.6';
//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//

$messaging_max_inbox_messages = 90;
$messaging_official_message_bg_color = '#E5F3FF';

$messaging_email_notification_subject = '[SITE_NAME] New Message'; // SITE_NAME
$messaging_email_notification_content = 'Dear TO_USER,

You have received a new message from FROM_USER.

Thanks,
SITE_NAME'; // TO_USER, FROM_USER, SITE_NAME, SITE_URL

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
//check for activating
if (!isset($_GET['key']) || $_GET['key'] == '' || $_GET['key'] === ''){
	add_action('admin_head', 'messaging_make_current');
}
if(isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'messaging_new'){
	add_action('admin_head', 'messaging_header_js');
}
if(isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'reply' && isset($_GET['mid']) && $_GET['mid'] != ''){
	add_action('admin_head', 'messaging_header_js');
}
if(isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'reply_process' && isset($_POST['message_to']) && $_POST['message_to'] != ''){
	add_action('admin_head', 'messaging_header_js');
}

add_action('admin_menu', 'messaging_plug_pages');
add_action('network_admin_menu', 'messaging_network_plug_pages');
add_action('wpabar_menuitems', 'messaging_admin_bar');
add_action('wp_ajax_messaging_suggest_user', 'messaging_suggest_user' );
add_filter('manage_users_columns', 'messaging_add_user_column');
add_action('manage_users_custom_column', 'messaging_manage_users_column', 10, 3);

// Not yet, may be when we add Messaging Menu to network admin
// add_filter('wpmu_users_columns', 'messaging_add_user_column');
// add_action('wpmu_users_custom_column', 'messaging_manage_users_column', 10, 3);

if (isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'view' && isset($_GET['mid']) && $_GET['mid'] != ''){
	messaging_update_message_status(intval($_GET['mid']),'read');
}
if (isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'reply' && isset($_GET['mid']) && $_GET['mid'] != ''){
	add_action('admin_footer', 'messaging_set_focus_js');
}
if (isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'reply_process'){
	add_action('admin_footer', 'messaging_set_focus_js');
}
add_action('init', 'messaging_init');
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
function messaging_init() {
	global $messaging_max_reached_message,$messaging_email_notification_subject;

	load_plugin_textdomain('messaging', false, dirname(plugin_basename(__FILE__)).'/languages');

	$messaging_max_reached_message = __('You are currently at or over your inbox message limit. You will not be able to view, reply to, or send new messages until you remove messages from your inbox.', 'messaging');
	$messaging_email_notification_subject = __('[SITE_NAME] New Message', 'messaging'); // SITE_NAME
}

function messaging_enqueue_scripts() {
	wp_enqueue_script('script-name', plugins_url() . '/messaging/js/messaging-admin.js', array('jquery', 'jquery-ui-autocomplete'), '1.0.0', true);
}

function messaging_suggest_user() {

	header('HTTP/1.1 200 OK');
	header('Content-Type: application/json');

	$user_query = new WP_User_Query(
		array(
			'blog_id' => 0,
			'fields' => array('ID', 'user_login', 'user_nicename', 'user_email', 'user_url'),
			'orderby' => 'user_login',
			'order' => 'ASC',
			'number' => 5,
			'search' => "*{$_REQUEST['user']}*",
			'search_columns' => array('user_login', 'user_nicename', 'user_email', 'user_url')
		)
	);

	$users = array();

	if ( ! empty( $user_query->results ) ) {
		foreach ( $user_query->results as $user ) {
			$users[] = array(
				'id' => $user->ID,
				'value' => $user->user_login,
				'label' => "{$user->user_nicename} ({$user->user_login} / {$user->user_email} {$user->user_url})",
			);
		}
	}

	echo json_encode($users);
	exit();
}

function messaging_make_current() {
	global $wpdb, $messaging_current_version;
	if (get_site_option( "messaging_version" ) == '') {
		add_site_option( 'messaging_version', '0.0.0' );
	}

	if (get_site_option( "messaging_version" ) == $messaging_current_version) {
		// do nothing
	} else {
		//up to current version
		messaging_global_install();
		update_site_option( "messaging_installed", "no" );
		update_site_option( "messaging_version", $messaging_current_version );
	}

	//--------------------------------------------------//
	if (get_option( "messaging_version" ) == '') {
		add_option( 'messaging_version', '0.0.0' );
	}

	if (get_option( "messaging_version" ) == $messaging_current_version) {
		// do nothing
	} else {
		//up to current version
		update_option( "messaging_version", $messaging_current_version );
		messaging_blog_install();
	}
}

function messaging_blog_install() {
	global $wpdb, $messaging_current_version;
}

function messaging_global_install() {
	global $wpdb, $messaging_current_version;
	if (get_site_option( "messaging_installed" ) == '') {
		add_site_option( 'messaging_installed', 'no' );
	}

	//if (get_site_option( "messaging_installed" ) == "yes") {
		// do nothing
	//} else {
		$messaging_table1 = "CREATE TABLE `" . $wpdb->base_prefix . "messages` (
  `message_ID` bigint(20) unsigned NOT NULL auto_increment,
  `message_from_user_ID` bigint(20) NOT NULL,
  `message_to_user_ID` bigint(20) NOT NULL,
  `message_to_all_user_IDs` TEXT CHARACTER SET utf8 NOT NULL,
  `message_subject` TEXT CHARACTER SET utf8 NOT NULL,
  `message_content` TEXT CHARACTER SET utf8 NOT NULL,
  `message_status` VARCHAR(255) CHARACTER SET utf8 NOT NULL,
  `message_stamp`  VARCHAR(255) CHARACTER SET utf8 NOT NULL,
  `message_official` tinyint(0) NOT NULL default '0',
  PRIMARY KEY  (`message_ID`)
) ENGINE=MyISAM;";
		$messaging_table2 = "CREATE TABLE `" . $wpdb->base_prefix . "sent_messages` (
  `sent_message_ID` bigint(20) unsigned NOT NULL auto_increment,
  `sent_message_from_user_ID` bigint(20) NOT NULL,
  `sent_message_to_user_IDs` TEXT CHARACTER SET utf8 NOT NULL,
  `sent_message_subject` TEXT CHARACTER SET utf8 NOT NULL,
  `sent_message_content` TEXT CHARACTER SET utf8 NOT NULL,
  `sent_message_stamp`  VARCHAR(255) CHARACTER SET utf8 NOT NULL,
  `sent_message_official` tinyint(0) NOT NULL default '0',
  PRIMARY KEY  (`sent_message_ID`)
) ENGINE=MyISAM;";
		$messaging_table3 = "";
		$messaging_table4 = "";
		$messaging_table5 = "";

		$wpdb->query( $messaging_table1 );
		$wpdb->query( $messaging_table2 );
		update_site_option( "messaging_installed", "yes" );
	//}
}

function messaging_plug_pages() {
	global $wpdb, $user_ID;
	$tmp_unread_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status = %s", $user_ID, 'unread'));
	if ($tmp_unread_message_count > 0){
                $count_output = '&nbsp;<span class="update-plugins"><span class="updates-count count-' . $tmp_unread_message_count . '">' . $tmp_unread_message_count . '</span></span>';
	} else {
		$count_output = '';
	}

	$inbox_page = add_menu_page(__('Inbox', 'messaging'), __('Inbox', 'messaging').$count_output, 'read', 'messaging', 'messaging_inbox_page_output');
	$new_message_page = add_submenu_page('messaging', __('Inbox', 'messaging'), __('New Message', 'messaging'), 'read', 'messaging_new', 'messaging_new_page_output' );
	$sent_messages_page = add_submenu_page('messaging', __('Inbox', 'messaging'), __('Sent Messages', 'messaging'), 'read', 'messaging_sent', 'messaging_sent_page_output' );
	$notification_settings_page = add_submenu_page('messaging', __('Inbox', 'messaging'), __('Notifications', 'messaging'), 'read', 'messaging_message-notifications', 'messaging_notifications_page_output' );

	if (!is_multisite()) {
		$messaging_network_settings_page = add_submenu_page('messaging', __('Messaging Settings', 'messaging'), __('Messaging Settings', 'messaging'), 'manage_options', 'messaging_settings', 'messaging_network_settings' );
	}

	add_action('admin_print_scripts-' . $new_message_page, 'messaging_enqueue_scripts');
}

function messaging_network_plug_pages() {
	add_submenu_page('settings.php', __('Messaging Settings', 'messaging'), __('Messaging', 'messaging'), 'manage_network_settings', 'messaging_settings', 'messaging_network_settings' );
}

function messaging_network_settings() {
	global $messaging_email_notification_content, $messaging_email_notification_subject,$user_ID;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field($_GET['updatedmsg'])) ?></p></div><?php
	}
	$action = isset($_GET[ 'action' ]) ? sanitize_text_field($_GET[ 'action' ]) : '';
	echo '<div class="wrap">';
	switch( $action ) {
		//---------------------------------------------------//
		default:
			$tmp_message_email_notification = get_user_meta($user_ID,'message_email_notification');
			?>
			<h2><?php _e('Messaging Settings', 'messaging') ?></h2>
				<?php
				if (is_multisite()) {
                	?><form method="post" action="settings.php?page=messaging_settings&action=process"><?php
				} else {
					?><form method="post" action="admin.php?page=messaging_settings&action=process"><?php
				}
				?>
                <table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Notification e-mail subject', 'messaging') ?></th>
				<td>
				<input id="messaging_email_notification_subject" name="messaging_email_notification_subject"
				value="<?php echo get_site_option('messaging_email_notification_subject', $messaging_email_notification_subject); ?>" />
				<br/>
				<?php _e('Variables:', 'messaging'); ?> SITE_NAME</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Notification e-mail contents', 'messaging') ?></th>
				<td>
				<textarea id="messaging_email_notification_content" name="messaging_email_notification_content"
				rows="6" cols="40"><?php echo get_site_option('messaging_email_notification_content', $messaging_email_notification_content); ?></textarea>
				<br/>
				<?php _e('Variables:', 'messaging'); ?> TO_USER, FROM_USER, SITE_NAME, SITE_URL</td>
			</tr>
                </table>
                <p class="submit">
                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'messaging') ?>" />
                </p>
                </form>
            <?php
		break;
		//---------------------------------------------------//
		case "process":
			update_site_option('messaging_email_notification_subject',stripslashes(sanitize_text_field($_POST['messaging_email_notification_subject'])));
			update_site_option('messaging_email_notification_content',wp_kses_post($_POST['messaging_email_notification_content']));

			if (is_multisite()) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='settings.php?page=messaging_settings&updated=true&updatedmsg=" . urlencode(_e('Settings saved.', 'messaging')) . "';
				</script>
				";
			} else {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='admin.php?page=messaging_settings&updated=true&updatedmsg=" . urlencode(_e('Settings saved.', 'messaging')) . "';
				</script>
				";

			}
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function messaging_admin_bar( $menu ) {
	unset( $menu['admin.php?page=messaging'] );
	return $menu;
}

function messaging_insert_message($tmp_to_uid,$tmp_to_all_uids,$tmp_from_uid,$tmp_subject,$tmp_content,$tmp_status,$tmp_official = 0) {
	global $wpdb;
	/*
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "messages (message_from_user_ID,message_to_user_ID,message_to_all_user_IDs,message_subject,message_content,message_status,message_stamp,message_official) VALUES ( '" . $tmp_from_uid . "','" . $tmp_to_uid . "','" . $tmp_to_all_uids . "','" . addslashes($tmp_subject) . "','" . addslashes($tmp_content) . "','" . $tmp_status . "','" . time() . "','" . $tmp_official . "' )" );
	*/
	$wpdb->insert($wpdb->base_prefix . "messages",
		array(
			'message_from_user_ID'		=>	$tmp_from_uid,
			'message_to_user_ID'		=>	$tmp_to_uid,
			'message_to_all_user_IDs'	=>	$tmp_to_all_uids,
			'message_subject'			=>	$tmp_subject,
			'message_content'			=>	$tmp_content,
			'message_status'			=>	$tmp_status,
			'message_stamp'				=>	time(),
			'message_official'			=>	$tmp_official
		), array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d')
	);
}

function messaging_update_message_status($tmp_mid,$tmp_status) {
	global $wpdb;
	//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "messages SET message_status = '" . $tmp_status . "' WHERE message_ID = '" . $tmp_mid . "' " );
	$wpdb->update($wpdb->base_prefix . "messages",
		array('message_status' 	=> 	$tmp_status),
		array('message_ID' 		=>	$tmp_mid),
		array('%s'), array('%d')
	);
}

function messaging_remove_message($tmp_mid) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $tmp_mid ));
}

function messaging_insert_sent_message($tmp_to_all_uids,$tmp_from_uid,$tmp_subject,$tmp_content,$tmp_official = 0) {
	global $wpdb;
/*
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "sent_messages (sent_message_from_user_ID,sent_message_to_user_IDs,sent_message_subject,sent_message_content,sent_message_stamp,sent_message_official) VALUES ( '" . $tmp_from_uid . "','" . $tmp_to_all_uids . "','" . addslashes($tmp_subject) . "','" . addslashes($tmp_content) . "','" . time() . "','" . $tmp_official . "' )" );
*/
	$wpdb->insert($wpdb->base_prefix . "sent_messages",
		array(
			'sent_message_from_user_ID'		=>	$tmp_from_uid,
			'sent_message_to_user_IDs'		=>	$tmp_to_all_uids,
			'sent_message_subject'			=>	$tmp_subject,
			'sent_message_content'			=>	$tmp_content,
			'sent_message_stamp'			=>	time(),
			'sent_message_official'			=>	$tmp_official
		), array('%d', '%s', '%s', '%s', '%s', '%d')
	);
}

function messaging_remove_sent_message($tmp_mid) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_ID = %d", $tmp_mid));
}

function messaging_new_message_notification($tmp_to_uid,$tmp_from_uid,$tmp_subject,$tmp_content) {
	global $wpdb, $current_site, $user_ID, $messaging_email_notification_subject, $messaging_email_notification_content;

	if (is_multisite()) {
		$SITE_NAME 	= $current_site->site_name;
		$SITE_URL	= 'http://'. $current_site->domain;
	} else {
		$SITE_NAME 	= get_option('blogname');
		$SITE_URL	= get_option('siteurl');
	}

	if (get_user_meta($tmp_to_uid,'message_email_notification') != 'no'){
		$tmp_to_username =  $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_to_uid));
		$tmp_to_email =  $wpdb->get_var($wpdb->prepare("SELECT user_email FROM " . $wpdb->users . " WHERE ID = %s", $tmp_to_uid));
		$tmp_from_username =  $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_from_uid));

		$message_content = get_site_option('messaging_email_notification_content', $messaging_email_notification_content);

		$message_content = str_replace( "SITE_NAME", $SITE_NAME, $message_content );
		$message_content = str_replace( "SITE_URL", $SITE_URL, $message_content );

		$message_content = str_replace( "TO_USER", $tmp_to_username, $message_content );
		$message_content = str_replace( "FROM_USER", $tmp_from_username, $message_content );
		$message_content = str_replace( "\'", "'", $message_content );

		$subject_content = get_site_option('messaging_email_notification_subject', $messaging_email_notification_subject);
		$subject_content = str_replace( "SITE_NAME", $SITE_NAME, $subject_content );

		$admin_email = get_site_option('admin_email');
		if ($admin_email == ''){
			$admin_email = 'admin@' . $current_site->domain;
		}
		$from_email = $admin_email;

		$message_headers = "MIME-Version: 1.0\n" . "From: " . $SITE_NAME .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
		wp_mail($tmp_to_email, $subject_content, $message_content, $message_headers);
	}
}

//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//

function messaging_set_focus_js(){
	?>
	<SCRIPT LANGUAGE='JavaScript'>
		setTimeout("tinyMCE.execCommand('mceFocus', false, 'message_content');window.blur();window.focus();tinyMCE.execCommand('mceFocus', false, 'message_content');", 0);
	</SCRIPT>
	<?php
}

function messaging_header_js(){
	global $current_site;
	$valid_elements = 'p/-div[*],-strong/-b[*],-em/-i[*],-font[*],-ul[*],-ol[*],-li[*],*[*]';
	$valid_elements = apply_filters('mce_valid_elements', $valid_elements);
	$mce_buttons = apply_filters('mce_buttons', array('bold', 'italic', 'underline', 'strikethrough', 'separator', 'bullist', 'numlist', 'outdent', 'indent', 'separator', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'separator', 'link', 'unlink', 'image', 'wp_more', 'separator', 'spellchecker', 'separator', 'wp_help', 'wp_adv'));
	$mce_buttons = implode($mce_buttons, ',');

	$mce_buttons_2 = apply_filters('mce_buttons_2', array('wp_adv_start', 'forecolor', 'separator', 'pastetext', 'pasteword', 'separator', 'removeformat', 'cleanup', 'separator', 'charmap', 'separator', 'undo', 'redo', 'wp_adv_end'));
	$mce_buttons_2 = implode($mce_buttons_2, ',');

	$mce_buttons_3 = apply_filters('mce_buttons_3', array());
	$mce_buttons_3 = implode($mce_buttons_3, ',');

	$mce_browsers = apply_filters('mce_browsers', array('msie', 'gecko', 'opera', 'safari'));
	$mce_browsers = implode($mce_browsers, ',');

	$mce_popups_css = get_option('siteurl') . '/wp-includes/js/tinymce/plugins/wordpress/popups.css';
	$mce_css = get_option('siteurl') . '/wp-includes/js/tinymce/plugins/wordpress/css/content.css';
	$mce_css = apply_filters('mce_css', $mce_css);
	if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
		$mce_css = str_replace('http://', 'https://', $mce_css);
		$mce_popups_css = str_replace('http://', 'https://', $mce_popups_css);
	}

	$mce_locale = ( '' == get_locale() ) ? 'en' : strtolower(get_locale());
	if (preg_match('/_/i', $mce_locale)) {
		$mce_locale_parts = preg_split('/_/', $mce_locale);
		$mce_locale = $mce_locale_parts[0];
	}
	?>
<script type="text/javascript">
/* <![CDATA[ */
tinyMCEPreInit = {
	base : "<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce",
	suffix : "",
	query : "ver=3393a",
	mceInit : {
		mode:"specific_textareas",
		editor_selector:"mceEditor",
		width:"100%",
		theme:"advanced",
		skin:"wp_theme",
		theme_advanced_buttons1:"<?php echo $mce_buttons; ?>",
		theme_advanced_buttons2:"<?php echo $mce_buttons_2; ?>",
		theme_advanced_buttons3:"<?php echo $mce_buttons_3; ?>",
		theme_advanced_buttons4:"",
		language:"<?php print $mce_locale; ?>",
		spellchecker_languages:"+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv",
		theme_advanced_toolbar_location:"top",
		theme_advanced_toolbar_align:"left",
		theme_advanced_statusbar_location:"bottom",
		browsers : "<?php echo $mce_browsers; ?>",
		theme_advanced_resizing:true,
		theme_advanced_resize_horizontal:false,
		dialog_type:"modal",
		content_css : "<?php echo $mce_css; ?>",
		valid_elements : "<?php echo $valid_elements; ?>",
		formats:{
			alignleft : [
				{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'left'}},
				{selector : 'img,table', classes : 'alignleft'}
			],
			aligncenter : [
				{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'center'}},
				{selector : 'img,table', classes : 'aligncenter'}
			],
			alignright : [
				{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'right'}},
				{selector : 'img,table', classes : 'alignright'}
			],
			strikethrough : {inline : 'del'}
		},
		relative_urls:false,
		remove_script_host:false,
		convert_urls:false,
		apply_source_formatting:false,
		remove_linebreaks:true,
		gecko_spellcheck:true,
		entities:"38,amp,60,lt,62,gt",
		accessibility_focus:true,
		tabfocus_elements:"major-publishing-actions",
		media_strict:false,
		paste_remove_styles:true,
		paste_remove_spans:true,
		paste_strip_class_attributes:"all",
		paste_text_use_dialog:true,
		wpeditimage_disable_captions:false,
		plugins:"inlinepopups,spellchecker,paste,wordpress,fullscreen,wpeditimage,wpgallery,tabfocus,wplink,wpdialogs"},
		load_ext : function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
};
/* ]]> */
</script>
<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/tiny_mce.js?ver=20070528'></script>
<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/langs/wp-langs-en.js?ver=3393a'></script>
<script type="text/javascript">
/* <![CDATA[ */
(function(){var t=tinyMCEPreInit,sl=tinymce.ScriptLoader,ln=t.mceInit.language,th=t.mceInit.theme,pl=t.mceInit.plugins;sl.markDone(t.base+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'_dlg.js');tinymce.each(pl.split(','),function(n){if(n&&n.charAt(0)!='-'){sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'.js');sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'_dlg.js');}});})();
tinyMCE.init(tinyMCEPreInit.mceInit);
/* ]]> */
</script>
<style type="text/css">
#message_content_tbl {
	border: 1px solid #DFDFDF;
}
</style>
	<?php
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function messaging_inbox_page_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $messaging_official_message_bg_color, $messaging_max_inbox_messages, $messaging_max_reached_message, $wp_version;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field($_GET['updatedmsg'])) ?></p></div><?php
	}

	$action = isset($_GET[ 'action' ]) ? $_GET[ 'action' ] : '';

	echo '<div class="wrap">';
	switch( $action ) {
		//---------------------------------------------------//
		default:
			if ( isset($_POST['Remove']) ) {
				messaging_update_message_status(intval($_POST['mid']),'removed');
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='admin.php?page=messaging&updated=true&updatedmsg=" . urlencode(__('Message removed.', 'messaging')) . "';
				</script>
				";
			}
			if ( isset($_POST['Reply']) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='admin.php?page=messaging&action=reply&mid=" . intval($_POST['mid']) . "';
				</script>
				";
			}
			$tmp_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status != %s", $user_ID, 'removed'));
			$tmp_unread_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status = %s", $user_ID, 'unread'));
			$tmp_read_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status = %s", $user_ID, 'read'));
			?>
            <h2><?php _e('Inbox', 'messaging') ?> <a class="add-new-h2" href="admin.php?page=messaging_new"><?php _e('New Message', 'messaging') ?></a></h2>
            <?php
			if ($tmp_message_count == 0){
			?>
            <p><?php _e('No messages to display', 'messaging') ?></p>
            <?php
			} else {
				?>
				<h3><?php _e('Usage', 'messaging') ?></h3>
                <p>
				<?php _e('Maximum inbox messages', 'messaging') ?>: <strong><?php echo $messaging_max_inbox_messages; ?></strong>
                <br />
                <?php _e('Current inbox messages', 'messaging') ?>: <strong><?php echo $tmp_message_count; ?></strong>
                </p>
                <?php
				if ($tmp_message_count >= $messaging_max_inbox_messages){
				?>
                <p><strong><center><?php _e($messaging_max_reached_message, 'messaging') ?></center></strong></p>
				<?php
				}
				if ($tmp_unread_message_count > 0){
				?>
				<h3><?php _e('Unread', 'messaging') ?></h3>
				<?php
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %s AND message_status = %s ORDER BY message_ID DESC", $user_ID, 'unread');
				$tmp_messages = $wpdb->get_results( $query, ARRAY_A );
				echo "
				<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
				<thead><tr>
				<th scope='col'>" . __("From", 'messaging') . "</th>
				<th scope='col'>" . __("Subject", 'messaging') . "</th>
				<th scope='col'>" . __("Recieved", 'messaging') . "</th>
				<th scope='col'>" . __("Actions", 'messaging') . "</th>
				<th scope='col'></th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				";
				$class = '';
				if (count($tmp_messages) > 0){
					$class = ('alternate' == $class) ? '' : 'alternate';
					foreach ($tmp_messages as $tmp_message){
					if ($tmp_message['message_official'] == 1){
						$style = "'style=background-color:" . $messaging_official_message_bg_color . ";'";
					} else {
						$style = "";
					}
					//=========================================================//
					echo "<tr class='" . $class . "' " . $style . ">";
					if ($tmp_message['message_official'] == 1){
						$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						$tmp_display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						if ( $tmp_display_name == '' ) {
							$tmp_display_name = $tmp_username;
						}
						$tmp_user_url = messaging_user_primary_blog_url($tmp_message['message_from_user_ID']);
						if ($tmp_user_url == ''){
							echo "<td valign='top'><strong>" . $tmp_display_name . "</strong></td>";
						} else {
							echo "<td valign='top'><strong><a href='" . $tmp_user_url . "'>" . $tmp_display_name . "</a></strong></td>";
						}
						echo "<td valign='top'><strong>" . stripslashes($tmp_message['message_subject']) . "</strong></td>";
						echo "<td valign='top'><strong>" . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_message['message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "</strong></td>";
					} else {
						$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						$tmp_display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						if ( $tmp_display_name == '' ) {
							$tmp_display_name = $tmp_username;
						}
						$tmp_user_url = messaging_user_primary_blog_url($tmp_message['message_from_user_ID']);
						if ($tmp_user_url == ''){
							echo "<td valign='top'>" . $tmp_display_name . "</td>";
						} else {
							echo "<td valign='top'><a href='" . $tmp_user_url . "'>" . $tmp_display_name . "</a></td>";
						}
						echo "<td valign='top'>" . stripslashes($tmp_message['message_subject']) . "</td>";
						echo "<td valign='top'>" . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_message['message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "</td>";
					}
					if ($tmp_message_count >= $messaging_max_inbox_messages){
						echo "<td valign='top'><a class='edit'>" . __('View', 'messaging') . "</a></td>";
						echo "<td valign='top'><a class='edit'>" . __('Reply', 'messaging') . "</a></td>";
					} else {
						echo "<td valign='top'><a href='admin.php?page=messaging&action=view&mid=" . $tmp_message['message_ID'] . "' rel='permalink' class='edit'>" . __('View', 'messaging') . "</a></td>";
						echo "<td valign='top'><a href='admin.php?page=messaging&action=reply&mid=" . $tmp_message['message_ID'] . "' rel='permalink' class='edit'>" . __('Reply', 'messaging') . "</a></td>";
					}
					echo "<td valign='top'><a href='admin.php?page=messaging&action=remove&mid=" . $tmp_message['message_ID'] . "' rel='permalink' class='delete'>" . __('Remove', 'messaging') . "</a></td>";
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				}
				?>
				</tbody></table>
				<?php
				}
				//=========================================================//
				if ($tmp_read_message_count > 0){
				?>
				<h3><?php _e('Read', 'messaging') ?></h3>
				<?php
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status = %s ORDER BY message_ID DESC", $user_ID, 'read');
				$tmp_messages = $wpdb->get_results( $query, ARRAY_A );
				echo "
				<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
				<thead><tr>
				<th scope='col'>" . __("From", 'messaging') . "</th>
				<th scope='col'>" . __("Subject", 'messaging') . "</th>
				<th scope='col'>" . __("Recieved", 'messaging') . "</th>
				<th scope='col'>" . __("Actions", 'messaging') . "</th>
				<th scope='col'></th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				";
				$class = '';
				if (count($tmp_messages) > 0){
					$class = ('alternate' == $class) ? '' : 'alternate';
					foreach ($tmp_messages as $tmp_message){
					if ($tmp_message['message_official'] == 1){
						$style = "'style=background-color:" . $messaging_official_message_bg_color . ";'";
					} else {
						$style = "";
					}
					//=========================================================//
					echo "<tr class='" . $class . "' " . $style . ">";
					if ($tmp_message['message_official'] == 1){
						$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						$tmp_display_name = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						if ( $tmp_display_name == '' ) {
							$tmp_display_name = $tmp_username;
						}
						$tmp_user_url = messaging_user_primary_blog_url($tmp_message['message_to_user_ID']);
						if ($tmp_user_url == ''){
							echo "<td valign='top'><strong>" . $tmp_display_name . "</strong></td>";
						} else {
							echo "<td valign='top'><strong><a href='" . $tmp_user_url . "'>" . $tmp_display_name . "</a></strong></td>";
						}
						echo "<td valign='top'><strong>" . stripslashes($tmp_message['message_subject']) . "</strong></td>";
						echo "<td valign='top'><strong>" . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_message['message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "</strong></td>";
					} else {
						$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						$tmp_display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						if ( $tmp_display_name == '' ) {
							$tmp_display_name = $tmp_username;
						}
						$tmp_user_url = messaging_user_primary_blog_url($tmp_message['message_to_user_ID']);
						if ($tmp_user_url == ''){
							echo "<td valign='top'>" . $tmp_display_name . "</td>";
						} else {
							echo "<td valign='top'><a href='" . $tmp_user_url . "'>" . $tmp_display_name . "</a></td>";
						}
						echo "<td valign='top'>" . stripslashes($tmp_message['message_subject']) . "</td>";
						echo "<td valign='top'>" . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $tmp_message['message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "</td>";
					}
					if ($tmp_message_count >= $messaging_max_inbox_messages){
						echo "<td valign='top'><a class='edit'>" . __('View', 'messaging') . "</a></td>";
						echo "<td valign='top'><a class='edit'>" . __('Reply', 'messaging') . "</a></td>";
					} else {
						echo "<td valign='top'><a href='admin.php?page=messaging&action=view&mid=" . $tmp_message['message_ID'] . "' rel='permalink' class='edit'>" . __('View', 'messaging') . "</a></td>";
						echo "<td valign='top'><a href='admin.php?page=messaging&action=reply&mid=" . $tmp_message['message_ID'] . "' rel='permalink' class='edit'>" . __('Reply', 'messaging') . "</a></td>";
					}
					echo "<td valign='top'><a href='admin.php?page=messaging&action=remove&mid=" . $tmp_message['message_ID'] . "' rel='permalink' class='delete'>" . __('Remove', 'messaging') . "</a></td>";
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				}
				?>
				</tbody></table>
				<?php
				}
			}
		break;
		//---------------------------------------------------//
		case "view":
			$tmp_total_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status != %s", $user_ID, 'removed'));
			$tmp_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d AND message_to_user_ID = %d", $_GET['mid'], $user_ID));
			if ($tmp_message_count > 0){
				if ($tmp_total_message_count >= $messaging_max_inbox_messages){
					?>
					<p><strong><center><?php _e($messaging_max_reached_message, 'messaging') ?></center></strong></p>
					<?php
					} else {
					messaging_update_message_status(intval($_GET['mid']),'read');
					$tmp_message_subject = stripslashes($wpdb->get_var($wpdb->prepare("SELECT message_subject FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid'])));
					$tmp_message_content = stripslashes($wpdb->get_var($wpdb->prepare("SELECT message_content FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid'])));
					$tmp_message_from_user_ID = $wpdb->get_var($wpdb->prepare("SELECT message_from_user_ID FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid']));
					$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message_from_user_ID));
					$tmp_message_status = $wpdb->get_var($wpdb->prepare("SELECT message_status FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid']));
					$tmp_message_status = ucfirst($tmp_message_status);
					$tmp_message_status = __($tmp_message_status, 'messaging');
					$tmp_message_stamp = $wpdb->get_var($wpdb->prepare("SELECT message_stamp FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid']));
					?>

					<h2><?php _e('View Message: ', 'messaging') ?><?php echo intval($_GET['mid']); ?></h2>
					<form name="new_message" method="POST" action="admin.php?page=messaging">
					<input type="hidden" name="mid" value="<?php echo intval($_GET['mid']); ?>" />
					<h3><?php _e('Sent', 'messaging') ?></h3>
					<p><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $tmp_message_stamp + (get_option( 'gmt_offset' ) * 3600), true); ?></p>
					<h3><?php _e('Status', 'messaging') ?></h3>
					<p><?php echo $tmp_message_status; ?></p>
					<h3><?php _e('From', 'messaging') ?></h3>
					<p><?php echo $tmp_username; ?></p>
					<h3><?php _e('Subject', 'messaging') ?></h3>
					<p><?php echo $tmp_message_subject; ?></p>
					<h3><?php _e('Content', 'messaging') ?></h3>
					<p><?php echo wpautop($tmp_message_content); ?></p>
                    <p class="submit">
					<input class="button button-secondary" type="submit" name="Submit" value="<?php _e('Back', 'messaging') ?>" />
					<input class="button button-secondary" type="submit" name="Remove" value="<?php _e('Remove', 'messaging') ?>" />
					<input class="button button-primary" type="submit" name="Reply" value="<?php _e('Reply', 'messaging') ?>" />
                    </p>
					</form>
					<?php
				}
			} else {
			?>
            <p><?php _e('You do not have permission to view this message', 'messaging') ?></p>
            <?php
			}
		break;
		//---------------------------------------------------//
		case "remove":
			//messaging_update_message_status($_GET['mid'],'removed');
			messaging_remove_message(intval($_GET['mid']));
			echo "
			<SCRIPT LANGUAGE='JavaScript'>
			window.location='admin.php?page=messaging&updated=true&updatedmsg=" . urlencode('Message removed.') . "';
			</script>
			";
		break;
		//---------------------------------------------------//
		case "reply":
			$tmp_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d AND message_to_user_ID = %d", $_GET['mid'], $user_ID));
			if ($tmp_message_count > 0){
			$tmp_message_from_user_ID = $wpdb->get_var($wpdb->prepare("SELECT message_from_user_ID FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid']));
			$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message_from_user_ID));
			$tmp_message_subject = stripslashes($wpdb->get_var($wpdb->prepare("SELECT message_subject FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid'])));
			$tmp_message_subject = __('RE: ', 'messaging') . $tmp_message_subject;
			$tmp_message_content = stripslashes($wpdb->get_var($wpdb->prepare("SELECT message_content FROM " . $wpdb->base_prefix . "messages WHERE message_ID = %d", $_GET['mid'])));
			//$tmp_message_content = "\n\n" . $tmp_username . __(' wrote:') . '<hr>' . $tmp_message_content;

			$rows = get_option('default_post_edit_rows');
            if (($rows < 3) || ($rows > 100)){
                $rows = 12;
			}
            $rows = "rows='$rows'";

            if ( user_can_richedit() ){
                add_filter('the_editor_content', 'wp_richedit_pre');
			}
			//	$the_editor_content = apply_filters('the_editor_content', $content);
            ?>
			<h2><?php _e('Send Reply', 'messaging') ?></h2>
			<form name="reply_to_message" method="POST" action="admin.php?page=messaging&action=reply_process">
            <input type="hidden" name="message_to" value="<?php echo $tmp_username; ?>" class="messaging-suggest-user ui-autocomplete-input" autocomplete="off" />
            <input type="hidden" name="message_subject" value="<?php echo $tmp_message_subject; ?>" />
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('To', 'messaging') ?></th>
                <td><input disabled="disabled" type="text" name="message_to" id="message_to_disabled" style="width: 95%" maxlength="200" value="<?php echo $tmp_username; ?>" />
                <br />
                <?php //_e('Required - seperate multiple usernames by commas Ex: demouser1,demouser2') ?></td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php _e('Subject', 'messaging') ?></th>
                <td><input disabled="disabled" type="text" name="message_subject" id="message_subject_disabled" style="width: 95%" maxlength="200" value="<?php echo $tmp_message_subject; ?>" />
                <br />
                <?php //_e('Required') ?></td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php echo $tmp_username . __(' wrote', 'messaging'); ?></th>
                <td><?php echo $tmp_message_content; ?>
                <br />
                <?php _e('Required', 'messaging') ?></td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php _e('Content', 'messaging') ?></th>
                <td>
			<?php if (version_compare($wp_version, "3.3") >= 0 && user_can_richedit()) { ?>
				<?php wp_editor('', 'message_content'); ?>
			<?php } else { ?>
				<textarea <?php if ( user_can_richedit() ){ echo "class='mceEditor'"; } ?> <?php echo $rows; ?> style="width: 95%" name='message_content' tabindex='1' id='message_content'></textarea>
			<?php } ?>

		<br />
                <?php _e('Required', 'messaging') ?></td>
                </tr>
                </table>
            <p class="submit">
            <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'messaging') ?>" />
            </p>
            </form>                <?php
			} else {
			?>
			<p><?php _e('You do not have permission to view this message', 'messaging') ?></p>
			<?php
			}
		break;
		//---------------------------------------------------//
		case "reply_process":
			if ($_POST['message_to'] == '' || $_POST['message_subject'] == '' || $_POST['message_content'] == ''){
				$rows = get_option('default_post_edit_rows');
				if (($rows < 3) || ($rows > 100)){
					$rows = 12;
				}
				$rows = "rows='$rows'";

				if ( user_can_richedit() ){
					add_filter('the_editor_content', 'wp_richedit_pre');
				}
				//	$the_editor_content = apply_filters('the_editor_content', $content);
				?>
				<h2><?php _e('Send Reply', 'messaging') ?></h2>
                <p><?php _e('Please fill in all required fields', 'messaging') ?></p>
				<form name="reply_to_message" method="POST" action="admin.php?page=messaging&action=reply_process">
                <input type="hidden" name="message_to" value="<?php echo sanitize_text_field($_POST['message_to']); ?>" />
                <input type="hidden" name="message_subject" value="<?php echo stripslashes(sanitize_text_field($_POST['message_subject'])); ?>" />
					<table class="form-table">
					<tr valign="top">
					<th scope="row"><?php _e('To', 'messaging') ?></th>
					<td><input disabled="disabled" type="text" name="message_to" id="message_to_disabled"
						class="messaging-suggest-user ui-autocomplete-input" autocomplete="off"
						style="width: 95%" maxlength="200" 
						value="<?php echo sanitize_text_field($_POST['message_to']); ?>" />
					<br />
					<?php //_e('Required - seperate multiple usernames by commas Ex: demouser1,demouser2') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Subject', 'messaging') ?></th>
					<td><input disabled="disabled" type="text" name="message_subject" id="message_subject_disabled" style="width: 95%" maxlength="200" value="<?php echo stripslashes(sanitize_text_field($_POST['message_subject'])); ?>" />
					<br />
					<?php //_e('Required') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Content', 'messaging') ?></th>
					<td><textarea <?php if ( user_can_richedit() ){ echo "class='mceEditor'"; } ?> <?php echo $rows; ?> style="width: 95%" name='message_content' tabindex='1' id='message_content'><?php echo wp_kses_post($_POST['message_content']); ?></textarea>
					<br />
					<?php _e('Required', 'messaging') ?></td>
					</tr>
					</table>
                <p class="submit">
                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'messaging') ?>" />
                </p>
				</form>
		<?php
					if ( user_can_richedit() ){
						wp_print_scripts( array( 'wpdialogs-popup' ) );
						wp_print_styles('wp-jquery-ui-dialog');

						require_once ABSPATH . 'wp-admin/includes/template.php';
						require_once ABSPATH . 'wp-admin/includes/internal-linking.php';
						?><div style="display:none;"><?php wp_link_dialog(); ?></div><?php
						wp_print_scripts('wplink');
						wp_print_styles('wplink');
					}
			} else {
				//==========================================================//
				$tmp_usernames = sanitize_text_field($_POST['message_to']);
				//$tmp_usernames = str_replace( ",", ', ', $tmp_usernames );
				//$tmp_usernames = ',,' . $tmp_usernames . ',,';
				//$tmp_usernames = str_replace( " ", '', $tmp_usernames );
				$tmp_usernames_array = explode(",", $tmp_usernames);
				$tmp_usernames_array = array_unique($tmp_usernames_array);

				$tmp_username_error = 0;
				$tmp_error_usernames = '';
				$tmp_to_all_uids = '|';

				foreach ($tmp_usernames_array as $tmp_username){
					$tmp_username = trim($tmp_username);
					if ($tmp_username != ''){
						$tmp_username_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->users . " WHERE user_login = %s", $tmp_username));
						if ($tmp_username_count > 0){
							$tmp_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->users . " WHERE user_login = %s", $tmp_username));
							$tmp_to_all_uids = $tmp_to_all_uids . $tmp_user_id . '|';
							//found
						} else {
							$tmp_username_error = $tmp_username_error + 1;
							$tmp_error_usernames = $tmp_error_usernames . $tmp_username . ', ';
						}
					}
				}
				$tmp_error_usernames = trim($tmp_error_usernames, ", ");
				//==========================================================//
				if ($tmp_username_error > 0){
					$rows = get_option('default_post_edit_rows');
					if (($rows < 3) || ($rows > 100)){
						$rows = 12;
					}
					$rows = "rows='$rows'";
					?>
					<h2><?php _e('Send Reply', 'messaging') ?></h2>
					<p><?php _e('The following usernames could not be found in the system', 'messaging') ?> <em><?php echo $tmp_error_usernames; ?></em></p>
                    <form name="new_message" method="POST" action="admin.php?page=messaging&action=reply_process">
                    <input type="hidden" name="message_to" value="<?php echo sanitize_text_field($_POST['message_to']); ?>" />
                    <input type="hidden" name="message_subject" value="<?php echo stripslashes(sanitize_text_field($_POST['message_subject'])); ?>" />
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('To', 'messaging') ?></th>
                        <td><input disabled="disabled" type="text" name="message_to" id="message_to_disabled"
                        	class="messaging-suggest-user ui-autocomplete-input" autocomplete="off"
                        	style="width: 95%" tabindex='1' maxlength="200"
                        	value="<?php echo sanitize_text_field($_POST['message_to']); ?>" />
                        <br />
                        <?php //_e('Required - seperate multiple usernames by commas Ex: demouser1,demouser2') ?></td>
                        </tr>
                        <tr valign="top">
                        <th scope="row"><?php _e('Subject', 'messaging') ?></th>
                        <td><input disabled="disabled" type="text" name="message_subject" id="message_subject_disabled" style="width: 95%" tabindex='2' maxlength="200" value="<?php echo stripslashes(sanitize_text_field($_POST['message_subject'])); ?>" />
                        <br />
                        <?php //_e('Required') ?></td>
                        </tr>
                        <tr valign="top">
                        <th scope="row"><?php _e('Content', 'messaging') ?></th>
                        <td><textarea <?php if ( user_can_richedit() ){ echo "class='mceEditor'"; } ?> <?php echo $rows; ?> style="width: 95%" name='message_content' tabindex='3' id='message_content'><?php echo wp_kses_post($_POST['message_content']); ?></textarea>
			<br />
                        <?php _e('Required', 'messaging') ?></td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'messaging') ?>" />
                    </p>
                    </form>
		    <?php
					if ( user_can_richedit() ){
						wp_print_scripts( array( 'wpdialogs-popup' ) );
						wp_print_styles('wp-jquery-ui-dialog');

						require_once ABSPATH . 'wp-admin/includes/template.php';
						require_once ABSPATH . 'wp-admin/includes/internal-linking.php';
						?><div style="display:none;"><?php wp_link_dialog(); ?></div><?php
						wp_print_scripts('wplink');
						wp_print_styles('wplink');
					}
				} else {
					//everything checked out - send the messages
					?>
					<p><?php _e('Sending message(s)...', 'messaging') ?></p>
                    <?php
					foreach ($tmp_usernames_array as $tmp_username){
						if ($tmp_username != ''){
							$tmp_to_uid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->users . " WHERE user_login = %s", $tmp_username));
							messaging_insert_message($tmp_to_uid,$tmp_to_all_uids,$user_ID, stripslashes(sanitize_text_field($_POST['message_subject'])), wp_kses_post($_POST['message_content']), 'unread', 0);
							messaging_new_message_notification($tmp_to_uid,$user_ID, stripslashes(sanitize_text_field($_POST['message_subject'])), wp_kses_post($_POST['message_content']));
						}
					}
					messaging_insert_sent_message($tmp_to_all_uids,$user_ID, sanitize_text_field($_POST['message_subject']),wp_kses_post($_POST['message_content']),0);
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='admin.php?page=messaging&updated=true&updatedmsg=" . urlencode('Reply Sent.') . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "test":
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function messaging_new_page_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $messaging_max_inbox_messages, $messaging_max_reached_message, $wp_version;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field($_GET['updatedmsg'])) ?></p></div><?php
	}
	$action = isset($_GET[ 'action' ]) ? sanitize_text_field($_GET[ 'action' ]) : '';
	echo '<div class="wrap">';
	switch( $action ) {
		//---------------------------------------------------//
		default:
			$tmp_total_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d", $user_ID));
			if ($tmp_total_message_count >= $messaging_max_inbox_messages){
				?>
				<p><strong><center><?php _e($messaging_max_reached_message, 'messaging') ?></center></strong></p>
				<?php
			} else {
				$rows = get_option('default_post_edit_rows');
				if (($rows < 3) || ($rows > 100)){
					$rows = 12;
				}
				$rows = "rows='$rows'";

				if ( user_can_richedit() ){
					add_filter('the_editor_content', 'wp_richedit_pre');
				}
				?>
				<h2><?php _e('New Message', 'messaging') ?></h2>
				<form name="new_message" method="POST" action="admin.php?page=messaging_new&action=process">
					<table class="form-table">
					<tr valign="top">
					<th scope="row"><?php _e('To (usernames)', 'messaging') ?></th>
                    <?php
					$message_to = isset($_POST['message_to']) ? sanitize_text_field($_POST['message_to']) : '';
					if ( empty( $message_to ) ) {
						$message_to = isset($_GET['message_to']) ? sanitize_text_field($_GET['message_to']) : '';
					}
					?>
					<td><input type="text" name="message_to" id="message_to"
						class="messaging-suggest-user ui-autocomplete-input" autocomplete="off"
						style="width: 95%" tabindex='1' maxlength="200"
						value="<?php echo $message_to; ?>" />
					<br />
					<?php _e('Required - seperate multiple usernames by commas Ex: demouser1,demouser2', 'messaging') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Subject', 'messaging') ?></th>
					<td><input type="text" name="message_subject" id="message_subject" style="width: 95%" tabindex='2' maxlength="200" value="<?php echo isset($_POST['message_subject']) ? stripslashes(sanitize_text_field($_POST['message_subject'])) : ''; ?>" />
					<br />
					<?php _e('Required', 'messaging') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Content', 'messaging') ?></th>
					<td>
						<?php if (version_compare($wp_version, "3.3") >= 0 && user_can_richedit()) { ?>
							<?php wp_editor(isset($_POST['message_content'])?wp_kses_post($_POST['message_content']):'', 'message_content'); ?>
						<?php } else { ?>
							<textarea <?php if ( user_can_richedit() ){ echo "class='mceEditor'"; } ?> <?php echo $rows; ?> style="width: 95%" name='message_content' tabindex='1' id='message_content'><?php echo isset($_POST['message_content'])?wp_kses_post($_POST['message_content']):''; ?></textarea>
						<?php } ?>
						<br />
					<?php _e('Required', 'messaging') ?></td>
					</tr>
					</table>
                <p class="submit">
                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'messaging') ?>" />
                </p>
				</form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "process":
			if ($_POST['message_to'] == '' || $_POST['message_subject'] == '' || $_POST['message_content'] == ''){
				$rows = get_option('default_post_edit_rows');
				if (($rows < 3) || ($rows > 100)){
					$rows = 12;
				}
				$rows = "rows='$rows'";
				if ( user_can_richedit() ){
					add_filter('the_editor_content', 'wp_richedit_pre');
				}
				?>
				<h2><?php _e('New Message', 'messaging') ?></h2>
                <p><?php _e('Please fill in all required fields', 'messaging') ?></p>
				<form name="new_message" method="POST" action="admin.php?page=messaging_new&action=process">
					<table class="form-table">
					<tr valign="top">
					<th scope="row"><?php _e('To (usernames)', 'messaging') ?></th>
					<td><input type="text" name="message_to" id="message_to"
						class="messaging-suggest-user ui-autocomplete-input" autocomplete="off"
						style="width: 95%" tabindex='1' maxlength="200"
						value="<?php echo sanitize_text_field($_POST['message_to']); ?>" />
					<br />
					<?php _e('Required - seperate multiple usernames by commas Ex: demouser1,demouser2', 'messaging') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Subject', 'messaging') ?></th>
					<td><input type="text" name="message_subject" id="message_subject" style="width: 95%" tabindex='2' maxlength="200" value="<?php echo stripslashes(sanitize_text_field($_POST['message_subject'])); ?>" />
					<br />
					<?php _e('Required', 'messaging') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Content', 'messaging') ?></th>
					<td>
						<?php if (version_compare($wp_version, "3.3") >= 0 && user_can_richedit()) { ?>
							<?php wp_editor(isset($_POST['message_content'])?wp_kses_post($_POST['message_content']):'', 'message_content'); ?>
						<?php } else { ?>
							<textarea <?php if ( user_can_richedit() ){ echo "class='mceEditor'"; } ?> <?php echo $rows; ?> style="width: 95%" name='message_content' tabindex='3' id='message_content'><?php echo wp_kses_post($_POST['message_content']); ?></textarea>
						<?php } ?>
					<br />
					<?php _e('Required', 'messaging') ?></td>
					</tr>
					</table>
                <p class="submit">
                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'messaging') ?>" />
                </p>
                </form>
			<?php
			} else {
				//==========================================================//
				$tmp_usernames = isset($_POST['message_to']) ? sanitize_text_field($_POST['message_to']) : '';
				// $tmp_usernames = str_replace( ",", ', ', $tmp_usernames );
				// $tmp_usernames = ',,' . $tmp_usernames . ',,';
				// $tmp_usernames = str_replace( " ", '', $tmp_usernames );
				$tmp_usernames_array = explode(",", $tmp_usernames);
				$tmp_usernames_array = array_unique($tmp_usernames_array);

				$tmp_username_error = 0;
				$tmp_error_usernames = '';
				$tmp_to_all_uids = '|';
				foreach ($tmp_usernames_array as $tmp_username){
					$tmp_username = trim($tmp_username);
					if ($tmp_username != ''){
						$tmp_username_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->users . " WHERE user_login = %s", $tmp_username));
						if ($tmp_username_count > 0){
							$tmp_uid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->users . " WHERE user_login = %s", $tmp_username));
							$tmp_to_all_uids = $tmp_to_all_uids . $tmp_uid . '|';
							//found
						} else {
							$tmp_username_error = $tmp_username_error + 1;
							$tmp_error_usernames = $tmp_error_usernames . $tmp_username . ', ';
						}
					}
				}
				$tmp_error_usernames = trim($tmp_error_usernames, ", ");
				//==========================================================//
				if ( user_can_richedit() ){
					add_filter('the_editor_content', 'wp_richedit_pre');
				}
				if ($tmp_username_error > 0){
					$rows = get_option('default_post_edit_rows');
					if (($rows < 3) || ($rows > 100)){
						$rows = 12;
					}
					$rows = "rows='$rows'";
					?>
					<h2><?php _e('New Message', 'messaging') ?></h2>
					<p><?php _e('The following usernames could not be found in the system', 'messaging') ?> <em><?php echo $tmp_error_usernames; ?></em></p>
					<form name="new_message" method="POST" action="admin.php?page=messaging_new&action=process">
						<table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php _e('To (usernames)', 'messaging') ?></th>
                                <td><input type="text" name="message_to" id="message_to"
                                	class="messaging-suggest-user ui-autocomplete-input" autocomplete="off"
                                	style="width: 95%" tabindex='1' maxlength="200"
                                	value="<?php echo sanitize_text_field($_POST['message_to']); ?>" />
                                <br />
                                <?php _e('Required - seperate multiple usernames by commas Ex: demouser1,demouser2', 'messaging') ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Subject', 'messaging') ?></th>
                                <td><input type="text" name="message_subject" id="message_subject" style="width: 95%" tabindex='2' maxlength="200" value="<?php echo stripslashes(sanitize_text_field($_POST['message_subject'])); ?>" />
                                <br />
                                <?php _e('Required', 'messaging') ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Content', 'messaging') ?></th>
                                <td>
					<?php if (version_compare($wp_version, "3.3") >= 0 && user_can_richedit()) { ?>
						<?php wp_editor(isset($_POST['message_content'])?wp_kses_post($_POST['message_content']):'', 'message_content'); ?>
					<?php } else { ?>
						<textarea <?php if ( user_can_richedit() ){ echo "class='mceEditor'"; } ?> <?php echo $rows; ?> style="width: 95%" name='message_content' tabindex='3' id='message_content'><?php echo wp_kses_post($_POST['message_content']); ?></textarea>
					<?php } ?>
				<br />
                                <?php _e('Required', 'messaging') ?></td>
                            </tr>
						</table>
                    <p class="submit">
                    <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'messaging') ?>" />
                    </p>
                    </form>
			<?php
				} else {
					//everything checked out - send the messages
					?>
					<p><?php _e('Sending message(s)...', 'messaging') ?></p>
                    <?php
					foreach ($tmp_usernames_array as $tmp_username){
						if ($tmp_username != ''){
							$tmp_to_uid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->users . " WHERE user_login = %s", $tmp_username));
							messaging_insert_message($tmp_to_uid,$tmp_to_all_uids,$user_ID, stripslashes(sanitize_text_field($_POST['message_subject'])),wp_kses_post($_POST['message_content']),'unread',0);
							messaging_new_message_notification($tmp_to_uid,$user_ID,stripslashes(sanitize_text_field($_POST['message_subject'])),wp_kses_post($_POST['message_content']));
						}
					}
					messaging_insert_sent_message($tmp_to_all_uids,$user_ID,stripslashes(sanitize_text_field($_POST['message_subject'])),wp_kses_post($_POST['message_content']),0);
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='admin.php?page=messaging&updated=true&updatedmsg=" . urlencode(__('Message(s) Sent.', 'messaging')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function messaging_sent_page_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $messaging_official_message_bg_color, $messaging_max_inbox_messages, $messaging_max_reached_message;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field($_GET['updatedmsg'])) ?></p></div><?php
	}
	$action = isset($_GET[ 'action' ]) ? sanitize_text_field($_GET[ 'action' ]) : '';

	echo '<div class="wrap">';
	switch( $action ) {
		//---------------------------------------------------//
		default:
		$tmp_sent_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_from_user_ID = %d", $user_ID));
			?>
            <h2><?php _e('Sent Messages', 'messaging') ?></h2>
            <?php
			if ($tmp_sent_message_count == 0){
			?>
            <p><?php _e('No messages to display', 'messaging') ?></p>
            <?php
			} else {
			$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_from_user_ID = %d ORDER BY sent_message_ID DESC LIMIT 50", $user_ID);
			$tmp_sent_messages = $wpdb->get_results( $query, ARRAY_A );
			echo "
			<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
			<thead><tr>
			<th scope='col'>" . __("To", 'messaging') . "</th>
			<th scope='col'>" . __("Subject", 'messaging') . "</th>
			<th scope='col'>" . __("Sent", 'messaging') . "</th>
			<th scope='col'>" . __("Actions", 'messaging') . "</th>
			</tr></thead>
			<tbody id='the-list'>
			";
			$class = '';
			if (count($tmp_sent_messages) > 0){
				$class = ('alternate' == $class) ? '' : 'alternate';
				foreach ($tmp_sent_messages as $tmp_sent_message){
				if (isset($tmp_sent_message['message_official']) && $tmp_sent_message['message_official'] == 1){
					$style = "'style=background-color:" . $messaging_official_message_bg_color . ";'";
				} else {
					$style = "";
				}
				//=========================================================//
				$tmp_user_ids = $tmp_sent_message['sent_message_to_user_IDs'];
				$tmp_user_ids_array = explode("|", $tmp_user_ids);

				$tmp_usernames = '';
				foreach ($tmp_user_ids_array as $tmp_user_id) {
					$tmp_user_id = intval($tmp_user_id);
					if (!$tmp_user_id) continue;

					$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_user_id));
					$tmp_display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM " . $wpdb->users . " WHERE ID = %d", $tmp_user_id));
					if ( $tmp_display_name != '' ) {
						$tmp_username = $tmp_display_name;
					}
					$tmp_user_url = messaging_user_primary_blog_url($tmp_user_id);
					//echo "tmp_user_url=[". $tmp_user_url ."]<br />";

					if (!empty($tmp_usernames)) $tmp_usernames .= ", ";
					if ($tmp_user_url == ''){
						$tmp_usernames .= $tmp_username;
					} else {
						$tmp_usernames .= "<a href='" . $tmp_user_url . "'>" . $tmp_username . "</a>";
					}
				}
				$tmp_usernames = trim($tmp_usernames, ", ");
				//=========================================================//
				echo "<tr class='" . $class . "' " . $style . ">";
				if (isset($tmp_sent_message['message_official']) && $tmp_sent_message['message_official'] == 1){
					echo "<td valign='top'><strong>" . $tmp_usernames . "</strong></td>";
					echo "<td valign='top'><strong>" . stripslashes($tmp_sent_message['sent_message_subject']) . "</strong></td>";
					echo "<td valign='top'><strong>" . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_sent_message['sent_message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "</strong></td>";
				} else {
					echo "<td valign='top'>" . $tmp_usernames . "</td>";
					echo "<td valign='top'>" . stripslashes($tmp_sent_message['sent_message_subject']) . "</td>";
					echo "<td valign='top'>" . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_sent_message['sent_message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "</td>";
				}
				echo "<td valign='top'><a href='admin.php?page=messaging_sent&action=view&mid=" . $tmp_sent_message['sent_message_ID'] . "' rel='permalink' class='edit'>" . __('View', 'messaging') . "</a></td>";
				echo "</tr>";
				$class = ('alternate' == $class) ? '' : 'alternate';
				//=========================================================//
				}
			}
			?>
			</tbody></table>
            <?php
			}
		break;
		//---------------------------------------------------//
		case "view":
			$tmp_message_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_ID = %d AND sent_message_from_user_ID = %d", $_GET['mid'], $user_ID));
			if ($tmp_message_count > 0){
			$tmp_message_subject = stripslashes($wpdb->get_var($wpdb->prepare("SELECT sent_message_subject FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_ID = %d", $_GET['mid'])));
			$tmp_message_content = stripslashes($wpdb->get_var($wpdb->prepare("SELECT sent_message_content FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_ID = %d", $_GET['mid'])));
			$tmp_message_to_user_IDs = $wpdb->get_var($wpdb->prepare("SELECT sent_message_to_user_IDs FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_ID = %s", $_GET['mid']));
			$tmp_message_stamp = $wpdb->get_var($wpdb->prepare("SELECT sent_message_stamp FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_ID = %d", $_GET['mid']));
			//=========================================================//
			$tmp_user_ids = $tmp_message_to_user_IDs;
			$tmp_user_ids_array = explode("|", $tmp_user_ids);
			$tmp_usernames = '';
			foreach ($tmp_user_ids_array as $tmp_user_id){
				$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_user_id));
				$tmp_user_url = messaging_user_primary_blog_url($tmp_user_id);
				if ($tmp_user_url == ''){
					$tmp_usernames = $tmp_usernames . $tmp_username . ", ";
				} else {
					$tmp_usernames = $tmp_usernames . "<a href='" . $tmp_user_url . "'>" . $tmp_username . "</a>, ";
				}
			}
			$tmp_usernames = trim($tmp_usernames, ", ");
			//$tmp_usernames = str_replace(", ","<br />",$tmp_usernames);
			//=========================================================//
			?>

            <h2><?php _e('View Message: ', 'messaging') ?><?php echo intval($_GET['mid']); ?></h2>
			<form name="new_message" method="POST" action="admin.php?page=messaging_sent">
            <h3><?php _e('To', 'messaging') ?></h3>
            <p><?php echo $tmp_usernames; ?></p>
            <h3><?php _e('Subject', 'messaging') ?></h3>
            <p><?php echo $tmp_message_subject; ?></p>
            <h3><?php _e('Date', 'messaging') ?></h3>
            <p><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_message_stamp + (get_option( 'gmt_offset' ) * 3600)); ?></p>
            <h3><?php _e('Content', 'messaging') ?></h3>
            <p><?php echo wpautop($tmp_message_content); ?></p>
            <p class="submit">
            <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Back', 'messaging') ?>" />
            </p>
            </form>
            <?php
			} else {
			?>
            <p><?php _e('You do not have permission to view this message', 'messaging') ?></p>
            <?php
			}
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function messaging_export_page_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field($_GET['updatedmsg'])) ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( sanitize_text_field($_GET[ 'action' ]) ) {
		//---------------------------------------------------//
		default:
			?>
			<h2><?php _e('Export Messages', 'messaging') ?></h2>
                <form method="post" action="admin.php?page=messaging_export&action=process">
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Generate export data for', 'messaging') ?></th>
                <td>
                <select name="export_type" id="export_type">
                <option value="received" ><?php _e('Received Messages', 'messaging') ?></option>
                <option value="sent" ><?php _e('Sent Messages', 'messaging') ?></option>
                </select>
                </td>
                </tr>
                </table>
                <p class="submit">
                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Next', 'messaging') ?>" />
                <input type="hidden" name="action" value="update" />
                </p>
                </form>
            <?php
		break;
		//---------------------------------------------------//
		case "process":
			$export_data_divider = "==============================================================================\n";
			$export_data = $export_data_divider;
			//============================================//
			if (sanitize_text_field($_POST['export_type']) == 'received'){
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d ORDER BY message_ID DESC", $user_ID);
				$tmp_messages = $wpdb->get_results( $query, ARRAY_A );
				if (count($tmp_messages) > 0){
					foreach ($tmp_messages as $tmp_message){
						$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_message['message_from_user_ID']));
						$export_data = $export_data . __('From', 'messaging'). ': ' . $tmp_username . "\n";
						$export_data = $export_data . __('Received', 'messaging'). ': ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_message['message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "\n";
						$export_data = $export_data . __('Subject', 'messaging'). ': ' . stripslashes($tmp_message['message_subject']) . "\n";
						$export_data = $export_data . __('Content', 'messaging'). ': ' . stripslashes($tmp_message['message_content']) . "\n";
						$export_data = $export_data . $export_data_divider;
					}
				}
			}
			//============================================//
			if (sanitize_text_field($_POST['export_type']) == 'sent'){
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "sent_messages WHERE sent_message_from_user_ID = %d ORDER BY sent_message_ID DESC", $user_ID);
				$tmp_sent_messages = $wpdb->get_results( $query, ARRAY_A );
				if (count($tmp_sent_messages) > 0){
					foreach ($tmp_sent_messages as $tmp_sent_message){
						//=========================================================//
						$tmp_user_ids = $tmp_sent_message['sent_message_to_user_IDs'];
						$tmp_user_ids_array = explode("|", $tmp_user_ids);
						$tmp_usernames = '';
						foreach ($tmp_user_ids_array as $tmp_user_id){
							$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_user_id));
							$tmp_usernames = $tmp_usernames . $tmp_username . ", ";
						}
						$tmp_usernames = trim($tmp_usernames, ", ");
						//=========================================================//
						$tmp_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM " . $wpdb->users . " WHERE ID = %d", $tmp_sent_message['sent_message_from_user_ID']));
						$export_data = $export_data . __('To', 'messaging'). ': ' . $tmp_usernames . "\n";
						$export_data = $export_data . __('Sent', 'messaging'). ': ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'),$tmp_sent_message['sent_message_stamp'] + (get_option( 'gmt_offset' ) * 3600)) . "\n";
						$export_data = $export_data . __('Subject', 'messaging'). ': ' . stripslashes($tmp_sent_message['sent_message_subject']) . "\n";
						$export_data = $export_data . __('Content', 'messaging'). ': ' . stripslashes($tmp_sent_message['sent_message_content']) . "\n";
						$export_data = $export_data . $export_data_divider;
					}
				}
			}
			//============================================//
			if ($export_data == $export_data_divider){
				$export_data = '';
			}
			//============================================//
			if (sanitize_text_field($_POST['export_type']) == 'received'){
				?>
	            <h2><?php _e('Export Data', 'messaging') ?> <?php _e('Received Messages', 'messaging') ?></h2>
                <?php
			} else if (sanitize_text_field($_POST['export_type']) == 'sent'){
				?>
	            <h2><?php _e('Export Data', 'messaging') ?> <?php _e('Sent Messages', 'messaging') ?></h2>
                <?php
			} else {
				?>
	            <h2><?php _e('Export Data', 'messaging') ?></h2>
                <?php
			}
			?>
			<form name="back" method="POST" action="admin.php?page=messaging_export">
            <p style="padding-left:0px;padding-right:10px;"><textarea style="width:100%" rows="35"><?php echo $export_data; ?></textarea></p>
            <p class="submit">
            <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Back', 'messaging') ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function messaging_notifications_page_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field($_GET['updatedmsg'])) ?></p></div><?php
	}
	$action = isset($_GET[ 'action' ]) ? sanitize_text_field($_GET[ 'action' ]) : '';
	echo '<div class="wrap">';
	switch( $action ) {
		//---------------------------------------------------//
		default:
			$tmp_message_email_notification = get_user_meta($user_ID, 'message_email_notification', true);

			?>
			<h2><?php _e('Notification Settings', 'messaging') ?></h2>
                <form method="post" action="admin.php?page=messaging_message-notifications&action=process">
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Receive an email notifying you of new messages', 'messaging') ?></th>
                <td>
                <select name="message_email_notification" id="message_email_notification">
                <option value="yes" <?php if ($tmp_message_email_notification == 'yes'){ echo 'selected="selected"'; } ?> ><?php _e('Yes', 'messaging') ?></option>
                <option value="no" <?php if ($tmp_message_email_notification == 'no'){ echo 'selected="selected"'; } ?> ><?php _e('No', 'messaging') ?></option>
                </select>
                </td>
                </tr>
                </table>
                <p class="submit">
                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'messaging') ?>" />
                </p>
                </form>
            <?php
		break;
		//---------------------------------------------------//
		case "process":
			update_usermeta($user_ID,'message_email_notification', sanitize_text_field($_POST['message_email_notification']));
			echo "
			<SCRIPT LANGUAGE='JavaScript'>
			window.location='admin.php?page=messaging_message-notifications&updated=true&updatedmsg=" . urlencode(__('Settings saved.', 'messaging')) . "';
			</script>
			";
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function messaging_add_user_column($columns) {
	$columns['message'] = __('Contact', 'messaging');

	return $columns;
}

function messaging_manage_users_column($value, $column_name, $user_id) {
	if ( 'message' == $column_name ) {
		$user = get_userdata($user_id);
		return '<a href="'.get_admin_url(null, 'admin.php?page=messaging_new&message_to='.$user->user_login).'">'.__('Message', 'messaging').'</a>';
	}
	return $value;
}

//------------------------------------------------------------------------//
//---Support Functions----------------------------------------------------//
//------------------------------------------------------------------------//

function messaging_user_primary_blog_url($tmp_uid){
	global $wpdb;

	if (is_multisite()) {
		$tmp_blog_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM " . $wpdb->base_prefix . "usermeta WHERE meta_key = 'primary_blog' AND user_id = %d", $tmp_uid));
		if ($tmp_blog_id == ''){
			return;
		}
		$tmp_blog_domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = %d", $tmp_blog_id));
		$tmp_blog_path = $wpdb->get_var($wpdb->prepare("SELECT path FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = %d", $tmp_blog_id));
		return 'http://' . $tmp_blog_domain . $tmp_blog_path;
	} else {
		return get_option('siteurl');
	}
}

global $wpmudev_notices;
$wpmudev_notices[] = array( 'id'=> 68, 'name'=> 'Messaging', 'screens' => array( 'toplevel_page_messaging', 'inbox_page_messaging_new', 'inbox_page_messaging_sent', 'inbox_page_messaging_message-notifications' ) );
include_once(plugin_dir_path( __FILE__ ).'lib/dash-notices/wpmudev-dash-notification.php');

