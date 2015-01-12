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
        add_filter('user_has_cap', array(&$this, 'update_cap'), 10, 4);
    }
    function update_cap($allcaps, $caps, $args, $user)
    {
        if (in_array('upload_files', $caps)) {
            if (!isset($allcaps['upload_files'])) {
                $flag = false;
                if (mmg()->post('action') == 'query-attachments') {
                    ///just query media belong to someone
                    $flag = true;
                } elseif (mmg()->post('action') == 'upload-attachment') {
                    //case upload a file, we only allow when upload via je uploader
                    if (mmg()->post('igu_uploading') == 1) {
                        $flag = true;
                    }
                }
                if ($flag == true) {
                    //check
                    // var_dump($_POST);die;
                    $allowed = mmg()->setting()->allow_attachment;
                    if (!is_array($allowed)) {
                        $allowed = array();
                    }
                    $allowed = array_filter($allowed);
                    foreach ($user->roles as $role) {
                        if (in_array($role, $allowed)) {
                            $allcaps['upload_files'] = true;
                            break;
                        }
                    }
                }
            }
        }
        //die;
        return $allcaps;
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