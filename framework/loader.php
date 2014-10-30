<?php
/**
 * Author: Hoang Ngo
 */

spl_autoload_register('mmessaging_ig_loader');

function mmessaging_ig_loader($class)
{
    $classes = array(
        'IG_Model' => __DIR__ . '/database/ig-model.php',
        'IG_Post_Model' => __DIR__ . '/database/ig-post-model.php',
        'IG_DB_Model' => __DIR__ . '/database/ig-db-model.php',
        'IG_Option_Model' => __DIR__ . '/database/ig-option-model.php',
        'IG_Grid' => __DIR__ . '/database/ig-grid.php',
        'IG_Form' => __DIR__ . '/form/ig-form.php',
        'IG_Active_Form' => __DIR__ . '/form/ig-active-form.php',
        'IG_Form_Generator' => __DIR__ . '/generator/ig-form-generator.php',
        'IG_Request' => __DIR__ . '/request/ig-request.php',
    );

    if (isset($classes[$class])) {
        require_once $classes[$class];
    } else {
        // Customize this to your root Flourish directory
        $flourish_root = __DIR__ . '/vendors/flourishlib/';

        $file = $flourish_root . $class . '.php';

        if (file_exists($file)) {
            include $file;
            return;
        }
    }
}

if (!class_exists('RedBean_SimpleModel')) {
    include_once __DIR__ . '/vendors/rb.php';
}

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