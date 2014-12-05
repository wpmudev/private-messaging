<?php
/**
 * Author: Hoang Ngo
 */
if (!function_exists('ig_loader')) {
    /**
     * @param $class
     */
    function ig_loader($class)
    {
        $classes = array(
            'IG_Model' => dirname(__FILE__) . '/database/ig-model.php',
            'IG_Post_Model' => dirname(__FILE__) . '/database/ig-post-model.php',
            'IG_DB_Model' => dirname(__FILE__) . '/database/ig-db-model.php',
            'IG_DB_Model_Ex' => dirname(__FILE__) . '/database/ig-db-model-ex.php',
            'IG_Option_Model' => dirname(__FILE__) . '/database/ig-option-model.php',
            'IG_Grid' => dirname(__FILE__) . '/database/ig-grid.php',
            'IG_Form' => dirname(__FILE__) . '/form/ig-form.php',
            'IG_Active_Form' => dirname(__FILE__) . '/form/ig-active-form.php',
            'IG_Form_Generator' => dirname(__FILE__) . '/generator/ig-form-generator.php',
            'IG_Request' => dirname(__FILE__) . '/request/ig-request.php',
            'IG_Logger' => dirname(__FILE__) . '/logger/ig-logger.php',
        );

        if (isset($classes[$class])) {
            require_once $classes[$class];
        } else {
            // Customize this to your root Flourish directory
            $flourish_root = dirname(__FILE__) . '/vendors/flourishlib/';

            $file = $flourish_root . $class . '.php';

            if (file_exists($file)) {
                include $file;

                return;
            }
        }
    }

    spl_autoload_register('ig_loader');

    include_once dirname(__FILE__) . '/vendors/Container.php';

    if (!function_exists('ig_enqueue_scripts')) {
        add_action('wp_enqueue_scripts', 'ig_enqueue_scripts');
        add_action('admin_enqueue_scripts', 'ig_enqueue_scripts');
        function ig_enqueue_scripts()
        {
            $url = plugin_dir_url(__FILE__);

            wp_register_style('ig-bootstrap', $url . 'assets/bootstrap.css');
            wp_register_style('ig-bootstrap-lumen', $url . 'assets/lumen.css');
            wp_register_style('ig-bootstrap-flaty', $url . 'assets/flaty.css');
            wp_register_style('ig-bootstrap-paper', $url . 'assets/paper.css');
            wp_register_style('ig-bootstrap-united', $url . 'assets/united.css');
            wp_register_script('ig-bootstrap', $url . 'assets/bootstrap.min.js', array('jquery'));
            wp_register_style('ig-fontawesome', $url . 'assets/fa/css/font-awesome.css');
        }
    }
}