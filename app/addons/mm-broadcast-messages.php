<?php

/**
 * Author: WPMU DEV
 * Name: Broadcast
 * Description: As the administrator send a message to all users at the same time.
 */
class MM_BroadCast_Messages
{
    public function __construct()
    {
        if (current_user_can('manage_options')) {
            add_action('mm_compose_form_after_send_to', array(&$this, 'broadcast_checkbox'));
            add_action('mm_compose_form_end', array(&$this, 'broadcast_script'));
            add_filter('mm_before_send_message', array(&$this, 'broadcast_ids'));
        }
    }

    function broadcast_ids(MM_Message_Model $model)
    {
        //check if broadcast, include all users
        if (mmg()->post('broadcast', null) != null) {
            $users = get_users(array(
                'fields' => array('ID'),
                'exclude' => array(get_current_user_id())
            ));
            $ids = wp_list_pluck($users, 'ID');
            $model->send_to = implode(',', $ids);
        }
        return $model;
    }

    function broadcast_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                var selectize = window.mm_compose_select[0].selectize;
                $('#mmg-broadcast').click(function () {
                    if ($(this).is(':checked')) {
                        selectize.disable();
                    } else {
                        selectize.enable();
                    }
                })
            })
        </script>
    <?php
    }

    function broadcast_checkbox()
    {
        ?>
        <label><input type="checkbox" name="broadcast" id="mmg-broadcast">
            <?php _e("Send this message to all users", mmg()->domain) ?></label>
    <?php
    }
}
new MM_BroadCast_Messages();