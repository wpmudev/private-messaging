<?php

/**
 * Author: Hoang Ngo
 */
class MMessage_Backend_Controller extends IG_Request
{
    public function __construct()
    {
        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('wp_loaded', array(&$this, 'process_request'));
        add_action('wp_ajax_mm_plugin_action', array(&$this, 'plugins_action'));
    }

    function plugins_action()
    {
        $setting = mmg()->setting();
        $addons = $setting->plugins;
        if (!is_array($addons))
            $addons = array();
        $id = fRequest::get('id');
        $meta = get_file_data($id, array(
            'Name' => 'Name',
            'Author' => 'Author',
            'Description' => 'Description',
            'AuthorURI' => 'Author URI',
            'Network' => 'Network'
        ), 'component');

        if (!in_array($id, $addons)) {
            //activate it
            $addons[] = $id;
            $setting->plugins = $addons;
            $setting->save();
            fJSON::output(array(
                'noty' => __("The add on <strong>{$meta['Name']}</strong> activated", mmg()->domain),
                'text' => __("Deactivate", mmg()->domain)
            ));
            exit;
        } else {
            unset($addons[array_search($id, $addons)]);
            $setting->plugins = $addons;
            $setting->save();
            fJSON::output(array(
                'noty' => __("The add on <strong>{$meta['Name']}</strong> deactivate", mmg()->domain),
                'text' => __("Deactivate", mmg()->domain)
            ));
            exit;
        }
    }

    function admin_menu()
    {
        add_menu_page(__('Messaging', mmg()->domain), __('Messaging', mmg()->domain), 'manage_options', mmg()->prefix . 'main', array(&$this, 'main'), 'dashicons-email-alt');
        add_submenu_page(null, __('View Messages', mmg()->domain), __('View Messages', mmg()->domain), 'manage_options', mmg()->prefix . 'view', array(&$this, 'view'));
        add_submenu_page(mmg()->prefix . 'main', __('Settings', mmg()->domain), __('Settings', mmg()->domain), 'manage_options', mmg()->prefix . 'setting', array(&$this, 'setting'));
    }

    public function main()
    {
        include mmg()->plugin_path . 'app/components/mm-messages-table.php';
        $this->render('backend/main');
    }

    function process_request()
    {
        if (isset($_POST['MM_Setting_Model'])) {
            $model = new MM_Setting_Model();
            $model->load();
            $model->import($_POST['MM_Setting_Model']);
            $model->save();

            $this->set_flash('setting_save', __("Your settings have been successfully updated.", mmg()->domain));
            wp_redirect(fURL::getWithQueryString());
            exit;
        }
    }

    function view()
    {
        wp_enqueue_style('mm_style');

        $id = fRequest::get('id', 'int', 0);
        $model = MM_Conversation_Model::find($id);
        if (is_object($model)) {
            $this->render('backend/view', array(
                'model' => $model
            ));
        } else {
            echo __("Conversation not found!", mmg()->domain);
        }
    }

    function setting()
    {
        wp_enqueue_style('mm_style');
        add_action('mm_setting_general', array(&$this, 'general_view'));
        add_action('mm_setting_email', array(&$this, 'email_view'));
        add_action('mm_setting_shortcode', array(&$this, 'shortcode_view'));
        $model = new MM_Setting_Model();
        $model->load();

        $this->render('backend/setting', array(
            'model' => $model
        ));
    }

    function shortcode_view()
    {
        $this->render('backend/setting/shortcode');
    }

    function general_view($model)
    {
        $this->render('backend/setting/general', array(
            'model' => $model
        ));
    }

    function email_view($model)
    {
        $this->render('backend/setting/email', array(
            'model' => $model
        ));
    }
}