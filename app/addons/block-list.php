<?php

/**
 * Author: WPMU DEV
 * Name: Block List
 * Description: Add ability for users can block message from another
 */
class MM_Block_List extends IG_Request
{
    public function __construct()
    {
        add_action('mm_after_user_setting_form', array(&$this, 'append_form'));
        add_action('mm_user_setting_saved', array(&$this, 'update_database'));
        add_filter('mm_suggest_users_args', array(&$this, 'filter_user_return'));
        add_filter('mm_send_to_this_users', array(&$this, 'filter_user_send'));
    }

    function filter_user_send($ids)
    {
        //the different is the ids can send
        return array_diff($ids, $this->_filter_user());
        //return $ids;
    }

    function filter_user_return($args)
    {
        //we need to find all the guys block this current user and hide the result
        $ids = $this->_filter_user();
        if (!empty($ids)) {
            $args['exclude'] = array_merge($args, $ids);
        }
        return $args;
    }

    function _filter_user()
    {
        global $wpdb;;
        $current_user = wp_get_current_user();
        $sql = "SELECT * FROM " . $wpdb->prefix . 'usermeta WHERE meta_key=%s AND meta_value LIKE %s';
        $results = $wpdb->get_results($wpdb->prepare($sql, 'mm_block_list', '%' . $current_user->user_login . '%'), ARRAY_A);
        $ids = array();
        foreach ($results as $row) {
            $list = $row['meta_value'];
            $list = array_filter(array_unique(explode(',', $list)));
            if (in_array($current_user->user_login, $list)) {
                $ids[] = $row['user_id'];
            }
        }
        return $ids;
    }

    function update_database()
    {
        update_user_meta(get_current_user_id(), 'mm_block_list', fRequest::get('mm_user_block'));
    }

    function append_form()
    {
        $block_list = get_user_meta(get_current_user_id(), 'mm_block_list', true);
        if (!$block_list) {
            $block_list = '';
        }
        ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <p class="help-block"><?php _e("Block users list, separate by commas", mmg()->domain) ?></p>
                <textarea style="height: 70px" class="form-control"
                          name="mm_user_block"><?php echo $block_list ?></textarea>
            </div>
            <div class="clearfix"></div>
        </div>
    <?php
    }
}

new MM_Block_List();