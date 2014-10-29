<?php

/**
 * Author: hoangngo
 */
class Message_Me_Shortcode_Controller extends IG_Request
{
    public function __construct()
    {
        add_shortcode('message_me', array(&$this, 'message_me'));
    }

    function message_me($atts)
    {
        $a = shortcode_atts(array(
            'user_id' => '',
            'text' => __('Message me', mmg()->domain),
            'class' => 'btn btn-sm btn-primary',
            'subject' => __('You have new message!', mmg()->domain)
        ), $atts);
        if (empty($a['user_id']))
            return;

        $user = get_user_by('id', $a['user_id']);
        if (!is_object($user))
            return;

        wp_enqueue_style('mm_style');
        return $this->render('shortcode/message_me', array(
            'a' => $a,
            'user' => $user
        ), false);
    }
}