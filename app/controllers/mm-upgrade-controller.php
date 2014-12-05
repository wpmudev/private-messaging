<?php

/**
 * Author: hoangngo
 */
class MM_Upgrade_Controller extends IG_Request
{
    public function __construct()
    {
        //add_action('admin_notices', array(&$this, 'admin_notice'));
        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('wp_ajax_mm_create_table', array(&$this, 'create_table'));
    }

    function create_table()
    {
        if (!wp_verify_nonce(fRequest::get('_wpnonce'), 'mm_create_table')) {
            exit;
        }
        global $wpdb;
        $type = fRequest::get('type');
        $charset_collate = '';

        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
        if ($type == 'c-table') {

            $sql = "-- ----------------------------;
CREATE TABLE `{$wpdb->base_prefix}mm_conversation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_created` datetime DEFAULT NULL,
  `message_count` tinyint(3) DEFAULT NULL,
  `message_index` varchar(255) DEFAULT NULL,
  `user_index` varchar(255) DEFAULT NULL,
  `send_from` tinyint(3) DEFAULT NULL,
  `site_id` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  UNIQUE KEY id (id)
) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") === $wpdb->base_prefix . 'mm_conversation') {
                fJSON::output(array(
                    'status' => 'success'
                ));
            } else {
                fJSON::output(array(
                    'status' => 'fail',
                    'error' => __("Can not create table {$wpdb->base_prefix}mm_conversation", mmg()->domain)
                ));
            }
        } elseif ($type == 's-table') {
            $sql = "CREATE TABLE `{$wpdb->base_prefix}mm_status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  UNIQUE KEY id (id)
) $charset_collate;
";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            @dbDelta($sql);
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_status'") === $wpdb->base_prefix . 'mm_status') {
                fJSON::output(array(
                    'status' => 'success'
                ));
            } else {
                fJSON::output(array(
                    'status' => 'fail',
                    'error' => __("Can not create table {$wpdb->base_prefix}mm_status", mmg()->domain)
                ));
            }
        }
        exit;
    }

    function cleanup()
    {
        if (!wp_verify_nonce(fRequest::get('_wpnonce'), 'mm_cleanup')) {
            return;
        }

        $messages = MM_Message_Model::all_with_condition(array(
            'nopaging' => true
        ));
        foreach ($messages as $m) {
            $m->delete();
        }

        $convs = MM_Conversation_Model::all_with_condition();
        foreach ($convs as $conv) {
            $conv->delete();
        }
    }

    /**
     * This will backup all the current conversation and messages
     */
    function backup()
    {
        $data = array();
        $convs = MM_Conversation_Model::all_with_condition();
        $messages = MM_Message_Model::all_with_condition(array(
            'nopaging' => true
        ));
        $data['conversations'] = $convs;
        $data['messages'] = $messages;
        update_option('mm_backup_' . time(), $data);
    }

    function import()
    {
        if (!wp_verify_nonce(fRequest::get('_wpnonce'), 'mm_import')) {
            return;
        }

        if (get_option('messaging_version') >= 1.2) {
            return;
        }
        $this->backup();

        $data = $this->_get_import_data();
        foreach ($data as $v) {
            $conv = new MM_Conversation_Model();
            $conv->save();

            foreach ($v as $m) {
                $model = new MM_Message_Model();
                $model->subject = $m['message_subject'];
                $model->content = $m['message_content'];
                $model->conversation_id = $conv->id;
                $model->send_from = $m['message_from_user_ID'];
                $model->send_to = $m['message_to_user_ID'];
                $model->status = $m['message_status'];
                $model->date = date('Y-m-d H:i:s', $m['message_stamp']);
                $model->save();
                $conv->update_index($model->id);
            }

            $conv->update_count();
        }
        update_option('messaging_version', '1.2');
    }

    function admin_menu()
    {
        add_menu_page(__('Messaging', mmg()->domain), __('Messaging', mmg()->domain), 'manage_options', mmg()->prefix . 'main', array(&$this, 'main'), 'dashicons-email-alt');
    }

    function main()
    {
        wp_enqueue_style('mm_style');
        global $wpdb;
        $c_status = false;
        $s_status = false;
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") === $wpdb->base_prefix . 'mm_conversation') {
            $c_status = true;
        }
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_status'") === $wpdb->base_prefix . 'mm_status') {
            $s_status = true;
        }

        $this->render('backend/upgrade', array(
            'c_status' => $c_status,
            's_status' => $s_status
        ));
    }

    function _get_import_data()
    {
        global $wpdb;
        $messages = $wpdb->get_results('SELECT * FROM ' . $wpdb->base_prefix . 'messages', ARRAY_A);
        //guess root message
        $roots = array();
        foreach ($messages as $message) {
            if (!stristr($message['message_subject'], 'Re:')) {
                $roots[] = $message;
            }
        }
        $data = array();
        foreach ($roots as $root) {
            if (!isset($data[$root['message_ID']])) {
                $data[$root['message_ID']] = array();
            }
            $check = 'Re: ' . $root['message_subject'];
            $sql = 'SELECT * FROM ' . $wpdb->base_prefix . 'messages WHERE message_subject LIKE %s ORDER BY message_stamp';
            $ms = $wpdb->get_results($wpdb->prepare($sql, '%' . $check), ARRAY_A);
            //check does the message come from root
            if (!empty($ms)) {
                $right = array();
                $d = array_merge(array($root), $ms);
                //gett he right message
                //we will loop throught message, and get all the messages have send to/send from id
                $check = array($root['message_from_user_ID'], $root['message_to_user_ID']);
                sort($check);
                $right = array();
                foreach ($d as $key => $r) {
                    $compare = array($r['message_from_user_ID'], $r['message_to_user_ID']);
                    sort($compare);

                    $is_right = array_diff($check, $compare);
                    if (empty($is_right)) {
                        $right[] = $r;
                    }
                }
                $data[$root['message_ID']] = $right;
            } else {
                $data[$root['message_ID']] = $root;
            }
        }
        return $data;
    }

    function admin_notice()
    {
        ?>
        <div class="updated">
            <p><?php _e(sprintf("There's some issue with your database, Private Messaging can not create necessary tables, please try it <a href=\"%s\">here</a>", admin_url('admin.php?page=mm_main')), mmg()->domain); ?></p>
        </div>
    <?php

    }
}