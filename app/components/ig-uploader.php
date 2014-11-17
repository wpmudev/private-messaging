<?php
/**
 * Author: Hoang Ngo
 */
if (!class_exists('ig-uploader')) {
    class IG_Uploader
    {
        private static $_instance;

        public $plugin_url;
        public $plugin_path;
        public $domain;
        public $prefix;

        private $controller;

        private function __construct()
        {
            //variables init
            $this->plugin_url = plugin_dir_url(__FILE__) . 'ig-uploader/';
            $this->plugin_path = plugin_dir_path(__FILE__) . 'ig-uploader/';
            $this->domain = 'ig_uploader';
            $this->prefix = 'iup';

            spl_autoload_register(array(&$this, 'loader'));

            add_action('init', array(&$this, 'custom_content'));
            add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
            add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
            $this->controller = new IG_Uploader_Controller();
        }

        function scripts()
        {
            wp_register_style('igu-uploader', $this->plugin_url . 'assets/style.css');
            wp_enqueue_script('popoverasync', $this->plugin_url . 'assets/popover/popoverasync.js',array('jquery','ig-bootstrap'));

            wp_register_script('jquery-frame-transport', $this->plugin_url . 'assets/iframe-transport/jquery.iframe-transport.js');
        }

        function custom_content()
        {
            if (!post_type_exists($this->prefix . 'media')) {
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
                register_post_type($this->prefix . 'media', $args);
            }
        }

        function show_upload_control(IG_Model $p_model, $attribute, $container)
        {
            $this->controller->upload_form($attribute, $p_model, $container);
        }

        function loader($class)
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

        static function get_instance()
        {
            if (!is_object(self::$_instance)) {
                self::$_instance = new IG_Uploader();
            }
            return self::$_instance;
        }
    }

    function ig_uploader()
    {
        return IG_Uploader::get_instance();
    }
}
ig_uploader();