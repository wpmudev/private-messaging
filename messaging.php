<?php
/*
Plugin Name: Private Messaging
Plugin URI:
Description:
Author: WPMU DEV
Version: 1.0
Author URI: http://premium.wpmudev.org
WDP ID: ***
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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if (!class_exists('MMessaging')) {
    include_once __DIR__ . '/framework/loader.php';

    class MMessaging
    {
        public $plugin_url;
        public $plugin_path;
        public $domain;
        public $prefix;

        public $version = "1.2";

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
            add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
            add_action('admin_enqueue_scripts', array(&$this, 'scripts'));

            $this->check_upgrade();
            add_action('init', array(&$this, 'dispatch'));
        }

        function check_upgrade()
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
CREATE TABLE `wp_mm_conversation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `count` tinyint(3) unsigned DEFAULT NULL,
  `index` varchar(255) DEFAULT NULL,
  `user_index` varchar(255) DEFAULT NULL,
  `from` tinyint(3) DEFAULT NULL,
  `site_id` tinyint(1) DEFAULT NULL,
  UNIQUE KEY id (id)
) $charset_collate;";

            if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->base_prefix . "mm_conversation'") !== $wpdb->base_prefix . 'mm_conversation') {
                //do upgrade
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
        }

        function scripts()
        {
            wp_enqueue_script('jquery');
            wp_enqueue_script('ig-bootstrap');
            if (is_admin()) {
                wp_enqueue_style('ig-bootstrap');
            } else {
                wp_enqueue_style(apply_filters('mm_front_theme', 'ig-bootstrap'));
            }
            wp_enqueue_style('ig-fontawesome');

            wp_register_style('mm_style', $this->plugin_url . 'assets/main.css', array(), $this->version);
            wp_register_style('mm_scroll', $this->plugin_url . 'assets/perfect-scrollbar.min.css', array(), $this->version);
            wp_register_script('mm_scroll', $this->plugin_url . 'assets/perfect-scrollbar.min.js', array('jquery'), $this->version);

            wp_register_script('selectivejs', $this->plugin_url . 'assets/selectivejs/js/standalone/selectize.js', array('jquery'), $this->version);
            wp_register_style('selectivejs', $this->plugin_url . 'assets/selectivejs/css/selectize.bootstrap3.css', array(), $this->version);

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
            include $this->plugin_path . 'app/components/ig-uploader.php';
            include $this->plugin_path . 'app/components/mm-addon-table.php';
            //load add on
            $addons = $this->setting()->plugins;
            if (!is_array($addons))
                $addons = array();
            foreach ($addons as $addon) {
                if (file_exists($addon))
                    include_once $addon;
            }
            //loading add on & components
            new MAjax();
            $inbox_sc = new Inbox_Shortcode_Controller();
            $messge_me_sc = new Message_Me_Shortcode_Controller();

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
                'publicly_queryable' => true,
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
            } else {
                //include normal file inside app folder
                if (file_exists($this->plugin_path . 'app/' . $filename)) {
                    include_once $this->plugin_path . 'app/' . $filename;
                }
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
                    if (is_dir($dir . '/' . $ff))
                        $list = array_merge($list, $this->listFolderFiles($dir . '/' . $ff));
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

            if ($html_linebreaks) $string = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
            $string = strip_tags($string); //gets rid of the HTML

            if (empty($string) || mb_strlen($string) <= $max_length) {
                if ($html_linebreaks) $string = nl2br($string);
                return $string;
            }

            if ($end_substitute) $max_length -= mb_strlen($end_substitute, 'UTF-8');

            $stack_count = 0;
            while ($max_length > 0) {
                $char = mb_substr($string, --$max_length, 1, 'UTF-8');
                if (preg_match('#[^\p{L}\p{N}]#iu', $char)) $stack_count++; //only alnum characters
                elseif ($stack_count > 0) {
                    $max_length++;
                    break;
                }
            }
            $string = mb_substr($string, 0, $max_length, 'UTF-8') . $end_substitute;
            if ($html_linebreaks) $string = nl2br($string);

            return $string;
        }

        function encrypt($text)
        {
            return str_replace('fCryptography::symmetric', '', fCryptography::symmetricKeyEncrypt($text, SECURE_AUTH_KEY));
        }

        function decrypt($text)
        {
            $text = 'fCryptography::symmetric' . $text;
            return fCryptography::symmetricKeyDecrypt($text, SECURE_AUTH_KEY);
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
            require_once $this->plugin_path . 'vendors/SmartDOMDocument.class.php';
            $x = new SmartDOMDocument();
            $x->loadHTML($html);
            $clean = $x->saveHTMLExact();
            return $clean;
        }
    }

//include dashboard
    global $wpmudev_notices;
    $wpmudev_notices[] = array('id' => 68, 'name' => 'Messaging', 'screens' => array('toplevel_page_messaging', 'inbox_page_messaging_new', 'inbox_page_messaging_sent', 'inbox_page_messaging_message-notifications'));
    include_once(plugin_dir_path(__FILE__) . 'lib/dash-notices/wpmudev-dash-notification.php');

    function mmg()
    {
        return MMessaging::get_instance();
    }

//init once
    mmg();
}