<?php

/**
 * Author: WPMU DEV
 * Name: Block List
 * Description: Allows users to block messages from another user.
 */
if (!class_exists('MM_Block_List')) {
    class MM_Block_List extends IG_Request
    {
        public function __construct()
        {
            add_action('mm_after_user_setting_form', array(&$this, 'append_form'));
            add_action('mm_user_setting_saved', array(&$this, 'update_database'));
            add_filter('mm_suggest_users_args', array(&$this, 'filter_user_return'));
            add_filter('mm_send_to_this_users', array(&$this, 'filter_user_send'));

            add_action('wp_ajax_mm_all_users', array(&$this, 'all_users'));
        }

        function all_users()
        {
            if (!wp_verify_nonce(mmg()->get('_wpnonce'), 'mm_all_users')) {
                exit;
            }

            $query = new WP_User_Query(array(
                'search' => '*' . mmg()->post('query') . '*',
                'search_columns' => array('user_login'),
                'exclude' => array(get_current_user_id()),
                'number' => 10,
                'orderby' => 'user_login',
                'order' => 'ASC'
            ));

            $data = array();
            foreach ($query->get_results() as $user) {
                $obj = new stdClass();
                $obj->id = $user->ID;
                $obj->name = $user->user_login;
                $data[] = $obj;
            }

            wp_send_json($data);

            exit;
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
            update_user_meta(get_current_user_id(), 'mm_block_list', mmg()->post('mm_user_block'));
        }

        function append_form()
        {
            wp_enqueue_style('selectivejs');
            wp_enqueue_script('selectivejs');
            $block_list = get_user_meta(get_current_user_id(), 'mm_block_list', true);

            if (!$block_list) {
                $block_list = '';
            }
            ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <p class="help-block"><?php _e("Block users list, separate by commas", mmg()->domain) ?></p>
                    <input id="mm-block-list-input" name="mm_user_block" type="text" class="form-control"
                           value="<?php echo $block_list ?>"/>
                </div>
                <div class="clearfix"></div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#mm-block-list-input').selectize({
                        plugins: ['remove_button'],
                        delimiter: ',',
                        persist: false,
                        create: false,
                        valueField: 'name',
                        labelField: 'name',
                        searchField: 'name',
                        load: function (query, callback) {
                            if (!query.length) return callback();
                            $.ajax({
                                type: 'POST',
                                url: '<?php echo admin_url('admin-ajax.php?action=mm_all_users&_wpnonce='.wp_create_nonce('mm_all_users')) ?>',
                                data: {
                                    'query': query
                                },
                                beforeSend: function () {
                                    $('.selectize-input').append('<i style="position: absolute;right: 10px;" class="fa fa-circle-o-notch fa-spin"></i>');
                                },
                                success: function (data) {
                                    $('.selectize-input').find('i').remove();
                                    callback(data);
                                }
                            });
                        }
                    });
                })
            </script>
        <?php
        }
    }
}

new MM_Block_List();