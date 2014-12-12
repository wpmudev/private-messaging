<div class="page-header">
    <h3><?php _e('Shortcodes') ?></h3>
</div>
<div class="row">
    <div class="col-md-6 col-xs-6 col-sm-6 text-center">
        <p><strong><?php _e("Inbox Page", mmg()->domain) ?></strong></p>

        <div class="clearfix"></div>

        <div class="text-left">
            <p><code>[message_inbox]</code></p>
            <ul>
                <li>
                    <?php _e("This shortcode will display the private message interface.There are no parameters for this shortcode.", mmg()->domain) ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-6 col-xs-6 col-sm-6 text-center">
        <p><strong><?php _e("PM User", mmg()->domain) ?></strong></p>

        <div class="clearfix"></div>

        <div class="text-left">
            <p><code>[pm_user]</code></p>
            <ul>
                <li>
                    <mark><?php _e("user_id", mmg()->domain) ?></mark>
                    : <?php _e("The id of the user who is the message recipient.", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("user_name", mmg()->domain) ?></mark>
                    : <?php _e("The user name of the message recipient. User ID will be given higher priority than user_name.", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("in_the_loop", mmg()->domain) ?></mark>
                    : <?php _e("Use 1 for true, 0 for false. This shortcode is used for case when there is no user ID or user_name. This shortcode will pull the author user_ID if the shortcode is inside the loop.", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("text", mmg()->domain) ?></mark>
                    : <?php _e("The button text to display, default is \"Message me.\"", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("class", mmg()->domain) ?></mark>
                    : <?php _e("If you want to style the message button, use this shortcodes parameter to define the class of the message button. ", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("subject", mmg()->domain) ?></mark>
                    : <?php _e("This will define the subject of the message sent, if one is not added by the user. ", mmg()->domain) ?>
                </li>
                <li class="text-info">
                    <?php _e("Please note that <strong>user_id</strong> or <strong>user_name</strong> or <strong>in_the_loop</strong> must be defined.") ?>
                </li>
            </ul>

        </div>
    </div>
    <div class="clearfix"></div>
</div>