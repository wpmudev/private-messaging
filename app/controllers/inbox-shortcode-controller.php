<?php

/**
 * Author: Hoang Ngo
 */
class Inbox_Shortcode_Controller extends IG_Request
{
    protected $layout = 'main';

    protected $messages = array();

    public function __construct()
    {
        add_shortcode('message_inbox', array(&$this, 'inbox'));
        add_action('wp_loaded', array(&$this, 'process_request'));
        add_action('wp_ajax_mm_send_message', array(&$this, 'send_message'));
        add_action('wp_ajax_mm_suggest_users', array(&$this, 'suggest_users'));
        add_action('wp_ajax_mm_load_conversation', array(&$this, 'load_conversation'));
        add_action('wp_ajax_mm_status', array(&$this, 'change_status'));
        add_action('wp_footer', array(&$this, 'footer'));
    }

    function footer()
    {
        ?>
        <div class="ig-container attachments-footer"></div>
    <?php
    }

    function change_status()
    {
        if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'mm_status')) {
            exit;
        }
        $id = mmg()->post('id');
        $id = mmg()->decrypt($id);
        $type = mmg()->post('type');
        $model = MM_Conversation_Model::model()->find($id);
        if (is_object($model) && !is_null($type)) {
            $status = $model->get_current_status();

            $status->status = $type;
            $status->save();
        }
    }

    function process_request()
    {
        if (isset($_POST['mm_user_setting']) && $_POST['mm_user_setting'] == 1) {
            if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'mm_user_setting_' . get_current_user_id())) {
                exit;
            }

            $user_id = get_current_user_id();
            $enable_receipt = $_POST['receipt'];
            $prevent_receipt = $_POST['prevent'];
            $setting = get_user_meta($user_id, '_messages_setting', true);
            if (!$setting) {
                $setting = array();
            }
            $setting['enable_receipt'] = $enable_receipt;
            $setting['prevent_receipt'] = $prevent_receipt;

            update_user_meta($user_id, '_messages_setting', $setting);
            do_action('mm_user_setting_saved', $setting, get_current_user_id());
            $this->set_flash('user_setting_' . $user_id, __("Your settings have been successfully updated", mmg()->domain));
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }

    function load_conversation()
    {
        if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'mm_load_conversation')) {
            exit;
        }

        $id = mmg()->decrypt(mmg()->post('id'));
        $model = MM_Conversation_Model::model()->find($id);
        $html = $this->render_inbox_message($model);
        if (!$model->is_archive()) {
            $model->mark_as_read();
            do_action('mm_conversation_read', $model);
        }
        $messages = $model->get_messages();
        //update replace form
        if ($model->is_lock() == false) {
            $reply_form = $this->render_partial('shortcode/_reply_form', array(
                'message' => array_shift($messages)
            ), false);
        } else {
            $reply_form = '';
        }
        wp_send_json(array(
            'html' => $html,
            'reply_form' => $reply_form,
            'count_unread' => MM_Conversation_Model::count_unread(true),
            'count_read' => MM_Conversation_Model::count_read(true)
        ));
        exit;
    }

    function inbox($atts)
    {
        $a = wp_parse_args($atts, array(
            'nav_view' => 'both'
        ));

        if (!is_user_logged_in()) {
            do_action('mmg_before_load_login_form');
            mmg()->load_script('login');
            return $this->render('shortcode/login', array(
                'show_nav' => $this->can_show_nav($a['nav_view'])
            ), false);
        }
        mmg()->load_script('inbox');
        add_action('wp_footer', array(&$this, 'render_compose_form'));
        //$a = shortcode_atts($atts, array());
        $type = mmg()->get('box', 'inbox');
        if (isset($_GET['query']) && !empty($_GET['query'])) {
            $type = 'search';
        }
        $total_pages = 0;
        switch ($type) {
            case 'inbox':
                $models = MM_Conversation_Model::get_conversation();
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
            case 'unread':
                $models = MM_Conversation_Model::get_unread();
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
            case 'read':
                $models = MM_Conversation_Model::get_read();
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
            case 'sent':
                $models = MM_Conversation_Model::get_sent();
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
            case 'archive':
                $models = MM_Conversation_Model::get_archive();
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
            case'setting':
                return $this->render('shortcode/setting', array('show_nav' => $this->can_show_nav($a['nav_view'])), false);
                break;
            case 'search':
                $models = MM_Conversation_Model::search(mmg()->get('query'));
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
        }

        return $this->render('shortcode/inbox', array(
            'models' => $models,
            'total_pages' => $total_pages,
            'paged' => mmg()->get('mpaged', 'int', 1),
            'show_nav' => $this->can_show_nav($a['nav_view'])
        ), false);
    }

    function render_compose_form()
    {
        $this->render_partial('shortcode/_compose_form');
        $messages = array_values($this->messages);
        $this->render_partial('shortcode/_reply_form', array(
            'message' => array_shift($messages)
        ));
    }

    function suggest_users()
    {
        if (!wp_verify_nonce(mmg()->get('_wpnonce'), 'mm_suggest_users')) {
            exit;
        }
        $query_string = mmg()->post('query');
        $query = new WP_User_Query(apply_filters('mm_suggest_users_args', array(
            'search' => '*' . mmg()->post('query') . '*',
            'search_columns' => array('user_login'),
            'exclude' => array(get_current_user_id()),
            'number' => 10,
            'orderby' => 'user_login',
            'order' => 'ASC'
        )));
        $name_query = new WP_User_Query(apply_filters('mm_suggest_users_first_last_args', array(
            'exclude' => array(get_current_user_id()),
            'number' => 10,
            'orderby' => 'user_login',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => $query_string,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'last_name',
                    'value' => $query_string,
                    'compare' => 'LIKE'
                )
            )
        )));
        $results = array_merge($query->get_results(), $name_query->get_results());

        $data = array();
        foreach ($results as $user) {
            $userdata = get_userdata($user->ID);
            $name = $user->user_login;
            $full_name = trim($userdata->first_name . ' ' . $userdata->last_name);
            if (strlen($full_name)) {
                $name = $user->user_login . ' - ' . $full_name;
            }
            $obj = new stdClass();
            $obj->id = $user->ID;
            $obj->name = $name;
            $data[] = $obj;
        }

        $data = apply_filters('mm_suggest_users_result', $data);

        wp_send_json($data);

        exit;
    }

    function send_message()
    {
        if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'compose_message')) {
            exit;
        }

        $model = new MM_Message_Model();
        $model->import(mmg()->post('MM_Message_Model'));
        $model = apply_filters('mm_before_send_message', $model);

        if ($model->validate()) {
            if (mmg()->post('is_reply', 0) == 1) {
                //reply case, we will send message to all users, but not the sender
                $message_id = mmg()->decrypt(mmg()->post('id'));
                $conv_id = mmg()->decrypt(mmg()->post('parent_id'));

                $c_model = MM_Conversation_Model::model()->find($conv_id);
                $user_ids = $c_model->user_index;

                $user_ids = $this->logins_to_ids($user_ids);
                //we will need to explude the sender
                unset($user_ids[array_search(get_current_user_id(), $user_ids)]);
                //we will check does any one included
                $included = mmg()->post('user_include');
                if (!empty($included)) {
                    $included = $this->logins_to_ids($included);
                }
                if ($included) {
                    $user_ids = array_merge($user_ids, $included);
                }

                $user_ids = apply_filters('mm_reply_user_ids', $user_ids);

                $this->_reply_message($conv_id, $message_id, $user_ids, $model);

                $this->set_flash('mm_sent_' . get_current_user_id(), __("Your message has been sent.", mmg()->domain));
                wp_send_json(array(
                    'status' => 'success'
                ));
            } else {
                $send_to = $model->send_to;
                $user_ids = $this->logins_to_ids($send_to);

                $cc_list = mmg()->post('cc');
                $cc_list = explode(',', $cc_list);
                $cc_list = array_filter($cc_list);
                if (empty($cc_list)) {
                    //create message
                    foreach ($user_ids as $user_id) {
                        $this->_send_message($user_id, $model);
                    }
                } else {
                    foreach ($user_ids as $user_id) {
                        $send_to_lists = array($user_id);
                        $send_to_lists = array_merge($send_to_lists, $cc_list);
                        $this->_send_message_group($send_to_lists, $model);
                    }
                }

                $this->set_flash('mm_sent_' . get_current_user_id(), __("Your message has been sent.", mmg()->domain));
                wp_send_json(array(
                    'status' => 'success'
                ));
            }
        } else {
            wp_send_json(array(
                'status' => 'fail',
                'errors' => $model->get_errors()
            ));
        }
        exit;
    }

    function _reply_message($conv_id, $message_id, $user_ids, $model)
    {
        //load conversation
        $conversation = MM_Conversation_Model::model()->find($conv_id);
        foreach ($user_ids as $user_id) {
            MM_Message_Status_Model::model()->status($conversation->id, MM_Message_Status_Model::STATUS_UNREAD, $user_id);
        }
        $id = MM_Message_Model::reply(implode(',', $user_ids), $message_id, $conv_id, $model->export());
        //update index
        $conversation->update_index($id);
    }

    function _send_message($user_id, $model)
    {
        //create new conservation
        $conservation = new MM_Conversation_Model();
        $conservation->save();
        //apply status of this conversation for sender and receive
        MM_Message_Status_Model::model()->status($conservation->id, MM_Message_Status_Model::STATUS_READ, get_current_user_id());
        //apply status for receive
        MM_Message_Status_Model::model()->status($conservation->id, MM_Message_Status_Model::STATUS_UNREAD, $user_id);
        $id = MM_Message_Model::send($user_id, $conservation->id, $model->export());
        $conservation->update_index($id);
        return $id;
    }

    function _send_message_group($user_ids, $model)
    {
        //create new conservation
        $conservation = new MM_Conversation_Model();
        $conservation->save();
        //apply status of this conversation for sender and receive
        MM_Message_Status_Model::model()->status($conservation->id, MM_Message_Status_Model::STATUS_READ, get_current_user_id());
        foreach ($user_ids as $user_id) {
            MM_Message_Status_Model::model()->status($conservation->id, MM_Message_Status_Model::STATUS_UNREAD, $user_id);
        }
        $message_id = MM_Message_Model::send(implode(',', $user_ids), $conservation->id, $model->export());
        $conservation->update_index($message_id);
    }

    function logins_to_ids($users)
    {
        if (!is_array($users)) {
            $users = explode(',', $users);
        }
        $data = array();
        foreach ($users as $username) {
            if (filter_var($username, FILTER_VALIDATE_INT)) {
                $user = get_user_by('id', $username);
                if (is_object($user)) {
                    $data[] = $user->ID;
                }
            } else {
                $user = get_user_by('login', $username);
                if (is_object($user)) {
                    $data[] = $user->ID;
                }
            }
        }
        return apply_filters('mm_send_to_this_users', $data);
    }

    function render_inbox_message(MM_Conversation_Model $model)
    {
        //get all the message from this conversation
        $messages = $model->get_messages();
        $this->messages = $messages;
        return $this->render_partial('shortcode/_inbox_message', array(
            'messages' => $messages
        ), false);
    }

    public function can_show_nav($condition)
    {
        if ($condition == 'both') {
            return true;
        }
        if ($condition == 'loggedin') {
            return is_user_logged_in();
        }
        if ($condition == 'loggedout') {
            return !is_user_logged_in();
        }
    }
}