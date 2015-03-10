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
        add_action('wp_loaded', array(&$this, 'check'));
    }

    /**
     * The plugin activate should create necessary tables. This is the case of PM is add-on of another plugins
     *
     */

    function check()
    {
        //check does main table created
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") == $wpdb->base_prefix . 'mm_conversation') {
            //check around for update column name
            $this->fix_column_name();
        } else {
            //create new table
            $this->create_c_table();
        }
        //status table
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_status'") !== $wpdb->base_prefix . 'mm_status') {
            $this->create_s_table();
        }
        //if everything ok, we will store in option
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") !== $wpdb->base_prefix . 'mm_conversation'
            || $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_status'") !== $wpdb->base_prefix . 'mm_status'
        ) {
            return false;
        }
        update_option('mm_db_version', mmg()->db_version);
        //redirect
        //wp_redirect(admin_url('admin.php?page=mm_main'));
    }

    //fix around for bad naming
    function fix_column_name()
    {
        global $wpdb;
        //upgrade script
        //check does column status exist
        $sql = "SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '{$wpdb->prefix}mm_conversation'
AND table_schema = '" . DB_NAME . "'
AND column_name = 'status'";
        $exist = $wpdb->get_var($sql);
        if (is_null($exist)) {
            $sql = "ALTER TABLE {$wpdb->prefix}mm_conversation ADD COLUMN `status` INT(11) DEFAULT 1;";
            $wpdb->query($sql);
        }
        //rename column
        $sql = "SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '{$wpdb->prefix}mm_conversation'
AND table_schema = '" . DB_NAME . "'
AND column_name = 'date'";
        $exist = $wpdb->get_var($sql);
        if (!is_null($exist)) {
            //change date name
            $sql = "ALTER TABLE {$wpdb->prefix}mm_conversation CHANGE `date` `date_created` DATETIME";
            $wpdb->query($sql);
        }

        //rename column
        $sql = "SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '{$wpdb->prefix}mm_conversation'
AND table_schema = '" . DB_NAME . "'
AND column_name = 'count'";
        $exist = $wpdb->get_var($sql);
        if (!is_null($exist)) {
            //change date name
            $sql = "ALTER TABLE {$wpdb->prefix}mm_conversation CHANGE `count` `message_count` TINYINT;";
            $wpdb->query($sql);
        }

        //rename column
        $sql = "SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '{$wpdb->prefix}mm_conversation'
AND table_schema = '" . DB_NAME . "'
AND column_name = 'index'";
        $exist = $wpdb->get_var($sql);
        if (!is_null($exist)) {
            //change date name
            $sql = "ALTER TABLE {$wpdb->prefix}mm_conversation CHANGE `index` `message_index` VARCHAR(255);";
            $wpdb->query($sql);
        }

        //rename column
        $sql = "SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '{$wpdb->prefix}mm_conversation'
AND table_schema = '" . DB_NAME . "'
AND column_name = 'from'";
        $exist = $wpdb->get_var($sql);
        if (!is_null($exist)) {
            //change date name
            $sql = "ALTER TABLE {$wpdb->prefix}mm_conversation CHANGE `from` `send_from` TINYINT;";
            $wpdb->query($sql);
        }
    }

    function create_c_table()
    {
        global $wpdb;
        $charset_collate = '';

        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
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
    }

    function create_s_table()
    {
        global $wpdb;
        $charset_collate = '';

        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
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
    }

    function create_table()
    {
        if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'mm_create_table')) {
            exit;
        }

        $type = mmg()->post('type');
        global $wpdb;
        if ($type == 'c-table') {
            $this->create_c_table();
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") === $wpdb->base_prefix . 'mm_conversation') {
                wp_send_json(array(
                    'status' => 'success'
                ));
            } else {
                wp_send_json(array(
                    'status' => 'fail',
                    'error' => __("Can not create table {$wpdb->base_prefix}mm_conversation", mmg()->domain)
                ));
            }
        } elseif ($type == 's-table') {
            $this->create_s_table();
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_status'") === $wpdb->base_prefix . 'mm_status') {
                wp_send_json(array(
                    'status' => 'success'
                ));
            } else {
                wp_send_json(array(
                    'status' => 'fail',
                    'error' => __("Can not create table {$wpdb->base_prefix}mm_status", mmg()->domain)
                ));
            }
        }
        exit;
    }

    function admin_menu()
    {
        add_menu_page(__('Messaging', mmg()->domain), __('Messaging', mmg()->domain), 'manage_options', mmg()->prefix . 'main', array(&$this, 'main'), 'dashicons-email-alt');
    }

    function main()
    {

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

    static function ready_to_use()
    {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") !== $wpdb->base_prefix . 'mm_conversation'
            || $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_status'") !== $wpdb->base_prefix . 'mm_status'
        ) {
            return false;
        }

        return true;
    }
}