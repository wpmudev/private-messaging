<?php

/**
 * Author: Hoang Ngo
 */
class MM_Backend
{
    public function __construct()
    {
        new MMessage_Backend_Controller();
        add_action('wp_ajax_mm_create_message_page', array(&$this, 'create_page'));
    }

    function create_page()
    {
        if (isset($_POST['m_type'])) {
            $model = new MM_Setting_Model();
            $model->load();
            switch ($_POST['m_type']) {
                case 'inbox':
                    $new_id = wp_insert_post(apply_filters('mm_create_inbox_page', array(
                        'post_title' => "Inbox",
                        'post_content' => '[message_inbox]',
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'ping_status' => 'closed',
                        'comment_status' => 'closed'
                    )));

                    $model->inbox_page = $new_id;
                    $model->save();
                    //update
                    echo $new_id;
                    break;
            }
        }
        exit;
    }
}