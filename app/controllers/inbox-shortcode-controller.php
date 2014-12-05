<?php

/**
 * Author: Hoang Ngo
 */
class Inbox_Shortcode_Controller extends IG_Request
{
    protected $layout = 'main';

    public function __construct()
    {
        add_shortcode('message_inbox', array(&$this, 'inbox'));
        add_action('wp_loaded', array(&$this, 'process_request'));
        add_action('wp_ajax_mm_send_message', array(&$this, 'send_message'));
        add_action('wp_ajax_mm_suggest_users', array(&$this, 'suggest_users'));
        add_action('wp_ajax_mm_load_conversation', array(&$this, 'load_conversation'));
        add_action('wp_ajax_mm_status', array(&$this, 'change_status'));
    }

    function change_status()
    {
        if (!wp_verify_nonce(FRequest::get('_wpnonce'), 'mm_status')) {
            exit;
        }
        $id = fRequest::get('id', 'string');
        $id = mmg()->decrypt($id);
        $type = fRequest::get('type', 'string', null);
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
            if (!wp_verify_nonce(FRequest::get('_wpnonce'), 'mm_user_setting')) {
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
            do_action('mm_user_setting_saved', $setting);
            $this->set_flash('user_setting_' . $user_id, __("Your settings have been successfully updated", mmg()->domain));
            wp_redirect(fURL::getWithQueryString());
            exit;
        }
    }

    function load_conversation()
    {
        if (!wp_verify_nonce(FRequest::get('_wpnonce'), 'mm_load_conversation')) {
            exit;
        }

        $id = mmg()->decrypt(fRequest::get('id'));
        $model = MM_Conversation_Model::model()->find($id);
        $html = $this->render_inbox_message($model);

        if (!$model->is_archive()) {
            $model->mark_as_read();
            do_action('mm_conversation_read', $model);
        }

        fJSON::output(array(
            'html' => $html,
            'count_unread' => MM_Conversation_Model::count_unread(true),
            'count_read' => MM_Conversation_Model::count_read(true)
        ));
        exit;
    }

    function inbox($atts)
    {
        wp_enqueue_style('mm_style');
        wp_enqueue_script('mm_scroll');
        wp_enqueue_style('mm_scroll');

        wp_enqueue_style('selectivejs');
        wp_enqueue_script('selectivejs');

        if (!is_user_logged_in()) {
            return $this->render('shortcode/login', array(), false);
        }
        add_action('wp_footer', array(&$this, 'render_compose_form'));
        //$a = shortcode_atts($atts, array());
        $type = fRequest::get('box', 'string', 'inbox');
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
                return $this->render('shortcode/setting', array(), false);
                break;
            case 'search':
                $models = MM_Conversation_Model::search(fRequest::get('query'));
                $total_pages = mmg()->global['conversation_total_pages'];
                break;
        }

        return $this->render('shortcode/inbox', array(
            'models' => $models,
            'total_pages' => $total_pages,
            'paged' => fRequest::get('mpaged', 'int', 1)
        ), false);
    }

    function render_compose_form()
    {

        $this->render_partial('shortcode/_compose_form');
        $this->render_partial('shortcode/_reply_form');
    }

    function suggest_users()
    {
        if (!wp_verify_nonce(FRequest::get('_wpnonce'), 'mm_suggest_users')) {
            exit;
        }

        $query = new WP_User_Query(apply_filters('mm_suggest_users_args', array(
            'search' => '*' . fRequest::get('query') . '*',
            'search_columns' => array('user_login'),
            'exclude' => array(get_current_user_id()),
            'number' => 10,
            'orderby' => 'user_login',
            'order' => 'ASC'
        )));

        $data = array();
        foreach ($query->get_results() as $user) {
            $obj = new stdClass();
            $obj->id = $user->ID;
            $obj->name = $user->user_login;
            $data[] = $obj;
        }

        $data = apply_filters('mm_suggest_users_result', $data);

        fJSON::output($data);

        exit;
    }

    function send_message()
    {
        if (!wp_verify_nonce(FRequest::get('_wpnonce'), 'compose_message')) {
            exit;
        }

        $model = new MM_Message_Model();
        $model->import(fRequest::get('MM_Message_Model', 'array'));

        if ($model->validate()) {
            if (fRequest::get('is_reply', 'int', 0) == 1) {
                //reply case, we will send message to all users, but not the sender
                $message_id = mmg()->decrypt(fRequest::get('id', 'string', null));
                $conv_id = mmg()->decrypt(fRequest::get('parent_id', 'string', null));

                $c_model = MM_Conversation_Model::model()->find($conv_id);
                $user_ids = $c_model->user_index;

                $user_ids = $this->logins_to_ids($user_ids);

                foreach ($user_ids as $user_id) {
                    if ($user_id != get_current_user_id()) {
                        $this->_reply_message($conv_id, $message_id, $user_id, $model);
                    }
                }
                $this->set_flash('mm_sent_' . get_current_user_id(), __("Your message has been sent.", mmg()->domain));
                fJSON::output(array(
                    'status' => 'success'
                ));
            } else {
                $users = fRequest::get('MM_Message_Model[send_to]');
                $user_ids = $this->logins_to_ids($users);

                $is_single = true;
                if ($is_single) {
                    //create message
                    foreach ($user_ids as $user_id) {
                        $this->_send_message($user_id, $model);
                        //check does this is new message or reply
                    }
                } else {
                    //todo update group conversation
                }
                $this->set_flash('mm_sent_' . get_current_user_id(), __("Your message has been sent.", mmg()->domain));
                fJSON::output(array(
                    'status' => 'success'
                ));
            }
        } else {
            fJSON::output(array(
                'status' => 'fail',
                'errors' => $model->get_errors()
            ));
        }
        exit;
    }

    function _reply_message($conv_id, $message_id, $user_id, $model)
    {
        //load conversation
        $conversation = MM_Conversation_Model::model()->find($conv_id);
        $conversation->status = MM_Message_Status_Model::STATUS_UNREAD;
        //we will add new message to this conversation
        $conversation->save();
        //update users from this conversation, now save the message
        $m = new MM_Message_Model();
        $m->import($model->export());
        $m->send_to = $user_id;
        $m->conversation_id = $conversation->id;
        $m->status = MM_Message_Model::UNREAD;
        $mess = MM_Message_Model::model()->find($message_id);
        $m->subject = __("Re:", mmg()->domain) . ' ' . $mess->subject;

        $m->save();
        //update status for send to
        $status = MM_Message_Status_Model::model()->find_one_with_attributes(array(
            'conversation_id' => $conversation->id,
            'user_id' => $user_id
        ));
        if (is_object($status)) {
            $status->status = MM_Message_Status_Model::STATUS_UNREAD;
            $status->save();
        }

        //update index
        $conversation->update_index($m->id);
        do_action('mm_message_sent', $m);
    }

    function _send_message($user_id, $model)
    {
        //create new conservation
        $conservation = new MM_Conversation_Model();
        $conservation->status = MM_Message_Status_Model::STATUS_UNREAD;
        $conservation->save();
        //save message
        $m = new MM_Message_Model();
        $m->import($model->export());
        $m->send_to = $user_id;
        $m->conversation_id = $conservation->id;
        $m->status = MM_Message_Model::UNREAD;
        $m->save();
        //update index
        $conservation->update_index($m->id);
        do_action('mm_message_sent', $m);
        //update status
        $model = new MM_Message_Status_Model();
        $model->user_id = $user_id;
        $model->conversation_id = $conservation->id;
        $model->status = MM_Message_Status_Model::STATUS_UNREAD;
        $model->type = MM_Message_Status_Model::TYPE_CONVERSATION;
        $model->save();
        //we need both for each sender & reciver
        $model = new MM_Message_Status_Model();
        $model->user_id = get_current_user_id();
        $model->conversation_id = $conservation->id;
        //because we send so status should be read
        $model->status = MM_Message_Status_Model::STATUS_READ;
        $model->type = MM_Message_Status_Model::TYPE_CONVERSATION;
        $model->save();
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

        return $this->render_partial('shortcode/_inbox_message', array(
            'messages' => $messages
        ), false);
    }
}