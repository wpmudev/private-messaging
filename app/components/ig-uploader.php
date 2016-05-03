<?php
/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Uploader')) {
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

        }

        public function init_uploader($can_upload = false, $domain = '')
        {
            $this->controller = new IG_Uploader_Controller($can_upload);
            if ($domain) {
                $this->domain = $domain;
            }
        }

        function scripts()
        {
            wp_enqueue_script('jquery');
            wp_register_style('igu-uploader', $this->plugin_url . 'assets/style.css');
            wp_register_script('webuipopover', $this->plugin_url . 'assets/popover/webuipopover.js', array('jquery'));
            wp_register_style('webuipopover', $this->plugin_url . 'assets/popover/webuipopover.css');

            wp_register_script('jquery-frame-transport', $this->plugin_url . 'assets/iframe-transport/jquery.iframe-transport.js');
            wp_register_script('ig-leanmodal', $this->plugin_url . 'assets/jquery.leanModal.min.js');
            wp_register_script('igu-uploader', $this->plugin_url . 'assets/uploader.js');
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
                    'exclude_from_search' => true,
                    'publicly_queryable' => true,
                    'capability_type' => $this->prefix . 'media',
                );
                register_post_type($this->prefix . 'media', $args);
            }
        }

        function show_upload_control(IG_Model $p_model, $attribute, $is_admin = false, $attributes = array())
        {
            $this->controller->upload_form($attribute, $p_model, $is_admin, $attributes);
        }

        function show_media(IG_Model $p_model, $attribute)
        {
            $this->controller->show_media($p_model, $attribute);
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

        function get($key, $default = NULL)
        {
            $value = isset($_GET[$key]) ? $_GET[$key] : $default;
            return apply_filters('igu_query_get_' . $key, $value);
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
            return apply_filters('igu_query_get_' . $key, $value);
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

        function can_compress()
        {
            //getting basepath, because this component always inside the components folder,just need to back to root
            $root_path = dirname(dirname(dirname($this->plugin_path)));
            $runtime_path = $root_path . '/framework/runtime';

            if (!is_dir($runtime_path)) {
                //try to create
                mkdir($runtime_path);
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

        function compress_assets($css = array(), $js = array(), $write_path)
        {
            if (defined('DOING_AJAX') && DOING_AJAX)
                return;

            $css_write_path = $write_path . '/' . implode('-', $css) . '.css';
            $css_cache = get_option('iguploader_style_last_cache');
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
                update_option('iguploader_style_last_cache', time());
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
    }

    function ig_uploader()
    {
        return IG_Uploader::get_instance();
    }
}
ig_uploader();