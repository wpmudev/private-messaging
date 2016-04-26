<?php
/*
Plugin Name: Private Messaging
Plugin URI: https://premium.wpmudev.org/project/private-messaging
Description: Private user-to-user communication for placing bids, sharing project specs and hidden internal communication. Complete with front end integration, guarded contact information and protected file sharing.
Author: WPMU DEV
Version: 1.0.1.3
Author URI: http://premium.wpmudev.org
WDP ID: 938495
Text Domain: private_messaging
*/

/*
Copyright 2007-2014 Incsub (http://incsub.com)
Author – Hoang Ngo (Incsub)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/
if (!class_exists('MMessaging')) {
    require_once(dirname(__FILE__) . '/framework/loader.php');

    class MMessaging
    {
        public $plugin_url;
        public $plugin_path;
        public $domain;
        public $prefix;

        public $version = "1.0.1.3";
        public $db_version = '1.0';

        public $global = array();

        private static $_instance;

        private function __construct()
        {
            //variables init
            $this->plugin_url = plugin_dir_url(__FILE__);
            $this->plugin_path = plugin_dir_path(__FILE__);
            $this->domain = 'private_messaging';
            $this->prefix = 'mm_';
            //load the framework

            //autoload
            spl_autoload_register(array(&$this, 'autoload'));

            //enqueue scripts, use it here so both frontend and backend can use
            add_action('wp_enqueue_scripts', array(&$this, 'scripts'), 20);
            add_action('admin_enqueue_scripts', array(&$this, 'scripts'), 20);

            if ($this->ready_to_use()) {
                add_action('init', array(&$this, 'dispatch'));
            } else {
                new MM_Upgrade_Controller();
            }
        }

        //Add maintain page
        function ready_to_use()
        {
            if (get_option('mm_db_version') == $this->db_version) {
                return true;
            } else {
                return false;
            }

        }

        function load_script($scenario = '')
        {
            $runtime_path = $this->can_compress();
            switch ($scenario) {
                case 'inbox':
                    wp_enqueue_script('jquery-ui-tooltip');
                    break;
                case 'login':
                    wp_enqueue_style('mm_style', $this->plugin_url . 'assets/main.css', array('ig-packed'), $this->version);
                    //we need the modal for popup login
                    wp_enqueue_script('mm_lean_model', $this->plugin_url . 'assets/jquery.leanModal.min.js', array('jquery'), $this->version);
                    break;
                case 'backend':
                    wp_enqueue_script('jquery-ui-tabs');
                    break;
                default:
                    if (is_user_logged_in()) {
                        if ($runtime_path) {
                            //still need include core for fonts
                            wp_enqueue_style('ig-packed');
                            wp_enqueue_script('jquery');
                            $csses = array('mm_style', 'mm_scroll', 'selectivejs');
                            $jses = array(
                                'mm_scroll', 'selectivejs', 'mm_lean_model', 'jquery-ui-tooltip');
                            if ($this->can_upload() == true) {
                                $csses[] = 'igu-uploader';
                                $jses = array_merge($jses, array('popoverasync', 'jquery-frame-transport'));
                            }
                            if (wp_script_is('mm_sceditor', 'registered') && wp_script_is('mm_sceditor_xhtml', 'registered')) {
                                $jses = array_merge($jses, array('mm_sceditor','mm_sceditor_translate', 'mm_sceditor_xhtml'));
                            }

                            $this->compress_assets($csses, $jses, $runtime_path);
                        } else {
                            //needed everywhere
                            wp_enqueue_style('mm_style');
                            wp_enqueue_script('mm_scroll');
                            wp_enqueue_script('selectivejs');
                            wp_enqueue_style('selectivejs');
                            wp_enqueue_script('mm_lean_model');
                            //wysiwyg
                            if (wp_script_is('mm_sceditor', 'registered') && wp_script_is('mm_sceditor_xhtml', 'registered')) {
                                wp_enqueue_script('mm_sceditor');
                                wp_enqueue_script('mm_sceditor_translate');
                                wp_enqueue_script('mm_sceditor_xhtml');
                            }

                            if ($this->setting()->allow_attachment == 1) {
                                wp_enqueue_style('igu-uploader');
                                wp_enqueue_script('popoverasync');
                                wp_enqueue_script('jquery-frame-transport');
                            }
                        }
                    }
                    break;
            }
        }

        function can_upload()
        {
            if (!is_user_logged_in()) {
                return false;
            }

            if (current_user_can('upload_files'))
                return true;

            $allowed = $this->setting()->allow_attachment;
            if (!is_array($allowed)) {
                $allowed = array();
            }
            $allowed = array_filter($allowed);
            $user = new WP_User(get_current_user_id());
            foreach ($user->roles as $role) {
                if (in_array($role, $allowed)) {
                    return true;
                }
            }
            return false;
        }

        function compress_assets($css = array(), $js = array(), $write_path)
        {
            if (defined('DOING_AJAX') && DOING_AJAX)
                return;

            $css_write_path = $write_path . '/' . implode('-', $css) . '.css';
            $css_cache = get_option('mm_style_last_cache');
            if ($css_cache && file_exists($css_write_path) && strtotime('+1 hour', $css_cache) < time()) {
                //remove cache
                unlink($css_write_path);
            }
            $js_write_path = $write_path . '/' . implode('-', $js) . '.js';
            if (!file_exists($css_write_path)) {
                global $wp_styles;
                $css_paths = array();
                //loop twice, position is important
                foreach ($css as $c) {
                    foreach ($wp_styles->registered as $style) {
                        if ($style->handle == $c) {
                            $css_paths[] = $style->src;
                        }
                    }
                }
                //started
                $css_strings = '';
                foreach ($css_paths as $path) {
                    //path is an url, we need to changeed it to local
                    $path = str_replace($this->plugin_url, $this->plugin_path, $path);
                    $css_strings = $css_strings . PHP_EOL . file_get_contents($path);
                }

                file_put_contents($css_write_path, trim($css_strings));
                update_option('mm_style_last_cache', time());
            }
            $css_write_path = str_replace($this->plugin_path, $this->plugin_url, $css_write_path);
            wp_enqueue_style(implode('-', $css), $css_write_path);

            $js_cache = get_option('mm_script_last_cache');
            if ($js_cache && file_exists($js_write_path) && strtotime('+1 hour', $js_cache) < time()) {
                //remove cache
                unlink($js_write_path);
            }
            if (!file_exists($js_write_path)) {
                global $wp_scripts;
                $js_paths = array();
                //js
                foreach ($js as $j) {
                    foreach ($wp_scripts->registered as $script) {
                        if ($script->handle == $j) {
                            $js_paths[] = $script->src;
                        }
                    }
                }
                $js_strings = '';
                foreach ($js_paths as $path) {
                    //path is an url, we need to changeed it to local
                    $path = str_replace($this->plugin_url, $this->plugin_path, $path);
                    if (file_exists($path)) {
                        $js_strings = $js_strings . PHP_EOL . file_get_contents($path);
                    }
                }

                file_put_contents($js_write_path, trim($js_strings));
                update_option('mm_script_last_cache', time());
            }
            $js_write_path = str_replace($this->plugin_path, $this->plugin_url, $js_write_path);
            wp_enqueue_script(implode('-', $js), $js_write_path);

        }

        function compress_css($path)
        {

        }

        function can_compress()
        {
            return false;

            $runtime_path = $this->plugin_path . 'framework/runtime';
            if (!is_dir($runtime_path)) {
                //try to create
                @mkdir($runtime_path);
            }
            if (!is_dir($runtime_path))
                return false;
            $use_compress = false;
            if (!is_writeable($runtime_path)) {
                chmod($runtime_path, 775);
            }
            if (is_writeable($runtime_path)) {
                $use_compress = $runtime_path;;
            }
            return $use_compress;
        }

        function scripts()
        {
            wp_register_style('mm_style', $this->plugin_url . 'assets/main.min.css', array('ig-packed'), $this->version);
            wp_register_style('mm_style_admin', $this->plugin_url . 'assets/admin.css', array('ig-packed'), $this->version);
            wp_register_style('mm_scroll', $this->plugin_url . 'assets/perfect-scrollbar.min.css', array(), $this->version);
            wp_register_script('mm_scroll', $this->plugin_url . 'assets/perfect-scrollbar.min.js', array('jquery'), $this->version);

            wp_register_script('selectivejs', $this->plugin_url . 'assets/selectivejs/js/standalone/selectize.js', array('jquery'), $this->version);
            wp_register_style('selectivejs', $this->plugin_url . 'assets/selectivejs/css/selectize.bootstrap3.css', array(), $this->version);

            wp_register_script('mm_lean_model', $this->plugin_url . 'assets/jquery.leanModal.min.js', array('jquery'), $this->version);

            $this->load_script();
        }

        function dispatch()
        {
            //load post type
            $this->load_post_type();

            if (is_admin()) {
                $backend = new MM_Backend();
            } else {
                $front = new MM_Frontend();
            }
            //include components we need to use
            include($this->plugin_path . 'app/components/ig-uploader.php');
            //init uploader controller, if user can not upload, we only let it display attachment files
            ig_uploader()->init_uploader($this->can_upload(), $this->domain);

            include $this->plugin_path . 'app/components/mm-addon-table.php';
            //load add on
            $addons = $this->setting()->plugins;
            if (!is_array($addons)) {
                $addons = array();
            }
            foreach ($addons as $addon) {
                if (file_exists($addon) && stristr($addon, $this->plugin_path)) {
                    include_once $addon;
                }
            }
            //loading add on & components
            new MAjax();
            $this->global['inbox_sc'] = new Inbox_Shortcode_Controller();
            $this->global['messge_me_sc'] = new Message_Me_Shortcode_Controller();
            $this->global['admin_bar_notification'] = new Admin_Bar_Notification_Controller();
        }

        function load_post_type()
        {
            $args = array(
                'supports' => array(),
                'hierarchical' => false,
                'public' => false,
                'show_ui' => false,
                'show_in_menu' => false,
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
                'can_export' => true,
                'has_archive' => false,
                'exclude_from_search' => false,
                'publicly_queryable' => false,
                'capability_type' => 'page',
            );
            register_post_type('mm_message', $args);
        }

        function autoload($class)
        {
            $filename = str_replace('_', '-', strtolower($class)) . '.php';
            if (strstr($filename, '-controller.php')) {
                //looking in the controllers folder and sub folders to get this class
                $files = $this->listFolderFiles($this->plugin_path . 'app/controllers');
                foreach ($files as $file) {
                    if (strcmp($filename, pathinfo($file, PATHINFO_BASENAME)) === 0) {
                        include_once $file;
                        break;
                    }
                }
            } elseif (strstr($filename, '-model.php')) {
                $files = $this->listFolderFiles($this->plugin_path . 'app/models');

                foreach ($files as $file) {
                    if (strcmp($filename, pathinfo($file, PATHINFO_BASENAME)) === 0) {
                        include_once $file;
                        break;
                    }
                }
            } elseif (file_exists($this->plugin_path . 'app/' . $filename)) {
                include_once $this->plugin_path . 'app/' . $filename;
            } elseif (file_exists($this->plugin_path . 'app/components/' . $filename)) {
                include_once $this->plugin_path . 'app/components/' . $filename;
            }
        }

        public static function get_instance()
        {
            if (!self::$_instance instanceof MMessaging) {
                self::$_instance = new MMessaging();
            }

            return self::$_instance;
        }

        function listFolderFiles($dir)
        {
            $ffs = scandir($dir);
            $i = 0;
            $list = array();
            foreach ($ffs as $ff) {
                if ($ff != '.' && $ff != '..') {
                    if (strlen($ff) >= 5) {
                        if (substr($ff, -4) == '.php') {
                            $list[] = $dir . '/' . $ff;
                        }
                    }
                    if (is_dir($dir . '/' . $ff)) {
                        $list = array_merge($list, $this->listFolderFiles($dir . '/' . $ff));
                    }
                }
            }

            return $list;
        }

        function get_avatar_url($get_avatar)
        {
            if (preg_match("/src='(.*?)'/i", $get_avatar, $matches)) {
                preg_match("/src='(.*?)'/i", $get_avatar, $matches);

                return $matches[1];
            } else {
                preg_match("/src=\"(.*?)\"/i", $get_avatar, $matches);

                return $matches[1];
            }
        }

        function mb_word_wrap($string, $max_length = 100, $end_substitute = null, $html_linebreaks = false)
        {

            if ($html_linebreaks) {
                $string = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
            }
            $string = strip_tags($string); //gets rid of the HTML

            if (empty($string) || mb_strlen($string) <= $max_length) {
                if ($html_linebreaks) {
                    $string = nl2br($string);
                }

                return $string;
            }

            if ($end_substitute) {
                $max_length -= mb_strlen($end_substitute, 'UTF-8');
            }

            $stack_count = 0;
            while ($max_length > 0) {
                $char = mb_substr($string, --$max_length, 1, 'UTF-8');
                if (preg_match('#[^\p{L}\p{N}]#iu', $char)) {
                    $stack_count++;
                } //only alnum characters
                elseif ($stack_count > 0) {
                    $max_length++;
                    break;
                }
            }
            $string = mb_substr($string, 0, $max_length, 'UTF-8') . $end_substitute;
            if ($html_linebreaks) {
                $string = nl2br($string);
            }

            return $string;
        }

        function install()
        {
            global $wpdb;

            $charset_collate = '';

            if (!empty($wpdb->charset)) {
                $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
            }

            if (!empty($wpdb->collate)) {
                $charset_collate .= " COLLATE {$wpdb->collate}";
            }
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $sql = "-- ----------------------------;
CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}mm_conversation` (
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

            dbDelta($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}mm_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  UNIQUE KEY id (id)
) $charset_collate;
";

            dbDelta($sql);
        }

        function encrypt($text)
        {
            if (function_exists('mcrypt_encrypt')) {
                $key = SECURE_AUTH_KEY;
                $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $text, MCRYPT_MODE_CBC, md5(md5($key))));
                return $encrypted;
            } else {
                return $text;
            }
        }

        function decrypt($text)
        {
            if (function_exists('mcrypt_decrypt')) {
                $key = SECURE_AUTH_KEY;
                $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($text), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
                return $decrypted;
            } else {
                return $text;
            }
        }

        function trim_text($input, $length, $ellipses = true, $strip_html = true)
        {
            //strip tags, if desired
            if ($strip_html) {
                $input = strip_tags($input);
            }

            //no need to trim, already shorter than trim length
            if (strlen($input) <= $length) {
                return $input;
            }

            //find last space within length
            $last_space = strrpos(substr($input, 0, $length), ' ');
            $trimmed_text = substr($input, 0, $last_space);

            //add ellipses (...)
            if ($ellipses) {
                $trimmed_text .= '...';
            }

            return $trimmed_text;
        }

        function get_available_addon()
        {
            //load all shortcode
            $coms = glob($this->plugin_path . 'app/addons/*.php');
            $data = array();
            foreach ($coms as $com) {
                if (file_exists($com)) {
                    $meta = get_file_data($com, array(
                        'Name' => 'Name',
                        'Author' => 'Author',
                        'Description' => 'Description',
                        'AuthorURI' => 'Author URI',
                        'Network' => 'Network'
                    ), 'component');

                    if (strlen(trim($meta['Name'])) > 0) {
                        $data[$com] = $meta;
                    }
                }
            }

            return $data;
        }

        function setting()
        {
            $setting = new MM_Setting_Model();
            $setting->load();

            return $setting;
        }

        function html_beautifier($html)
        {
            if (!class_exists('SmartDOMDocument')) {
                require_once $this->plugin_path . 'vendors/SmartDOMDocument.class.php';
            }
            $x = new SmartDOMDocument();
            $x->loadHTML($html);
            $clean = $x->saveHTMLExact();

            return $clean;
        }

        function get_logger($type = 'file', $location = '')
        {
            if (empty($location)) {
                $location = $this->domain;
            }
            $logger = new IG_Logger($type, $location);

            return $logger;
        }

        function get($key, $default = NULL)
        {
            $value = isset($_GET[$key]) ? $_GET[$key] : $default;
            return apply_filters('mm_query_get_' . $key, $value);
        }

        function post($key, $default = NULL)
        {
            $array_dereference = NULL;
            if (strpos($key, '[')) {
                $bracket_pos = strpos($key, '[');
                $array_dereference = substr($key, $bracket_pos);
                $key = substr($key, 0, $bracket_pos);
            }
            $value = isset($_POST[$key]) ? $_POST[$key] : $default;
            if ($array_dereference) {
                preg_match_all('#(?<=\[)[^\[\]]+(?=\])#', $array_dereference, $array_keys, PREG_SET_ORDER);
                $array_keys = array_map('current', $array_keys);
                foreach ($array_keys as $array_key) {
                    if (!is_array($value) || !isset($value[$array_key])) {
                        $value = $default;
                        break;
                    }
                    $value = $value[$array_key];
                }
            }
            return apply_filters('mm_query_post_' . $key, $value);
        }
    }

//include dashboard
    global $wpmudev_notices;
    $wpmudev_notices[] = array(
        'id' => '938495',
        'name' => 'Private Messaging',
        'screens' => array(
            'toplevel_page_mm_main',
            'messaging_page_mm_setting',
            'admin_page_mm_view'
        )
    );
    //include_once(plugin_dir_path(__FILE__) . 'lib/dash-notices/wpmudev-dash-notification.php');

    function mmg()
    {
        return MMessaging::get_instance();
    }

//init once
    register_activation_hook(__FILE__, array(mmg(), 'install'));
    include_once mmg()->plugin_path . 'functions.php';
    //add action to load language
    add_action('plugins_loaded', 'mmg_load_languages');
    function mmg_load_languages()
    {
        load_plugin_textdomain(mmg()->domain, false, plugin_basename(mmg()->plugin_path . 'languages/'));
    }

}