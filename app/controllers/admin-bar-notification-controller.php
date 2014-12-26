<?php

/**
 * @author:Hoang Ngo
 */
class Admin_Bar_Notification_Controller extends IG_Request
{
    public function __construct()
    {
        if (is_user_logged_in()) {
            add_action('admin_bar_menu', array(&$this, 'notification_buttons'), 80);
            add_action('wp_footer', array(&$this, 'compose_form_footer'));
            add_action('admin_footer', array(&$this, 'compose_form_footer'));
        }
    }

    function compose_form_footer()
    {
        $this->render('bar/_compose_form');
    }

    function notification_buttons($wp_admin_bar)
    {
        //create new menu
        $unread = MM_Conversation_Model::count_unread();
        $args = array(
            'id' => 'mm-button',
            'title' => __('<div class="ig-container mm-admin-bar"><i class="fa fa-envelope"></i>&nbsp;<span>' . $unread . '</span>
</div>', mmg()->domain),
            'href' => '#',
        );
        $wp_admin_bar->add_menu($args);

        //create group
        $args = array(
            'id'     => 'mm-buttons-group',
            'parent' => 'mm-button',
        );
        $wp_admin_bar->add_group( $args );
        //add node send new message
        $args = array(
            'id' => 'mm-compose-button',
            'title' => __("Send New Message", mmg()->domain),
            'href' => '#compose-form-container-admin-bar',
            'parent' => 'mm-buttons-group',
            'meta' => array(
                'class' => 'mm-compose-admin-bar',
            )
        );
        $wp_admin_bar->add_node($args);
        //add node inbox page
        $args = array(
            'id' => 'mm-inbox-button',
            'title' => __("View Inbox", mmg()->domain),
            'href' => get_permalink(mmg()->setting()->inbox_page),
            'parent' => 'mm-buttons-group',
            'meta' => array(
                'class' => 'mm-view-inbox-admin-bar',
            )
        );
        $wp_admin_bar->add_node($args);
    }
}