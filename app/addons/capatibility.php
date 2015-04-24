<?php

/**
 * Author: WPMU DEV
 * Name: Capability
 * Description: Limit sending capabilities to specific WordPress roles.
 */
if (!class_exists('MM_User_Capability')) {
    class MM_User_Capability extends IG_Request
    {
        public function __construct()
        {
            add_action('mm_setting_menu', array(&$this, 'setting_menu'));
            add_action('mm_setting_cap', array(&$this, 'setting_content'));
            add_action('wp_loaded', array(&$this, 'process_request'));
            if (is_user_logged_in()) {
                add_filter('mm_suggest_users_args', array(&$this, 'filter_user_return'));
                add_filter('mm_send_to_this_users', array(&$this, 'filter_user_reply'));
            }
        }

        function filter_user_reply($ids)
        {
            $data = get_option('mm_user_cap');
            $user = new WP_User(get_current_user_id());
            $roles = array();
            //not init, use default
            if (!$data) {
                return $ids;
            }

            foreach ($user->roles as $role) {
                if (isset($data[$role])) {
                    $roles = array_merge($roles, $data[$role]);
                    //we will need to add this roles
                    $roles[] = $role;
                }
            }
            $roles = array_unique($roles);
            foreach ($ids as $id) {
                if ($id != get_current_user_id()) {
                    $send_to = new WP_User($id);
                    //check if this user role in the list can send
                    if (count(array_intersect($send_to->roles, $roles)) == 0) {
                        unset($ids[array_search($id, $ids)]);
                    }
                }
            }
            return $ids;
        }

        function filter_user_return($args)
        {
            $data = get_option('mm_user_cap');
            //not init, use default
            if (!$data) {
                return $args;
            }

            $params = array(
                'relation' => 'OR'

            );
            global $wpdb;
            //getting current user role
            foreach ($data as $key => $val) {
                if ($this->check_user_role($key)) {
                    //this user role has data,
                    foreach ($val as $r) {
                        $params[] = array(
                            'key' => $wpdb->get_blog_prefix() . 'capabilities',
                            'value' => $r,
                            'compare' => 'like'
                        );
                    };
                    //include self role
                    $params[] = array(
                        'key' => $wpdb->get_blog_prefix() . 'capabilities',
                        'value' => $key,
                        'compare' => 'like'
                    );
                }
            }
            $args['meta_query'] = $params;

            return $args;

        }

        function check_user_role($role, $user_id = null)
        {

            if (is_numeric($user_id))
                $user = get_userdata($user_id);
            else
                $user = wp_get_current_user();

            if (empty($user))
                return false;

            return in_array($role, (array)$user->roles);
        }

        function process_request()
        {
            if (isset($_POST['mm_user_cap'])) {
                $data = mmg()->post('mm_role');
                update_option('mm_user_cap', $data);
                $this->set_flash('mm_user_cap', __("Settings saved!", mmg()->domain));
                $this->redirect($_SERVER['REQUEST_URI']);
            }
        }

        function setting_content()
        {
            wp_enqueue_script('jquery-ui-tabs');
            //wp_enqueue_style('mm_style');
            $roles = get_editable_roles();
            $index = array_keys($roles);
            $data = get_option('mm_user_cap');
            if (!$data) {
                $data = array();
            }
            ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-pane active">
                        <div class="page-header">
                            <h3><?php _e("Capability Settings", mmg()->domain) ?></h3>
                        </div>
                        <?php if ($this->has_flash('mm_user_cap')): ?>
                            <div class="alert alert-success">
                                <?php echo $this->get_flash('mm_user_cap') ?>
                            </div>
                        <?php endif; ?>
                        <!-- Nav tabs -->
                        <div class="row">
                            <div class="col-md-12">
                                <form method="post">
                                    <div class="row" id="tabs">
                                        <ul id="role-list" class="nav nav-tabs tabs-left col-md-3" role="tablist">
                                            <?php
                                            foreach ($roles as $key => $role):
                                                ?>
                                                <li style="float: none;clear: both">
                                                    <a href="#tab_<?php echo $key ?>">
                                                        <?php echo $role['name'] ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="col-md-9" style="padding: 0">
                                            <div id="myTabContent" class="tab-content" style="min-height: 200px">
                                                <?php foreach ($roles as $key => $role): ?>
                                                    <div
                                                        class=" <?php echo array_search($key, $index) == 0 ? 'active' : '' ?>"
                                                        id="tab_<?php echo $key ?>">
                                                        <?php foreach ($roles as $k => $r): ?>
                                                            <?php if ($k != $key): ?>
                                                                <?php if (!isset($data[$key])): ?>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="mm_role[<?php echo $key ?>][]"
                                                                                   type="checkbox"
                                                                                   checked="checked"
                                                                                   value="<?php echo $k ?>">
                                                                            <?php _e(sprintf("User from this role can send to <strong>%s</strong>", $r['name']), mmg()->domain) ?>
                                                                        </label>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="mm_role[<?php echo $key ?>][]"
                                                                                   type="checkbox"
                                                                                <?php echo checked($data[$key][array_search($k, $data[$key])], $k) ?>
                                                                                   value="<?php echo $k ?>">
                                                                            <?php _e(sprintf("User from this role can send to <strong>%s</strong>", $r['name']), mmg()->domain) ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <div class="clearfix"></div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <br/>

                                        <div class="row">
                                            <div class="col-md-9 col-md-offset-3">
                                                <button name="mm_user_cap" type="submit"
                                                        class="btn btn-primary"><?php _e("Save Changes", mmg()->domain) ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#myTabContent').height($('#role-list').height());
                    $("#tabs").tabs({
                        active: 0,
                        activate: function (event, ui) {
                            ui.newTab.addClass('active');
                            ui.oldTab.removeClass('active');
                        },
                        create: function (event, ui) {
                            ui.tab.addClass('active');
                        }
                    })
                })
            </script>
        <?php
        }

        function setting_menu()
        {
            ?>
            <li class="<?php echo mmg()->get('tab') == 'cap' ? 'active' : null ?>">
                <a href="<?php echo esc_url(add_query_arg('tab', 'cap')) ?>">
                    <i class="fa fa-binoculars"></i> <?php _e("Capability Settings", mmg()->domain) ?></a>
            </li>
        <?php
        }
    }
}
new MM_User_Capability();