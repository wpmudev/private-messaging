<?php

/**
 * Author: WPMU DEV
 * Name: Group Conversation
 * Description: Enable include more people to a conversation
 */
class MM_Group_Conversation
{
    public function __construct()
    {
        add_action('mm_before_reply_form', array(&$this, 'include_textbox'));
        add_action('wp_ajax_mm_suggest_include_users', array(&$this, 'mm_suggest_include_users'));
        add_action('mm_before_subject_field', array(&$this, 'append_group_checkbox'));
    }

    function append_group_checkbox()
    {
        ?>
        <div class="form-group">
            <div class="col-md-10 col-sm-12 col-xs-12 col-md-offset-2">
                <div style="margin-top: 0" class="checkbox">
                    <label>
                        <input name="is_group" value="1" type="checkbox">
                        <?php _e("This is group conversation", mmg()->domain) ?>
                    </label>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    <?php
    }

    function mm_suggest_include_users()
    {
        if (!wp_verify_nonce(mmg()->get('_wpnonce'), 'mm_suggest_include_users')) {
            return;
        }
        $model = MM_Conversation_Model::model()->find(mmg()->post('parent_id'));
        if (!is_object($model)) {
            return;
        }
        $excludes = explode(',', $model->user_index);
        $query_string = mmg()->post('query');
        if (!empty($query_string)) {
            $query = new WP_User_Query(array(
                'search' => '*' . mmg()->post('query') . '*',
                'search_columns' => array('user_login'),
                'exclude' => $excludes,
                'number' => 10,
                'orderby' => 'user_login',
                'order' => 'ASC'
            ));
            $name_query = new WP_User_Query(array(
                'exclude' => $excludes,
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
            ));
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
            wp_send_json($data);
        }

        die;
    }

    function include_textbox($model)
    {
        ?>
        <div class="form-group">
            <label class="col-md-12 hidden-xs hidden-sm">
                <?php _e("Include more:", mmg()->domain) ?>
            </label>

            <div class="col-md-12 col-xs-12 col-sm-12">
                <input type="text" name="user_include" id="user_include" class="form-control">
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                window.mm_reply_select = $('#user_include').selectize({
                    valueField: 'id',
                    labelField: 'name',
                    searchField: 'name',
                    options: [],
                    create: false,
                    load: function (query, callback) {
                        if (!query.length) return callback();

                        $.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php?action=mm_suggest_include_users&_wpnonce='.wp_create_nonce('mm_suggest_include_users')) ?>',
                            data: {
                                'query': query,
                                'parent_id': '<?php echo $model->conversation_id ?>'
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

new MM_Group_Conversation();