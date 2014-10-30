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
            $this->set_flash('user_setting', __("Your settings has saved!", mmg()->domain));
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
        $model = MM_Conversation_Model::find($id);
        $this->render_inbox_message($model);
        $last = $model->get_last_message();
        if ($last->send_from != get_current_user_id()) {
            do_action('mm_conversation_read', $model);
            $model->mark_as_read();
        }
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

        //$a = shortcode_atts($atts, array());
        $type = fRequest::get('box', 'string', 'inbox');
        if (isset($_GET['query']) && !empty($_GET['query'])) {
            $type = 'search';
        }
        switch ($type) {
            case 'inbox':
                $models = MM_Conversation_Model::get_conversation();
                break;
            case 'unread':
                $models = MM_Conversation_Model::get_unread();
                break;
            case 'read':
                $models = MM_Conversation_Model::get_read();
                break;
            case 'sent':
                $models = MM_Conversation_Model::get_sent();
                break;
            case'setting':
                return $this->render('shortcode/setting', array(), false);
                break;
            case 'search':
                $models = MM_Conversation_Model::search(fRequest::get('query'));
                break;
        }
        return $this->render('shortcode/inbox', array(
            'models' => $models
        ), false);
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

                $c_model = MM_Conversation_Model::find($conv_id);
                $user_ids = $c_model->user_index;

                $user_ids = $this->logins_to_ids($user_ids);

                foreach ($user_ids as $user_id) {
                    if ($user_id != get_current_user_id()) {
                        $this->_reply_message($conv_id, $message_id, $user_id, $model);
                    }
                }
                $this->set_flash('mm_sent', __("Your message has sent!", mmg()->domain));
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
                $this->set_flash('mm_sent', __("Your message has sent!", mmg()->domain));
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
        $conversation = MM_Conversation_Model::find($conv_id);
        //we will add new message to this conversation
        $conversation->save();
        //update users from this conversation, now save the message
        $m = new MM_Message_Model();
        $m->import($model->export());
        $m->send_to = $user_id;
        $m->conversation_id = $conversation->id;
        $m->status = MM_Message_Model::UNREAD;
        $mess = MM_Message_Model::find($message_id);
        $m->subject = __("Re:", mmg()->domain) . ' ' . $mess->subject;

        $m->save();


        //update index
        $conversation->update_index($m->id);
        do_action('mm_message_sent', $m);
    }

    function _send_message($user_id, $model)
    {
        //create new conservation
        $conservation = new MM_Conversation_Model();
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
        $this->render_partial('shortcode/_inbox_message', array(
            'messages' => $messages
        ));
    }

    /**
     * @param array $args
     * @return string
     * Getting from Worpdress, we can have custom design
     */
    function wp_login_form($args = array())
    {
        $defaults = array(
            'echo' => true,
            'redirect' => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Default redirect is back to the current page
            'form_id' => 'loginform',
            'label_username' => __('Username'),
            'label_password' => __('Password'),
            'label_remember' => __('Remember Me'),
            'label_log_in' => __('Sign In'),
            'id_username' => 'user_login',
            'id_password' => 'user_pass',
            'id_remember' => 'rememberme',
            'id_submit' => 'wp-submit',
            'remember' => true,
            'value_username' => '',
            'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
        );

        /**
         * Filter the default login form output arguments.
         *
         * @since 3.0.0
         *
         * @see wp_login_form()
         *
         * @param array $defaults An array of default login form arguments.
         */
        $args = wp_parse_args($args, apply_filters('login_form_defaults', $defaults));

        /**
         * Filter content to display at the top of the login form.
         *
         * The filter evaluates just following the opening form tag element.
         *
         * @since 3.0.0
         *
         * @param string $content Content to display. Default empty.
         * @param array $args Array of login form arguments.
         */
        $login_form_top = apply_filters('login_form_top', '', $args);

        /**
         * Filter content to display in the middle of the login form.
         *
         * The filter evaluates just following the location where the 'login-password'
         * field is displayed.
         *
         * @since 3.0.0
         *
         * @param string $content Content to display. Default empty.
         * @param array $args Array of login form arguments.
         */
        $login_form_middle = apply_filters('login_form_middle', '', $args);

        /**
         * Filter content to display at the bottom of the login form.
         *
         * The filter evaluates just preceding the closing form tag element.
         *
         * @since 3.0.0
         *
         * @param string $content Content to display. Default empty.
         * @param array $args Array of login form arguments.
         */
        $login_form_bottom = apply_filters('login_form_bottom', '', $args);

        $form = '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url(site_url('wp-login.php', 'login_post')) . '" method="post">
			' . $login_form_top . '
			 <div class="form-group">
				<label for="' . esc_attr($args['id_username']) . '">' . esc_html($args['label_username']) . '</label>
				<input type="text" name="log" id="' . esc_attr($args['id_username']) . '" class="form-control" value="' . esc_attr($args['value_username']) . '" size="20" />
			</div>
			<div class="form-group">
				<label for="' . esc_attr($args['id_password']) . '">' . esc_html($args['label_password']) . '</label>
				<input type="password" name="pwd" id="' . esc_attr($args['id_password']) . '" class="form-control" value="" size="20" />
			</div>
			' . $login_form_middle . '
			' . ($args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr($args['id_remember']) . '" value="forever"' . ($args['value_remember'] ? ' checked="checked"' : '') . ' /> ' . esc_html($args['label_remember']) . '</label>
			<a class="pull-right" href="' . wp_lostpassword_url() . '">' . __("Forgot password?", mmg()->domain) . '</a></p>' : '') . '
			<p class="login-submit">
				<button type="submit" name="wp-submit" id="' . esc_attr($args['id_submit']) . '" class="btn btn-primary">' . esc_attr($args['label_log_in']) . '</button>
				<input type="hidden" name="redirect_to" value="' . esc_url($args['redirect']) . '" />
			</p>
			' . $login_form_bottom . '
		</form>';

        if ($args['echo'])
            echo $form;
        else
            return $form;
    }
}