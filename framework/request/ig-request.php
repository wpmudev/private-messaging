<?php

/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Request')) {
    class IG_Request
    {
        protected $layout;

        /**
         * @param $view
         * @param array $params
         */
        public function render($view, $params = array(), $output = true)
        {
            $pool = explode('\\', dirname(__FILE__));
            //we will get the path below the controller
            $reflector = new ReflectionClass(get_class($this));

            $base_path = substr($reflector->getFileName(), 0, stripos($reflector->getFileName(), 'controllers'));

            $layout_path = '';
            if ($this->layout != null) {
                $layout_path = $base_path . '/views/layout/' . $this->layout . '.php';
            }

            $content = $this->render_partial($view, $params, false);

            if ($this->layout) {
                ob_start();
                include $layout_path;
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
            
            $base_path = substr($reflector->getFileName(), 0, stripos($reflector->getFileName(), 'controllers'));
            $view_path = $base_path . '/views/' . $view . '.php';

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
            $class = get_class($this);
            $db = get_option('ig_flash');
            if (!is_array($db)) {
                $db = array();
            }
            //save the flash
            $db[$class . '_' . $key] = $message;
            update_option('ig_flash', $db);
        }

        public function has_flash($key)
        {
            $db = get_option('ig_flash');
            $class = get_class($this);
            $index = $class . '_' . $key;
            return isset($db[$index]);
        }

        public function get_flash($key)
        {
            $db = get_option('ig_flash');
            $class = get_class($this);
            $index = $class . '_' . $key;
            if (isset($db[$index])) {
                $msg = $db[$index];
                unset($db[$index]);
                update_option('ig_flash', $db);
                return $msg;
            }
            return null;
        }

        public function log($message)
        {

        }
    }
}