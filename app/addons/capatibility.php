<?php

/**
 * Author: WPMU DEV
 * Name: Capability
 * Description: Limit roles each capability can send too
 */
class MM_User_Capability extends IG_Request
{
    public function __construct()
    {
        add_action('mm_setting_menu', array(&$this, 'setting_menu'));
        add_action('mm_setting_cap', array(&$this, 'setting_content'));
        add_action('wp_loaded', array(&$this, 'process_request'));

        add_filter('mm_suggest_users_args', array(&$this, 'filter_user_return'));
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
            $data = fRequest::get('mm_role');
            update_option('mm_user_cap', $data);
            $this->set_flash('mm_user_cap', __("Settings saved!", mmg()->domain));
            $this->redirect(fURL::getWithQueryString());
        }
    }

    function setting_content()
    {
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
                            <div class="row no-margin">
                                <div class="col-md-3" style="padding: 0">
                                    <ul class="nav nav-tabs tabs-left" role="tablist">
                                        <?php
                                        foreach ($roles as $key => $role):
                                            ?>
                                            <li class="<?php echo array_search($key, $index) == 0 ? 'active' : null ?>">
                                                <a
                                                    href="#tab_<?php echo $key ?>" role="tab"
                                                    data-toggle="tab">
                                                    <?php echo $role['name'] ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <form method="post">
                                    <div class="col-md-9" style="padding: 0">
                                        <div id="myTabContent" class="tab-content" style="min-height: 200px">
                                            <?php foreach ($roles as $key => $role): ?>
                                                <div
                                                    class="tab-pane <?php echo array_search($key, $index) == 0 ? 'active' : 'fade' ?>"
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
                                        <br/>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <button name="mm_user_cap" type="submit"
                                                        class="btn btn-primary"><?php _e("Save Changes", mmg()->domain) ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    <?php
    }

    function setting_menu()
    {
        ?>
        <li class="<?php echo fRequest::get('tab') == 'cap' ? 'active' : null ?>">
            <a href="<?php echo add_query_arg('tab', 'cap') ?>">
                <i class="fa fa-binoculars"></i> <?php _e("Capability Settings", mmg()->domain) ?></a>
        </li>
    <?php
    }
}

new MM_User_Capability();