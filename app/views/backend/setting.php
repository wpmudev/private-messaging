<div class="wrap">
    <div class="mmessage-container">
        <h2><?php _e('Settings', mmg()->domain) ?></h2>

        <div class="row">
            <div class="col-md-12">
                <?php if ($this->has_flash('setting_save') == 1): ?>
                    <div class="alert alert-success">
                        <?php echo $this->get_flash('setting_save') ?>
                    </div>
                <?php endif; ?>
                <ul style="margin-top: 0;padding-top: 0;margin-right: -1px;z-index:9;padding-right: 0"
                    class="nav nav-tabs tabs-left col-md-3 hidden-sm hidden-xs mm_setting_menu">
                    <li class="<?php echo fRequest::get('tab', 'string', 'general') == 'general' ? 'active' : null ?>">
                        <a href="<?php echo add_query_arg('tab', 'general') ?>">
                            <i class="glyphicon glyphicon-wrench"></i> <?php _e("General Settings", mmg()->domain) ?></a>
                    </li>
                    <li class="<?php echo fRequest::get('tab') == 'email' ? 'active' : null ?>">
                        <a href="<?php echo add_query_arg('tab', 'email') ?>">
                            <i class="glyphicon glyphicon-envelope"></i> <?php _e("Email Settings", mmg()->domain) ?></a>
                    </li>
                    <li class="<?php echo fRequest::get('tab') == 'shortcode' ? 'active' : null ?>">
                        <a href="<?php echo add_query_arg('tab', 'shortcode') ?>">
                            <i class="glyphicon glyphicon-cog"></i> <?php _e("Shortcode", mmg()->domain) ?></a>
                    </li>
                    <?php do_action('mm_setting_menu', $model) ?>
                </ul>
                <div class="tab-content col-md-9">
                    <?php do_action('mm_setting_' . fRequest::get('tab', 'string', 'general'), $model); ?>
                </div>
            </div>
        </div>
    </div>
</div>