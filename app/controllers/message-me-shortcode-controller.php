<?php

/**
 * Author: hoangngo
 */
class Message_Me_Shortcode_Controller extends IG_Request
{
    public function __construct()
    {
        add_shortcode('pm_user', array(&$this, 'pm_user'));
    }

    function pm_user($atts)
    {
        $a = shortcode_atts(array(
            'user_id' => '',
            'user_name' => '',
            'text' => __('Message me', mmg()->domain),
            'class' => 'btn btn-sm btn-primary',
            'subject' => __('You have new message!', mmg()->domain),
            'in_the_loop' => false
        ), $atts);

        if (!empty($a['user_id'])) {
            $user = get_user_by('id', $a['user_id']);
        } elseif (!empty($a['user_name'])) {
            $user = get_user_by('login', $a['user_name']);
        } elseif ($a['in_the_loop'] == true && in_the_loop()) {
            //this is in the loop, we can get author
            $username = get_the_author();
            if (!empty($username)) {
                $user = get_user_by('login', $username);
            }
        }

        if (!isset($user) || !is_object($user))
            return '';

        wp_enqueue_style('mm_style');

        return $this->render('shortcode/message_me', array(
            'a' => $a,
            'user' => $user
        ), false);
    }
}