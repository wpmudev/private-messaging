<?php

/**
 * Author: hoangngo
 */
class Message_Me_Shortcode_Controller extends IG_Request
{
    public function __construct()
    {
        add_shortcode('pm_user', array(&$this, 'message_me'));
        if (is_user_logged_in()) {
            add_action('wp_enqueue_scripts', array(&$this, 'script'));
            add_action('admin_enqueue_scripts', array(&$this, 'script'));
            add_action('admin_bar_menu', array(&$this, 'notification_buttons'), 80);
            add_action('wp_footer', array(&$this, 'compose_form_footer'));
            add_action('admin_footer', array(&$this, 'compose_form_footer'));
        }
    }

    function script()
    {
        wp_enqueue_style('selectivejs');
        wp_enqueue_script('selectivejs');
    }

    function compose_form_footer()
    {
        $this->render('bar/_compose_form');
    }

    function notification_buttons($wp_admin_bar)
    {

        $args = array(
            'id' => 'custom-button',
            'title' => __("Send New Message", mmg()->domain),
            'href' => '#',
            'meta' => array(
                'class' => 'mm-compose-admin-bar',
            )
        );
        $wp_admin_bar->add_node($args);

    }

    function message_me($atts)
    {
        $a = shortcode_atts(array(
            'user_id' => '',
            'name' => '',
            'text' => __('Message me', mmg()->domain),
            'class' => 'btn btn-sm btn-primary',
            'subject' => __('You have new message!', mmg()->domain)
        ), $atts);
        if (!empty($a['user_id'])) {
            $user = get_user_by('id', $a['user_id']);
        } elseif (!empty($a['name'])) {
            $user = get_user_by('login', $a['name']);
        } elseif (in_the_loop()) {
            //this is in the loop, we can get author
            $username = get_the_author();
            if (!empty($username)) {
                $user = get_user_by('login', $username);
            }
        }
        if (!isset($user) || !is_object($user))
            return;

        wp_enqueue_style('mm_style');

        return $this->render('shortcode/message_me', array(
            'a' => $a,
            'user' => $user
        ), false);
    }
}