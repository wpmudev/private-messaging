<?php

/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Request')) {
    class IG_Request
    {
        protected $layout;
        protected $flash_key = 'ig_flash';
        protected $base_path = null;

        /**
         * @param $view
         * @param array $params
         */
        public function render($view, $params = array(), $output = true)
        {
            $ig_request_params_cache = $params;
            extract($params);
            $layout = apply_filters('ig_view_layout', $this->layout);
            $pool = explode('\\', dirname(__FILE__));
            //we will get the path below the controller
            $reflector = new ReflectionClass(get_class($this));
            if (is_null($this->base_path)) {
                $base_path = substr($reflector->getFileName(), 0, stripos($reflector->getFileName(), 'controllers'));
            } else {
                $base_path = $this->base_path;
            }

            $layout_path = '';
            if ($layout != null) {
                $layout_path = $base_path . '/views/layout/' . $layout. '.php';
            }

            $content = $this->render_partial($view, $ig_request_params_cache, false);

            if ($layout) {
                ob_start();
                include apply_filters('ig_layout_path', $layout_path);
                $content = ob_get_clean();
            }

            if ($output) {
                echo $content;
            } else {
                return $content;
            }
        }

        public function render_partial($view, $params = array(), $output = true)
        {
            //we will get the path below the controller
            $reflector = new ReflectionClass(get_class($this));

            if (is_null($this->base_path)) {
                $base_path = substr($reflector->getFileName(), 0, stripos($reflector->getFileName(), 'controllers'));
            } else {
                $base_path = $this->base_path;
            }
            $view_path = $base_path . '/views/' . $view . '.php';
            $view_path = apply_filters('ig_view_file', $view_path, $view);

            if (file_exists($view_path)) {
                extract($params);
                ob_start();
                include $view_path;
                $content = ob_get_clean();
                if ($output) {
                    echo $content;
                }
                return $content;
            } else {
                echo __("View not found!");
            }
        }

        public function redirect($url)
        {
            wp_redirect($url);
            exit;
        }

        public function set_flash($key, $message)
        {
            $db = get_option($this->flash_key);
            if (!is_array($db)) {
                $db = array();
            }
            //save the flash
            $db[$key] = $message;
            update_option($this->flash_key, $db);
        }

        public function has_flash($key)
        {
            $db = get_option($this->flash_key);
            $index = $key;
            return isset($db[$index]);
        }

        public function get_flash($key)
        {
            $db = get_option($this->flash_key);
            $index = $key;
            if (isset($db[$index])) {
                $msg = $db[$index];
                unset($db[$index]);
                update_option($this->flash_key, $db);
                return $msg;
            }
            return null;
        }

        public function log($message)
        {

        }

        public function refresh()
        {
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }
}